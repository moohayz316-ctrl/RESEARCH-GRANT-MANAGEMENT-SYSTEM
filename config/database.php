<?php
// ============================================================
// Database Connection using PDO
// ============================================================

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Passwords to try in order: configured DB_PASS, then common AppServ/XAMPP defaults
        $passwords = [DB_PASS, '', 'root', '12345678', 'admin'];
        $passwords = array_values(array_unique($passwords));

        $connected = false;
        $lastError = null;

        foreach ($passwords as $pass) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $this->connection = new PDO($dsn, DB_USER, $pass, $options);
                $connected = true;
                break;
            } catch (PDOException $e) {
                $lastError = $e;

                // If error is 1049 (Unknown database), try connecting to server and creating the database
                if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                    try {
                        $serverPdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, $pass, $options);
                        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        
                        // Now connect to the new database
                        $this->connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, $pass, $options);
                        
                        // Import database.sql if available
                        $sqlFile = dirname(__DIR__) . '/database/database.sql';
                        if (file_exists($sqlFile)) {
                            $sql = file_get_contents($sqlFile);
                            if ($sql) {
                                $this->connection->exec($sql);
                            }
                        }
                        $connected = true;
                        break;
                    } catch (Exception $inner) {
                        $lastError = $inner;
                    }
                }
            }
        }

        if (!$connected) {
            $msg = $lastError ? htmlspecialchars($lastError->getMessage()) : 'Unknown error';
            $dbName = htmlspecialchars(DB_NAME);
            $dbUser = htmlspecialchars(DB_USER);
            echo "<!DOCTYPE html><html><head><title>Database Connection Error</title>";
            echo "<style>body{font-family:sans-serif;background:#060a12;color:#f5e6cc;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}";
            echo ".box{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:32px;max-width:560px;line-height:1.6;}";
            echo "h2{color:#ef4444;margin-top:0;}code{background:rgba(0,0,0,0.4);padding:3px 6px;border-radius:6px;color:#d4a373;}";
            echo ".tip{background:rgba(212,163,115,0.1);border-left:4px solid #d4a373;padding:12px;margin-top:16px;border-radius:0 8px 8px 0;}";
            echo "</style></head><body>";
            echo "<div class='box'>";
            echo "<h2>Database Connection Error</h2>";
            echo "<p>Could not connect to MySQL server with user <code>{$dbUser}</code> on <code>" . htmlspecialchars(DB_HOST) . "</code>.</p>";
            echo "<p><strong>MySQL Error:</strong> <code>{$msg}</code></p>";
            echo "<div class='tip'>";
            echo "<strong>How to fix:</strong><ol>";
            echo "<li>Make sure <strong>Apache and MySQL</strong> are started in AppServ.</li>";
            echo "<li>Open <code>http://localhost/phpmyadmin/</code> and create database <code>{$dbName}</code> if it doesn't exist, then import <code>database/database.sql</code>.</li>";
            echo "<li>Check your MySQL password in <code>config/config.php</code> line 14: <code>define('DB_PASS', 'your_password');</code>.</li>";
            echo "</ol></div></div></body></html>";
            exit();
        }

        // Auto-heal schema if missing tables or columns
        $this->autoHealSchema();
    }

    private function autoHealSchema() {
        try {
            // 1. Ensure fund_disbursements exists
            $tbl = $this->connection->query("SHOW TABLES LIKE 'fund_disbursements'")->fetch();
            if (!$tbl) {
                $this->connection->exec("CREATE TABLE IF NOT EXISTS `fund_disbursements` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `award_id` INT(11) NOT NULL,
                    `grant_id` INT(11) NOT NULL,
                    `researcher_id` INT(11) NOT NULL,
                    `disbursed_by` INT(11) DEFAULT NULL,
                    `stage` VARCHAR(100) NOT NULL,
                    `disbursed_amount` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
                    `payment_reference` VARCHAR(100) DEFAULT NULL,
                    `disbursement_date` DATE NOT NULL,
                    `status` ENUM('scheduled','paid','cancelled') NOT NULL DEFAULT 'paid',
                    `notes` TEXT DEFAULT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `award_id` (`award_id`),
                    KEY `grant_id` (`grant_id`),
                    KEY `researcher_id` (`researcher_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }

            // 2. Ensure proposal_reviews has recommended_amount
            $col = $this->connection->query("SHOW COLUMNS FROM `proposal_reviews` LIKE 'recommended_amount'")->fetch();
            if (!$col) {
                $this->connection->exec("ALTER TABLE `proposal_reviews` ADD COLUMN `recommended_amount` DECIMAL(15,2) DEFAULT NULL AFTER `recommendation`");
            }

            // 3. Ensure grant_awards has approved_amount and total_disbursed
            $colAppr = $this->connection->query("SHOW COLUMNS FROM `grant_awards` LIKE 'approved_amount'")->fetch();
            if (!$colAppr) {
                $this->connection->exec("ALTER TABLE `grant_awards` ADD COLUMN `approved_amount` DECIMAL(15,2) DEFAULT NULL AFTER `awarded_amount`");
            }
            $colDisb = $this->connection->query("SHOW COLUMNS FROM `grant_awards` LIKE 'total_disbursed'")->fetch();
            if (!$colDisb) {
                $this->connection->exec("ALTER TABLE `grant_awards` ADD COLUMN `total_disbursed` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `approved_amount`");
            }

            // 4. Ensure users.role only allows 3 roles
            $colRole = $this->connection->query("SHOW COLUMNS FROM `users` LIKE 'role'")->fetch();
            if ($colRole && strpos($colRole['Type'], 'grant_manager') !== false) {
                $this->connection->exec("UPDATE `users` SET `role` = 'admin' WHERE `role` = 'grant_manager'");
                $this->connection->exec("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','researcher','reviewer') NOT NULL DEFAULT 'researcher'");
            }
        } catch (Exception $e) {
            // Silently continue
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }
}

function getDb() {
    return Database::getInstance()->getConnection();
}
