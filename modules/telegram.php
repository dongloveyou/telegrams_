<?php
use \danog\MadelineProto\API;

class TelegramModule {
    private $mp;

    public function __construct() {
        $settings = [
            "logger" => ["max_size" => 0],
            "serialization" => ["cleanup_before_serialization" => true]
        ];
        $this->mp = new API("session_file.madeline", $settings);
        $this->mp->start();
    }

    public function collectUserMessages($searchValue) {
        try {
            echo "[*] Dang tai danh sach cac hoi thoai (Dialogs)...\n";
            $dialogs = $this->mp->getDialogs();
            foreach ($dialogs as $peer) {
                $info = $this->mp->getInfo($peer);
                if (in_array($info["type"], ["chat", "supergroup"])) {
                    $groupName = $info["Chat"]["title"] ?? "Unknown Group";
                    echo "[*] Quet tu khoa/ID muc tieu trong nhom: {$groupName}...\n";
                    try {
                        $history = $this->mp->messages->getHistory(["peer" => $peer, "limit" => 100]);
                        foreach ($history["messages"] as $msg) {
                            if (isset($msg["from_id"])) {
                                $fromId = $msg["from_id"]["user_id"] ?? null;
                                if ($fromId == $searchValue || (isset($msg["message"]) && strpos($msg["message"], $searchValue) !== false)) {
                                    Database::insertMessage($searchValue, $fromId, $groupName, $msg["message"] ?? "", $msg["date"]);
                                }
                            }
                        }
                    } catch (\Exception $e) {}
                }
            }
            echo "[+] Qua trinh quet hoan tat!\n";
        } catch (\Exception $e) {
            echo "[-] Loi Telegram: " . $e->getMessage() . "\n";
        }
    }

    public function collectGroupInfo($groupLink) {
        try {
            $groupInfo = $this->mp->getInfo($groupLink);
            $groupName = $groupInfo["Chat"]["title"] ?? "Unknown";
            $groupId = $groupInfo["bot_api_id"] ?? "Unknown";
            echo "[*] Dang doc cau truc nhom: $groupName...\n";
            $fileReport = "Group Name: $groupName\nGroup ID: $groupId\n" . str_repeat("=", 40) . "\n";
            $participants = $this->mp->getPaging($groupLink);
            $idx = 1;
            foreach ($participants as $p) {
                if (isset($p["user_id"])) {
                    $u = $this->mp->getInfo($p["user_id"]);
                    $fullName = ($u["User"]["first_name"] ?? "") . " " . ($u["User"]["last_name"] ?? "");
                    $username = $u["User"]["username"] ?? "No Username";
                    $fileReport .= "Member #$idx:\n  Full Name  : $fullName\n  Username   : @$username\n  Telegram ID: " . $p["user_id"] . "\n" . str_repeat("-", 40) . "\n";
                    $idx++;
                }
            }
            file_put_contents("{$groupName}_info.txt", $fileReport);
            echo "[+] Du lieu nhom da luu vao file.\n";
        } catch (\Exception $e) {
            echo "[-] That bai: " . $e->getMessage() . "\n";
        }
    }

    public function collectMessageStats($searchValue) {
        try {
            $dialogs = $this->mp->getDialogs();
            $statsReport = "User Target: $searchValue\n" . str_repeat("=", 30) . "\n";
            foreach ($dialogs as $peer) {
                $info = $this->mp->getInfo($peer);
                if (in_array($info["type"], ["chat", "supergroup"])) {
                    $groupName = $info["Chat"]["title"] ?? "Unknown";
                    try {
                        $history = $this->mp->messages->getHistory(["peer" => $peer, "limit" => 200]);
                        $count = 0;
                        foreach ($history["messages"] as $msg) {
                            if (isset($msg["from_id"]["user_id"]) && $msg["from_id"]["user_id"] == $searchValue) {
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            echo "Group: $groupName - So tin nhan: $count\n";
                            $statsReport .= "Group: $groupName - Count: $count\n";
                        }
                    } catch (\Exception $e) {}
                }
            }
            file_put_contents("{$searchValue}_message_statistics.txt", $statsReport);
            echo "[+] Thong ke hoan tat.\n";
        } catch (\Exception $e) {
            echo "[-] Loi: " . $e->getMessage() . "\n";
        }
    }

    public function downloadGroupMedia($groupLink) {
        if (!is_dir("media_downloads")) {
            mkdir("media_downloads", 0777, true);
        }
        try {
            echo "[*] Dang tai danh sach tep tin da phuong tien...\n";
            $history = $this->mp->messages->getHistory(["peer" => $groupLink, "limit" => 50]);
            foreach ($history["messages"] as $msg) {
                if (isset($msg["media"])) {
                    echo "[*] Dang tai xuong file tu Message ID: " . $msg["id"] . "...\n";
                    $output = $this->mp->downloadToDir($msg, "media_downloads/");
                    echo "[+] Hoan thanh: $output\n";
                }
            }
        } catch (\Exception $e) {
            echo "[-] Khong the tai file phuong tien: " . $e->getMessage() . "\n";
        }
    }
}
