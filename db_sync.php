<?php
class DatabaseSync {
    private $localDb;
    private $remoteDb;
    private $tables;
    private $logFile;
    private $tableDependencies;
    
    public function __construct() {
        $localConfig = [
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'db'   => 'quilana'
        ];
        
        // Remote database configuration (Hostinger)
        $remoteConfig = [
            'host' => '153.92.15.31',
            'user' => 'u599378104_quilana',
            'pass' => 'Quilanadb6',
            'db'   => 'u599378104_quilana'
        ];
        
        // Connect to databases
        $this->localDb = new mysqli(
            $localConfig['host'],
            $localConfig['user'],
            $localConfig['pass'],
            $localConfig['db']
        );
        
        $this->remoteDb = new mysqli(
            $remoteConfig['host'],
            $remoteConfig['user'],
            $remoteConfig['pass'],
            $remoteConfig['db']
        );
        
        // Check connections
        if ($this->localDb->connect_error || $this->remoteDb->connect_error) {
            throw new Exception("Connection failed: " . 
                ($this->localDb->connect_error ?? $this->remoteDb->connect_error));
        }
        
        // Define table dependencies
        $this->tableDependencies = [
            'student' => [],
            'faculty' => [],
            'course' => [],
            'class' => ['course', 'faculty'],
            'student_enrollment' => ['student', 'class'],
            'assessment' => ['class'],
            'questions' => ['assessment'],
            'question_options' => ['questions'],
            'question_identifications' => ['questions'],
            'administer_assessment' => ['assessment'],
            'schedule_assessments' => ['assessment'],
            'student_submission' => ['student', 'assessment'],
            'student_answer' => ['student_submission', 'questions'],
            'student_results' => ['student_submission'],
            'rw_reviewer' => [],
            'user_reviewers' => ['student', 'rw_reviewer'],
            'rw_flashcard' => ['rw_reviewer'],
            'rw_questions' => ['rw_flashcard'],
            'rw_question_opt' => ['rw_questions'],
            'rw_question_identifications' => ['rw_questions'],
            'rw_student_todo' => ['student', 'rw_flashcard'],
            'rw_student_submission' => ['student', 'rw_flashcard'],
            'rw_answer' => ['rw_student_submission', 'rw_questions'],
            'rw_student_results' => ['rw_student_submission'],
            'assessment_uploads' => ['assessment'],
            'join_assessment' => ['assessment']
        ];
        
        // Sort tables based on dependencies
        $this->tables = $this->sortTablesByDependency();
        
        // Set up logging
        $this->logFile = __DIR__ . '/sync_log.txt';
    }
    
    private function sortTablesByDependency() {
        $sorted = [];
        $visited = [];
        $visiting = [];
        
        foreach (array_keys($this->tableDependencies) as $table) {
            $this->visitTable($table, $sorted, $visited, $visiting);
        }
        
        return $sorted;
    }
    
    private function visitTable($table, &$sorted, &$visited, &$visiting) {
        if (isset($visited[$table])) {
            return;
        }
        
        if (isset($visiting[$table])) {
            throw new Exception("Circular dependency detected for table: $table");
        }
        
        $visiting[$table] = true;
        
        foreach ($this->tableDependencies[$table] as $dependency) {
            $this->visitTable($dependency, $sorted, $visited, $visiting);
        }
        
        unset($visiting[$table]);
        $visited[$table] = true;
        $sorted[] = $table;
    }
    
    public function sync() {
        $this->log("Starting database synchronization...");
        
        try {
            // First, verify all tables exist in both databases
            $this->verifyTables();
            
            // Then sync tables in dependency order
            foreach ($this->tables as $table) {
                $this->syncTable($table);
            }
            
            $this->log("Synchronization completed successfully.");
        } catch (Exception $e) {
            $this->log("Synchronization failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function verifyTables() {
        $localTables = $this->getExistingTables($this->localDb);
        $remoteTables = $this->getExistingTables($this->remoteDb);
        
        foreach ($this->tables as $table) {
            if (!in_array($table, $localTables)) {
                throw new Exception("Table '$table' not found in local database");
            }
            if (!in_array($table, $remoteTables)) {
                throw new Exception("Table '$table' not found in remote database");
            }
        }
    }
    
    private function getExistingTables($db) {
        $result = $db->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    private function syncTable($table) {
        $this->log("Syncing table: $table");
        
        try {
            // Disable foreign key checks temporarily
            $this->localDb->query("SET FOREIGN_KEY_CHECKS=0");
            $this->remoteDb->query("SET FOREIGN_KEY_CHECKS=0");
            
            // Get primary key column
            $primaryKey = $this->getPrimaryKey($table);
            if (!$primaryKey) {
                $this->log("Error: Could not determine primary key for table $table");
                return;
            }
            
            // Get latest records from both databases
            $localRecords = $this->getRecords($this->localDb, $table);
            $remoteRecords = $this->getRecords($this->remoteDb, $table);
            
            // Compare and sync records
            $this->syncRecords($table, $primaryKey, $localRecords, $remoteRecords);
            
            $this->log("Successfully synced table: $table");
            
        } catch (Exception $e) {
            $this->log("Error syncing table $table: " . $e->getMessage());
            throw $e;
        } finally {
            // Re-enable foreign key checks
            $this->localDb->query("SET FOREIGN_KEY_CHECKS=1");
            $this->remoteDb->query("SET FOREIGN_KEY_CHECKS=1");
        }
    }
    
    private function getPrimaryKey($table) {
        $query = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        $result = $this->localDb->query($query);
        if (!$result) {
            throw new Exception("Failed to get primary key for table $table: " . $this->localDb->error);
        }
        $row = $result->fetch_assoc();
        return $row['Column_name'] ?? null;
    }
    
    private function getRecords($db, $table) {
        $query = "SELECT * FROM $table";
        $result = $db->query($query);
        if (!$result) {
            throw new Exception("Failed to get records from table $table: " . $db->error);
        }
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return $records;
    }
    
    private function syncRecords($table, $primaryKey, $localRecords, $remoteRecords) {
        // Index records by primary key
        $localIndex = $this->indexRecords($localRecords, $primaryKey);
        $remoteIndex = $this->indexRecords($remoteRecords, $primaryKey);

        // Sync remote to local
        foreach ($remoteRecords as $record) {
            $id = $record[$primaryKey];
            
            if (!isset($localIndex[$id])) {
                // Insert new record to local
                $this->insertRecord($this->localDb, $table, $record);
                $this->log("Inserted record $id to local $table");
            } elseif ($this->recordsDiffer($record, $localIndex[$id])) {
                // Update different record in local
                $this->updateRecord($this->localDb, $table, $record, $primaryKey);
                $this->log("Updated record $id in local $table");
            }
        }
        
        // Sync local to remote
        foreach ($localRecords as $record) {
            $id = $record[$primaryKey];
            
            if (!isset($remoteIndex[$id])) {
                // Insert new record to remote
                $this->insertRecord($this->remoteDb, $table, $record);
                $this->log("Inserted record $id to remote $table");
            } elseif ($this->recordsDiffer($record, $remoteIndex[$id])) {
                // Update different record in remote
                $this->updateRecord($this->remoteDb, $table, $record, $primaryKey);
                $this->log("Updated record $id in remote $table");
            }
        }
    }
    
    private function indexRecords($records, $primaryKey) {
        $indexed = [];
        foreach ($records as $record) {
            $indexed[$record[$primaryKey]] = $record;
        }
        return $indexed;
    }
    
    private function recordsDiffer($record1, $record2) {
        // Compare records excluding auto-updated timestamp fields
        $timestampFields = ['date_updated', 'date_created', 'time_updated'];
        
        foreach ($record1 as $key => $value) {
            if (!in_array($key, $timestampFields) && $value !== ($record2[$key] ?? null)) {
                return true;
            }
        }
        return false;
    }
    
    private function insertRecord($db, $table, $record) {
        $columns = implode(', ', array_keys($record));
        $values = implode(', ', array_map(function($value) use ($db) {
            return $value === null ? 'NULL' : "'" . $db->real_escape_string($value) . "'";
        }, $record));
        
        $query = "INSERT INTO $table ($columns) VALUES ($values)";
        if (!$db->query($query)) {
            throw new Exception("Failed to insert record into $table: " . $db->error);
        }
    }
    
    private function updateRecord($db, $table, $record, $primaryKey) {
        $sets = [];
        foreach ($record as $key => $value) {
            if ($key !== $primaryKey) {
                $sets[] = "$key = " . ($value === null ? 'NULL' : 
                    "'" . $db->real_escape_string($value) . "'");
            }
        }
        
        $query = "UPDATE $table SET " . implode(', ', $sets) . 
                 " WHERE $primaryKey = '" . $db->real_escape_string($record[$primaryKey]) . "'";
        
        if (!$db->query($query)) {
            throw new Exception("Failed to update record in $table: " . $db->error);
        }
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // Also output to console if running from command line
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
    
    public function __destruct() {
        // Close database connections
        if ($this->localDb) {
            $this->localDb->close();
        }
        if ($this->remoteDb) {
            $this->remoteDb->close();
        }
    }
}

// Usage example
try {
    $sync = new DatabaseSync();
    $sync->sync();
} catch (Exception $e) {
    $errorMessage = date('Y-m-d H:i:s') . " Error: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/sync_error.txt', $errorMessage, FILE_APPEND);
    
    // Output error to console if running from command line
    if (php_sapi_name() === 'cli') {
        echo $errorMessage;
    }
}