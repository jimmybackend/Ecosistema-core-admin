<?php
declare(strict_types=1);
namespace App\Core\BrowserAnalytics;
final class EcosistemaBrowserAnalyticsAdapter{public function capabilities(): array{return ['dashboard_read'=>true,'sessions_read'=>true,'pageviews_read'=>true,'events_read'=>true,'collector_dry_run'=>true,'collector_write'=>false,'daily_rollups_read'=>true,'mode'=>'read-only','db_writes'=>false];}}
