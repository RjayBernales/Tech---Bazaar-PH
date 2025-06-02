<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Fetch reviews ordered by rating and creation date
    $stmt = $conn->query("
        SELECT id, name, designation, avatar_url, review_text, rating, created_at 
        FROM reviews 
        ORDER BY rating DESC, created_at DESC 
        LIMIT 4
    ");
    
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'reviews' => $reviews]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
