<?php
require_once __DIR__ . '/../bootstrap.php';
require_auth(['super_admin']);
json_response(['ok'=>true,'blogs'=>db()->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll()]);
