<?php
require_once __DIR__ . '/../bootstrap.php';require_method('POST');require_csrf();$u=require_auth(['super_admin']);$d=request_json();$id=(int)($d['id']??0);if(!$id)json_response(['ok'=>false,'error'=>'id required'],422);$st=db()->prepare('DELETE FROM blog_posts WHERE id=?');$st->execute([$id]);audit((int)$u['id'],null,'blog_deleted',['blog_id'=>$id]);json_response(['ok'=>true]);
