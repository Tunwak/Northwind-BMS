# Northwind BMS — API Documentation

Base URL: `https://northwind-bms-production.up.railway.app/api.php`

All responses use JSON (`application/json; charset=utf-8`). The API uses a PHP session cookie. Call `login` first, then keep the cookie for authenticated requests. `login`, `logout`, and `me` are public; all other actions require an authenticated session.

## Authentication

### `POST ?action=login`

JSON body:

```json
{"username":"admin","password":"<APP_ADMIN_PASSWORD>"}
```

Success: `200` with `{"user":{"name":"ผู้ดูแลระบบ"}}`; invalid credentials: `401`.

### `GET ?action=me`

Returns the current user or `{"user":null}`.

### `GET ?action=logout`

Ends the current session and returns `{"ok":true}`.

## Read endpoints

### `GET ?action=list&entity={entity}&q={search}&limit={limit}`

Lists records, optionally searching supported fields. `limit` defaults to 200 and is clamped to 1–500.

Supported entities:

- `products`: `i_ProductID`, `c_ProductName`, `i_SupplierID`, `i_CategoryID`, `c_Unit`, `i_Price`
- `categories`: `i_CategoryID`, `c_CategoryName`, `c_Description`
- `customers`: `i_customerid`, `c_customername`, `c_contactname`, `c_address`, `c_city`, `c_postalcode`, `c_country`
- `suppliers`: `i_SupplierID`, `c_SupplierName`, `c_ContactName`, `c_Address`, `c_City`, `c_PostalCode`, `c_Country`, `c_Phone`
- `employees`: `i_EmployeeID`, `c_LastName`, `c_FirstName`, `c_BirthDate`, `c_Photo`, `c_Notes`
- `shippers`: `i_ShipperID`, `c_ShipperName`, `c_Phone`
- `orders`: order summary with customer, employee, shipper, date, and calculated total

Example: `GET ?action=list&entity=products&q=tea&limit=50`

### `GET ?action=detail&id={orderId}`

Returns order information and its `items` array, including product, unit price, quantity, and line total. Returns `404` if the order does not exist.

### `GET ?action=dashboard`

Returns aggregate data: counts for products, categories, customers, and orders; estimated revenue; top products; top customers; and monthly order counts.

## Write endpoints

Writes are JSON `POST` requests. Every field listed for an entity is required by the current validation.

### `POST ?action=save`

Create by omitting `id`; update by providing `id`.

```json
{
  "entity":"products",
  "id":1,
  "row":{
    "c_ProductName":"Chai",
    "i_SupplierID":1,
    "i_CategoryID":1,
    "c_Unit":"10 boxes x 20 bags",
    "i_Price":18
  }
}
```

The `row` fields must match the selected entity's allowlisted columns. Success returns `{"ok":true,"id":1}`. CRUD is supported for products, categories, customers, suppliers, employees, and shippers. Orders are read-only in this application.

### `POST ?action=delete`

```json
{"entity":"products","id":1}
```

Deletes the selected record and returns `{"ok":true}`. Delete is supported for the same six CRUD entities.

## Status codes and errors

- `200` — request succeeded
- `400` — unsupported write entity
- `401` — login required or credentials invalid
- `404` — unknown entity, API action, or missing order
- `422` — required field missing
- `500` — server/database failure; detailed exception is written to server logs, not returned to clients

## SQL safety

User-provided values are bound through PDO prepared statements. PDO emulated prepares are disabled. Dynamic table and column names are selected only from the API's fixed allowlist; the list limit is cast to an integer and clamped. Do not add SQL string concatenation with client-provided values.
