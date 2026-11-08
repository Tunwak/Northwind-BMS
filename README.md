# Northwind BMS

ระบบจัดการข้อมูลธุรกิจ Northwind ภาษาไทย พัฒนาด้วย PHP 8+, PDO และ MySQL/MariaDB ใช้ Bootstrap 5 สำหรับ UI แบบ responsive โดยแยกไฟล์หน้าเว็บ, stylesheet, JavaScript และ JSON API ออกจากกัน ไม่มี ORM หรือ backend framework

## URL และสิ่งส่งงาน

- **Public URL:** https://northwind-bms-production.up.railway.app/
- **Source Code:** ซอร์สโค้ดใน repository นี้ หรือแพ็กเกจ [Northwind-BMS-source.zip](Northwind-BMS-source.zip)
- **API Documentation (Web):** [เปิดหน้าเอกสาร API](https://northwind-bms-production.up.railway.app/api-docs.html)
- **Download:** ปุ่มดาวน์โหลด Markdown ในหน้าเอกสาร หรือ [API.md](API.md)

> Public URL ต้องมี Railway deployment สถานะ Active พร้อม MySQL variables และข้อมูล SQL ครบก่อนใช้งาน

## 12 หน้าจอ

1. แดชบอร์ด
2. สินค้า
3. หมวดหมู่สินค้า
4. คำสั่งซื้อ
5. รายการสินค้าในคำสั่งซื้อ
6. ลูกค้า
7. ผู้จัดจำหน่าย
8. พนักงาน
9. บริษัทขนส่ง
10. รายงานภาพรวม
11. รายงานรายเดือน
12. สินค้าขายดี

## ความสามารถ

- CRUD พร้อม validation สำหรับตารางธุรกิจหลักทั้ง 8 ตาราง: products, categories, orders, orderdetails, customers, suppliers, employees และ shippers
- ฟอร์มใช้ dropdown สำหรับข้อมูล Foreign Key และ backend ตรวจว่าข้อมูลอ้างอิงมีอยู่จริง
- ลบคำสั่งซื้อจะลบรายการสินค้าในคำสั่งซื้อนั้นด้วย transaction; ป้องกันการลบข้อมูลหลักที่ยังมีรายการอื่นอ้างอิง
- Dashboard และรายงานสรุปจำนวน รายได้ สินค้าขายดี ลูกค้าตามจำนวนคำสั่งซื้อ และยอดคำสั่งซื้อรายเดือน
- ส่งออก CSV และพิมพ์/บันทึกเป็น PDF ผ่าน Print ของเบราว์เซอร์
- Login แบบ session และป้องกัน endpoint ข้อมูลด้วย authentication

## โครงสร้างไฟล์

- `index.php` — HTML shell, Bootstrap 5 layout และฟอร์ม login
- `assets/app.css` — responsive styling
- `assets/app.js` — UI, validation ฝั่ง client และเรียก JSON API
- `api.php` — backend, server-side validation, PDO และ CRUD
- `config.php` — อ่านค่าฐานข้อมูลจาก environment variables
- `northwind.sql` — schema และข้อมูลตัวอย่าง
- `Dockerfile` — PHP CLI พร้อม `pdo_mysql` สำหรับ Railway
- `API.md` — API documentation พร้อมตัวอย่าง request/response
- `api-docs.html` — หน้าเอกสาร API ที่เปิดได้จากเว็บ
- `download-api-docs.php` — endpoint ดาวน์โหลด Markdown

## ติดตั้งและรันในเครื่อง

1. ติดตั้ง PHP 8+ โดยเปิด extension `pdo_mysql` และติดตั้ง MySQL 8 หรือ MariaDB
2. นำเข้า `northwind.sql`; ฐานข้อมูลต้นทางชื่อ `db_northwind`
3. ตั้งค่า `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` และ `APP_ADMIN_PASSWORD`
4. รัน `php -S 127.0.0.1:8000` แล้วเปิด <http://127.0.0.1:8000>

## Deploy บน Railway

1. Push source code ไป GitHub และเชื่อม repository กับ Railway service
2. เพิ่ม MySQL service แล้ว import SQL เข้า database ที่ Railway สร้างให้ โดยแก้ `USE` ในสำเนา SQL ให้ตรงกับ `MYSQLDATABASE`
3. ใน web service ตั้ง variable references: `DB_HOST` → `MYSQLHOST`, `DB_PORT` → `MYSQLPORT`, `DB_NAME` → `MYSQLDATABASE`, `DB_USER` → `MYSQLUSER`, `DB_PASS` → `MYSQLPASSWORD`
4. ตั้ง `APP_ADMIN_PASSWORD` เป็นรหัสผ่านผู้ดูแลระบบที่คาดเดายาก
5. Push `Dockerfile` และ source ล่าสุด แล้วรอ deployment เป็น Active

## SQL safety

ค่าที่รับจากผู้ใช้ถูกผูกผ่าน PDO prepared statements (`PDO::ATTR_EMULATE_PREPARES=false`). ชื่อตารางและคอลัมน์ที่ประกอบใน query มาจาก allowlist ภายใน API เท่านั้น ส่วน `LIMIT` ถูกแปลงเป็นจำนวนเต็มและจำกัดช่วงไว้ ห้ามต่อ input จากผู้ใช้เข้า SQL โดยตรง

ห้าม commit หรือส่งต่อ credentials ในภาพ/logs ใช้ HTTPS และจำกัดสิทธิ์ฐานข้อมูลเท่าที่จำเป็น