<?php
declare(strict_types=1);
require dirname(__DIR__).'/src/bootstrap.php';
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
$method=$_SERVER['REQUEST_METHOD'];
$path=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if($method==='OPTIONS'){http_response_code(204);exit;}
try {
 if($method==='GET'&&$path==='/api/health') respond(200,['status'=>'ok','service'=>'MOVIE API']);
 if($method==='POST'&&$path==='/api/auth/register'){
   $v=body(); $name=trim((string)($v['name']??'')); $email=strtolower(trim((string)($v['email']??''))); $password=$v['password']??'';
   if(strlen($name)<2||strlen($name)>120||!filter_var($email,FILTER_VALIDATE_EMAIL)||!is_string($password)||strlen($password)<8||strlen($password)>72)
      respond(422,['error'=>'Nombre 2-120, correo válido y contraseña 8-72 caracteres obligatorios']);
   if(queryOne('SELECT id FROM users WHERE email=?',[$email]))respond(409,['error'=>'El correo ya está registrado']);
   executeSql('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
   respond(201,['message'=>'Usuario registrado','id'=>(int)db()->lastInsertId()]);
 }
 if($method==='POST'&&$path==='/api/auth/login'){
   $v=body();$email=strtolower(trim((string)($v['email']??'')));$password=$v['password']??'';
   $u=queryOne('SELECT * FROM users WHERE email=?',[$email]);
   if(!is_string($password)||!$u||!password_verify($password,$u['password_hash'])||!(int)$u['active'])respond(401,['error'=>'Credenciales inválidas o usuario inactivo']);
   $ttl=(int)($_ENV['JWT_TTL']??7200);
   $token=jwt(['sub'=>(int)$u['id'],'iat'=>time(),'exp'=>time()+$ttl]);
   respond(200,['token'=>$token,'token_type'=>'Bearer','expires_in'=>$ttl,'user'=>userPublic($u)]);
 }
 if($method==='GET'&&$path==='/api/users/me')respond(200,['user'=>auth()]);
 if($method==='GET'&&$path==='/api/users'){
   auth(true);
   $rows=db()->query('SELECT id,name,email,role,active,created_at FROM users ORDER BY id')->fetchAll();respond(200,['data'=>$rows]);
 }
 if(preg_match('#^/api/users/(\d+)/status$#',$path,$matches)&&$method==='PATCH'){
   auth(true);$v=body();
   if(!isset($v['active'])||!is_bool($v['active']))respond(422,['error'=>'active debe ser booleano']);
   $changed=executeSql('UPDATE users SET active=? WHERE id=?',[$v['active']?1:0,(int)$matches[1]]);
   if(!$changed&&!queryOne('SELECT id FROM users WHERE id=?',[(int)$matches[1]]))respond(404,['error'=>'Usuario no existe']);
   respond(200,['message'=>'Estado actualizado']);
 }
 if($method==='GET'&&$path==='/api/movies'){
   $rows=db()->query('SELECT * FROM movies ORDER BY id DESC')->fetchAll();respond(200,['data'=>$rows]);
 }
 if(preg_match('#^/api/movies/(\d+)$#',$path,$matches)){
   $id=(int)$matches[1];
   if($method==='GET'){
      $movie=queryOne('SELECT * FROM movies WHERE id=?',[$id]);
      if(!$movie)respond(404,['error'=>'Película no encontrada']);respond(200,['data'=>$movie]);
   }
   if($method==='PUT'){
      auth(true);[$title,$director,$year,$genre,$synopsis]=validateMovie(body());
      if(!queryOne('SELECT id FROM movies WHERE id=?',[$id]))respond(404,['error'=>'Película no encontrada']);
      executeSql('UPDATE movies SET title=?,director=?,release_year=?,genre=?,synopsis=? WHERE id=?',[$title,$director,$year,$genre,$synopsis,$id]);
      respond(200,['message'=>'Película actualizada','id'=>$id]);
   }
   if($method==='DELETE'){
      auth(true);$n=executeSql('DELETE FROM movies WHERE id=?',[$id]);
      if(!$n)respond(404,['error'=>'Película no encontrada']);respond(200,['message'=>'Película eliminada']);
   }
 }
 if($method==='POST'&&$path==='/api/movies'){
   auth(true);[$title,$director,$year,$genre,$synopsis]=validateMovie(body());
   executeSql('INSERT INTO movies(title,director,release_year,genre,synopsis) VALUES(?,?,?,?,?)',[$title,$director,$year,$genre,$synopsis]);
   respond(201,['message'=>'Película creada','id'=>(int)db()->lastInsertId()]);
 }
 if($method==='GET'&&$path==='/api/favorites'){
   $u=auth();$stmt=db()->prepare('SELECT m.* FROM favorites f JOIN movies m ON m.id=f.movie_id WHERE f.user_id=? ORDER BY f.created_at DESC');$stmt->execute([$u['id']]);respond(200,['data'=>$stmt->fetchAll()]);
 }
 if($method==='POST'&&$path==='/api/favorites'){
   $u=auth();$v=body();$id=filter_var($v['movie_id']??null,FILTER_VALIDATE_INT);
   if(!$id||$id<1)respond(422,['error'=>'movie_id debe ser entero positivo']);
   if(!queryOne('SELECT id FROM movies WHERE id=?',[$id]))respond(404,['error'=>'Película no encontrada']);
   executeSql('INSERT IGNORE INTO favorites(user_id,movie_id) VALUES(?,?)',[$u['id'],$id]);respond(201,['message'=>'Favorito guardado']);
 }
 if(preg_match('#^/api/favorites/(\d+)$#',$path,$m)&&$method==='DELETE'){
   $u=auth();$n=executeSql('DELETE FROM favorites WHERE user_id=? AND movie_id=?',[$u['id'],(int)$m[1]]);
   if(!$n)respond(404,['error'=>'Favorito no encontrado']);respond(200,['message'=>'Favorito eliminado']);
 }
 respond(404,['error'=>'Ruta no encontrada']);
} catch(PDOException $e){
 error_log($e->getMessage());respond(500,['error'=>'Error de base de datos; revisar configuración del servidor']);
} catch(Throwable $e){
 error_log($e->getMessage());respond(500,['error'=>'Error interno del servidor']);
}
