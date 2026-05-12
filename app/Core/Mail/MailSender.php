<?php

declare(strict_types=1);

namespace App\Core\Mail;

interface MailSender
{
    public function send(array $payload): array;
}
