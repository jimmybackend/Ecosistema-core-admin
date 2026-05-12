<?php

declare(strict_types=1);

namespace App\Core\Mail;

final class SmtpMailer implements MailSender
{
    public function __construct(private readonly array $config)
    {
    }

    public function send(array $payload): array
    {
        $host = (string) ($this->config['host'] ?? '');
        $port = (int) ($this->config['port'] ?? 0);
        $username = (string) ($this->config['username'] ?? '');
        $password = (string) ($this->config['password'] ?? '');
        $encryption = strtolower(trim((string) ($this->config['encryption'] ?? '')));
        $from = trim((string) ($payload['from'] ?? ''));
        $to = array_values(array_filter((array) ($payload['to'] ?? []), static fn (mixed $email): bool => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false));
        $subject = trim((string) ($payload['subject'] ?? ''));
        $body = (string) ($payload['body'] ?? '');

        if ($host === '' || $port <= 0 || $from === '' || $to === []) {
            return ['sent' => false, 'message' => 'Configuración SMTP o payload inválido.'];
        }

        $socketTarget = $encryption === 'ssl' ? "ssl://{$host}:{$port}" : "{$host}:{$port}";
        $socket = @stream_socket_client($socketTarget, $errno, $errstr, 10);
        if (!is_resource($socket)) {
            return ['sent' => false, 'message' => 'No se pudo conectar al servidor SMTP.'];
        }
        stream_set_timeout($socket, 10);

        try {
            $this->expectCode($socket, [220]);
            $this->sendLine($socket, 'EHLO localhost');
            $this->expectCode($socket, [250]);

            if ($encryption === 'tls') {
                $this->sendLine($socket, 'STARTTLS');
                $this->expectCode($socket, [220]);
                if (@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                    throw new \RuntimeException('No se pudo iniciar TLS.');
                }
                $this->sendLine($socket, 'EHLO localhost');
                $this->expectCode($socket, [250]);
            }

            if ($username !== '' && $password !== '') {
                $this->sendLine($socket, 'AUTH LOGIN');
                $this->expectCode($socket, [334]);
                $this->sendLine($socket, base64_encode($username));
                $this->expectCode($socket, [334]);
                $this->sendLine($socket, base64_encode($password));
                $this->expectCode($socket, [235]);
            }

            $this->sendLine($socket, 'MAIL FROM:<' . $from . '>');
            $this->expectCode($socket, [250]);
            foreach ($to as $recipient) {
                $this->sendLine($socket, 'RCPT TO:<' . $recipient . '>');
                $this->expectCode($socket, [250, 251]);
            }

            $this->sendLine($socket, 'DATA');
            $this->expectCode($socket, [354]);
            $headers = [
                'From: ' . $from,
                'To: ' . implode(', ', $to),
                'Subject: ' . $subject,
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
            ];
            $data = implode("\r\n", $headers) . "\r\n\r\n" . $this->escapeBody($body) . "\r\n.";
            $this->sendLine($socket, $data);
            $this->expectCode($socket, [250]);
            $this->sendLine($socket, 'QUIT');

            return ['sent' => true, 'message' => 'Correo enviado correctamente.'];
        } catch (\Throwable) {
            return ['sent' => false, 'message' => 'Falló el envío SMTP.'];
        } finally {
            fclose($socket);
        }
    }

    private function sendLine($socket, string $line): void
    {
        fwrite($socket, $line . "\r\n");
    }

    private function expectCode($socket, array $acceptedCodes): void
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $acceptedCodes, true)) {
            throw new \RuntimeException('SMTP response code no aceptado.');
        }
    }

    private function escapeBody(string $body): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $body);
        $lines = explode("\n", $normalized);
        $escaped = array_map(static fn (string $line): string => str_starts_with($line, '.') ? '.' . $line : $line, $lines);
        return implode("\r\n", $escaped);
    }
}
