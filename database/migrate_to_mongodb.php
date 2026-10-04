<?php
/**
 * EduNexAI Database Migration Script: MySQL -> MongoDB
 */

$mysqlHost = '127.0.0.1';
$mysqlUser = 'root';
$mysqlPass = '';
$mysqlDb   = 'student_ai_system';
$mysqlPort = 3306;

$mongoUri = getenv('MONGODB_URI') ?: 'mongodb://127.0.0.1:27017';
$mongoDb  = getenv('MONGODB_DATABASE') ?: 'student_ai_system';

echo "Connecting to MySQL ($mysqlHost:$mysqlPort / $mysqlDb)...\n";
$myConn = mysqli_connect($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDb, $mysqlPort);
if (!$myConn) {
    die("MySQL Connection failed: " . mysqli_connect_error() . "\n");
}
mysqli_set_charset($myConn, 'utf8mb4');

echo "Connecting to MongoDB ($mongoUri / $mongoDb)...\n";
$manager = new MongoDB\Driver\Manager($mongoUri);

// Get list of tables
$res = mysqli_query($myConn, "SHOW TABLES");
$tables = [];
while ($r = mysqli_fetch_row($res)) {
    $tables[] = $r[0];
}

$migrationStats = [];

foreach ($tables as $table) {
    echo "Migrating table: $table ...\n";
    
    // Drop existing collection in MongoDB to ensure clean migration
    try {
        $dropCmd = new MongoDB\Driver\Command(["drop" => $table]);
        $manager->executeCommand($mongoDb, $dropCmd);
    } catch (Exception $e) {
        // collection might not exist yet, ignore
    }
    
    // Get column types
    $cRes = mysqli_query($myConn, "SHOW COLUMNS FROM `$table`");
    $colTypes = [];
    while ($c = mysqli_fetch_assoc($cRes)) {
        $field = $c['Field'];
        $type = strtolower($c['Type']);
        $colTypes[$field] = $type;
    }
    
    // Fetch all rows
    $q = mysqli_query($myConn, "SELECT * FROM `$table`");
    $count = 0;
    
    $bulk = new MongoDB\Driver\BulkWrite();
    
    while ($row = mysqli_fetch_assoc($q)) {
        $doc = [];
        foreach ($row as $k => $v) {
            if ($v === null) {
                $doc[$k] = null;
                continue;
            }
            $t = $colTypes[$k] ?? 'string';
            if (strpos($t, 'int') !== false) {
                $doc[$k] = (int)$v;
            } elseif (strpos($t, 'decimal') !== false || strpos($t, 'float') !== false || strpos($t, 'double') !== false) {
                $doc[$k] = (float)$v;
            } else {
                $doc[$k] = (string)$v;
            }
        }
        $bulk->insert($doc);
        $count++;
    }
    
    if ($count > 0) {
        $result = $manager->executeBulkWrite("$mongoDb.$table", $bulk);
        $insertedCount = $result->getInsertedCount();
    } else {
        // Create empty collection if table had 0 rows (e.g. attendance_logs)
        $createCmd = new MongoDB\Driver\Command(["create" => $table]);
        try {
            $manager->executeCommand($mongoDb, $createCmd);
        } catch (Exception $e) {}
        $insertedCount = 0;
    }
    
    // Verify count in MongoDB
    $countCmd = new MongoDB\Driver\Command(["count" => $table]);
    $cursor = $manager->executeCommand($mongoDb, $countCmd);
    $mongoCount = $cursor->toArray()[0]->n;
    
    $migrationStats[$table] = [
        'mysql_count' => $count,
        'mongo_count' => $mongoCount,
        'status' => ($count === $mongoCount) ? 'OK' : 'MISMATCH'
    ];
    
    echo "  MySQL: $count rows | MongoDB: $mongoCount documents [{$migrationStats[$table]['status']}]\n";
}

// Create appropriate indexes on MongoDB collections
echo "\nCreating MongoDB Indexes...\n";
$indexes = [
    'users' => [
        ['key' => ['id' => 1], 'unique' => true],
        ['key' => ['email' => 1], 'unique' => true],
        ['key' => ['enrollment_no' => 1], 'sparse' => true]
    ],
    'students' => [
        ['key' => ['student_id' => 1], 'unique' => true],
        ['key' => ['user_id' => 1]],
        ['key' => ['roll_number' => 1]]
    ],
    'subjects' => [
        ['key' => ['subject_id' => 1], 'unique' => true],
        ['key' => ['faculty_id' => 1]]
    ],
    'marks' => [
        ['key' => ['mark_id' => 1], 'unique' => true],
        ['key' => ['student_id' => 1]],
        ['key' => ['subject_id' => 1]]
    ],
    'attendance' => [
        ['key' => ['attendance_id' => 1], 'unique' => true],
        ['key' => ['student_id' => 1]],
        ['key' => ['subject_id' => 1]]
    ],
    'predictions' => [
        ['key' => ['prediction_id' => 1], 'unique' => true],
        ['key' => ['student_id' => 1]]
    ],
    'prediction_history' => [
        ['key' => ['id' => 1], 'unique' => true],
        ['key' => ['student_id' => 1]]
    ],
    'assignments' => [
        ['key' => ['assignment_id' => 1], 'unique' => true],
        ['key' => ['subject_id' => 1]],
        ['key' => ['faculty_id' => 1]]
    ],
    'assignment_submissions' => [
        ['key' => ['submission_id' => 1], 'unique' => true],
        ['key' => ['assignment_id' => 1, 'student_id' => 1]],
        ['key' => ['student_id' => 1]]
    ],
    'student_fees' => [
        ['key' => ['fee_id' => 1], 'unique' => true],
        ['key' => ['student_id' => 1], 'unique' => true]
    ],
    'fee_payments' => [
        ['key' => ['payment_id' => 1], 'unique' => true],
        ['key' => ['student_id' => 1]],
        ['key' => ['fee_id' => 1]]
    ],
    'attendance_logs' => [
        ['key' => ['log_id' => 1], 'unique' => true]
    ]
];

foreach ($indexes as $coll => $idxList) {
    $idxDocs = [];
    foreach ($idxList as $idx) {
        $name = implode('_', array_keys($idx['key']));
        $doc = [
            'key' => $idx['key'],
            'name' => $name . '_idx'
        ];
        if (!empty($idx['unique'])) $doc['unique'] = true;
        if (!empty($idx['sparse'])) $doc['sparse'] = true;
        $idxDocs[] = $doc;
    }
    
    try {
        $createIdxCmd = new MongoDB\Driver\Command([
            'createIndexes' => $coll,
            'indexes' => $idxDocs
        ]);
        $manager->executeCommand($mongoDb, $createIdxCmd);
        echo "  Created indexes for $coll\n";
    } catch (Exception $e) {
        echo "  Index note for $coll: " . $e->getMessage() . "\n";
    }
}

file_put_contents(__DIR__ . "/migration_summary.json", json_encode($migrationStats, JSON_PRETTY_PRINT));
echo "\nMigration finished successfully!\n";
