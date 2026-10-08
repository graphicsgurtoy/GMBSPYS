<?php
declare(strict_types=1);

$ROOT = dirname(__DIR__);

function load_env_file(string $file): void {
    if (!is_file($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}
load_env_file($ROOT . '/.env');

function envv(string $key, ?string $default = null): ?string {
    $v = getenv($key);
    return $v === false ? $default : $v;
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name(envv('SESSION_NAME', 'growreview_session'));
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $host = envv('DB_HOST', 'localhost');
    $port = envv('DB_PORT', '3306');
    $name = envv('DB_NAME');
    $user = envv('DB_USER');
    $pass = envv('DB_PASS', '');
    if (!$name || !$user) throw new RuntimeException('Database environment variables are missing.');
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function request_json(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function require_method(string $method): void {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
    }
}

function current_user(): ?array {
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) return null;
    $st = db()->prepare('SELECT id,business_id,name,email,role,is_active FROM users WHERE id=? LIMIT 1');
    $st->execute([$id]);
    $u = $st->fetch();
    if (!$u || !$u['is_active']) return null;
    return $u;
}

function require_auth(array $roles = []): array {
    $u = current_user();
    if (!$u) json_response(['ok' => false, 'error' => 'Authentication required'], 401);
    if ($roles && !in_array($u['role'], $roles, true)) json_response(['ok' => false, 'error' => 'Forbidden'], 403);
    return $u;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}

function require_csrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$token || !hash_equals(csrf_token(), $token)) json_response(['ok' => false, 'error' => 'Invalid CSRF token'], 419);
}

function clean_slug(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function staff_code(string $name): string {
    $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', substr($name, 0, 8)) ?: 'STAFF');
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function business_scope(array $user, ?int $requested = null): int {
    if ($user['role'] === 'super_admin') {
        if (!$requested) json_response(['ok'=>false,'error'=>'business_id is required'],422);
        return $requested;
    }
    $bid = (int)($user['business_id'] ?? 0);
    if (!$bid) json_response(['ok'=>false,'error'=>'No business assigned'],403);
    if ($requested && $requested !== $bid) json_response(['ok'=>false,'error'=>'Cross-business access denied'],403);
    return $bid;
}

function save_image(?array $file, string $slug, string $label): ?string {
    global $ROOT;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) throw new RuntimeException("{$label} upload failed");
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) throw new RuntimeException("{$label} must be under 5MB");
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($allowed[$mime])) throw new RuntimeException("{$label} must be JPG, PNG or WEBP");
    $dir = $ROOT . '/uploads/' . $slug;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) throw new RuntimeException('Cannot create upload directory');
    $name = $label . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException("Could not save {$label}");
    return '/uploads/' . $slug . '/' . $name;
}

function audit(?int $userId, ?int $businessId, string $action, array $context = []): void {
    try {
        $st = db()->prepare('INSERT INTO audit_logs(user_id,business_id,action,context_json) VALUES(?,?,?,?)');
        $st->execute([$userId, $businessId, $action, $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : null]);
    } catch (Throwable $e) {}
}

set_exception_handler(function(Throwable $e): void {
    error_log($e->__toString());
    $message = envv('APP_ENV','production') === 'development' ? $e->getMessage() : 'Server error';
    json_response(['ok'=>false,'error'=>$message],500);
});
