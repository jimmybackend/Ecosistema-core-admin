<?php

declare(strict_types=1);

namespace App\Core\Mail;

final class SmtpMailer implements MailSender
{
    public function send(array $payload): array
    {
        return [
            'sent' => false,
            'mode' => 'not_implemented',
            'message' => 'Envío SMTP pendiente para PR posterior.',
            'payload_keys' => array_keys($payload),
        ];
    }
}
