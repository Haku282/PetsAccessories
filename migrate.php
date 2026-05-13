<?php
// Migration script to add lock_reason and locked_at columns

require_once __DIR__ . '/backend/config/database.php';

try {
    /** @var PDO $pdo */
    $pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS lock_reason TEXT DEFAULT NULL');
    echo "✓ Column lock_reason added or already exists\n";
    
    $pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_at TIMESTAMP DEFAULT NULL');
    echo "✓ Column locked_at added or already exists\n";
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent users table structure:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n✓ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
