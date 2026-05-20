#!/usr/bin/env php
<?php
declare(strict_types=1);
use App\Core\Database\PdoFactory;
$root = dirname(__DIR__); require_once $root . '/vendor/autoload.php'; $app = require $root . '/bootstrap/app.php';
$options = getopt('', ['tenant:', 'user:', 'message:']);
$tenantId=(int)($options['tenant']??0); $userId=(int)($options['user']??0); $messageId=(int)($options['message']??0);
if($tenantId<=0||$userId<=0||$messageId<=0){fwrite(STDERR,"Uso: php scripts/mail-attachment-debug.php --tenant=1 --user=1 --message=16\n");exit(1);}
$pdo=PdoFactory::make((array)($app['config']['database']??[]));
$sql='SELECT ea.id,ea.original_filename,ea.mime_type,ea.size_bytes,ea.import_status,ea.cloud_file_id,ea.error_message,ea.raw_payload_json FROM mail_external_attachments ea INNER JOIN mail_messages m ON m.id=ea.message_id AND m.tenant_id=ea.tenant_id WHERE ea.tenant_id=:t AND m.user_id=:u AND ea.message_id=:m ORDER BY ea.id ASC';
$st=$pdo->prepare($sql); $st->execute([':t'=>$tenantId,':u'=>$userId,':m'=>$messageId]); $rows=$st->fetchAll(PDO::FETCH_ASSOC)?:[];
$sanitize=static function(string $v):string{$v=preg_replace('/\s+/',' ',trim($v))??''; return mb_substr($v,0,180);};
$out=[]; foreach($rows as $r){$raw=json_decode((string)($r['raw_payload_json']??''),true); if(!is_array($raw)){$raw=[];} $has=['imap_folder'=>!empty($raw['imap_folder']),'imap_uid'=>!empty($raw['imap_uid']),'imap_part_number'=>!empty($raw['imap_part_number'])]; $can=$has['imap_folder']&&$has['imap_uid']&&$has['imap_part_number']; $imported=((int)($r['cloud_file_id']??0))>0||((string)$r['import_status']==='imported'); $action=$imported?'already_imported':($can?'import_attachment':'backfill_imap_metadata'); $out[]=['external_attachment_id'=>(int)$r['id'],'original_filename'=>(string)$r['original_filename'],'mime_type'=>(string)$r['mime_type'],'size_bytes'=>(int)$r['size_bytes'],'import_status'=>(string)$r['import_status'],'has_cloud_file'=>((int)($r['cloud_file_id']??0))>0,'last_error'=>$sanitize((string)($r['error_message']??'')),'raw_payload_has'=>$has,'can_import_binary'=>$can,'suggested_action'=>$action];}
echo json_encode(['ok'=>true,'message_id'=>$messageId,'attachments'=>$out], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
