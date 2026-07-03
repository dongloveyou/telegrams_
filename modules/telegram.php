<?php
use \danog\MadelineProto\API;

class TelegramModule {
    private $mp;

    public function __construct() {
        // Nâng cấp: Nhúng cấu hình cứng API cá nhân để chặn hoàn toàn lỗi API_ID_INVALID
        $settings = [
            'app_info' => [
                'api_id'   => 32970188,
                'api_hash' => '86aee175983f1c4e3696c3159501aeaf'
            ],
            'logger' => [
                'max_size' => 0 // Tắt ghi log rác hệ thống để tăng tốc độ trên Termux
            ]
        ];

        // Khởi tạo thực thể theo cấu hình chuẩn tương thích MadelineProto 8.7.0
        $this->mp = new API("session_file.madeline", $settings);
        $this->mp->start();
    }

    // Tính năng 1: Quét tìm tin nhắn của đối tượng mục tiêu trong nhóm
    public function collectUserMessages($searchValue) {
        try {
            echo "[*] Đang tải danh sách các hội thoại (Dialogs)...\n";
            $dialogs = $this->mp->getDialogs();

            foreach ($dialogs as $peer) {
                $info = $this->mp->getInfo($peer);
                if (in_array($info['type'], ['chat', 'supergroup'])) {
                    $groupName = $info['Chat']['title'] ?? 'Unknown Group';
                    echo "[*] Quét mục tiêu trong nhóm: {$groupName}...\n";

                    try {
                        $history = $this->mp->messages->getHistory(['peer' => $peer, 'limit' => 100]);
                        foreach ($history['messages'] as $msg) {
                            if (isset($msg['from_id'])) {
                                $fromId = $msg['from_id']['user_id'] ?? null;
                                if ($fromId == $searchValue || (isset($msg['message']) && strpos($msg['message'], $searchValue) !== false)) {
                                    Database::insertMessage($searchValue, $fromId, $groupName, $msg['message'] ?? '', $msg['date']);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Bỏ qua nếu nhóm phân quyền chặn đọc lịch sử tin nhắn
                    }
                }
            }
            echo "[+] Quá trình quét hoàn tất!\n";
        } catch (\Exception $e) {
            echo "[-] Lỗi kết nối Telegram: " . $e->getMessage() . "\n";
        }
    }

    // Tính năng 2: Thu thập thông tin nhóm & Danh sách thành viên (OSINT)
    public function collectGroupInfo($groupLink) {
        try {
            $groupInfo = $this->mp->getInfo($groupLink);
            $groupName = $groupInfo['Chat']['title'] ?? 'Unknown';
            $groupId = $groupInfo['bot_api_id'] ?? 'Unknown';

            echo "[*] Đang phân tích cấu trúc nhóm: $groupName...\n";
            
            $fileReport = "🛡️ Group Information 🛡️\nGroup Name: $groupName\nGroup ID: $groupId\n" . str_repeat("=", 40) . "\n";

            $participants = $this->mp->getPaging($groupLink);
            $idx = 1;
            foreach ($participants as $p) {
                if (isset($p['user_id'])) {
                    $u = $this->mp->getInfo($p['user_id']);
                    $fullName = ($u['User']['first_name'] ?? '') . ' ' . ($u['User']['last_name'] ?? '');
                    $username = $u['User']['username'] ?? 'No Username';
                    
                    $fileReport .= "Member #$idx:\n  Full Name  : $fullName\n  Username   : @$username\n  Telegram ID: " . $p['user_id'] . "\n" . str_repeat("-", 40) . "\n";
                    $idx++;
                }
            }

            file_put_contents("{$groupName}_info.txt", $fileReport);
            echo "[+] Dữ liệu thành viên nhóm đã xuất thành công vào file: {$groupName}_info.txt\n";
        } catch (\Exception $e) {
            echo "[-] Thất bại khi thu thập thông tin nhóm: " . $e->getMessage() . "\n";
        }
    }

    // Tính năng 3: Thống kê tần suất tương tác của mục tiêu
    public function collectMessageStats($searchValue) {
        try {
            $dialogs = $this->mp->getDialogs();
            $statsReport = "User Target: $searchValue\n" . str_repeat("=", 30) . "\n";

            foreach ($dialogs as $peer) {
                $info = $this->mp->getInfo($peer);
                if (in_array($info['type'], ['chat', 'supergroup'])) {
                    $groupName = $info['Chat']['title'] ?? 'Unknown';
                    try {
                        $history = $this->mp->messages->getHistory(['peer' => $peer, 'limit' => 200]);
                        $count = 0;
                        foreach ($history['messages'] as $msg) {
                            if (isset($msg['from_id']['user_id']) && $msg['from_id']['user_id'] == $searchValue) {
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            echo "Group: $groupName - Số tin nhắn: $count\n";
                            $statsReport .= "Group: $groupName - Count: $count\n";
                        }
                    } catch (\Exception $e) {}
                }
            }
            file_put_contents("{$searchValue}_message_statistics.txt", $statsReport);
            echo "[+] Thống kê mật độ tương tác hoàn tất.\n";
        } catch (\Exception $e) {
            echo "[-] Lỗi thống kê: " . $e->getMessage() . "\n";
        }
    }

    // Tính năng 4: Tải xuống file đa phương tiện hàng loạt từ nhóm
    public function downloadGroupMedia($groupLink) {
        if (!is_dir("media_downloads")) {
            mkdir("media_downloads", 0777, true);
        }

        try {
            echo "[*] Đang quét danh sách file đa phương tiện (Giới hạn 50 tin nhắn gần nhất)...\n";
            $history = $this->mp->messages->getHistory(['peer' => $groupLink, 'limit' => 50]);

            foreach ($history['messages'] as $msg) {
                if (isset($msg['media'])) {
                    echo "[*] Đang tải xuống dữ liệu từ Message ID: " . $msg['id'] . "...\n";
                    $output = $this->mp->downloadToDir($msg, "media_downloads/");
                    echo "[+] Đã lưu: $output\n";
                }
            }
        } catch (\Exception $e) {
            echo "[-] Không thể tải tập tin: " . $e->getMessage() . "\n";
        }
    }
}
