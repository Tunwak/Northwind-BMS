# Northwind BMS API

Base URL: `https://northwind-bms-production.up.railway.app/api.php`

ทุก endpoint ส่ง JSON (`application/json; charset=utf-8`) และใช้ session cookie. เรียก `login` ก่อน endpoint ที่ต้องยืนยันตัวตน. Endpoint สาธารณะคือ `login`, `logout`, `me`; endpoint อื่นต้องมี session.

## Authentication

### `POST ?action=login`

Request JSON:

```json
{"username":"admin","password":"<APP_ADMIN_PASSWORD>"}
```

เมื่อสำเร็จได้ `200` และ `{"user":{"name":"ผู้ดูแลระบบ"}}`; รหัสผิดได้ `401`. ตั้งรหัสผ่านผ่าน `APP_ADMIN_PASSWORD` เท่านั้น.

### `GET ?action=me`

ส่งคืน user ปัจจุบัน หรือ `{"user":null}` เมื่อยังไม่ login.

### `GET ?action=logout`

จบ session และส่งคืน `{"ok":true}`.

## Read endpoints

### `GET ?action=list&entity={entity}&q={search}&limit={limit}`

ค้นหาและแสดงรายการ; `limit` เริ่มต้น 200 และจำกัดระหว่าง 1–500. รองรับ entities:

- `products`: `i_ProductID`, `c_ProductName`, `i_SupplierID`, `i_CategoryID`, `c_Unit`, `i_Price`
- `categories`: `i_CategoryID`, `c_CategoryName`, `c_Description`
- `orders`: order ID/date, customer, employee, shipper และยอดรวมจาก order details
- `orderdetails`: detail ID, order ID/date, product ID/name และ quantity
- `customers`: `i_customerid`, `c_customername`, `c_contactname`, `c_address`, `c_city`, `c_postalcode`, `c_country`
- `suppliers`: `i_SupplierID`, `c_SupplierName`, `c_ContactName`, `c_Address`, `c_City`, `c_PostalCode`, `c_Country`, `c_Phone`
- `employees`: `i_EmployeeID`, `c_LastName`, `c_FirstName`, `c_BirthDate`, `c_Photo`, `c_Notes`
- `shippers`: `i_ShipperID`, `c_ShipperName`, `c_Phone`

Example: `GET ?action=list&entity=products&q=tea&limit=50`.

### `GET ?action=detail&id={orderId}`

ส่งคืนรายละเอียดคำสั่งซื้อพร้อม `items` ซึ่งมีสินค้า ราคา จำนวน และยอดต่อบรรทัด; ถ้าไม่พบได้ `404`.

### `GET ?action=dashboard`

ส่งคืน count ของตารางหลัก, `revenue`, `topProducts`, `topCustomers` และจำนวนคำสั่งซื้อตามเดือนใน `monthly`.

## Write endpoints

ทุก write เป็น JSON `POST`. ฟิลด์ที่กำหนดสำหรับ entity ต้องมีครบ; backend ตรวจชนิดตัวเลขและการมีอยู่ของ foreign-key records.

### `POST ?action=save`

ไม่ส่ง `id` เพื่อเพิ่ม; ส่ง `id` เพื่อแก้ไข. ตัวอย่างเพิ่มสินค้า:

```json
{
  "entity":"products",
  "row":{
    "c_ProductName":"Chai",
    "i_SupplierID":1,
    "i_CategoryID":1,
    "c_Unit":"10 boxes x 20 bags",
    "i_Price":18
  }
}
```

Update ใช้ body เดิมและเพิ่ม `"id":1`. รองรับ CRUD ทั้ง 8 entities ที่ระบุใน `list`.

### `POST ?action=delete`

```json
{"entity":"products","id":1}
```

ลบ record แล้วตอบ `{"ok":true}`. ป้องกันการลบ supplier/category/customer/employee/shipper/product ที่ยังถูกใช้อ้างอิง; ลบ order จะลบ order details ใน transaction เดียวกัน.

## Status codes

- `200` สำเร็จ
- `400` entity หรือ input ไม่ถูกต้อง
- `401` login จำเป็น หรือข้อมูล login ไม่ถูกต้อง
- `404` entity/action/record ไม่พบ
- `409` ลบไม่ได้เพราะ record ยังถูกอ้างอิง
- `422` validation ไม่ผ่าน
- `500` server/database error (รายละเอียดอยู่ใน server log)

## SQL safety

ค่าจาก client ใช้ PDO prepared statements และปิด emulated prepares. ตาราง/คอลัมน์ dynamic มาจาก allowlist เท่านั้น; limit cast เป็น integer และ clamp. ห้ามต่อค่าที่ผู้ใช้ส่งเข้า SQL โดยตรง.