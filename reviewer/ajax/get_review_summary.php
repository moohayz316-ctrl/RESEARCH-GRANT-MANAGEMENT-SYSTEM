<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('reviewer');

$userId = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    jsonResponse(['error' => 'Invalid ID'], 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT pr.*, p.title as proposal_title, g.title as grant_title, u.first_name, u.last_name FROM proposal_reviews pr JOIN proposals p ON pr.proposal_id = p.id JOIN grants g ON p.grant_id = g.id JOIN users u ON p.researcher_id = u.id WHERE pr.id = :id AND pr.reviewer_id = :user_id");
$stmt->execute([':id' => $id, ':user_id' => $userId]);
$review = $stmt->fetch();

if (!$review) {
    jsonResponse(['error' => 'Review not found'], 404);
}

jsonResponse([
    'proposal_title' => htmlspecialchars($review['proposal_title']),
    'grant_title' => htmlspecialchars($review['grant_title']),
    'researcher_name' => htmlspecialchars($review['first_name'] . ' ' . $review['last_name']),
    'score' => $review['score'] !== null ? number_format($review['score'], 1) : '-',
    'recommendation_badge' => $review['recommendation'] ? getStatusBadge($review['recommendation']) : '-',
    'completed_at' => $review['completed_at'] ? formatDateTime($review['completed_at']) : 'N/A',
    'comments' => nl2br(htmlspecialchars($review['comments'] ?? '')),
    'evaluation_report' => nl2br(htmlspecialchars($review['evaluation_report'] ?? '')),
]);
