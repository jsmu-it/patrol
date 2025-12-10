<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=jsmuguard_db', 'jsmu_user', '*Jsmu@378');
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
