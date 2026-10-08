<?php
require_once __DIR__ . '/../bootstrap.php';
$slug=clean_slug((string)($_GET['slug']??''));if($slug==='')json_response(['ok'=>false,'error'=>'Blog not found'],404);
$st=db()->prepare("SELECT title,slug,category,excerpt,content,cover_image,published_at FROM blog_posts WHERE slug=? AND status='published' LIMIT 1");$st->execute([$slug]);$b=$st->fetch();if(!$b)json_response(['ok'=>false,'error'=>'Blog not found'],404);json_response(['ok'=>true,'blog'=>$b]);
