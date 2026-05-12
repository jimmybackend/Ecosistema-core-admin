<?php

declare(strict_types=1);

namespace App\Core\Auth;

use DateTimeImmutable;

final readonly class CronSessionCleanupRunner
{
    public function __construct(private SessionRepository $sessions)
    {
    }

    /**
     * @return array{job:string,idle_timeout:int,threshold_at:string,candidates:int,revoked:int}
     */
    public function run(int $idleTimeout): array
    {
        if ($idleTimeout <= 0) {
            throw new \InvalidArgumentException('SESSION_IDLE_TIMEOUT inválido.');
        }

        $threshold = (new DateTimeImmutable())->modify(sprintf('-%d seconds', $idleTimeout));
        $result = $this->sessions->revokeExpiredSessions($threshold);

        return [
            'job' => 'session-cleanup',
            'idle_timeout' => $idleTimeout,
            'threshold_at' => $threshold->format('Y-m-d H:i:s'),
            'candidates' => $result['candidates'],
            'revoked' => $result['revoked'],
        ];
    }
}
