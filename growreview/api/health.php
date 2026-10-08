<?php
require_once __DIR__ . '/bootstrap.php';
try{$v=db()->query('SELECT 1')->fetchColumn();json_response(['ok'=>true,'app'=>envv('APP_NAME','GrowReview'),'database'=>(bool)$v,'php'=>PHP_VERSION]);}catch(Throwable $e){json_response(['ok'=>false,'database'=>false,'error'=>$e->getMessage()],500);}
