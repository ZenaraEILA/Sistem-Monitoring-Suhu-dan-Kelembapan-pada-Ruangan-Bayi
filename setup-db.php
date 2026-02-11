<?php
// Quick database setup script

try {
    // Connect to MySQL without database
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    
    // Create database
    $pdo->exec('CREATE DATABASE IF NOT EXISTS monitoring_suhu_bayi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    
    echo "✓ Database 'monitoring_suhu_bayi' berhasil dibuat!\n";
    
    // List databases
    $databases = $pdo->query("SHOW DATABASES LIKE 'monitoring%'")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nDatabase yang tersedia:\n";
    foreach ($databases as $db) {
        echo "  - " . $db['Database'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nPastikan:\n";
    echo "  1. MySQL sudah running\n";
    echo "  2. MySQL user 'root' bisa diakses tanpa password\n";
    exit(1);
}
