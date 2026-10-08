<?php
require_once __DIR__ . '/../bootstrap.php';require_once __DIR__.'/_google.php';require_method('POST');$u=require_auth(['super_admin']);require_csrf();$d=request_json();
$bid=(int)($d['business_id']??0);$items=$d['services']??[];if(!$bid||!is_array($items)||!count($items))json_response(['ok'=>false,'error'=>'Business and at least one service are required'],422);
$c=db()->prepare('SELECT * FROM gmb_connections WHERE business_id=? AND is_enabled=1');$c->execute([$bid]);$conn=$c->fetch();if(!$conn)json_response(['ok'=>false,'error'=>'Connect this business to a Google account/location first'],422);
$category=trim((string)($conn['category_id']??''));$serviceItems=[];
foreach($items as $row){$name=trim((string)($row['name']??''));if($name==='')continue;$type=trim((string)($row['service_type_id']??''));if($type!==''){$serviceItems[]=['isOffered'=>true,'structuredServiceItem'=>['serviceTypeId'=>$type]];}elseif($category!==''){$serviceItems[]=['isOffered'=>true,'freeFormServiceItem'=>['categoryId'=>$category,'label'=>['displayName'=>$name]]];}}
if(!$serviceItems)json_response(['ok'=>false,'error'=>'For free-form services save a Google category ID in the GMB connection, or provide structured serviceTypeId values'],422);
$loc=preg_replace('#^locations/#','',(string)$conn['location_id']);
try{$r=gbp_request('PATCH','https://mybusinessbusinessinformation.googleapis.com/v1/locations/'.rawurlencode($loc).'?updateMask=serviceItems',['serviceItems'=>$serviceItems]);audit((int)$u['id'],$bid,'gmb_services_updated',['count'=>count($serviceItems)]);json_response(['ok'=>true,'result'=>$r,'count'=>count($serviceItems)]);}catch(Throwable $e){json_response(['ok'=>false,'error'=>$e->getMessage()],502);}
