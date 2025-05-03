<?php

$dbPath = __DIR__ . '/../storage/database.db';

if (false === \file_exists($dbPath)) {
    \touch($dbPath);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queries = [
    'DROP TABLE IF EXISTS video',
    'CREATE TABLE video (
        id TEXT UNIQUE PRIMARY KEY,
        playlist VARCHAR(255),
        title TEXT NOT NULL,
        thumbnail TEXT NOT NULL,
        captions BOOLEAN DEFAULT FALSE,
        indexed BOOLEAN DEFAULT FALSE
    )',
];

foreach ($queries as $query) {
    $pdo->exec($query);
}

echo "Database initialized successfully.\n";
