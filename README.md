# Workshop Booking Portal – PHPLab02

Ứng dụng PHP thuần (không framework, không database) minh họa **GET, POST, Validation, Sanitization, Cookies & Sessions**.
Dữ liệu booking được lưu trong PHP Session; catalog workshop nằm trong `data/workshops.php`.

Phần mở rộng của Lab02:

- **View Booking Detail** – `booking_detail.php?ref=BK-XXXXXX`
- **Edit Booking** – `booking_edit.php?ref=BK-XXXXXX` (GET prefill + POST update)

## Yêu cầu

- PHP **8.0+** (đã chạy thử với PHP 8.0.28 của XAMPP)
- XAMPP (Apache) hoặc PHP built-in server

## Cách chạy

### Cách 1 – XAMPP

1. Đặt thư mục dự án vào `htdocs`:
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/php-s07-workshop`
   - Windows: `C:\xampp\htdocs\php-s07-workshop`
2. Mở XAMPP Control Panel → **Start Apache**.
3. Truy cập: <http://localhost/php-s07-workshop/>

### Cách 2 – PHP built-in server

```bash
cd php-s07-workshop
php -S localhost:8000
```

Truy cập: <http://localhost:8000/>

## Cấu trúc thư mục

```
php-s07-workshop/
├── index.php            # Catalog: search (q) + filter (category) qua GET, form đổi theme
├── booking.php          # Form đặt chỗ: GET hiển thị, POST validate + lưu Session (PRG)
├── dashboard.php        # Danh sách booking trong Session + View Details / Edit
├── booking_detail.php   # [Lab02] Xem chi tiết booking theo ref (GET, chỉ đọc)
├── booking_edit.php     # [Lab02] Sửa booking: GET prefill, POST + CSRF + validate + 303
├── preference.php       # POST lưu cookie theme light/dark
├── logout.php           # POST xóa Session (Clear session & start over)
├── request_demo.php     # So sánh $_GET, $_POST, $_REQUEST
├── lib/
│   ├── bootstrap.php    # session_start, CSRF token, nạp catalog, đọc cookie theme
│   ├── functions.php    # e(), normalize_text(), validate_booking(), csrf_valid(),
│   │                    # redirect_to(), find_booking_index(), booking_workshop()
│   └── workshops.php
├── data/
│   └── workshops.php    # Catalog 3 workshop (id, title, category, fee, seats)
└── assets/
    └── style.css
```

## Các route chính

| Route | Method | Mô tả |
|---|---|---|
| `index.php?q=&category=` | GET | Tìm kiếm và lọc workshop |
| `booking.php?workshop_id=` | GET / POST | Form đặt chỗ / lưu booking |
| `dashboard.php` | GET | Danh sách booking |
| `booking_detail.php?ref=` | GET | Chi tiết một booking |
| `booking_edit.php?ref=` | GET | Form sửa booking (điền sẵn dữ liệu) |
| `booking_edit.php` | POST | Lưu thay đổi (cần `csrf_token`, `ref`) |
| `preference.php` | POST | Lưu cookie theme |
| `logout.php` | POST | Xóa Session |
| `request_demo.php` | GET / POST | Request inspector |

## Quy tắc nghiệp vụ & bảo mật

- **Validation phía server** (`validate_booking()`): họ tên bắt buộc, 2–120 bytes; email hợp lệ (`FILTER_VALIDATE_EMAIL`); số vé là số nguyên 1–4 (`FILTER_VALIDATE_INT`) và không vượt `seats`; workshop phải tồn tại trong catalog.
- **Sanitization & output escaping**: `normalize_text()` làm sạch input; mọi dữ liệu xuất ra HTML đi qua `e()` (`htmlspecialchars` + `ENT_QUOTES` + UTF-8).
- **CSRF**: mọi form POST có `csrf_token`, kiểm tra bằng `hash_equals()`; sai/thiếu token → HTTP 403.
- **POST/Redirect/GET**: sau POST luôn redirect HTTP 303, refresh không gửi lại form.
- **View Detail**: chỉ đọc Session, không thay đổi dữ liệu; `ref` thiếu/không tồn tại → 404 “Booking not found”, không warning.
- **Edit Booking**:
  - Chỉ cho sửa Full Name, Email, Tickets; `ref` và workshop không sửa được.
  - `workshop_id` và đơn giá luôn lấy từ catalog server; các field `price`, `total`, `workshop_id` do client gửi đều bị bỏ qua.
  - `amount = fee (server) × quantity (đã validate)`.
  - Dữ liệu sai: hiển thị lỗi cạnh field, giữ giá trị vừa nhập, Session không thay đổi.
  - Chỉ cập nhật đúng booking theo `ref`; `ref` không tồn tại → 404, không tạo/sửa booking khác.
- **Cookie vs Session**: theme lưu trong cookie (30 ngày) nên vẫn còn sau khi Clear Session; booking chỉ tồn tại trong Session.

## Kịch bản kiểm thử nhanh

| ID | Thao tác | Kết quả mong đợi |
|---|---|---|
| T01 | View ref hợp lệ | Hiện đúng bản ghi |
| T02 | View ref không tồn tại / thiếu ref | “Booking not found”, không warning |
| T03 | Edit tickets 2 → 3 | Amount tính lại đúng (vd. 450,000 × 3 = 1,350,000) |
| T04 | Edit email sai | Báo lỗi, Session không đổi |
| T05 | Edit tickets 0, 5, 1.5 | Đều bị từ chối |
| T06 | POST thiếu/sai CSRF | HTTP 403 |
| T07 | Refresh sau update | Không gửi lại POST |
| T08 | Có 2 booking, sửa 1 | Booking còn lại không đổi |
| T09 | Mở bằng cửa sổ private | Không thấy Session của trình duyệt trước |

## Giới hạn

- Dữ liệu chỉ nằm trong Session: đóng trình duyệt/hết hạn session là mất.
- Không có khóa đồng thời: sửa cùng booking ở hai tab thì tab lưu sau sẽ ghi đè.
- `seats` chỉ là giới hạn cho mỗi booking, chưa trừ số ghế còn lại.

## Thông tin sinh viên

- Họ tên: `...`
- MSSV: `...`
- Lớp: `...`
