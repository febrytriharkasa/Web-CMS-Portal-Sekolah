<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien tipis GA4 Data API (batchRunReports) tanpa paket google/apiclient.
 *
 * Autentikasi memakai service account: JWT RS256 ditandatangani openssl lalu
 * ditukar jadi access token OAuth2. Token di-cache 50 menit, ringkasan laporan
 * 6 jam, jadi halaman admin tidak menembak API tiap dibuka.
 */
class GoogleAnalytics
{
    private const SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';

    private const DEFAULT_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const REPORT_URL = 'https://analyticsdata.googleapis.com/v1beta/%s:batchRunReports';

    private const REPORT_TTL_HOURS = 6;

    private const RANGE_DAYS = 28;

    /** Urutan harus sama dengan daftar metrics di summaryRequest(). */
    private const METRIC_KEYS = ['users', 'sessions', 'views', 'avg_duration', 'engagement_rate', 'key_events'];

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function __construct(
        private readonly array $credentials,
        private readonly string $propertyId,
    ) {}

    /**
     * Null kalau belum siap: ID properti kosong atau file kredensial tidak terbaca.
     */
    public static function make(?string $propertyId): ?self
    {
        $propertyId = trim((string) $propertyId);
        $path = config('services.google_analytics.credentials');

        if (! preg_match('/^\d+$/', $propertyId) || ! is_string($path) || ! is_readable($path)) {
            return null;
        }

        $credentials = json_decode((string) file_get_contents($path), true);

        if (! is_array($credentials) || ! isset($credentials['client_email'], $credentials['private_key'])) {
            return null;
        }

        return new self($credentials, $propertyId);
    }

    public function propertyId(): string
    {
        return $this->propertyId;
    }

    /**
     * Ringkasan RANGE_DAYS hari terakhir. Kegagalan dikembalikan sebagai data,
     * bukan exception, supaya halaman admin tetap terbuka.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $key = 'ga4:summary:'.$this->propertyId;

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $result = ['ok' => true, 'error' => null, 'days' => self::RANGE_DAYS] + $this->fetch();
        } catch (\Throwable $e) {
            report($e);

            return ['ok' => false, 'error' => $e->getMessage(), 'days' => self::RANGE_DAYS];
        }

        Cache::put($key, $result, now()->addHours(self::REPORT_TTL_HOURS));

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetch(): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(20)
            ->post(sprintf(self::REPORT_URL, 'properties/'.$this->propertyId), [
                'requests' => $this->requests(),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('GA4 menolak permintaan: '.$response->status().' '.$response->body());
        }

        $reports = $response->json('reports') ?? [];

        $metrics = [];
        foreach (self::METRIC_KEYS as $index => $key) {
            $metrics[$key] = (string) ($reports[0]['rows'][0]['metricValues'][$index]['value'] ?? '0');
        }

        return [
            'metrics' => $metrics,
            'pages' => $this->pairs($reports[1] ?? []),
            'countries' => $this->pairs($reports[2] ?? []),
            'channels' => $this->pairs($reports[3] ?? []),
            'devices' => $this->pairs($reports[4] ?? []),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function requests(): array
    {
        $range = [['startDate' => self::RANGE_DAYS.'daysAgo', 'endDate' => 'today']];

        return [
            [
                'dateRanges' => $range,
                'metrics' => array_map(
                    fn (string $name): array => ['name' => $name],
                    ['activeUsers', 'sessions', 'screenPageViews', 'averageSessionDuration', 'engagementRate', 'keyEvents'],
                ),
            ],
            $this->breakdown($range, 'pagePath', 'screenPageViews', 5),
            $this->breakdown($range, 'country', 'activeUsers', 5),
            $this->breakdown($range, 'sessionDefaultChannelGroup', 'sessions', 5),
            $this->breakdown($range, 'deviceCategory', 'activeUsers', 3),
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $range
     * @return array<string, mixed>
     */
    private function breakdown(array $range, string $dimension, string $metric, int $limit): array
    {
        return [
            'dateRanges' => $range,
            'dimensions' => [['name' => $dimension]],
            'metrics' => [['name' => $metric]],
            'orderBys' => [['metric' => ['metricName' => $metric], 'desc' => true]],
            'limit' => $limit,
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<int, array{label: string, value: int}>
     */
    private function pairs(array $report): array
    {
        return collect($report['rows'] ?? [])
            ->map(fn (array $row): array => [
                'label' => (string) ($row['dimensionValues'][0]['value'] ?? ''),
                'value' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ])
            ->all();
    }

    private function accessToken(): string
    {
        $cacheKey = 'ga4:token:'.md5((string) $this->credentials['client_email']);

        $cached = Cache::get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::asForm()->timeout(20)->post(
            (string) ($this->credentials['token_uri'] ?? self::DEFAULT_TOKEN_URL),
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->assertion(),
            ],
        );

        if ($response->failed() || ! $response->json('access_token')) {
            throw new RuntimeException('Google menolak kredensial service account: '.$response->status().' '.$response->body());
        }

        $token = (string) $response->json('access_token');

        Cache::put($cacheKey, $token, now()->addMinutes(50));

        return $token;
    }

    private function assertion(): string
    {
        $now = time();

        $signingInput = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
            .'.'.$this->base64Url(json_encode([
                'iss' => $this->credentials['client_email'],
                'scope' => self::SCOPE,
                'aud' => $this->credentials['token_uri'] ?? self::DEFAULT_TOKEN_URL,
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

        if (! openssl_sign($signingInput, $signature, (string) $this->credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Kunci privat service account tidak bisa dipakai untuk menandatangani JWT.');
        }

        return $signingInput.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
