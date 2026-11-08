<?php
declare(strict_types=1);
$file = __DIR__ . '/API.md';
if (!is_file($file)) { http_response_code(404); exit('Documentation not found'); }
header('Content-Type: text/markdown; charset=utf-8');
header('Content-Disposition: attachment; filename="Northwind-BMS-API.md"');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($file));
readfile($file);