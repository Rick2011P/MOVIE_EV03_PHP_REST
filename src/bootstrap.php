<?php
declare(strict_types=1);
function envLoad(string $path): void {
    if (!is_file($path)) throw new RuntimeException('Falta archivo .env. Copia .env.example como .env');
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}
envLoad(dirname(__DIR__).'/.env');
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'] ?? '127.0.0.1', $_ENV['DB_PORT'] ?? 3306, $_ENV['DB_NAME'] ?? 'movie_ev03');
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [
       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
       PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
function respond(int $status, array $body): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function body(): array {
    $raw = file_get_contents('php://input');
    $value = json_decode($raw ?: '{}', true);
    if (!is_array($value) || json_last_error() !== JSON_ERROR_NONE || array_is_list($value) && count($value)>0) respond(400, ['error'=>'El cuerpo debe ser un objeto JSON válido']);
    return $value;
}
function queryOne(string $sql, array $args=[]): ?array {
    $stmt=db()->prepare($sql); $stmt->execute($args);
    $row=$stmt->fetch(); return $row === false ? null : $row;
}
function executeSql(string $sql, array $args=[]): int {
    $stmt=db()->prepare($sql); $stmt->execute($args); return $stmt->rowCount();
}
function b64url(string $v): string { return rtrim(strtr(base64_encode($v), '+/', '-_'), '='); }
function jwt(array $claims): string {
    $secret=$_ENV['JWT_SECRET'] ?? '';
    if (strlen($secret)<32) throw new RuntimeException('JWT_SECRET debe tener al menos 32 caracteres');
    $header=b64url(json_encode(['typ'=>'JWT','alg'=>'HS256']));
    $payload=b64url(json_encode($claims));
    $base="$header.$payload";
    return "$base.".b64url(hash_hmac('sha256',$base,$secret,true));
}
function verifyJwt(string $token): ?array {
    $parts=explode('.', $token);
    if(count($parts)!==3) return null;
    [$h,$p,$signature]=$parts;
    $header=json_decode(base64_decode(strtr($h,'-_','+/')) ?: '',true);
    $claims=json_decode(base64_decode(strtr($p,'-_','+/')) ?: '',true);
    if(!is_array($header)||($header['alg']??'')!=='HS256'||!is_array($claims))return null;
    $expected=b64url(hash_hmac('sha256',"$h.$p",$_ENV['JWT_SECRET']??'',true));
    if(!hash_equals($expected,$signature) || !isset($claims['exp'],$claims['sub']) || !is_int($claims['exp']) || $claims['exp']<=time())return null;
    return $claims;
}
function auth(bool $admin=false): array {
    $authorization=$_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if(!preg_match('/^Bearer\s+(\S+)$/i',$authorization,$m))respond(401,['error'=>'Se requiere token Bearer']);
    $claims=verifyJwt($m[1]);
    if(!$claims)respond(401,['error'=>'Token inválido o expirado']);
    $u=queryOne('SELECT id,name,email,role,active FROM users WHERE id=?',[(int)$claims['sub']]);
    if(!$u || !(int)$u['active'])respond(401,['error'=>'Usuario no autorizado o inactivo']);
    if($admin && $u['role']!=='ADMIN')respond(403,['error'=>'Solo administradores']);
    return $u;
}
function userPublic(array $u): array { unset($u['password_hash']); return $u; }
function validateMovie(array $v): array {
    $title=trim((string)($v['title']??''));
    $director=trim((string)($v['director']??''));
    $genre=trim((string)($v['genre']??''));
    $year=filter_var($v['release_year']??null, FILTER_VALIDATE_INT);
    if(strlen($title)<2||strlen($title)>180||strlen($director)<2||strlen($director)>160||strlen($genre)<2||strlen($genre)>100||$year===false||$year<1888||$year>(int)date('Y')+5)respond(422,['error'=>'Se requieren title (2-180), director (2-160), genre (2-100) y release_year válido']);
    $synopsis=isset($v['synopsis'])?trim((string)$v['synopsis']):null;
    return [$title,$director,$year,$genre,$synopsis];
}
