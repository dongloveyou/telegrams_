<?php
class Database {
    private static $dbFile = 'telegram_messages.db';

    public static function init() {
        try {
            $db = new PDO("sqlite:" . self::$dbFile);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->exec("CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT,
                tg_id INTEGER,
                group_name TEXT,
                message_text TEXT,
                message_date TEXT
            )");
            echo "[+] Khởi tạo cấu trúc Database thành công.\n";
        } catch (PDOException $e) {
            echo "[-] Lỗi khởi tạo Database: " . $e->getMessage() . "\n";
        }
    }

    public static function insertMessage($username, $tg_id, $group_name, $message_text, $message_date) {
        try {
            $db = new PDO("sqlite:" . self::$dbFile);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE message_text = ? AND message_date = ? AND username = ?");
            $stmt->execute([$message_text, $message_date, $username]);
            
            if ($stmt->fetchColumn() == 0) {
                $insert = $db->prepare("INSERT INTO messages (username, tg_id, group_name, message_text, message_date) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$username, $tg_id, $group_name, $message_text, $message_date]);

                // Xuất file text bổ trợ
                $logContent = "Group: $group_name\nMessage: $message_text\nDate: $message_date\n" . str_repeat("-", 30) . "\n";
                file_put_contents("{$username}_messages.txt", $logContent, FILE_APPEND);
                echo "[+] Tin nhắn mới được thêm từ nhóm: $group_name\n";
            } else {
                echo "[*] Tin nhắn đã tồn tại trong DB: $group_name\n";
            }
        } catch (PDOException $e) {
            echo "[-] Lỗi xử lý Database: " . $e->getMessage() . "\n";
        }
    }
}

