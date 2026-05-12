<?php

declare(strict_types=1);

namespace App\Core\Mail;

final readonly class MailOutgoingAttachmentService
{
    public function __construct(private MailAttachmentRepository $attachments, private array $cloudConfig, private array $mailConfig)
    {
    }

    public function resolveForSend(int $tenantId, int $userId, int $messageId): array
    {
        $rows = $this->attachments->listOutgoingByMessageForUser($tenantId, $userId, $messageId, 100);
        $maxCount = max(1, (int) ($this->mailConfig['max_attachments'] ?? 5));
        $maxItemBytes = max(1, (int) ($this->mailConfig['max_attachment_mb'] ?? 10)) * 1024 * 1024;
        $maxTotalBytes = max(1, (int) ($this->mailConfig['max_total_attachment_mb'] ?? 20)) * 1024 * 1024;
        $baseReal = realpath(dirname(__DIR__, 3) . '/' . trim((string) ($this->cloudConfig['local_storage_path'] ?? 'storage/app/cloud'), '/'));
        if ($baseReal === false || !is_dir($baseReal)) { return ['ok'=>false,'reason'=>'Almacenamiento local Cloud no disponible.','attachments'=>[],'count'=>0,'total_bytes'=>0]; }
        $basePrefix = rtrim($baseReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $items=[]; $total=0; $blocked=[];
        if (count($rows) > $maxCount) { $blocked[] = 'Se excede el máximo de adjuntos permitidos.'; }
        foreach ($rows as $row) {
            $name = $this->safeName((string) ($row['original_name'] ?? 'archivo'));
            $size = max(0, (int) ($row['size_bytes'] ?? 0));
            $status = strtolower(trim((string) ($row['status'] ?? '')));
            $mime = $this->safeMime((string) ($row['mime_type'] ?? ''));
            $ext = strtolower(trim((string) ($row['extension'] ?? '')));
            $isReady = true; $reason = 'Listo';
            if ($status !== 'active') { $isReady=false; $reason='Archivo no disponible por estado.'; }
            if ((int)($row['found_in_s3'] ?? 0) === 1) { $isReady=false; $reason='Archivo remoto/S3 no soportado en envío.'; }
            if (in_array($ext, ['php','phtml','phar','sh','bat','cmd','js','exe'], true)) { $isReady=false; $reason='Tipo de archivo no permitido para envío.'; }
            $key = ltrim((string) ($row['s3_key'] ?? ''), '/');
            $targetReal = $key !== '' ? realpath($baseReal . '/' . $key) : false;
            if ($targetReal === false || !is_file($targetReal)) { $isReady=false; $reason='Archivo físico no encontrado.'; }
            if ($targetReal !== false && !str_starts_with($targetReal, $basePrefix)) { $isReady=false; $reason='Ruta de adjunto inválida.'; }
            if ($size > $maxItemBytes) { $isReady=false; $reason='Adjunto supera tamaño máximo por archivo.'; }
            $total += $size;
            if (!$isReady) { $blocked[] = $name . ': ' . $reason; }
            $items[]=['id'=>(int)$row['id'],'name'=>$name,'mime_type'=>$mime,'size_bytes'=>$size,'ready'=>$isReady,'blocked_reason'=>$reason,'path'=>$targetReal?:null];
        }
        if ($total > $maxTotalBytes) { $blocked[]='Adjuntos superan el tamaño total permitido.'; }
        return ['ok'=>$blocked===[],'reason'=>$blocked===[]?'Adjuntos listos para envío.':'Uno o más adjuntos bloquean el envío.','attachments'=>$items,'count'=>count($items),'total_bytes'=>$total,'blocked_reasons'=>$blocked,'limits'=>['max_attachments'=>$maxCount,'max_attachment_bytes'=>$maxItemBytes,'max_total_bytes'=>$maxTotalBytes]];
    }

    private function safeName(string $name): string { $base=basename(str_replace('\\','/',$name)); $clean=preg_replace('/[^a-zA-Z0-9._-]/','_',$base); return $clean!==''?$clean:'archivo'; }
    private function safeMime(string $mime): string { $mime=strtolower(trim($mime)); return preg_match('/^[a-z0-9][a-z0-9.+-]*\/[a-z0-9][a-z0-9.+-]*$/',$mime)===1?$mime:'application/octet-stream'; }
}
