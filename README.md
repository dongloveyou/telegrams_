## 🎯 Mô Tả Công Cụ (Features & Capabilities)

**SILVERTGOSINT** là một giải pháp kiểm thử an ninh và khai thác thông tin tình báo nguồn mở (OSINT) chuyên biệt dành cho nền tảng Telegram. Công cụ hoạt động dưới dạng giao diện dòng lệnh (CLI), kết nối trực tiếp với máy chủ Telegram thông qua giao thức MTProto gốc, giúp tối ưu hóa tốc độ truy vấn, vượt qua các giới hạn của Bot API thông thường và giảm thiểu tỷ lệ bị quét tài khoản (Rate Limit).

### Các Tính Năng Cốt Lõi:

*   **🕵️ Quét và Thu Thập Tin Nhắn Mục Tiêu (Target Message Scraper):** Tự động duyệt qua toàn bộ các hội thoại, nhóm công khai/riêng tư mà tài khoản đang tham gia. Trích xuất toàn bộ lịch sử tin nhắn của một cá nhân dựa trên **Username** hoặc **Telegram ID**, sau đó tự động phân loại và lưu trữ.
*   **📊 Thống Kê Mật Độ Hoạt Động (Activity Statistics):** Phân tích và đo lường tần suất gửi tin nhắn của đối tượng trên từng hội thoại nhóm. Xuất báo cáo trực quan dưới dạng danh sách kèm số lượng tin nhắn cụ thể, giúp định vị các cộng đồng mà mục tiêu hoạt động tích cực nhất.
*   **🛡️ Trích Xuất Dữ Liệu Hội Nhóm (Group Intelligence):** Thu thập toàn diện siêu dữ liệu (Metadata) của nhóm mục tiêu bao gồm: *Tên nhóm, ID nội bộ, Tiểu sử (Bio), Quyền sở hữu*. Đồng thời quét toàn bộ danh sách thành viên, bóc tách các trường thông tin quan trọng như *Họ tên, Username, Telegram ID, và Số điện thoại công khai*.
*   **📁 Tải Xuống Tệp Đa Phương Tiện Hàng Loạt (Bulk Media Downloader):** Tự động lọc, phân loại và tải xuống hàng loạt các tệp hình ảnh, video được chia sẻ trong các hội nhóm về thư mục cục bộ phục vụ cho công tác điều tra hình ảnh địa lý hoặc phân tích tệp tin.
*   **🗄️ Cơ Chế Lưu Trữ Kép (Dual-Storage Architecture):** 
    *   **SQLite3:** Dữ liệu tin nhắn được chuẩn hóa và lưu vào Cơ sở dữ liệu để phục vụ việc truy vấn nhanh, tự động kiểm tra trùng lặp (Anti-deuplication) để không lưu đè dữ liệu cũ.
    *   **Text Report (.txt):** Tự động kết xuất ra các tệp văn bản riêng biệt cho từng đối tượng/nhóm giúp dễ dàng báo cáo hoặc chuyển giao dữ liệu.
 
    *   🚀 Hướng Dẫn Cài Đặt Trên Các Nền Tảng
1. Nền tảng Windows
Bước 1: Tải phiên bản PHP mới nhất (Khuyến nghị >= 8.1) và Tải Composer.

Bước 2: Mở tệp cấu hình php.ini trong thư mục cài đặt PHP của bạn, tìm và loại bỏ dấu chấm phẩy (;) ở đầu các dòng sau để kích hoạt tiện ích mở rộng:
Ini, TOML
extension=curl
extension=mbstring
extension=openssl
extension=sqlite3
Bước 3: Di chuyển Terminal (CMD / PowerShell) vào thư mục dự án và cài đặt thư viện lõi:
composer install

Bước 4: Khởi chạy công cụ:
php main.php

2. Nền tảng Kali Linux
Mở Terminal của Kali Linux lên và chạy chuỗi lệnh sau để cập nhật hệ thống, cấu hình môi trường PHP hoàn chỉnh và khởi động dự án
# Cập nhật danh sách gói và cài đặt các gói phụ thuộc cần thiết
sudo apt update
sudo apt install php php-cli php-curl php-xml php-mbstring php-sqlite3 composer -y

# Di chuyển vào thư mục chứa mã nguồn của bạn
cd /path/to/SILVERTGOSINT

# Cài đặt gói thư viện qua Composer
composer install

# Khởi chạy công cụ
php main.php




3. Nền tảng Termux (Android)
Đối với môi trường giả lập Termux trên thiết bị Android, các tiện ích mở rộng mở rộng của PHP đa số đã được tích hợp sẵn vào gói cài đặt chính.
# Bước 1: Cập nhật hệ thống Termux
pkg update && pkg upgrade -y

# Bước 2: Cài đặt PHP, Curl và Composer
pkg install php curl composer -y

# Bước 3: Di chuyển vào thư mục chứa code trong máy của bạn
# (Nếu lưu tại thư mục Download của điện thoại, sử dụng đường dẫn dưới đây)
cd /sdcard/Download/SILVERTGOSINT

# Bước 4: Khởi tạo cài đặt cấu trúc thư viện phụ thuộc
composer install

# Bước 5: Khởi chạy công cụ
php main.php

🔑 Hướng Dẫn Sử Dụng Lần Đầu
Khi khởi chạy tool thông qua lệnh php main.php lần đầu tiên, thư viện sẽ thiết lập kết nối giao thức an toàn với máy chủ Telegram.

Giao diện Terminal sẽ yêu cầu bạn cung cấp các thông tin xác thực tài khoản bao gồm:

Số điện thoại: Nhập số điện thoại tài khoản Telegram của bạn (Bắt buộc phải kèm mã vùng quốc gia, ví dụ: +84xxxxxxxxx).

Mã xác thực (OTP): Nhập mã số mà hệ thống Telegram gửi trực tiếp về ứng dụng của bạn.

Sau khi xác thực thành công, hệ thống tự động tạo tệp session_file.madeline để duy trì trạng thái đăng nhập cho những lần chạy tiếp theo mà không cần cấu hình lại.

⚠️ Khuyến Cáo Bảo Mật
Công cụ này được phát triển phục vụ mục đích học tập, nghiên cứu kỹ thuật kiểm thử an toàn thông tin và thu thập thông tin tình báo nguồn mở (OSINT).

Người dùng chịu trách nhiệm hoàn toàn đối với mọi hành vi sử dụng tài khoản cá nhân để thu thập dữ liệu bất hợp pháp hoặc vi phạm Điều khoản dịch vụ của Telegram.

