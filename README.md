# Northwind BMS

ระบบจัดการธุรกิจ Northwind ภาษาไทย พัฒนาด้วย PHP 8+, PDO และ MySQL โดยแยกส่วนหน้าเว็บไว้ใน `index.php`, `assets/app.css`, `assets/app.js` และแยก JSON API ไว้ใน `api.php` ไม่มี ORM หรือ framework สำเร็จรูป

## URL และไฟล์ส่งงาน

- **Public URL:** https://northwind-bms-production.up.railway.app/
- **Source Code:** ซอร์สโค้ดใน repository นี้ (`index.php`, `api.php`, `config.php`, `assets/`, `northwind.sql`, `Dockerfile`)
- **API Documentation:** [API.md](API.md)

> URL จะเข้าใช้งานได้เมื่อ Railway deployment เป็น Active และตั้งค่า MySQL variables ครบแล้ว

## ความสามารถ

- Dashboard สรุปจำนวนสินค้า คำสั่งซื้อ ลูกค้า และรายได้ประมาณการ
- หน้ารายงานพร้อมกราฟสินค้าขายดีและลูกค้าที่มีคำสั่งซื้อมาก
- ส่งออกข้อมูล CSV และพิมพ์/บันทึกรายงานเป็น PDF ผ่านหน้าต่าง Print ของเบราว์เซอร์
- ค้นหา เพิ่ม แก้ไข และลบสินค้า หมวดหมู่ ลูกค้า ผู้จัดจำหน่าย พนักงาน และบริษัทขนส่ง
- ค้นหาคำสั่งซื้อและดูรายละเอียดรายการสินค้าในคำสั่งซื้อ
- Login ด้วย session; API ป้องกัน endpoint ที่อ่านหรือแก้ไขข้อมูลด้วย session authentication

## โครงสร้าง

- `index.php` — โครง HTML และหน้าล็อกอิน
- `assets/app.css` — รูปแบบและ responsive layout
- `assets/app.js` — การแสดงผลหน้าเว็บและเรียก JSON API
- `api.php` — backend endpoints, ตรวจสอบข้อมูล และคำสั่ง PDO
- `config.php` — อ่านการตั้งค่าฐานข้อมูลจาก environment variables
- `northwind.sql` — schema และข้อมูลตัวอย่าง
- `Dockerfile` — PHP CLI image พร้อม `pdo_mysql` สำหรับ Railway

## ติดตั้งและรันในเครื่อง

1. ติดตั้ง PHP 8+ โดยเปิด extension `pdo_mysql` และติดตั้ง MySQL 8 หรือ MariaDB
2. นำเข้า `northwind.sql`; สคริปต์สร้างฐานข้อมูล `db_northwind`
3. ตั้งค่า `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` และ `APP_ADMIN_PASSWORD`
4. รัน `php -S 127.0.0.1:8000` แล้วเปิด <http://127.0.0.1:8000>

## Deploy บน Railway

1. Push source code ไปยัง GitHub และเชื่อม repository กับ Railway service
2. เพิ่ม MySQL service แล้ว import `northwind.sql` เข้า database ที่ Railway สร้างให้ (แก้ `USE` ในสำเนา SQL ให้ตรงกับ `MYSQLDATABASE` ก่อน import)
3. ใน service เว็บ เพิ่ม variable references: `DB_HOST` → `MYSQLHOST`, `DB_PORT` → `MYSQLPORT`, `DB_NAME` → `MYSQLDATABASE`, `DB_USER` → `MYSQLUSER`, `DB_PASS` → `MYSQLPASSWORD`
4. ตั้ง `APP_ADMIN_PASSWORD` เป็นรหัสผ่านเฉพาะที่คาดเดายาก
5. Push `Dockerfile` แล้วรอ deployment เป็น Active

## SQL และความปลอดภัย

ค่าที่ผู้ใช้ส่งให้ SQL ถูกผูกผ่าน PDO prepared statements (`PDO::ATTR_EMULATE_PREPARES=false`). ชื่อตาราง/คอลัมน์ที่ประกอบใน query มาจาก allowlist ภายใน API เท่านั้น ส่วน `LIMIT` ถูกแปลงเป็นจำนวนเต็มและจำกัดช่วงไว้ ห้ามนำ input ผู้ใช้ไปต่อใน SQL โดยตรง

ตั้ง `APP_ADMIN_PASSWORD` ในทุก environment ก่อน login; แอปไม่มีรหัสผ่านเริ่มต้นที่เปิดเผยในหน้า login. ห้าม commit credentials หรือส่งต่อ secret ในภาพ/logs. ใช้ HTTPS และจำกัดสิทธิ์บัญชีฐานข้อมูลตามความจำเป็น
