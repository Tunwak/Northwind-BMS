<?php
declare(strict_types=1);

$forwardedProto = strtolower(trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0] ?? ''));
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https';
session_set_cookie_params(['httponly' => true, 'secure' => $https, 'samesite' => 'Lax', 'path' => '/']);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function respond($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $cfg = require __DIR__ . '/config.php';
    $dsn = 'mysql:host=' . $cfg['host'] . ';port=' . $cfg['port'] . ';dbname=' . $cfg['database'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
function body(): array {
    $value = json_decode(file_get_contents('php://input'), true);
    return is_array($value) ? $value : $_POST;
}
function requireAuth(): void {
    if (empty($_SESSION['user'])) respond(['error' => 'กรุณาเข้าสู่ระบบก่อน'], 401);
}
function tableMap(): array {
    return [
        'products' => ['tb_products', 'i_ProductID', ['c_ProductName', 'i_SupplierID', 'i_CategoryID', 'c_Unit', 'i_Price']],
        'categories' => ['tb_categories', 'i_CategoryID', ['c_CategoryName', 'c_Description']],
        'customers' => ['tb_customers', 'i_customerid', ['c_customername', 'c_contactname', 'c_address', 'c_city', 'c_postalcode', 'c_country']],
        'suppliers' => ['tb_suppliers', 'i_SupplierID', ['c_SupplierName', 'c_ContactName', 'c_Address', 'c_City', 'c_PostalCode', 'c_Country', 'c_Phone']],
        'employees' => ['tb_employees', 'i_EmployeeID', ['c_LastName', 'c_FirstName', 'c_BirthDate', 'c_Photo', 'c_Notes']],
        'shippers' => ['tb_shippers', 'i_ShipperID', ['c_ShipperName', 'c_Phone']],
        'orders' => ['tb_orders', 'i_OrderID', ['i_CustomerID', 'i_EmployeeID', 'c_OrderDate', 'i_ShipperID']],
        'orderdetails' => ['tb_orderdetails', 'i_OrderDetailID', ['i_OrderID', 'i_ProductID', 'i_Quantity']],
    ];
}
function foreignKeys(): array {
    return [
        'i_SupplierID' => ['tb_suppliers', 'i_SupplierID'],
        'i_CategoryID' => ['tb_categories', 'i_CategoryID'],
        'i_CustomerID' => ['tb_customers', 'i_customerid'],
        'i_customerid' => ['tb_customers', 'i_customerid'],
        'i_EmployeeID' => ['tb_employees', 'i_EmployeeID'],
        'i_ShipperID' => ['tb_shippers', 'i_ShipperID'],
        'i_OrderID' => ['tb_orders', 'i_OrderID'],
        'i_ProductID' => ['tb_products', 'i_ProductID'],
    ];
}
function existsById(PDO $pdo, string $table, string $column, int $id): bool {
    $allowed = array_merge(array_values(array_map(fn($v) => [$v[0], $v[1]], tableMap())), array_values(foreignKeys()));
    foreach ($allowed as [$allowedTable, $allowedColumn]) {
        if ($allowedTable === $table && $allowedColumn === $column) {
            $st = $pdo->prepare("SELECT 1 FROM `$table` WHERE `$column` = ? LIMIT 1");
            $st->execute([$id]);
            return (bool)$st->fetchColumn();
        }
    }
    return false;
}
function validateRow(PDO $pdo, string $entity, array $row, array $fields): array {
    $values = [];
    foreach ($fields as $field) {
        $value = $row[$field] ?? null;
        if ($value === null || trim((string)$value) === '') respond(['error' => "กรุณากรอก {$field}"], 422);
        if (str_starts_with($field, 'i_') || $field === 'i_Price') {
            if (!is_numeric($value)) respond(['error' => "{$field} ต้องเป็นตัวเลข"], 422);
            if ((float)$value < 0 || (str_starts_with($field, 'i_') && (int)$value != (float)$value)) respond(['error' => "{$field} มีค่าไม่ถูกต้อง"], 422);
            if ($field === 'i_Quantity' && (int)$value < 1) respond(['error' => 'จำนวนสินค้าต้องมากกว่า 0'], 422);
            $value = str_starts_with($field, 'i_') ? (int)$value : (float)$value;
        } else {
            $value = trim((string)$value);
        }
        if ($field === 'c_OrderDate') {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $dateParts)) {
                if (!checkdate((int)$dateParts[2], (int)$dateParts[1], (int)$dateParts[3])) respond(['error' => 'วันที่คำสั่งซื้อไม่ถูกต้อง'], 422);
            } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $dateParts)) {
                if (!checkdate((int)$dateParts[2], (int)$dateParts[3], (int)$dateParts[1])) respond(['error' => 'วันที่คำสั่งซื้อไม่ถูกต้อง'], 422);
            } else {
                respond(['error' => 'วันที่คำสั่งซื้อให้ใช้รูปแบบ d/m/yyyy หรือ yyyy-mm-dd'], 422);
            }
        }
        $fk = foreignKeys()[$field] ?? null;
        if ($fk && !existsById($pdo, $fk[0], $fk[1], (int)$value)) respond(['error' => "ไม่พบข้อมูลอ้างอิงสำหรับ {$field}"], 422);
        $values[$field] = $value;
    }
    return $values;
}
function nextId(PDO $pdo, string $table, string $pk): int {
    $st = $pdo->query("SELECT COALESCE(MAX(`$pk`), 0) + 1 FROM `$table`");
    return (int)$st->fetchColumn();
}
function listRows(PDO $pdo, string $entity, string $q, int $limit): array {
    $map = tableMap();
    if ($entity === 'orders') {
        $sql = "SELECT o.i_OrderID,o.c_OrderDate,o.i_CustomerID,c.c_customername,o.i_EmployeeID,CONCAT(e.c_FirstName,' ',e.c_LastName) employee,o.i_ShipperID,s.c_ShipperName,COALESCE(SUM(d.i_Quantity*p.i_Price),0) total
                FROM tb_orders o LEFT JOIN tb_customers c ON c.i_customerid=o.i_CustomerID LEFT JOIN tb_employees e ON e.i_EmployeeID=o.i_EmployeeID LEFT JOIN tb_shippers s ON s.i_ShipperID=o.i_ShipperID LEFT JOIN tb_orderdetails d ON d.i_OrderID=o.i_OrderID LEFT JOIN tb_products p ON p.i_ProductID=d.i_ProductID
                WHERE (:q = '' OR CAST(o.i_OrderID AS CHAR) LIKE :q2 OR c.c_customername LIKE :q3)
                GROUP BY o.i_OrderID ORDER BY o.i_OrderID DESC LIMIT $limit";
        $st = $pdo->prepare($sql);
        $st->execute(['q' => $q, 'q2' => "%$q%", 'q3' => "%$q%"]);
        return $st->fetchAll();
    }
    if ($entity === 'orderdetails') {
        $sql = "SELECT d.*,p.c_ProductName,o.c_OrderDate FROM tb_orderdetails d LEFT JOIN tb_products p ON p.i_ProductID=d.i_ProductID LEFT JOIN tb_orders o ON o.i_OrderID=d.i_OrderID
                WHERE (:q='' OR CAST(d.i_OrderDetailID AS CHAR) LIKE :q1 OR CAST(d.i_OrderID AS CHAR) LIKE :q2 OR p.c_ProductName LIKE :q3)
                ORDER BY d.i_OrderDetailID DESC LIMIT $limit";
        $st = $pdo->prepare($sql);
        $st->execute(['q' => $q, 'q1' => "%$q%", 'q2' => "%$q%", 'q3' => "%$q%"]);
        return $st->fetchAll();
    }
    if (!isset($map[$entity])) respond(['error' => 'ไม่พบรายการ'], 404);
    [$table, $pk, $fields] = $map[$entity];
    $where = '';
    $params = [];
    if ($q !== '') {
        $parts = [];
        foreach ($fields as $i => $field) {
            $parts[] = "CAST(`$field` AS CHAR) LIKE :q$i";
            $params["q$i"] = "%$q%";
        }
        $where = ' WHERE ' . implode(' OR ', $parts);
    }
    $st = $pdo->prepare("SELECT * FROM `$table`$where ORDER BY `$pk` DESC LIMIT $limit");
    $st->execute($params);
    return $st->fetchAll();
}
try {
    $pdo = db();
    $action = $_GET['action'] ?? '';
    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = body();
        $name = trim((string)($input['username'] ?? ''));
        $pass = (string)($input['password'] ?? '');
        $adminPass = (string)(getenv('APP_ADMIN_PASSWORD') ?: '');
        if ($name === 'admin' && $adminPass !== '' && hash_equals($adminPass, $pass)) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['name' => 'ผู้ดูแลระบบ'];
            respond(['user' => $_SESSION['user']]);
        }
        respond(['error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], 401);
    }
    if ($action === 'logout') { $_SESSION = []; session_destroy(); respond(['ok' => true]); }
    if ($action === 'me') respond(['user' => $_SESSION['user'] ?? null]);
    requireAuth();

    if ($action === 'list') {
        $entity = (string)($_GET['entity'] ?? '');
        $q = trim((string)($_GET['q'] ?? ''));
        $limit = max(1, min(500, (int)($_GET['limit'] ?? 200)));
        respond(listRows($pdo, $entity, $q, $limit));
    }
    if ($action === 'detail' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $st = $pdo->prepare("SELECT o.i_OrderID,o.c_OrderDate,o.i_CustomerID,c.c_customername,o.i_EmployeeID,CONCAT(e.c_FirstName,' ',e.c_LastName) employee,o.i_ShipperID,s.c_ShipperName FROM tb_orders o LEFT JOIN tb_customers c ON c.i_customerid=o.i_CustomerID LEFT JOIN tb_employees e ON e.i_EmployeeID=o.i_EmployeeID LEFT JOIN tb_shippers s ON s.i_ShipperID=o.i_ShipperID WHERE o.i_OrderID=?");
        $st->execute([$id]);
        $order = $st->fetch();
        if (!$order) respond(['error' => 'ไม่พบคำสั่งซื้อ'], 404);
        $st = $pdo->prepare('SELECT d.i_OrderDetailID,d.i_ProductID,p.c_ProductName,p.i_Price,d.i_Quantity,d.i_Quantity*p.i_Price line_total FROM tb_orderdetails d LEFT JOIN tb_products p ON p.i_ProductID=d.i_ProductID WHERE d.i_OrderID=?');
        $st->execute([$id]);
        $order['items'] = $st->fetchAll();
        respond($order);
    }
    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = body();
        $entity = (string)($input['entity'] ?? '');
        $map = tableMap();
        if (!isset($map[$entity])) respond(['error' => 'ไม่รองรับการแก้ไขรายการนี้'], 400);
        [$table, $pk, $fields] = $map[$entity];
        $row = $input['row'] ?? null;
        if (!is_array($row)) respond(['error' => 'รูปแบบ row ไม่ถูกต้อง'], 422);
        $values = validateRow($pdo, $entity, $row, $fields);
        $id = (int)($input['id'] ?? 0);
        if ($id > 0) {
            if (!existsById($pdo, $table, $pk, $id)) respond(['error' => 'ไม่พบรายการที่ต้องการแก้ไข'], 404);
            $set = implode(',', array_map(fn($field) => "`$field`=?", $fields));
            $st = $pdo->prepare("UPDATE `$table` SET $set WHERE `$pk`=?");
            $st->execute([...array_values($values), $id]);
        } else {
            if ($entity !== 'products') $values = [$pk => nextId($pdo, $table, $pk)] + $values;
            $columns = array_keys($values);
            $colSql = implode(',', array_map(fn($field) => "`$field`", $columns));
            $marks = implode(',', array_fill(0, count($columns), '?'));
            $st = $pdo->prepare("INSERT INTO `$table` ($colSql) VALUES ($marks)");
            $st->execute(array_values($values));
            if ($entity === 'products') $id = (int)$pdo->lastInsertId();
            else $id = (int)$values[$pk];
        }
        respond(['ok' => true, 'id' => $id]);
    }
    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = body();
        $entity = (string)($input['entity'] ?? '');
        $id = (int)($input['id'] ?? 0);
        $map = tableMap();
        if (!isset($map[$entity]) || $id < 1) respond(['error' => 'ข้อมูล entity หรือ id ไม่ถูกต้อง'], 400);
        [$table, $pk] = $map[$entity];
        if (!existsById($pdo, $table, $pk, $id)) respond(['error' => 'ไม่พบรายการที่ต้องการลบ'], 404);
        $dependencies = [
            'products' => ['tb_orderdetails', 'i_ProductID'],
            'categories' => ['tb_products', 'i_CategoryID'],
            'suppliers' => ['tb_products', 'i_SupplierID'],
            'customers' => ['tb_orders', 'i_CustomerID'],
            'employees' => ['tb_orders', 'i_EmployeeID'],
            'shippers' => ['tb_orders', 'i_ShipperID'],
        ];
        if (isset($dependencies[$entity])) {
            [$childTable, $childField] = $dependencies[$entity];
            $check = $pdo->prepare("SELECT 1 FROM `$childTable` WHERE `$childField`=? LIMIT 1");
            $check->execute([$id]);
            if ($check->fetchColumn()) respond(['error' => 'ลบไม่ได้ เพราะมีข้อมูลอื่นอ้างอิงรายการนี้'], 409);
        }
        if ($entity === 'orders') {
            $pdo->beginTransaction();
            $child = $pdo->prepare('DELETE FROM tb_orderdetails WHERE i_OrderID=?');
            $child->execute([$id]);
            $parent = $pdo->prepare('DELETE FROM tb_orders WHERE i_OrderID=?');
            $parent->execute([$id]);
            $pdo->commit();
        } else {
            $st = $pdo->prepare("DELETE FROM `$table` WHERE `$pk`=?");
            $st->execute([$id]);
        }
        respond(['ok' => true]);
    }
    if ($action === 'dashboard') {
        $out = [];
        foreach (['products' => 'tb_products', 'categories' => 'tb_categories', 'customers' => 'tb_customers', 'orders' => 'tb_orders', 'orderdetails' => 'tb_orderdetails', 'suppliers' => 'tb_suppliers', 'employees' => 'tb_employees', 'shippers' => 'tb_shippers'] as $key => $table) {
            $out[$key] = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        }
        $out['revenue'] = (float)$pdo->query('SELECT COALESCE(SUM(d.i_Quantity*p.i_Price),0) FROM tb_orderdetails d JOIN tb_products p ON p.i_ProductID=d.i_ProductID')->fetchColumn();
        $out['topProducts'] = $pdo->query('SELECT p.c_ProductName name,SUM(d.i_Quantity) quantity FROM tb_orderdetails d JOIN tb_products p ON p.i_ProductID=d.i_ProductID GROUP BY p.i_ProductID ORDER BY quantity DESC LIMIT 8')->fetchAll();
        $out['topCustomers'] = $pdo->query('SELECT c.c_customername name,COUNT(o.i_OrderID) orders FROM tb_customers c JOIN tb_orders o ON o.i_CustomerID=c.i_customerid GROUP BY c.i_customerid ORDER BY orders DESC LIMIT 8')->fetchAll();
        $out['monthly'] = $pdo->query("SELECT SUBSTRING_INDEX(c_OrderDate,'/',-1) year, SUBSTRING_INDEX(SUBSTRING_INDEX(c_OrderDate,'/',2),'/',-1) month,COUNT(*) orders FROM tb_orders GROUP BY year,month ORDER BY year,month")->fetchAll();
        respond($out);
    }
    respond(['error' => 'ไม่พบ API'], 404);
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    error_log((string)$e);
    respond(['error' => 'ระบบทำรายการไม่สำเร็จ โปรดตรวจสอบข้อมูลและการเชื่อมต่อฐานข้อมูล'], 500);
}
