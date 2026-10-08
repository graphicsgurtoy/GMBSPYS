<?php
require_once __DIR__ . '/../bootstrap.php';

try {
    $clients = db()->query("SELECT name,slug,category,city,logo_path,banner_path FROM businesses WHERE status='active' ORDER BY created_at DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) { $clients = []; }
try {
    $testimonials = db()->query("SELECT t.author_name,t.author_role,t.business_name,t.quote,t.rating,b.name linked_business FROM testimonials t LEFT JOIN businesses b ON b.id=t.business_id WHERE t.is_published=1 ORDER BY t.sort_order ASC,t.created_at DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) { $testimonials = []; }
try {
    $blogs = db()->query("SELECT title,slug,category,excerpt,cover_image,published_at FROM blog_posts WHERE status='published' ORDER BY COALESCE(published_at,created_at) DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) { $blogs = []; }
json_response(['ok'=>true,'clients'=>$clients,'testimonials'=>$testimonials,'blogs'=>$blogs]);
