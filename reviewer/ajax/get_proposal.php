<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('reviewer');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    jsonResponse(['error' => 'Invalid ID'], 400);
}

$db = getDb();

$stmtReview = $db->prepare("SELECT id, status FROM proposal_reviews WHERE proposal_id = :proposal_id AND reviewer_id = :user_id LIMIT 1");
$stmtReview->execute([':proposal_id' => $id, ':user_id' => $_SESSION['user_id']]);
$reviewAssignment = $stmtReview->fetch();
if (!$reviewAssignment) {
    jsonResponse(['error' => 'Access denied. You are not assigned to review this proposal.'], 403);
}

$stmt = $db->prepare("
    SELECT 
        p.*, 
        g.title AS grant_title, 
        g.funding_amount AS grant_funding,
        u.id AS researcher_id,
        u.first_name, 
        u.last_name, 
        u.email,
        u.phone,
        u.department, 
        u.institution,
        u.qualification,
        u.expertise_area,
        u.orcid_id,
        u.google_scholar,
        u.biography,
        u.research_interests,
        u.cv_file,
        u.profile_picture
    FROM proposals p 
    JOIN grants g ON p.grant_id = g.id 
    JOIN users u ON p.researcher_id = u.id 
    WHERE p.id = :id
");
$stmt->execute([':id' => $id]);
$proposal = $stmt->fetch();

if (!$proposal) {
    jsonResponse(['error' => 'Proposal not found'], 404);
}

$researcherId = (int)$proposal['researcher_id'];

// Researcher track record
$stmtStats = $db->prepare("
    SELECT 
        COUNT(*) as total_proposals,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_proposals
    FROM proposals 
    WHERE researcher_id = :rid AND status != 'draft'
");
$stmtStats->execute([':rid' => $researcherId]);
$resStats = $stmtStats->fetch() ?: ['total_proposals' => 0, 'approved_proposals' => 0];

$stmtAwards = $db->prepare("
    SELECT 
        COUNT(*) as total_awards,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_projects
    FROM grant_awards 
    WHERE researcher_id = :rid
");
$stmtAwards->execute([':rid' => $researcherId]);
$awardStats = $stmtAwards->fetch() ?: ['total_awards' => 0, 'completed_projects' => 0];

// Fetch attached documents
$stmtDocs = $db->prepare("SELECT * FROM proposal_documents WHERE proposal_id = :proposal_id ORDER BY uploaded_at ASC");
$stmtDocs->execute([':proposal_id' => $id]);
$docsRaw = $stmtDocs->fetchAll();

$docTypeLabels = getDocumentTypes();
$documents = [];
foreach ($docsRaw as $doc) {
    $documents[] = [
        'file_name' => $doc['file_name'],
        'document_type' => $docTypeLabels[$doc['document_type']] ?? ucfirst($doc['document_type']),
        'file_size_formatted' => $doc['file_size'] ? round($doc['file_size'] / 1024, 1) . ' KB' : 'N/A',
        'file_icon' => getFileIcon($doc['file_name']),
        'download_url' => SITE_URL . 'uploads/proposals/' . urlencode($doc['file_path'] ?? $doc['file_name']),
    ];
}

$avatar = getAvatarUrl($proposal['first_name'] . ' ' . $proposal['last_name']);
if (!empty($proposal['profile_picture']) && file_exists(PROFILE_PATH . $proposal['profile_picture'])) {
    $avatar = SITE_URL . 'uploads/profiles/' . $proposal['profile_picture'];
}

$cvUrl = null;
if (!empty($proposal['cv_file'])) {
    $cvUrl = SITE_URL . 'uploads/cvs/' . urlencode($proposal['cv_file']);
}

jsonResponse([
    'id' => (int)$proposal['id'],
    'review_id' => (int)$reviewAssignment['id'],
    'review_status' => $reviewAssignment['status'],
    'review_url' => SITE_URL . 'reviewer/pages/review.php?id=' . (int)$reviewAssignment['id'],
    'title' => htmlspecialchars($proposal['title']),
    'abstract' => nl2br(htmlspecialchars($proposal['abstract'] ?? '')),
    'problem_statement' => nl2br(htmlspecialchars($proposal['problem_statement'] ?? '')),
    'methodology' => nl2br(htmlspecialchars($proposal['methodology'] ?? '')),
    'expected_outcomes' => nl2br(htmlspecialchars($proposal['expected_outcomes'] ?? '')),
    'budget_amount' => $proposal['budget_amount'] ? formatAmount($proposal['budget_amount']) : 'N/A',
    'budget_breakdown' => nl2br(htmlspecialchars($proposal['budget_breakdown'] ?? '')),
    'grant_title' => htmlspecialchars($proposal['grant_title']),
    'grant_funding' => $proposal['grant_funding'] ? formatAmount($proposal['grant_funding']) : 'N/A',
    'status_badge' => getStatusBadge($proposal['status']),
    'submitted_at' => $proposal['submitted_at'] ? formatDate($proposal['submitted_at']) : 'Not submitted',
    'researcher' => [
        'id' => $researcherId,
        'name' => htmlspecialchars($proposal['first_name'] . ' ' . $proposal['last_name']),
        'email' => htmlspecialchars($proposal['email']),
        'phone' => htmlspecialchars($proposal['phone'] ?: 'N/A'),
        'department' => htmlspecialchars($proposal['department'] ?: 'N/A'),
        'institution' => htmlspecialchars($proposal['institution'] ?: 'N/A'),
        'qualification' => htmlspecialchars($proposal['qualification'] ?: 'N/A'),
        'expertise_area' => htmlspecialchars($proposal['expertise_area'] ?: 'N/A'),
        'orcid_id' => $proposal['orcid_id'] ? htmlspecialchars($proposal['orcid_id']) : null,
        'google_scholar' => $proposal['google_scholar'] ? htmlspecialchars($proposal['google_scholar']) : null,
        'biography' => nl2br(htmlspecialchars($proposal['biography'] ?? '')),
        'research_interests' => nl2br(htmlspecialchars($proposal['research_interests'] ?? '')),
        'avatar' => $avatar,
        'cv_url' => $cvUrl,
        'stats' => [
            'total_proposals' => (int)($resStats['total_proposals'] ?? 0),
            'approved_proposals' => (int)($resStats['approved_proposals'] ?? 0),
            'total_awards' => (int)($awardStats['total_awards'] ?? 0),
            'completed_projects' => (int)($awardStats['completed_projects'] ?? 0),
        ]
    ],
    'documents' => $documents,
]);
