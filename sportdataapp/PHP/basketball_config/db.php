<?php
$pdo = new PDO(
    'mysql:host=localhost;dbname=sportsdata;charset=utf8mb4',
    getenv('DB_USER') ?: 'sportsdata_user',
    getenv('DB_PASS') ?: 'fujidai14',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
