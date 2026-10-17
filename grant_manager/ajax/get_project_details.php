<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

requireLogin();

// Grant manager and admin can access project details
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['grant_manager', 'admin'])) {
    jsonResponse(['success' => false, 'message' => 'Access denied.'], 403);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    jsonResponse(['success' => false, 'message' => 'Invalid project ID.'], 400);
}

$db = getDb();

// Fetch award, proposal, grant, researcher, and awarder details
$stmt = $db->prepare("
    SELECT 
        ga.*,
        u.id AS researcher_id,
        u.first_name,
        u.last_name,
        u.email AS researcher_email,
        u.phone AS researcher_phone,
        u.department,
        u.institution,
        u.qualification,
        u.expertise_area,
        u.orcid_id,
        u.google_scholar,
        u.biography,
        u.research_interests,
        u.profile_picture,
        g.id AS grant_id,
        g.title AS grant_title,
        g.description AS grant_description,
        g.objectives AS grant_objectives,
        g.eligibility_criteria AS grant_eligibility,
        g.funding_amount AS grant_funding_amount,
        gc.name AS category_name,
        gc.color AS category_color,
        p.id AS proposal_id,
        p.title AS proposal_title,
        p.abstract,
        p.problem_statement,
        p.methodology,
        p.expected_outcomes,
        p.budget_breakdown,
        p.budget_amount,
        p.status AS proposal_status,
        p.review_score,
        p.review_comment,
        p.submitted_at AS proposal_submitted_at,
        p.reviewed_at AS proposal_reviewed_at,
        awarder.first_name AS awarder_first_name,
        awarder.last_name AS awarder_last_name
    FROM grant_awards ga
    JOIN users u ON ga.researcher_id = u.id
    JOIN grants g ON ga.grant_id = g.id
    LEFT JOIN grant_categories gc ON g.category_id = gc.id
    JOIN proposals p ON ga.proposal_id = p.id
    LEFT JOIN users awarder ON ga.awarded_by = awarder.id
    WHERE ga.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch();

if (!$project) {
    jsonResponse(['success' => false, 'message' => 'Project record not found.'], 404);
}

// Fetch proposal documents
$stmtDocs = $db->prepare("SELECT * FROM proposal_documents WHERE proposal_id = :proposal_id ORDER BY uploaded_at ASC");
$stmtDocs->execute([':proposal_id' => $project['proposal_id']]);
$docsRaw = $stmtDocs->fetchAll();

$docTypeLabels = getDocumentTypes();
$documents = [];
foreach ($docsRaw as $d) {
    $documents[] = [
        'id' => (int)$d['id'],
        'document_type' => $d['document_type'],
        'document_type_label' => $docTypeLabels[$d['document_type']] ?? ucfirst($d['document_type']),
        'file_name' => $d['file_name'],
        'file_size' => $d['file_size'],
        'file_size_formatted' => $d['file_size'] ? (round($d['file_size'] / 1024, 1) . ' KB') : 'N/A',
        'file_icon' => getFileIcon($d['file_name']),
        'download_url' => SITE_URL . 'uploads/proposals/' . urlencode($d['file_name']),
        'uploaded_at' => $d['uploaded_at'] ? formatDate($d['uploaded_at']) : null,
    ];
}

// Fetch project milestones
$stmtM = $db->prepare("SELECT * FROM project_milestones WHERE award_id = :award_id ORDER BY due_date ASC, id ASC");
$stmtM->execute([':award_id' => $id]);
$milestonesRaw = $stmtM->fetchAll();

$milestones = [];
$completedMilestones = 0;
foreach ($milestonesRaw as $m) {
    if ($m['status'] === 'completed') {
        $completedMilestones++;
    }
    $milestones[] = [
        'id' => (int)$m['id'],
        'title' => $m['title'],
        'description' => $m['description'],
        'due_date' => $m['due_date'] ? formatDate($m['due_date']) : '-',
        'completion_date' => $m['completion_date'] ? formatDate($m['completion_date']) : '-',
        'status' => $m['status'],
        'status_badge' => getStatusBadge($m['status']),
        'deliverables' => $m['deliverables'],
    ];
}
$totalMilestones = count($milestones);
$milestoneProgress = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;

// Fetch progress reports
$stmtR = $db->prepare("SELECT * FROM progress_reports WHERE award_id = :award_id ORDER BY submitted_at DESC, id DESC");
$stmtR->execute([':award_id' => $id]);
$reportsRaw = $stmtR->fetchAll();

$reportTypeLabels = getReportTypes();
$reports = [];
foreach ($reportsRaw as $r) {
    $reports[] = [
        'id' => (int)$r['id'],
        'title' => $r['title'],
        'report_type' => $r['report_type'],
        'report_type_label' => $reportTypeLabels[$r['report_type']] ?? ucfirst($r['report_type']),
        'report_period_start' => $r['report_period_start'] ? formatDate($r['report_period_start']) : '-',
        'report_period_end' => $r['report_period_end'] ? formatDate($r['report_period_end']) : '-',
        'summary' => $r['summary'],
        'achievements' => $r['achievements'],
        'challenges' => $r['challenges'],
        'next_steps' => $r['next_steps'],
        'financial_update' => $r['financial_update'],
        'file_path' => $r['file_path'],
        'download_url' => !empty($r['file_path']) ? SITE_URL . 'uploads/reports/' . urlencode($r['file_path']) : null,
        'status' => $r['status'],
        'status_badge' => getStatusBadge($r['status']),
        'review_comment' => $r['review_comment'],
        'submitted_at' => $r['submitted_at'] ? formatDate($r['submitted_at']) : '-',
    ];
}

// Fetch final report if any
$stmtFR = $db->prepare("SELECT * FROM final_reports WHERE award_id = :award_id LIMIT 1");
$stmtFR->execute([':award_id' => $id]);
$fr = $stmtFR->fetch();
$finalReport = null;
if ($fr) {
    $finalReport = [
        'id' => (int)$fr['id'],
        'title' => $fr['title'],
        'executive_summary' => $fr['executive_summary'],
        'introduction' => $fr['introduction'],
        'methodology' => $fr['methodology'],
        'results' => $fr['results'],
        'discussion' => $fr['discussion'],
        'conclusion' => $fr['conclusion'],
        'recommendations' => $fr['recommendations'],
        'financial_report' => $fr['financial_report'],
        'file_path' => $fr['file_path'],
        'download_url' => !empty($fr['file_path']) ? SITE_URL . 'uploads/reports/' . urlencode($fr['file_path']) : null,
        'status' => $fr['status'],
        'status_badge' => getStatusBadge($fr['status']),
        'submitted_at' => $fr['submitted_at'] ? formatDate($fr['submitted_at']) : '-',
    ];
}

// Researcher avatar
$researcherAvatar = getAvatarUrl($project['first_name'] . ' ' . $project['last_name']);
if (!empty($project['profile_picture']) && file_exists(PROFILE_PATH . $project['profile_picture'])) {
    $researcherAvatar = SITE_URL . 'uploads/profiles/' . $project['profile_picture'];
}

$response = [
    'success' => true,
    'award' => [
        'id' => (int)$project['id'],
        'awarded_amount' => $project['awarded_amount'],
        'awarded_amount_formatted' => formatAmount($project['awarded_amount']),
        'award_date' => $project['award_date'],
        'award_date_formatted' => $project['award_date'] ? formatDate($project['award_date']) : '-',
        'start_date' => $project['start_date'],
        'start_date_formatted' => $project['start_date'] ? formatDate($project['start_date']) : '-',
        'end_date' => $project['end_date'],
        'end_date_formatted' => $project['end_date'] ? formatDate($project['end_date']) : '-',
        'status' => $project['status'],
        'status_badge' => getStatusBadge($project['status']),
        'terms_conditions' => $project['terms_conditions'],
        'approval_letter' => $project['approval_letter'],
        'approval_letter_url' => !empty($project['approval_letter']) ? SITE_URL . 'uploads/' . urlencode($project['approval_letter']) : null,
        'awarded_by_name' => ($project['awarder_first_name'] || $project['awarder_last_name']) ? ($project['awarder_first_name'] . ' ' . $project['awarder_last_name']) : null,
        'created_at_formatted' => formatDateTime($project['created_at']),
    ],
    'proposal' => [
        'id' => (int)$project['proposal_id'],
        'title' => $project['proposal_title'],
        'abstract' => $project['abstract'],
        'problem_statement' => $project['problem_statement'],
        'methodology' => $project['methodology'],
        'expected_outcomes' => $project['expected_outcomes'],
        'budget_amount' => $project['budget_amount'],
        'budget_amount_formatted' => $project['budget_amount'] !== null ? formatAmount($project['budget_amount']) : '-',
        'budget_breakdown' => $project['budget_breakdown'],
        'status' => $project['proposal_status'],
        'status_badge' => getStatusBadge($project['proposal_status']),
        'review_score' => $project['review_score'] !== null ? number_format($project['review_score'], 1) : null,
        'review_comment' => $project['review_comment'],
        'submitted_at_formatted' => $project['proposal_submitted_at'] ? formatDateTime($project['proposal_submitted_at']) : '-',
        'reviewed_at_formatted' => $project['proposal_reviewed_at'] ? formatDateTime($project['proposal_reviewed_at']) : '-',
    ],
    'researcher' => [
        'id' => (int)$project['researcher_id'],
        'name' => $project['first_name'] . ' ' . $project['last_name'],
        'first_name' => $project['first_name'],
        'last_name' => $project['last_name'],
        'email' => $project['researcher_email'],
        'phone' => $project['researcher_phone'] ?: '-',
        'department' => $project['department'] ?: '-',
        'institution' => $project['institution'] ?: '-',
        'qualification' => $project['qualification'] ?: '-',
        'expertise_area' => $project['expertise_area'] ?: '-',
        'orcid_id' => $project['orcid_id'] ?: null,
        'google_scholar' => $project['google_scholar'] ?: null,
        'biography' => $project['biography'] ?: null,
        'research_interests' => $project['research_interests'] ?: null,
        'avatar' => $researcherAvatar,
    ],
    'grant' => [
        'id' => (int)$project['grant_id'],
        'title' => $project['grant_title'],
        'category_name' => $project['category_name'] ?: 'General',
        'category_color' => $project['category_color'] ?: '#0D6EFD',
        'funding_amount_formatted' => $project['grant_funding_amount'] !== null ? formatAmount($project['grant_funding_amount']) : '-',
        'description' => $project['grant_description'],
        'objectives' => $project['grant_objectives'],
        'eligibility' => $project['grant_eligibility'],
    ],
    'documents' => $documents,
    'milestones' => [
        'items' => $milestones,
        'total' => $totalMilestones,
        'completed' => $completedMilestones,
        'progress_percent' => $milestoneProgress,
    ],
    'reports' => [
        'items' => $reports,
        'total' => count($reports),
    ],
    'final_report' => $finalReport,
];

jsonResponse($response);
