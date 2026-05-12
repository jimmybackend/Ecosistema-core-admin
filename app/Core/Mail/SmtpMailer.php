<?php

declare(strict_types=1);

namespace App\Core\Mail;

final class SmtpMailer implements MailSender
{
    public function __construct(private readonly array $config) {}
    public function send(array $payload): array
    {
        $host=(string)($this->config['host']??''); $port=(int)($this->config['port']??0); $username=(string)($this->config['username']??''); $password=(string)($this->config['password']??''); $encryption=strtolower(trim((string)($this->config['encryption']??'')));
        $from=trim((string)($payload['from']??'')); $to=array_values(array_filter((array)($payload['to']??[]),fn($e)=>is_string($e)&&filter_var($e,FILTER_VALIDATE_EMAIL)!==false));
        if($host===''||$port<=0||$from===''||$to===[]) return ['sent'=>false,'message'=>'Configuración SMTP o payload inválido.'];
        $socket=@stream_socket_client(($encryption==='ssl'?"ssl://$host:$port":"$host:$port"),$errno,$errstr,10); if(!is_resource($socket)) return ['sent'=>false,'message'=>'No se pudo conectar al servidor SMTP.']; stream_set_timeout($socket,10);
        try {
            $this->expectCode($socket,[220]); $this->sendLine($socket,'EHLO localhost'); $this->expectCode($socket,[250]);
            if($encryption==='tls'){ $this->sendLine($socket,'STARTTLS'); $this->expectCode($socket,[220]); if(@stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)!==true) throw new \RuntimeException(); $this->sendLine($socket,'EHLO localhost'); $this->expectCode($socket,[250]); }
            if($username!==''&&$password!==''){ $this->sendLine($socket,'AUTH LOGIN'); $this->expectCode($socket,[334]); $this->sendLine($socket,base64_encode($username)); $this->expectCode($socket,[334]); $this->sendLine($socket,base64_encode($password)); $this->expectCode($socket,[235]); }
            $this->sendLine($socket,'MAIL FROM:<'.$from.'>'); $this->expectCode($socket,[250]); foreach($to as $r){$this->sendLine($socket,'RCPT TO:<'.$r.'>');$this->expectCode($socket,[250,251]);}
            $this->sendLine($socket,'DATA'); $this->expectCode($socket,[354]);
            $data=$this->buildMimeMessage($from,$to,(string)($payload['subject']??''),(string)($payload['body']??''),(array)($payload['attachments']??[]));
            $this->sendLine($socket,$data."\r\n."); $this->expectCode($socket,[250]); $this->sendLine($socket,'QUIT'); return ['sent'=>true,'message'=>'Correo enviado correctamente.'];
        } catch (\Throwable) { return ['sent'=>false,'message'=>'Falló el envío SMTP.']; } finally { fclose($socket); }
    }
    private function buildMimeMessage(string $from, array $to, string $subject, string $body, array $attachments): string
    {
        $headers=['From: '.$from,'To: '.implode(', ',$to),'Subject: '.$subject,'MIME-Version: 1.0'];
        if($attachments===[]) return implode("\r\n",array_merge($headers,['Content-Type: text/plain; charset=UTF-8'])) ."\r\n\r\n".$this->escapeBody($body);
        $b='mix_'.bin2hex(random_bytes(12)); $headers[]='Content-Type: multipart/mixed; boundary="'.$b.'"';
        $parts=["--$b","Content-Type: text/plain; charset=UTF-8","Content-Transfer-Encoding: 8bit",'', $this->escapeBody($body)];
        foreach($attachments as $a){ $name=$this->safeName((string)($a['name']??'archivo')); $mime=$this->safeMime((string)($a['mime_type']??'')); $path=(string)($a['path']??''); if($path===''||!is_file($path)) continue; $content=chunk_split(base64_encode((string)file_get_contents($path))); $parts=array_merge($parts,["--$b",'Content-Type: '.$mime.'; name="'.$name.'"','Content-Transfer-Encoding: base64','Content-Disposition: attachment; filename="'.$name.'"','',$content]); }
        $parts[]="--$b--"; return implode("\r\n",$headers)."\r\n\r\n".implode("\r\n",$parts);
    }
    private function safeName(string $name): string { $base=basename(str_replace('\\','/',$name)); $clean=preg_replace('/[^a-zA-Z0-9._-]/','_',$base); return $clean!==''?$clean:'archivo'; }
    private function safeMime(string $mime): string { $mime=strtolower(trim($mime)); return preg_match('/^[a-z0-9][a-z0-9.+-]*\/[a-z0-9][a-z0-9.+-]*$/',$mime)===1?$mime:'application/octet-stream'; }
    private function sendLine($socket,string $line): void { fwrite($socket,$line."\r\n"); }
    private function expectCode($socket,array $codes): void { $r=''; while(($l=fgets($socket,515))!==false){$r.=$l; if(strlen($l)>=4&&$l[3]===' ') break;} if(!in_array((int)substr($r,0,3),$codes,true)) throw new \RuntimeException(); }
    private function escapeBody(string $body): string { $lines=explode("\n",str_replace(["\r\n","\r"],"\n",$body)); return implode("\r\n",array_map(fn($l)=>str_starts_with($l,'.')?'.'.$l:$l,$lines)); }
}
