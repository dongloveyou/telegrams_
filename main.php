<?php
if (!file_exists('vendor/autoload.php')) {
    die("[-] Hãy cài đặt thư viện lõi bằng câu lệnh: composer require danog/madelineproto\n");
}

require_once 'vendor/autoload.php';
require_once 'modules/display.php';
require_once 'modules/database.php';
require_once 'modules/telegram.php';

// Khởi tạo môi trường
Database::init();
$tg = new TelegramModule();

while (true) {
    Display::printBanner();
    Display::printMenu();
    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case "1":
            echo "Nhập Username hoặc Telegram ID đối tượng cần quét: ";
            $target = trim(fgets(STDIN));
            $tg->collectUserMessages($target);
            break;
        case "2":
            echo "Nhập liên kết nhóm hoặc Username nhóm (VD: @group_name): ";
            $link = trim(fgets(STDIN));
            $tg->collectGroupInfo($link);
            break;
        case "3":
            echo "Nhập Username hoặc ID đối tượng để thống kê mật độ: ";
            $target = trim(fgets(STDIN));
            $tg->collectMessageStats($target);
            break;
        case "4":
            echo "Nhập liên kết nhóm để tải đa phương tiện: ";
            $link = trim(fgets(STDIN));
            $tg->downloadGroupMedia($link);
            break;
        case "5":
            echo "Đang dừng chương trình...\n";
            exit(0);
        default:
            echo "Lựa chọn không hợp lệ. Vui lòng thử lại!\n";
    }
    
    echo "\nBấm Enter để quay lại menu chính...";
    fgets(STDIN);
}
