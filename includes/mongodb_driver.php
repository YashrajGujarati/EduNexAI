<?php
/**
 * EduNexAI — MongoDB Native Database Driver & SQL Compatibility Layer
 * Converts SQL/MySQL operations directly to MongoDB collections and documents.
 */

if (!class_exists('EduNexMongoDriver')) {

class EduNexMongoResult implements Countable, IteratorAggregate {
    public $rows = [];
    public $num_rows = 0;
    private $pointer = 0;

    public function __construct(array $rows = []) {
        $this->rows = array_values($rows);
        $this->num_rows = count($this->rows);
        $this->pointer = 0;
    }

    public function fetch_assoc() {
        if ($this->pointer < $this->num_rows) {
            $row = $this->rows[$this->pointer++];
            return is_array($row) ? $row : (array)$row;
        }
        return null;
    }

    public function fetch_array($result_type = 3) { // 1 = NUM, 2 = ASSOC, 3 = BOTH
        $row = $this->fetch_assoc();
        if ($row === null) return null;
        if ($result_type === 2) return $row; // ASSOC
        
        $numRow = array_values($row);
        if ($result_type === 1) return $numRow; // NUM
        
        // BOTH
        $both = $row;
        $i = 0;
        foreach ($numRow as $val) {
            $both[$i++] = $val;
        }
        return $both;
    }

    public function fetch_row() {
        $row = $this->fetch_assoc();
        if ($row === null) return null;
        return array_values($row);
    }

    public function data_seek($offset) {
        $offset = (int)$offset;
        if ($offset >= 0 && $offset <= $this->num_rows) {
            $this->pointer = $offset;
            return true;
        }
        return false;
    }

    public function count(): int {
        return $this->num_rows;
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->rows);
    }
}

class EduNexMongoStmt {
    private $driver;
    private $sql;
    private $params = [];
    private $lastResult = null;

    public function __construct(EduNexMongoDriver $driver, $sql) {
        $this->driver = $driver;
        $this->sql = $sql;
    }

    public function bind_param($types, &...$vars) {
        $this->params = [];
        foreach ($vars as &$v) {
            $this->params[] = &$v;
        }
        return true;
    }

    public function execute() {
        // Substitute ? with bound parameter values safely
        $parts = explode('?', $this->sql);
        $interpolated = '';
        $count = count($this->params);
        for ($i = 0; $i < count($parts); $i++) {
            $interpolated .= $parts[$i];
            if ($i < $count) {
                $val = $this->params[$i];
                if ($val === null) {
                    $interpolated .= 'NULL';
                } elseif (is_int($val) || is_float($val)) {
                    $interpolated .= $val;
                } else {
                    $escaped = addslashes((string)$val);
                    $interpolated .= "'" . $escaped . "'";
                }
            }
        }
        $this->lastResult = $this->driver->query($interpolated);
        return ($this->lastResult !== false);
    }

    public function get_result() {
        return $this->lastResult;
    }

    public function close() {
        $this->lastResult = null;
        $this->params = [];
        return true;
    }
}

class EduNexMongoDriver {
    public $manager;
    public $database;
    public $insert_id = 0;
    public $affected_rows = 0;
    public $error = '';
    public $errno = 0;

    private static $primaryKeys = [
        'users' => 'id',
        'students' => 'student_id',
        'subjects' => 'subject_id',
        'marks' => 'mark_id',
        'attendance' => 'attendance_id',
        'predictions' => 'prediction_id',
        'prediction_history' => 'id',
        'assignments' => 'assignment_id',
        'assignment_submissions' => 'submission_id',
        'student_fees' => 'fee_id',
        'fee_payments' => 'payment_id',
        'attendance_logs' => 'log_id'
    ];

    public function __construct($uri = null, $dbName = null) {
        $uri = $uri ?: (getenv('MONGODB_URI') ?: 'mongodb://127.0.0.1:27017');
        $dbName = $dbName ?: (getenv('MONGODB_DATABASE') ?: 'student_ai_system');
        
        $this->database = $dbName;
        $this->manager = new MongoDB\Driver\Manager($uri);
    }

    public function escape_string($string) {
        if ($string === null) return '';
        // Return string with quotes safely escaped
        return addslashes($string);
    }

    public function real_escape_string($string) {
        return $this->escape_string($string);
    }

    public function get_insert_id() {
        return $this->insert_id;
    }

    public function get_error() {
        return $this->error;
    }

    public function getNextId($collection) {
        $pk = self::$primaryKeys[$collection] ?? 'id';
        $filter = [$pk => ['$exists' => true, '$ne' => null]];
        $options = [
            'sort' => [$pk => -1],
            'limit' => 1,
            'projection' => [$pk => 1]
        ];
        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $this->manager->executeQuery("{$this->database}.$collection", $query);
        $docs = $cursor->toArray();
        if (!empty($docs) && isset($docs[0]->$pk)) {
            return ((int)$docs[0]->$pk) + 1;
        }
        return 1;
    }

    public function fetchAllDocuments($collection, array $filter = [], array $options = []) {
        try {
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery("{$this->database}.$collection", $query);
            $res = [];
            foreach ($cursor as $doc) {
                $arr = (array)$doc;
                if (isset($arr['_id']) && is_object($arr['_id'])) {
                    $arr['_id'] = (string)$arr['_id'];
                }
                $res[] = $arr;
            }
            return $res;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return [];
        }
    }

    public function prepare($sql) {
        return new EduNexMongoStmt($this, $sql);
    }

    public function query($sql) {
        $this->error = '';
        $this->errno = 0;
        $sql = trim($sql);
        $sql = rtrim($sql, ';');

        if ($sql === '') return true;

        $upper = strtoupper(ltrim($sql));

        // SHOW TABLES
        if (strpos($upper, 'SHOW TABLES') === 0) {
            $cmd = new MongoDB\Driver\Command(['listCollections' => 1]);
            try {
                $cursor = $this->manager->executeCommand($this->database, $cmd);
                $rows = [];
                $colName = "Tables_in_{$this->database}";
                foreach ($cursor as $c) {
                    $name = $c->name;
                    if (strpos($name, 'system.') !== 0) {
                        $rows[] = [$colName => $name, 0 => $name];
                    }
                }
                return new EduNexMongoResult($rows);
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        // SHOW COLUMNS FROM table [LIKE ...]
        if (strpos($upper, 'SHOW COLUMNS') === 0 || strpos($upper, 'SHOW FULL COLUMNS') === 0) {
            return $this->handleShowColumns($sql);
        }

        // CREATE TABLE IF NOT EXISTS
        if (strpos($upper, 'CREATE TABLE') === 0) {
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
                $tbl = $m[1];
                try {
                    $cmd = new MongoDB\Driver\Command(['create' => $tbl]);
                    $this->manager->executeCommand($this->database, $cmd);
                } catch (Exception $e) {
                    // exists, ignore
                }
                return true;
            }
            return true;
        }

        // INSERT
        if (strpos($upper, 'INSERT') === 0) {
            return $this->handleInsert($sql);
        }

        // UPDATE
        if (strpos($upper, 'UPDATE') === 0) {
            return $this->handleUpdate($sql);
        }

        // DELETE
        if (strpos($upper, 'DELETE') === 0) {
            return $this->handleDelete($sql);
        }

        // SELECT
        if (strpos($upper, 'SELECT') === 0) {
            return $this->handleSelect($sql);
        }

        // SET / TRUNCATE
        if (strpos($upper, 'SET ') === 0) {
            return true;
        }

        if (strpos($upper, 'TRUNCATE') === 0) {
            if (preg_match('/TRUNCATE\s+(?:TABLE\s+)?`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
                $tbl = $m[1];
                $bulk = new MongoDB\Driver\BulkWrite();
                $bulk->delete([]);
                $this->manager->executeBulkWrite("{$this->database}.$tbl", $bulk);
                return true;
            }
        }

        // Default fallback: parse as select
        return $this->handleSelect($sql);
    }

    private function handleShowColumns($sql) {
        if (preg_match('/FROM\s+`?([a-zA-Z0-9_]+)`?(?:\s+LIKE\s+[\'"]%?([a-zA-Z0-9_]+)%?[\'"])?/i', $sql, $m)) {
            $table = $m[1];
            $like = $m[2] ?? null;

            // Fetch one document to inspect keys
            $docs = $this->fetchAllDocuments($table, [], ['limit' => 1]);
            $fields = [];
            if (!empty($docs)) {
                foreach (array_keys($docs[0]) as $k) {
                    if ($k === '_id') continue;
                    $fields[] = $k;
                }
            } else {
                // Default table schema fallback if collection is empty
                $knownSchemas = [
                    'attendance_logs' => ['log_id', 'subject_id', 'faculty_id', 'topic', 'attendance_date', 'total_students', 'present_count', 'absent_count', 'created_at'],
                    'prediction_history' => ['id', 'student_id', 'predicted_score', 'result', 'risk_level', 'created_at', 'attendance', 'marks', 'prediction']
                ];
                $fields = $knownSchemas[$table] ?? ['id'];
            }

            if ($table === 'prediction_history' && !in_array('prediction', $fields)) {
                $fields[] = 'prediction';
                $fields[] = 'attendance';
                $fields[] = 'marks';
            }

            $rows = [];
            foreach ($fields as $f) {
                if ($like && stripos($f, $like) === false) continue;
                $rows[] = [
                    'Field' => $f,
                    'Type' => 'varchar(255)',
                    'Null' => 'YES',
                    'Key' => ($f === (self::$primaryKeys[$table] ?? 'id')) ? 'PRI' : '',
                    'Default' => null,
                    'Extra' => ''
                ];
            }
            return new EduNexMongoResult($rows);
        }
        return new EduNexMongoResult([]);
    }

    private function handleInsert($sql) {
        try {
            // Handle: INSERT [IGNORE] INTO table (cols) VALUES (...)
            if (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?([a-zA-Z0-9_]+)`?\s*\(([^)]+)\)\s*VALUES\s*(.*)/is', $sql, $m)) {
                $table = $m[1];
                $cols = array_map(function($c) { return trim($c, " `\t\n\r"); }, explode(',', $m[2]));
                $valuesRaw = trim($m[3]);

                // Multiple value tuples support: (...), (...)
                $tuples = $this->parseValueTuples($valuesRaw);
                $pk = self::$primaryKeys[$table] ?? 'id';
                $bulk = new MongoDB\Driver\BulkWrite();
                $lastId = 0;

                foreach ($tuples as $valList) {
                    $doc = [];
                    foreach ($cols as $idx => $colName) {
                        $rawVal = $valList[$idx] ?? 'NULL';
                        $doc[$colName] = $this->evaluateSqlValue($rawVal);
                    }

                    // Auto-increment primary key if missing or null or 0
                    if (!isset($doc[$pk]) || $doc[$pk] === null || $doc[$pk] === 0 || $doc[$pk] === '') {
                        $nextId = $this->getNextId($table);
                        $doc[$pk] = $nextId;
                        $lastId = $nextId;
                    } else {
                        $lastId = is_numeric($doc[$pk]) ? (int)$doc[$pk] : $doc[$pk];
                    }

                    // Defaults
                    if (!isset($doc['created_at'])) {
                        $doc['created_at'] = date('Y-m-d H:i:s');
                    }

                    $bulk->insert($doc);
                }

                $res = $this->manager->executeBulkWrite("{$this->database}.$table", $bulk);
                $this->insert_id = $lastId;
                $this->affected_rows = $res->getInsertedCount();
                return true;
            }

            // Handle: INSERT [IGNORE] INTO table (cols) SELECT ...
            if (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?([a-zA-Z0-9_]+)`?\s*\(([^)]+)\)\s*(SELECT\s+.*)/is', $sql, $m)) {
                $targetTable = $m[1];
                $targetCols = array_map(function($c) { return trim($c, " `\t\n\r"); }, explode(',', $m[2]));
                $selectSql = $m[3];

                $selectRes = $this->handleSelect($selectSql);
                $rows = $selectRes ? $selectRes->rows : [];
                $pk = self::$primaryKeys[$targetTable] ?? 'id';
                $bulk = new MongoDB\Driver\BulkWrite();
                $count = 0;

                foreach ($rows as $row) {
                    $vals = array_values($row);
                    $doc = [];
                    foreach ($targetCols as $idx => $cName) {
                        $doc[$cName] = $vals[$idx] ?? null;
                    }
                    if (!isset($doc[$pk]) || $doc[$pk] === null || $doc[$pk] === 0) {
                        $doc[$pk] = $this->getNextId($targetTable);
                    }
                    $bulk->insert($doc);
                    $count++;
                }

                if ($count > 0) {
                    $res = $this->manager->executeBulkWrite("{$this->database}.$targetTable", $bulk);
                    $this->affected_rows = $res->getInsertedCount();
                }
                return true;
            }

            // Handle: INSERT INTO table VALUES (...)
            if (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?([a-zA-Z0-9_]+)`?\s*VALUES\s*(.*)/is', $sql, $m)) {
                $table = $m[1];
                $tuples = $this->parseValueTuples($m[2]);
                $pk = self::$primaryKeys[$table] ?? 'id';
                $bulk = new MongoDB\Driver\BulkWrite();
                $lastId = 0;

                foreach ($tuples as $valList) {
                    $doc = [];
                    $nextId = $this->getNextId($table);
                    $doc[$pk] = $nextId;
                    $lastId = $nextId;
                    $bulk->insert($doc);
                }
                $res = $this->manager->executeBulkWrite("{$this->database}.$table", $bulk);
                $this->insert_id = $lastId;
                $this->affected_rows = $res->getInsertedCount();
                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    private function handleUpdate($sql) {
        try {
            // UPDATE table SET col1=val1, col2=val2 WHERE ...
            if (preg_match('/UPDATE\s+`?([a-zA-Z0-9_]+)`?\s+SET\s+(.*?)(?:\s+WHERE\s+(.*))?$/is', $sql, $m)) {
                $table = $m[1];
                $setPart = $m[2];
                $wherePart = $m[3] ?? '';

                $setPairs = $this->parseSetAssignments($setPart);
                $allDocs = $this->fetchAllDocuments($table);
                $bulk = new MongoDB\Driver\BulkWrite();
                $updatedCount = 0;
                $pk = self::$primaryKeys[$table] ?? 'id';

                foreach ($allDocs as $doc) {
                    if ($wherePart === '' || $this->matchesWhere($doc, $wherePart)) {
                        $updateFields = [];
                        foreach ($setPairs as $field => $valExpr) {
                            $updateFields[$field] = $this->evaluateSqlExpression($valExpr, $doc);
                        }
                        
                        $filter = [$pk => $doc[$pk] ?? $doc['_id']];
                        $bulk->update($filter, ['$set' => $updateFields]);
                        $updatedCount++;
                    }
                }

                if ($updatedCount > 0) {
                    $res = $this->manager->executeBulkWrite("{$this->database}.$table", $bulk);
                    $this->affected_rows = $res->getModifiedCount();
                } else {
                    $this->affected_rows = 0;
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    private function handleDelete($sql) {
        try {
            // DELETE FROM table WHERE ...
            if (preg_match('/DELETE\s+FROM\s+`?([a-zA-Z0-9_]+)`?(?:\s+WHERE\s+(.*))?$/is', $sql, $m)) {
                $table = $m[1];
                $wherePart = $m[2] ?? '';

                $allDocs = $this->fetchAllDocuments($table);
                $bulk = new MongoDB\Driver\BulkWrite();
                $delCount = 0;
                $pk = self::$primaryKeys[$table] ?? 'id';

                foreach ($allDocs as $doc) {
                    if ($wherePart === '' || $this->matchesWhere($doc, $wherePart)) {
                        $filter = [$pk => $doc[$pk] ?? $doc['_id']];
                        $bulk->delete($filter);
                        $delCount++;
                    }
                }

                if ($delCount > 0) {
                    $res = $this->manager->executeBulkWrite("{$this->database}.$table", $bulk);
                    $this->affected_rows = $res->getDeletedCount();
                } else {
                    $this->affected_rows = 0;
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    private function handleSelect($sql) {
        try {
            // 1. Check for UNION ALL
            if (preg_match('/\bUNION\s+(?:ALL\s+)?/i', $sql)) {
                $parts = preg_split('/\bUNION\s+(?:ALL\s+)?/i', $sql);
                $combined = [];
                foreach ($parts as $p) {
                    $subRes = $this->handleSelect(trim($p));
                    if ($subRes) {
                        foreach ($subRes->rows as $r) {
                            $combined[] = $r;
                        }
                    }
                }
                return new EduNexMongoResult($combined);
            }

            // 2. Parse SELECT clause, FROM clause, WHERE, GROUP BY, HAVING, ORDER BY, LIMIT
            // Extract clauses cleanly
            $parsed = $this->parseSelectQuery($sql);
            if (!$parsed) return new EduNexMongoResult([]);

            $fromTable = $parsed['from_table'];
            $fromAlias = $parsed['from_alias'] ?: $fromTable;

            // Load primary table documents
            $primaryDocs = $this->fetchAllDocuments($fromTable);
            $rows = [];
            foreach ($primaryDocs as $d) {
                $row = [];
                foreach ($d as $k => $v) {
                    $row["$fromAlias.$k"] = $v;
                    $row[$k] = $v;
                }
                $rows[] = $row;
            }

            // Handle JOINs
            foreach ($parsed['joins'] as $join) {
                $joinType = strtoupper($join['type']); // INNER or LEFT
                $joinTable = $join['table'];
                $joinAlias = $join['alias'] ?: $joinTable;
                $joinOn = $join['on'];

                $joinDocs = $this->fetchAllDocuments($joinTable);
                $newRows = [];

                foreach ($rows as $currRow) {
                    $matched = false;
                    foreach ($joinDocs as $jDoc) {
                        $candidate = $currRow;
                        foreach ($jDoc as $k => $v) {
                            $candidate["$joinAlias.$k"] = $v;
                            if (!isset($candidate[$k])) {
                                $candidate[$k] = $v;
                            }
                        }

                        if ($this->evalJoinCondition($candidate, $joinOn)) {
                            $newRows[] = $candidate;
                            $matched = true;
                        }
                    }

                    if (!$matched && $joinType === 'LEFT') {
                        // Keep current row with nulls for joined table
                        $nullRow = $currRow;
                        if (!empty($joinDocs)) {
                            foreach (array_keys($joinDocs[0]) as $k) {
                                $nullRow["$joinAlias.$k"] = null;
                                if (!isset($nullRow[$k])) {
                                    $nullRow[$k] = null;
                                }
                            }
                        }
                        $newRows[] = $nullRow;
                    }
                }
                $rows = $newRows;
            }

            // Apply WHERE filter
            if (!empty($parsed['where'])) {
                $filtered = [];
                foreach ($rows as $r) {
                    if ($this->matchesWhere($r, $parsed['where'])) {
                        $filtered[] = $r;
                    }
                }
                $rows = $filtered;
            }

            // Handle GROUP BY and Aggregations
            $hasAgg = $this->hasAggregateFunctions($parsed['select_exprs']);
            $groupByCols = $parsed['group_by'];

            if (!empty($groupByCols) || $hasAgg) {
                $rows = $this->applyAggregationAndGrouping($rows, $groupByCols, $parsed['select_exprs'], $parsed['having']);
            } else {
                // Apply projections
                $projected = [];
                foreach ($rows as $r) {
                    $pRow = [];
                    foreach ($parsed['select_exprs'] as $expr) {
                        $colAlias = $expr['alias'];
                        $rawExpr = trim($expr['expr'], " `");
                        if ($rawExpr === '*') {
                            foreach ($r as $colKey => $colVal) {
                                if (strpos($colKey, '.') === false) {
                                    $pRow[$colKey] = $colVal;
                                }
                            }
                        } elseif (preg_match('/^`?([a-zA-Z0-9_]+)`?\.\*$/', $expr['expr'], $tm)) {
                            $tblPrefix = $tm[1] . '.';
                            foreach ($r as $colKey => $colVal) {
                                if (str_starts_with($colKey, $tblPrefix)) {
                                    $colName = substr($colKey, strlen($tblPrefix));
                                    $pRow[$colName] = $colVal;
                                }
                            }
                        } else {
                            $val = $this->evaluateSqlExpression($expr['expr'], $r);
                            $pRow[$colAlias] = $val;
                        }
                    }
                    $projected[] = $pRow;
                }
                $rows = $projected;
            }

            // Handle ORDER BY
            if (!empty($parsed['order_by'])) {
                usort($rows, function($a, $b) use ($parsed) {
                    foreach ($parsed['order_by'] as $ob) {
                        $col = $ob['col'];
                        $dir = strtoupper($ob['dir']);
                        $valA = $a[$col] ?? ($this->evaluateSqlExpression($col, $a));
                        $valB = $b[$col] ?? ($this->evaluateSqlExpression($col, $b));

                        if ($valA == $valB) continue;
                        $cmp = ($valA < $valB) ? -1 : 1;
                        return ($dir === 'DESC') ? -$cmp : $cmp;
                    }
                    return 0;
                });
            }

            // Handle LIMIT / OFFSET
            if ($parsed['limit'] !== null) {
                $offset = $parsed['offset'] ?: 0;
                $rows = array_slice($rows, $offset, $parsed['limit']);
            }

            return new EduNexMongoResult($rows);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return new EduNexMongoResult([]);
        }
    }

    private function parseSelectQuery($sql) {
        $sql = trim($sql);
        // Extract subqueries in SELECT clause if any, replacing them with temporary markers
        $subqueries = [];
        $sqlClean = preg_replace_callback('/\(\s*SELECT\s+.*?\)/is', function($match) use (&$subqueries) {
            $key = '__SUBQUERY_' . count($subqueries) . '__';
            $subqueries[$key] = $match[0];
            return $key;
        }, $sql);

        // Pattern to separate SELECT, FROM, WHERE, GROUP BY, HAVING, ORDER BY, LIMIT
        if (!preg_match('/^SELECT\s+(DISTINCT\s+)?(.*?)\s+FROM\s+(.*)$/is', $sqlClean, $m)) {
            return null;
        }

        $isDistinct = !empty($m[1]);
        $selectPart = $m[2];
        $rest = $m[3];

        $where = '';
        $groupBy = [];
        $having = '';
        $orderBy = [];
        $limit = null;
        $offset = null;

        // LIMIT / OFFSET
        if (preg_match('/\s+LIMIT\s+(\d+)(?:\s+OFFSET\s+(\d+)|\s*,\s*(\d+))?\s*$/i', $rest, $lm)) {
            $limit = (int)$lm[1];
            if (isset($lm[3]) && $lm[3] !== '') {
                $offset = (int)$lm[1];
                $limit = (int)$lm[3];
            } elseif (isset($lm[2]) && $lm[2] !== '') {
                $offset = (int)$lm[2];
            }
            $rest = substr($rest, 0, -strlen($lm[0]));
        }

        // ORDER BY
        if (preg_match('/\s+ORDER\s+BY\s+(.*?)$/is', $rest, $om)) {
            $orderPart = $om[1];
            $rest = substr($rest, 0, -strlen($om[0]));
            $orderItems = explode(',', $orderPart);
            foreach ($orderItems as $oi) {
                $oi = trim($oi);
                if (preg_match('/^(.*?)\s+(ASC|DESC)$/i', $oi, $oim)) {
                    $orderBy[] = ['col' => trim($oim[1], " `"), 'dir' => strtoupper($oim[2])];
                } else {
                    $orderBy[] = ['col' => trim($oi, " `"), 'dir' => 'ASC'];
                }
            }
        }

        // HAVING
        if (preg_match('/\s+HAVING\s+(.*?)$/is', $rest, $hm)) {
            $having = trim($hm[1]);
            $rest = substr($rest, 0, -strlen($hm[0]));
        }

        // GROUP BY
        if (preg_match('/\s+GROUP\s+BY\s+(.*?)$/is', $rest, $gm)) {
            $groupPart = $gm[1];
            $rest = substr($rest, 0, -strlen($gm[0]));
            $groupBy = array_map(function($g) { return trim($g, " `\t\n\r"); }, explode(',', $groupPart));
        }

        // WHERE
        if (preg_match('/\s+WHERE\s+(.*?)$/is', $rest, $wm)) {
            $where = trim($wm[1]);
            $rest = substr($rest, 0, -strlen($wm[0]));
        }

        // Now $rest contains FROM table and JOIN clauses
        $fromAndJoins = trim($rest);
        $joinRegex = '/\b(?:(LEFT(?:\s+OUTER)?|INNER|RIGHT)\s+)?JOIN\s+`?([a-zA-Z0-9_]+)`?(?:\s+(?:AS\s+)?`?([a-zA-Z0-9_]+)`?)?\s+ON\s+(.*?)(?=\s+(?:LEFT(?:\s+OUTER)?|INNER|RIGHT)?\s*JOIN|$)/is';

        $joins = [];
        if (preg_match_all($joinRegex, $fromAndJoins, $jm, PREG_SET_ORDER)) {
            foreach ($jm as $j) {
                $type = (!empty($j[1]) && stripos($j[1], 'LEFT') !== false) ? 'LEFT' : 'INNER';
                $joins[] = [
                    'type' => $type,
                    'table' => $j[2],
                    'alias' => $j[3] ?: $j[2],
                    'on' => trim($j[4])
                ];
            }
            $fromTablePart = trim(substr($fromAndJoins, 0, strpos($fromAndJoins, $jm[0][0])));
        } else {
            $fromTablePart = $fromAndJoins;
        }

        if (preg_match('/^`?([a-zA-Z0-9_]+)`?(?:\s+(?:AS\s+)?`?([a-zA-Z0-9_]+)`?)?$/i', trim($fromTablePart), $ftm)) {
            $fromTable = $ftm[1];
            $fromAlias = $ftm[2] ?? $fromTable;
        } else {
            $fromTable = trim($fromTablePart, " `");
            $fromAlias = $fromTable;
        }

        // Parse SELECT expressions
        $exprs = $this->splitSelectExpressions($selectPart);
        $selectExprs = [];
        foreach ($exprs as $rawExpr) {
            // Restore subqueries
            foreach ($subqueries as $k => $sq) {
                $rawExpr = str_replace($k, $sq, $rawExpr);
            }
            $selectExprs[] = $this->parseSelectExpression($rawExpr);
        }

        return [
            'is_distinct' => $isDistinct,
            'select_exprs' => $selectExprs,
            'from_table' => $fromTable,
            'from_alias' => $fromAlias,
            'joins' => $joins,
            'where' => $where,
            'group_by' => $groupBy,
            'having' => $having,
            'order_by' => $orderBy,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    private function splitSelectExpressions($str) {
        $res = [];
        $len = strlen($str);
        $curr = '';
        $depth = 0;
        $inQuote = false;
        $quoteChar = '';

        for ($i = 0; $i < $len; $i++) {
            $ch = $str[$i];
            if (!$inQuote && ($ch === "'" || $ch === '"' || $ch === '`')) {
                $inQuote = true;
                $quoteChar = $ch;
                $curr .= $ch;
            } elseif ($inQuote && $ch === $quoteChar) {
                if ($i + 1 < $len && $str[$i + 1] === $quoteChar) {
                    $curr .= $ch . $quoteChar;
                    $i++;
                } else {
                    $inQuote = false;
                    $curr .= $ch;
                }
            } elseif (!$inQuote && $ch === '(') {
                $depth++;
                $curr .= $ch;
            } elseif (!$inQuote && $ch === ')') {
                $depth--;
                $curr .= $ch;
            } elseif (!$inQuote && $ch === ',' && $depth === 0) {
                $res[] = trim($curr);
                $curr = '';
            } else {
                $curr .= $ch;
            }
        }
        if (trim($curr) !== '') {
            $res[] = trim($curr);
        }
        return $res;
    }

    private function parseSelectExpression($expr) {
        $expr = trim($expr);
        if (preg_match('/^(.*?)\s+(?:AS\s+)?`?([a-zA-Z0-9_]+)`?$/is', $expr, $m)) {
            $inner = trim($m[1]);
            $alias = $m[2];
            // Verify inner doesn't end with operator
            if (!preg_match('/[+\-*\/]$/', $inner)) {
                return ['expr' => $inner, 'alias' => $alias];
            }
        }
        // No alias specified
        $cleanAlias = trim($expr, " `");
        if (strpos($cleanAlias, '.') !== false) {
            $parts = explode('.', $cleanAlias);
            $cleanAlias = end($parts);
        }
        return ['expr' => $expr, 'alias' => $cleanAlias];
    }

    private function hasAggregateFunctions(array $exprs) {
        foreach ($exprs as $e) {
            if (preg_match('/\b(COUNT|SUM|AVG|MIN|MAX)\s*\(/i', $e['expr'])) {
                return true;
            }
        }
        return false;
    }

    private function applyAggregationAndGrouping(array $rows, array $groupByCols, array $selectExprs, $having = '') {
        $groups = [];

        if (empty($groupByCols)) {
            $groups['__ALL__'] = $rows;
        } else {
            foreach ($rows as $r) {
                $keyParts = [];
                foreach ($groupByCols as $gCol) {
                    $keyParts[] = (string)($this->evaluateSqlExpression($gCol, $r) ?? '');
                }
                $gKey = implode('|||', $keyParts);
                if (!isset($groups[$gKey])) {
                    $groups[$gKey] = [];
                }
                $groups[$gKey][] = $r;
            }
        }

        $resultRows = [];
        foreach ($groups as $gKey => $groupRows) {
            $aggRow = [];
            $sampleRow = !empty($groupRows) ? $groupRows[0] : [];

            foreach ($selectExprs as $e) {
                $alias = $e['alias'];
                $expr = $e['expr'];
                $aggRow[$alias] = $this->evaluateAggregateExpression($expr, $groupRows, $sampleRow);
            }

            if ($having !== '' && !$this->matchesWhere($aggRow, $having)) {
                continue;
            }
            $resultRows[] = $aggRow;
        }

        return $resultRows;
    }

    private function evaluateAggregateExpression($expr, array $groupRows, array $sampleRow) {
        $expr = trim($expr);

        // COUNT(*)
        if (preg_match('/^COUNT\s*\(\s*\*\s*\)$/i', $expr)) {
            return count($groupRows);
        }

        // COUNT(DISTINCT col)
        if (preg_match('/^COUNT\s*\(\s*DISTINCT\s+`?([a-zA-Z0-9_\.]+)`?\s*\)$/i', $expr, $m)) {
            $col = $m[1];
            $distinct = [];
            foreach ($groupRows as $r) {
                $val = $this->evaluateSqlExpression($col, $r);
                if ($val !== null) $distinct[(string)$val] = true;
            }
            return count($distinct);
        }

        // COUNT(col)
        if (preg_match('/^COUNT\s*\(\s*`?([a-zA-Z0-9_\.]+)`?\s*\)$/i', $expr, $m)) {
            $col = $m[1];
            $c = 0;
            foreach ($groupRows as $r) {
                $val = $this->evaluateSqlExpression($col, $r);
                if ($val !== null) $c++;
            }
            return $c;
        }

        // SUM(...) or ROUND(SUM(...))
        if (preg_match('/^(?:ROUND\s*\(\s*)?SUM\s*\(\s*(.*?)\s*\)(?:\s*,\s*(\d+)\s*\))?$/is', $expr, $m)) {
            $innerExpr = $m[1];
            $roundDec = isset($m[2]) ? (int)$m[2] : null;
            $sum = 0;
            foreach ($groupRows as $r) {
                $sum += (float)($this->evaluateSqlExpression($innerExpr, $r) ?? 0);
            }
            return ($roundDec !== null) ? round($sum, $roundDec) : $sum;
        }

        // AVG(...) or ROUND(AVG(...))
        if (preg_match('/^(?:ROUND\s*\(\s*)?AVG\s*\(\s*(.*?)\s*\)(?:\s*,\s*(\d+)\s*\))?$/is', $expr, $m)) {
            $innerExpr = $m[1];
            $roundDec = isset($m[2]) ? (int)$m[2] : 2;
            if (empty($groupRows)) return 0;
            $sum = 0;
            $cnt = 0;
            foreach ($groupRows as $r) {
                $val = $this->evaluateSqlExpression($innerExpr, $r);
                if ($val !== null) {
                    $sum += (float)$val;
                    $cnt++;
                }
            }
            if ($cnt === 0) return 0;
            $avg = $sum / $cnt;
            return round($avg, $roundDec);
        }

        // Fallback: evaluate on sample row
        return $this->evaluateSqlExpression($expr, $sampleRow);
    }

    private function evaluateSqlExpression($expr, array $row) {
        $expr = trim($expr);
        if ($expr === '') return null;

        if (str_starts_with($expr, '(') && str_ends_with($expr, ')') && !preg_match('/^\(\s*SELECT\s/i', $expr)) {
            $inner = trim(substr($expr, 1, -1));
            if ($this->isBalancedParentheses($inner)) {
                $expr = $inner;
            }
        }

        // Subquery in expression: (SELECT COUNT(*) FROM assignment_submissions sub_m WHERE sub_m.assignment_id = a.assignment_id)
        if (preg_match('/^\(\s*SELECT\s+(.*?)\s+FROM\s+`?([a-zA-Z0-9_]+)`?(?:\s+`?([a-zA-Z0-9_]+)`?)?\s+WHERE\s+(.*?)\s*\)$/is', $expr, $sqm)) {
            $subSelect = $sqm[1];
            $subTable = $sqm[2];
            $subAlias = $sqm[3] ?: $subTable;
            $subWhere = $sqm[4];

            // Interpolate row values into subWhere
            $interpolatedWhere = preg_replace_callback('/`?([a-zA-Z0-9_]+)`?\.`?([a-zA-Z0-9_]+)`?/i', function($m) use ($row) {
                $val = $this->resolveRowValue($row, $m[0]);
                if ($val === null) return 'NULL';
                return is_numeric($val) ? $val : ("'" . addslashes((string)$val) . "'");
            }, $subWhere);

            $subSql = "SELECT $subSelect FROM $subTable $subAlias WHERE $interpolatedWhere";
            $subRes = $this->handleSelect($subSql);
            if ($subRes && $subRes->num_rows > 0) {
                $firstRow = $subRes->rows[0];
                return reset($firstRow);
            }
            return 0;
        }

        // Direct column match in row
        if (array_key_exists($expr, $row)) {
            return $row[$expr];
        }

        // Trim backticks
        $clean = trim($expr, " `");
        if (array_key_exists($clean, $row)) {
            return $row[$clean];
        }

        // table.column match
        $val = $this->resolveRowValue($row, $clean);
        if ($val !== null) return $val;

        // CASE WHEN ... THEN ... ELSE ... END
        if (preg_match('/^CASE\s+WHEN\s+(.*?)\s+THEN\s+(.*?)\s+ELSE\s+(.*?)\s+END$/is', $expr, $cm)) {
            $cond = $cm[1];
            $thenVal = $cm[2];
            $elseVal = $cm[3];
            if ($this->matchesWhere($row, $cond)) {
                return $this->evaluateSqlExpression($thenVal, $row);
            } else {
                return $this->evaluateSqlExpression($elseVal, $row);
            }
        }

        // Arithmetic expression (e.g. sf.total_fee - sf.paid_fee, internal_marks + external_marks)
        if (preg_match('/^(.*?)\s*([\+\-\*\/])\s*(.*?)$/', $expr, $am)) {
            $left = $this->evaluateSqlExpression($am[1], $row);
            $op = $am[2];
            $right = $this->evaluateSqlExpression($am[3], $row);
            if (is_numeric($left) && is_numeric($right)) {
                switch ($op) {
                    case '+': return (float)$left + (float)$right;
                    case '-': return (float)$left - (float)$right;
                    case '*': return (float)$left * (float)$right;
                    case '/': return ($right != 0) ? ((float)$left / (float)$right) : 0;
                }
            }
        }

        // Numeric literal
        if (is_numeric($expr)) {
            return strpos($expr, '.') !== false ? (float)$expr : (int)$expr;
        }

        // Quoted string literal
        if ((str_starts_with($expr, "'") && str_ends_with($expr, "'")) ||
            (str_starts_with($expr, '"') && str_ends_with($expr, '"'))) {
            return substr($expr, 1, -1);
        }

        // NULL / NOW() / CURRENT_DATE()
        if (strcasecmp($expr, 'NULL') === 0) return null;
        if (strcasecmp($expr, 'NOW()') === 0 || strcasecmp($expr, 'CURRENT_TIMESTAMP') === 0) return date('Y-m-d H:i:s');
        if (strcasecmp($expr, 'CURRENT_DATE()') === 0 || strcasecmp($expr, 'CURRENT_DATE') === 0) return date('Y-m-d');

        return $expr;
    }

    private function resolveRowValue(array $row, $field) {
        $field = trim($field, " `");
        if (array_key_exists($field, $row)) {
            return $row[$field];
        }
        if (preg_match('/^[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+$/', $field)) {
            $parts = explode('.', $field);
            $colOnly = end($parts);
            if (array_key_exists($colOnly, $row)) {
                return $row[$colOnly];
            }
        } elseif (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            // Find key ending with .$field
            foreach ($row as $k => $v) {
                if ($k === $field || str_ends_with($k, ".$field")) {
                    return $v;
                }
            }
        }
        return null;
    }

    private function evalJoinCondition(array $row, $onClause) {
        return $this->matchesWhere($row, $onClause);
    }

    public function matchesWhere(array $row, $whereClause) {
        $whereClause = trim($whereClause);
        if ($whereClause === '' || $whereClause === '1' || $whereClause === '1=1') return true;

        // Split top-level OR statements
        $orParts = $this->splitTopLevel($whereClause, 'OR');
        if (count($orParts) > 1) {
            foreach ($orParts as $orPart) {
                if ($this->matchesWhere($row, $orPart)) return true;
            }
            return false;
        }

        // Split top-level AND statements
        $andParts = $this->splitTopLevel($whereClause, 'AND');
        if (count($andParts) > 1) {
            foreach ($andParts as $andPart) {
                if (!$this->matchesWhere($row, $andPart)) return false;
            }
            return true;
        }

        // Strip surrounding parentheses if any
        if (str_starts_with($whereClause, '(') && str_ends_with($whereClause, ')')) {
            $inner = substr($whereClause, 1, -1);
            if ($this->isBalancedParentheses($inner)) {
                return $this->matchesWhere($row, $inner);
            }
        }

        // Single condition evaluation
        return $this->evalSingleCondition($row, $whereClause);
    }

    private function evalSingleCondition(array $row, $cond) {
        $cond = trim($cond);

        // IS NULL / IS NOT NULL
        if (preg_match('/^(.*?)\s+IS\s+NOT\s+NULL$/i', $cond, $m)) {
            $val = $this->evaluateSqlExpression($m[1], $row);
            return ($val !== null);
        }
        if (preg_match('/^(.*?)\s+IS\s+NULL$/i', $cond, $m)) {
            $val = $this->evaluateSqlExpression($m[1], $row);
            return ($val === null);
        }

        // IN (...) / NOT IN (...)
        if (preg_match('/^(.*?)\s+(NOT\s+)?IN\s*\((.*?)\)$/is', $cond, $m)) {
            $leftVal = $this->evaluateSqlExpression($m[1], $row);
            $isNot = !empty($m[2]);
            $listVals = $this->splitSelectExpressions($m[3]);
            $inList = false;
            foreach ($listVals as $lv) {
                $evalLv = $this->evaluateSqlExpression($lv, $row);
                if ((string)$leftVal == (string)$evalLv) {
                    $inList = true;
                    break;
                }
            }
            return $isNot ? !$inList : $inList;
        }

        // LIKE / NOT LIKE
        if (preg_match('/^(.*?)\s+(NOT\s+)?LIKE\s+(.*?)$/i', $cond, $m)) {
            $leftVal = (string)($this->evaluateSqlExpression($m[1], $row) ?? '');
            $isNot = !empty($m[2]);
            $patternVal = (string)($this->evaluateSqlExpression($m[3], $row) ?? '');

            // Convert SQL LIKE pattern (% and _) to regex
            $regex = '/^' . str_replace(['%', '_'], ['.*', '.'], preg_quote($patternVal, '/')) . '$/i';
            $matched = (bool)preg_match($regex, $leftVal);
            return $isNot ? !$matched : $matched;
        }

        // Comparison operators: =, !=, <>, >=, <=, >, <
        if (preg_match('/^(.*?)\s*(=|!=|<>|>=|<=|>|<)\s*(.*?)$/', $cond, $m)) {
            $left = $this->evaluateSqlExpression($m[1], $row);
            $op = $m[2];
            $right = $this->evaluateSqlExpression($m[3], $row);

            if (is_numeric($left) && is_numeric($right)) {
                $left = (float)$left;
                $right = (float)$right;
            } else {
                $left = (string)$left;
                $right = (string)$right;
            }

            switch ($op) {
                case '=': return ($left == $right);
                case '!=':
                case '<>': return ($left != $right);
                case '>=': return ($left >= $right);
                case '<=': return ($left <= $right);
                case '>': return ($left > $right);
                case '<': return ($left < $right);
            }
        }

        return false;
    }

    private function splitTopLevel($str, $delimiter) {
        $parts = [];
        $len = strlen($str);
        $delimLen = strlen($delimiter);
        $curr = '';
        $depth = 0;
        $inQuote = false;
        $quoteChar = '';

        for ($i = 0; $i < $len; $i++) {
            $ch = $str[$i];
            if (!$inQuote && ($ch === "'" || $ch === '"')) {
                $inQuote = true;
                $quoteChar = $ch;
                $curr .= $ch;
            } elseif ($inQuote && $ch === $quoteChar) {
                if ($i + 1 < $len && $str[$i + 1] === $quoteChar) {
                    $curr .= $ch . $quoteChar;
                    $i++;
                } else {
                    $inQuote = false;
                    $curr .= $ch;
                }
            } elseif (!$inQuote && $ch === '(') {
                $depth++;
                $curr .= $ch;
            } elseif (!$inQuote && $ch === ')') {
                $depth--;
                $curr .= $ch;
            } elseif (!$inQuote && $depth === 0 && (strncasecmp(substr($str, $i, $delimLen), $delimiter, $delimLen) === 0)) {
                // Check word boundaries
                $prev = ($i > 0) ? $str[$i - 1] : ' ';
                $next = ($i + $delimLen < $len) ? $str[$i + $delimLen] : ' ';
                if (ctype_space($prev) && ctype_space($next)) {
                    $parts[] = trim($curr);
                    $curr = '';
                    $i += ($delimLen - 1);
                    continue;
                } else {
                    $curr .= $ch;
                }
            } else {
                $curr .= $ch;
            }
        }
        if (trim($curr) !== '') {
            $parts[] = trim($curr);
        }
        return $parts;
    }

    private function isBalancedParentheses($str) {
        $depth = 0;
        $inQuote = false;
        $quoteChar = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $ch = $str[$i];
            if (!$inQuote && ($ch === "'" || $ch === '"')) {
                $inQuote = true;
                $quoteChar = $ch;
            } elseif ($inQuote && $ch === $quoteChar) {
                $inQuote = false;
            } elseif (!$inQuote && $ch === '(') {
                $depth++;
            } elseif (!$inQuote && $ch === ')') {
                $depth--;
                if ($depth < 0) return false;
            }
        }
        return ($depth === 0 && !$inQuote);
    }

    private function parseValueTuples($str) {
        $str = trim($str);
        $tuples = [];
        $len = strlen($str);
        $curr = '';
        $depth = 0;
        $inQuote = false;
        $quoteChar = '';

        for ($i = 0; $i < $len; $i++) {
            $ch = $str[$i];
            if (!$inQuote && ($ch === "'" || $ch === '"')) {
                $inQuote = true;
                $quoteChar = $ch;
                $curr .= $ch;
            } elseif ($inQuote && $ch === $quoteChar) {
                if ($i + 1 < $len && $str[$i + 1] === $quoteChar) {
                    $curr .= $ch . $quoteChar;
                    $i++;
                } else {
                    $inQuote = false;
                    $curr .= $ch;
                }
            } elseif (!$inQuote && $ch === '(') {
                if ($depth === 0) {
                    $curr = '';
                } else {
                    $curr .= $ch;
                }
                $depth++;
            } elseif (!$inQuote && $ch === ')') {
                $depth--;
                if ($depth === 0) {
                    $tuples[] = $this->splitSelectExpressions($curr);
                    $curr = '';
                } else {
                    $curr .= $ch;
                }
            } elseif ($depth > 0) {
                $curr .= $ch;
            }
        }
        return $tuples;
    }

    private function parseSetAssignments($str) {
        $pairs = $this->splitSelectExpressions($str);
        $res = [];
        foreach ($pairs as $p) {
            if (preg_match('/^`?([a-zA-Z0-9_]+)`?\s*=\s*(.*)$/s', $p, $m)) {
                $res[$m[1]] = trim($m[2]);
            }
        }
        return $res;
    }

    private function evaluateSqlValue($raw) {
        $raw = trim($raw);
        if (strcasecmp($raw, 'NULL') === 0) return null;
        if (strcasecmp($raw, 'NOW()') === 0 || strcasecmp($raw, 'CURRENT_TIMESTAMP') === 0) return date('Y-m-d H:i:s');
        if (strcasecmp($raw, 'CURRENT_DATE()') === 0 || strcasecmp($raw, 'CURRENT_DATE') === 0) return date('Y-m-d');
        if (preg_match('/DATE_ADD\s*\(\s*CURRENT_DATE\(\)\s*,\s*INTERVAL\s+(\d+)\s+DAY\s*\)/i', $raw, $dm)) {
            $days = (int)$dm[1];
            return date('Y-m-d', strtotime("+$days days"));
        }
        if ((str_starts_with($raw, "'") && str_ends_with($raw, "'")) ||
            (str_starts_with($raw, '"') && str_ends_with($raw, '"'))) {
            $inner = substr($raw, 1, -1);
            return stripslashes($inner);
        }
        if (is_numeric($raw)) {
            return (strpos($raw, '.') !== false) ? (float)$raw : (int)$raw;
        }
        return $raw;
    }
}

} // end if !class_exists
