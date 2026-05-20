#!/usr/bin/env php
<?php
declare(strict_types=1);
use App\Core\Database\PdoFactory; use App\Core\Mail\MailSmtpAccountRepository; use App\Core\Mail\MailMessageRepository;
require_once dirname(__DIR__) . '/vendor/autoload.php'; $app = require dirname(__DIR__) . '/bootstrap/app.php'; $config = $app['config'] ?? [];
$o=getopt('', ['tenant:','user:','account:','limit::']); $tenantId=(int)($o['tenant']??0); $userId=(int)($o['user']??0); $accountId=(int)($o['account']??0); $limit=max(1,min(50,(int)($o['limit']??10)));
if($tenantId<=0||$userId<=0||$accountId<=0){fwrite(STDERR,"Uso: php scripts/mail-db-debug.php --tenant=1 --user=1 --account=1 --limit=10\n");exit(1);} 
$pdo=PdoFactory::make($config['database']??[]); $smtp=(new MailSmtpAccountRepository($pdo))->findActiveForUser($tenantId,$userId,$accountId); if(!is_array($smtp)){echo "ACCOUNT_NOT_AUTHORIZED_OR_INACTIVE\n"; exit(2);} $mailboxId=(int)($smtp['mailbox_id']??0);
$base=[':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId,':user_id'=>$userId];
$q='FROM mail_messages WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id AND user_id=:user_id';
foreach(['total'=>'1=1','inbound'=>'direction="inbound"','deleted'=>'COALESCE(is_deleted,0)=1','spam'=>'COALESCE(is_spam,0)=1','draft'=>'COALESCE(is_draft,0)=1','with_attachments'=>'COALESCE(has_attachments,0)=1'] as $k=>$w){$st=$pdo->prepare('SELECT COUNT(*) '.$q.' AND '.$w);$st->execute($base);echo $k.'='.(int)$st->fetchColumn().PHP_EOL;}
$rows=(new MailMessageRepository($pdo))->listMessagesForMailbox($tenantId,$userId,$mailboxId,$limit);
$mask=fn(string $e)=>preg_replace('/(^.).*(@.*$)/','$1***$2',$e)??'***';
foreach($rows as $r){$sub=mb_substr((string)($r['subject']??''),0,80); echo sprintf("id=%d dir=%s from=%s subject=%s received=%s sent=%s has_attach=%d\n",(int)$r['id'],(string)($r['direction']??''),$mask((string)($r['from_address']??'')),$sub,(string)($r['received_at']??''),(string)($r['sent_at']??''),(int)($r['has_attachments']??0));}
