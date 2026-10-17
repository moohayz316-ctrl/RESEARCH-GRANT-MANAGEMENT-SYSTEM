<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

header('Content-Type: application/json');

$db     = getDb();
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'add_disbursement':
        handleAddDisbursement($db);
        break;
    case 'get_award_disbursements':
        handleGetAwardDisbursements($db);
        break;
    case 'get_award_info':
        handleGetAwardInfo($db);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
}

// ============================================================
function handleAddDisbursement($db) {
    $awardId         = (int)($_POST['award_id'] ?? 0);
    $stage           = trim($_POST['stage'] ?? '');
    $disbursedAmount = (float)($_POST['disbursed_amount'] ?? 0);
    $paymentRef      = trim($_POST['payment_reference'] ?? '');
    $disbDate        = trim($_POST['disbursement_date'] ?? '');
    $notes           = trim($_POST['notes'] ?? '');

    if (!$awardId || !$stage || $disbursedAmount <= 0 || !$disbDate) {
        jsonResponse(['success' => false, 'message' => 'Required fields missing or invalid amount.'], 400);
    }

    // Fetch award details
    $stmt = $db->prepare("SELECT ga.*, g.id as grant_id, g.title as grant_title FROM grant_awards ga JOIN grants g ON ga.grant_id = g.id WHERE ga.id = :id");
    $stmt->execute([':id' => $awardId]);
    $award = $stmt->fetch();
    if (!$award) {
        jsonResponse(['success' => false, 'message' => 'Award not found.'], 404);
    }

    $approvedAmt    = (float)($award['approved_amount'] > 0 ? $award['approved_amount'] : $award['awarded_amount']);
    $totalDisbursed = (float)$award['total_disbursed'];
    $newTotal       = $totalDisbursed + $disbursedAmount;

    // Prevent over-disbursement (0.1% tolerance for float rounding)
    if ($approvedAmt > 0 && $newTotal > $approvedAmt * 1.001) {
        jsonResponse([
            'success' => false,
            'message' => 'Disbursement would exceed approved amount. Approved: ' . formatAmount($approvedAmt) . ', Already Disbursed: ' . formatAmount($totalDisbursed) . ', Remaining: ' . formatAmount(max(0, $approvedAmt - $totalDisbursed))
        ], 400);
    }

    $db->beginTransaction();
    try {
        // Insert disbursement record
        $ins = $db->prepare("INSERT INTO fund_disbursements (award_id, grant_id, researcher_id, disbursed_by, stage, disbursed_amount, payment_reference, disbursement_date, status, notes) VALUES (:award_id, :grant_id, :researcher_id, :disbursed_by, :stage, :amount, :ref, :date, 'paid', :notes)");
        $ins->execute([
            ':award_id'      => $awardId,
            ':grant_id'      => $award['grant_id'],
            ':researcher_id' => $award['researcher_id'],
            ':disbursed_by'  => $_SESSION['user_id'],
            ':stage'         => $stage,
            ':amount'        => $disbursedAmount,
            ':ref'           => $paymentRef ?: null,
            ':date'          => $disbDate,
            ':notes'         => $notes ?: null,
        ]);
        $disbId = $db->lastInsertId();

        // Update total_disbursed on grant_awards
        $db->prepare("UPDATE grant_awards SET total_disbursed = total_disbursed + :amount WHERE id = :id")->execute([
            ':amount' => $disbursedAmount,
            ':id'     => $awardId,
        ]);

        // Auto-complete award if fully disbursed
        if ($approvedAmt > 0 && $newTotal >= $approvedAmt * 0.999) {
            $db->prepare("UPDATE grant_awards SET status = 'completed' WHERE id = :id AND status = 'active'")->execute([':id' => $awardId]);
        }

        // Notify researcher
        createNotification(
            $award['researcher_id'],
            'fund_disbursed',
            'Fund Disbursement Recorded',
            'A fund disbursement of ' . formatAmount($disbursedAmount) . ' (' . $stage . ') has been recorded for your project. Reference: ' . ($paymentRef ?: 'N/A') . '. Note: Actual fund transfer is carried out by the institution through its authorized financial channels.',
            SITE_URL . 'researcher/pages/funding_history.php'
        );

        logAudit($_SESSION['user_id'], 'disburse_fund', 'fund_disbursements', $disbId, 'Disbursed ' . $disbursedAmount . ' for award #' . $awardId . ' (' . $stage . ')');
        $db->commit();

        jsonResponse([
            'success'   => true,
            'message'   => 'Disbursement of ' . formatAmount($disbursedAmount) . ' recorded successfully.',
            'new_total' => $newTotal,
            'remaining' => max(0, $approvedAmt - $newTotal),
            'approved'  => $approvedAmt,
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ============================================================
function handleGetAwardDisbursements($db) {
    $awardId = (int)($_GET['award_id'] ?? 0);
    if (!$awardId) {
        jsonResponse(['success' => false, 'message' => 'Award ID required.'], 400);
    }

    $stmt = $db->prepare("SELECT fd.*, u.first_name, u.last_name FROM fund_disbursements fd LEFT JOIN users u ON fd.disbursed_by = u.id WHERE fd.award_id = :id ORDER BY fd.disbursement_date ASC, fd.created_at ASC");
    $stmt->execute([':id' => $awardId]);
    $disbs = $stmt->fetchAll();

    foreach ($disbs as &$d) {
        $d['disbursed_by_name']            = $d['first_name'] ? trim($d['first_name'] . ' ' . $d['last_name']) : 'System';
        $d['disbursed_amount_formatted']   = formatAmount($d['disbursed_amount']);
        $d['disbursement_date_formatted']  = formatDate($d['disbursement_date']);
        $d['status_badge']                 = getStatusBadge($d['status']);
    }
    unset($d);

    jsonResponse(['success' => true, 'disbursements' => $disbs]);
}

// ============================================================
function handleGetAwardInfo($db) {
    $awardId = (int)($_GET['award_id'] ?? 0);
    if (!$awardId) {
        jsonResponse(['success' => false, 'message' => 'Award ID required.'], 400);
    }

    $stmt = $db->prepare("SELECT ga.*, g.title as grant_title, u.first_name, u.last_name, u.email, p.title as proposal_title FROM grant_awards ga JOIN grants g ON ga.grant_id = g.id JOIN users u ON ga.researcher_id = u.id JOIN proposals p ON ga.proposal_id = p.id WHERE ga.id = :id");
    $stmt->execute([':id' => $awardId]);
    $award = $stmt->fetch();
    if (!$award) {
        jsonResponse(['success' => false, 'message' => 'Award not found.'], 404);
    }

    $approvedAmt = (float)($award['approved_amount'] > 0 ? $award['approved_amount'] : $award['awarded_amount']);
    $disbursed   = (float)$award['total_disbursed'];
    $remaining   = max(0, $approvedAmt - $disbursed);
    $pct         = $approvedAmt > 0 ? round(($disbursed / $approvedAmt) * 100) : 0;

    $award['researcher_name']       = trim($award['first_name'] . ' ' . $award['last_name']);
    $award['approved_amount_val']   = $approvedAmt;
    $award['approved_amount_fmt']   = formatAmount($approvedAmt);
    $award['total_disbursed_fmt']   = formatAmount($disbursed);
    $award['remaining_fmt']         = formatAmount($remaining);
    $award['disbursed_pct']         = $pct;
    $award['awarded_amount_fmt']    = formatAmount($award['awarded_amount']);
    $award['status_badge']          = getStatusBadge($award['status']);

    jsonResponse(['success' => true, 'award' => $award]);
}
