# Northwind BMS

ระบบจัดการข้อมูล Northwind ภาษาไทย สร้างด้วย PHP 8+, PDO และ MySQL/MariaDB โดยใช้ฐานข้อมูลจาก `northwind.sql`

## ความสามารถ

- เข้าสู่ระบบด้วย session
- แดชบอร์ดสรุปสินค้า คำสั่งซื้อ ลูกค้า และรายได้ประมาณการ
- ค้นหา เพิ่ม แก้ไข และลบสินค้า หมวดหมู่ ลูกค้า ผู้จัดจำหน่าย พนักงาน และบริษัทขนส่ง
- ค้นหารายการคำสั่งซื้อ ดูรายละเอียดสินค้าในคำสั่งซื้อ
- กราฟสินค้าขายดี/ลูกค้าตามจำนวนคำสั่งซื้อ และส่งออกข้อมูลเป็น CSV
- API แบบ JSON ใน `api.php` ใช้ PDO prepared statements

## ติดตั้งบนเครื่อง

1. ติดตั้ง PHP 8 ขึ้นไป โดยเปิดใช้ `pdo_mysql` และติดตั้ง MySQL 8 หรือ MariaDB
2. นำเข้า `northwind.sql` ผ่าน phpMyAdmin หรือรัน `mysql -u root -p < northwind.sql` ฐานข้อมูลที่สคริปต์สร้างชื่อ `db_northwind`
3. ตั้งค่าการเชื่อมต่อด้วย environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` หรือแก้ค่าใน `config.php`
4. เปลี่ยนรหัสผ่านผู้ดูแลระบบก่อนใช้งาน โดยกำหนด `APP_ADMIN_PASSWORD` เป็นรหัสผ่านที่คาดเดายาก (ค่าเริ่มต้นสำหรับเครื่องพัฒนาคือ `ChangeMe123!`)
5. เปิด terminal ในโฟลเดอร์นี้แล้วรัน `php -S 127.0.0.1:8000`
6. เปิด `http://127.0.0.1:8000`

## API

ทุก endpoint ยกเว้น `login`, `logout`, `me` ต้องเข้าสู่ระบบก่อน

- `POST api.php?action=login` JSON `{ "username": "admin", "password": "..." }`
- `GET api.php?action=list&entity=products&q=tea`
- `POST api.php?action=save` JSON `{ "entity": "products", "id": 1, "row": { ... } }` (ไม่ส่ง `id` เพื่อเพิ่ม)
- `POST api.php?action=delete` JSON `{ "entity": "products", "id": 1 }`
- `GET api.php?action=detail&id=10248`
- `GET api.php?action=dashboard`

ค่าในตารางเดิมบางตารางไม่ได้กำหนด AUTO_INCREMENT ไว้ใน SQL ต้นทาง แอปจะกำหนดรหัสถัดไปให้ตอนเพิ่มรายการ ส่วนรหัสสินค้าใช้ AUTO_INCREMENT จากฐานข้อมูล

## ก่อนเปิดให้ใช้งานผ่านอินเทอร์เน็ต

ตั้งค่า `APP_ADMIN_PASSWORD` เป็นความลับ, ใช้ HTTPS, จำกัดสิทธิ์ผู้ใช้ฐานข้อมูลให้เท่าที่จำเป็น และวางแอปไว้หลังเว็บเซิร์ฟเวอร์ที่รองรับ PHP เช่น Apache หรือ Nginx การเผยแพร่สู่ hosting ต้องใช้บัญชีและค่าฐานข้อมูลของ hosting ที่เจ้าของระบบจัดเตรียมไว้
