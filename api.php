<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function respond($data, int $status = 200): void { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit; }
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $cfg = require __DIR__ . '/config.php';
    $dsn = 'mysql:host='.$cfg['host'].';port='.$cfg['port'].';dbname='.$cfg['database'].';charset=utf8mb4';
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
    return $pdo;
}
function body(): array { $v=json_decode(file_get_contents('php://input'),true); return is_array($v)?$v:$_POST; }
function requireAuth(): void { if (empty($_SESSION['user'])) respond(['error'=>'กรุณาเข้าสู่ระบบก่อน'],401); }
function tableMap(): array { return [
 'products'=>['tb_products','i_ProductID',['c_ProductName','i_SupplierID','i_CategoryID','c_Unit','i_Price']],
 'categories'=>['tb_categories','i_CategoryID',['c_CategoryName','c_Description']],
 'customers'=>['tb_customers','i_customerid',['c_customername','c_contactname','c_address','c_city','c_postalcode','c_country']],
 'suppliers'=>['tb_suppliers','i_SupplierID',['c_SupplierName','c_ContactName','c_Address','c_City','c_PostalCode','c_Country','c_Phone']],
 'employees'=>['tb_employees','i_EmployeeID',['c_LastName','c_FirstName','c_BirthDate','c_Photo','c_Notes']],
 'shippers'=>['tb_shippers','i_ShipperID',['c_ShipperName','c_Phone']],
]; }
try {
 $pdo=db(); $action=$_GET['action']??'';
 if ($action==='login' && $_SERVER['REQUEST_METHOD']==='POST') { $b=body(); $name=trim((string)($b['username']??'')); $pass=(string)($b['password']??''); if ($name==='admin' && (string)(getenv('APP_ADMIN_PASSWORD') ?: '') !== '' && hash_equals((string)getenv('APP_ADMIN_PASSWORD'),$pass)) { session_regenerate_id(true); $_SESSION['user']=['name'=>'ผู้ดูแลระบบ']; respond(['user'=>$_SESSION['user']]); } respond(['error'=>'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'],401); }
 if ($action==='logout') { $_SESSION=[]; session_destroy(); respond(['ok'=>true]); }
 if ($action==='me') { respond(['user'=>$_SESSION['user']??null]); }
 requireAuth();
 if ($action==='list') {
   $entity=$_GET['entity']??''; $map=tableMap(); if (!isset($map[$entity]) && $entity!=='orders') respond(['error'=>'ไม่พบรายการ'],404);
   $q=trim((string)($_GET['q']??'')); $limit=max(1,min(500,(int)($_GET['limit']??200)));
   if ($entity==='orders') { $sql="SELECT o.i_OrderID,o.c_OrderDate,o.i_CustomerID,c.c_customername,o.i_EmployeeID,CONCAT(e.c_FirstName,' ',e.c_LastName) employee,o.i_ShipperID,s.c_ShipperName,COALESCE(SUM(d.i_Quantity*p.i_Price),0) total FROM tb_orders o LEFT JOIN tb_customers c ON c.i_customerid=o.i_CustomerID LEFT JOIN tb_employees e ON e.i_EmployeeID=o.i_EmployeeID LEFT JOIN tb_shippers s ON s.i_ShipperID=o.i_ShipperID LEFT JOIN tb_orderdetails d ON d.i_OrderID=o.i_OrderID LEFT JOIN tb_products p ON p.i_ProductID=d.i_ProductID WHERE (:q='' OR CAST(o.i_OrderID AS CHAR) LIKE :q2 OR c.c_customername LIKE :q3) GROUP BY o.i_OrderID ORDER BY o.i_OrderID DESC LIMIT $limit"; $st=$pdo->prepare($sql); $st->execute(['q'=>$q,'q2'=>"%$q%",'q3'=>"%$q%"]); respond($st->fetchAll()); }
   [$table,$pk,$fields]=$map[$entity]; $where=''; $params=[]; if($q!==''){ $parts=[]; foreach($fields as $i=>$field){$parts[]="CAST(`$field` AS CHAR) LIKE :q$i";$params["q$i"]="%$q%";} $where=' WHERE '.implode(' OR ',$parts); }
   $sql="SELECT * FROM `$table`$where ORDER BY `$pk` DESC LIMIT $limit"; $st=$pdo->prepare($sql);$st->execute($params);respond($st->fetchAll());
 }
 if ($action==='detail' && isset($_GET['id'])) { $id=(int)$_GET['id']; $st=$pdo->prepare("SELECT o.i_OrderID,o.c_OrderDate,o.i_CustomerID,c.c_customername,o.i_EmployeeID,CONCAT(e.c_FirstName,' ',e.c_LastName) employee,o.i_ShipperID,s.c_ShipperName FROM tb_orders o LEFT JOIN tb_customers c ON c.i_customerid=o.i_CustomerID LEFT JOIN tb_employees e ON e.i_EmployeeID=o.i_EmployeeID LEFT JOIN tb_shippers s ON s.i_ShipperID=o.i_ShipperID WHERE o.i_OrderID=?");$st->execute([$id]);$order=$st->fetch();if(!$order)respond(['error'=>'ไม่พบคำสั่งซื้อ'],404);$st=$pdo->prepare('SELECT d.i_ProductID,p.c_ProductName,p.i_Price,d.i_Quantity,d.i_Quantity*p.i_Price line_total FROM tb_orderdetails d LEFT JOIN tb_products p ON p.i_ProductID=d.i_ProductID WHERE d.i_OrderID=?');$st->execute([$id]);$order['items']=$st->fetchAll();respond($order); }
 if ($action==='save' && $_SERVER['REQUEST_METHOD']==='POST') { $b=body();$entity=$b['entity']??'';$row=$b['row']??[];$map=tableMap();if(!isset($map[$entity]))respond(['error'=>'ไม่รองรับการแก้ไขรายการนี้'],400);[$table,$pk,$fields]=$map[$entity];$id=(int)($b['id']??0);$vals=[];foreach($fields as $f){$vals[$f]=$row[$f]??null;if($vals[$f]===null||$vals[$f]==='')respond(['error'=>'กรุณากรอกข้อมูลให้ครบ'],422);}if($id){$set=implode(',',array_map(fn($f)=>"`$f`=?",$fields));$st=$pdo->prepare("UPDATE `$table` SET $set WHERE `$pk`=?");$st->execute([...array_values($vals),$id]);}else{if($entity!=='products'){$id=(int)$pdo->query("SELECT COALESCE(MAX(`$pk`),0)+1 FROM `$table`")->fetchColumn();$vals=[$pk=>$id]+$vals;} $cols=implode(',',array_map(fn($f)=>"`$f`",array_keys($vals)));$marks=implode(',',array_fill(0,count($vals),'?'));$st=$pdo->prepare("INSERT INTO `$table` ($cols) VALUES ($marks)");$st->execute(array_values($vals));if($entity==='products')$id=(int)$pdo->lastInsertId();}respond(['ok'=>true,'id'=>$id]); }
 if ($action==='delete' && $_SERVER['REQUEST_METHOD']==='POST') { $b=body();$map=tableMap();$entity=$b['entity']??'';if(!isset($map[$entity]))respond(['error'=>'ไม่รองรับการลบรายการนี้'],400);[$table,$pk]=$map[$entity];$st=$pdo->prepare("DELETE FROM `$table` WHERE `$pk`=?");$st->execute([(int)($b['id']??0)]);respond(['ok'=>true]); }
 if ($action==='dashboard') { $out=[];foreach(['products'=>'tb_products','categories'=>'tb_categories','customers'=>'tb_customers','orders'=>'tb_orders'] as $k=>$t)$out[$k]=(int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();$out['revenue']=(float)$pdo->query('SELECT COALESCE(SUM(d.i_Quantity*p.i_Price),0) FROM tb_orderdetails d JOIN tb_products p ON p.i_ProductID=d.i_ProductID')->fetchColumn();$out['topProducts']=$pdo->query('SELECT p.c_ProductName name,SUM(d.i_Quantity) quantity FROM tb_orderdetails d JOIN tb_products p ON p.i_ProductID=d.i_ProductID GROUP BY p.i_ProductID ORDER BY quantity DESC LIMIT 8')->fetchAll();$out['topCustomers']=$pdo->query('SELECT c.c_customername name,COUNT(o.i_OrderID) orders FROM tb_customers c JOIN tb_orders o ON o.i_CustomerID=c.i_customerid GROUP BY c.i_customerid ORDER BY orders DESC LIMIT 8')->fetchAll();$out['monthly']=$pdo->query("SELECT SUBSTRING_INDEX(c_OrderDate,'/',-1) year, SUBSTRING_INDEX(SUBSTRING_INDEX(c_OrderDate,'/',2),'/',-1) month,COUNT(*) orders FROM tb_orders GROUP BY year,month ORDER BY year,month")->fetchAll();respond($out); }
 respond(['error'=>'ไม่พบ API'],404);
} catch (Throwable $e) { error_log((string)$e); respond(['error'=>'ระบบเชื่อมต่อฐานข้อมูลไม่สำเร็จ ตรวจสอบการตั้งค่า config.php และการนำเข้า SQL'],500); }

