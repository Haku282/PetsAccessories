<?php
require_once __DIR__ . '/../../backend/config/database.php';

$promotions = [];
$news = [];
$db = $pdo;

if ($db instanceof PDO) {
    try {
        // Fetch promotions from posts
        $stmtPosts = $db->prepare(
            'SELECT post_id, title, slug, content, thumbnail, category, created_at
             FROM posts
             WHERE status = 1
             ORDER BY created_at DESC
             LIMIT 3'
        );
        $stmtPosts->execute();
        $promotions = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

        // Fetch news from pages
        $stmtPages = $db->prepare(
            'SELECT page_id, page_title, page_slug, page_content, updated_at
             FROM pages
             ORDER BY updated_at DESC
             LIMIT 3'
        );
        $stmtPages->execute();
        $news = $stmtPages->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $promotions = [];
        $news = [];
    }
}
?>