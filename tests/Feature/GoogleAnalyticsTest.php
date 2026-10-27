<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GoogleAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function setMeasurementId(string $id): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'google_analytics_id'],
            ['value' => $id, 'group_name' => 'analytics'],
        );
    }

    private function setPropertyId(string $id): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'google_analytics_property_id'],
            ['value' => $id, 'group_name' => 'analytics'],
        );
    }

    /**
     * Service account palsu dari tests/Fixtures. Kunci di berkas itu kunci
     * buangan tanpa akses apa pun, dipakai supaya jalur tanda tangan JWT RS256
     * ikut teruji dan bukan cuma parsing respons.
     */
    private function fakeServiceAccount(): void
    {
        config(['services.google_analytics.credentials' => base_path('tests/Fixtures/google-service-account.json')]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fakeReports(): array
    {
        return [
            ['rows' => [['metricValues' => array_map(
                fn (string $value): array => ['value' => $value],
                ['150', '200', '310', '95.5', '0.62', '12'],
            )]]],
            ['rows' => [['dimensionValues' => [['value' => '/']], 'metricValues' => [['value' => '120']]]]],
            ['rows' => [['dimensionValues' => [['value' => 'Indonesia']], 'metricValues' => [['value' => '140']]]]],
            ['rows' => [['dimensionValues' => [['value' => 'Organic Search']], 'metricValues' => [['value' => '170']]]]],
            ['rows' => [['dimensionValues' => [['value' => 'mobile']], 'metricValues' => [['value' => '110']]]]],
        ];
    }

    private function fakeGoogleApis(int $reportStatus = 200): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3600]),
            'analyticsdata.googleapis.com/*' => Http::response(['reports' => $this->fakeReports()], $reportStatus),
        ]);
    }

    public function test_gtag_renders_in_production_when_id_is_set(): void
    {
        $this->app['env'] = 'production';
        $this->setMeasurementId('G-TEST12345678');

        $this->get('https://localhost/')
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js?id=G-TEST12345678', false)
            ->assertSee("gtag('config', 'G-TEST12345678')", false);
    }

    public function test_gtag_is_not_rendered_outside_production(): void
    {
        $this->setMeasurementId('G-TEST12345678');

        $this->get('/')->assertOk()->assertDontSee('googletagmanager.com', false);
    }

    public function test_gtag_is_not_rendered_when_id_is_empty(): void
    {
        $this->app['env'] = 'production';

        $this->get('https://localhost/')->assertOk()->assertDontSee('googletagmanager.com', false);
    }

    public function test_admin_can_save_and_clear_measurement_id(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/settings/analytics', [
            'google_analytics_id' => 'G-ABC1234567',
        ])->assertRedirect();

        $this->assertSame('G-ABC1234567', SiteSetting::where('key', 'google_analytics_id')->value('value'));

        $this->actingAs($admin)->post('/admin/settings/analytics', [
            'google_analytics_id' => '',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('', SiteSetting::where('key', 'google_analytics_id')->value('value'));
    }

    public function test_invalid_measurement_id_is_rejected(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/settings/analytics', [
            'google_analytics_id' => 'UA-123456-1',
        ])->assertSessionHasErrors('google_analytics_id');

        $this->assertDatabaseMissing('site_settings', ['key' => 'google_analytics_id', 'value' => 'UA-123456-1']);
    }

    public function test_analytics_page_renders_for_admin_only(): void
    {
        $this->get('/admin/settings/analytics')->assertRedirect('/login');

        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/settings/analytics')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Settings/Analytics')->has('groupedSettings'));
    }

    public function test_analytics_page_shows_report_when_service_account_is_ready(): void
    {
        $this->fakeServiceAccount();
        $this->fakeGoogleApis();
        $this->setPropertyId('123456789');

        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/settings/analytics')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('report.ok', true)
                ->where('report.days', 28)
                ->where('report.metrics.users', '150')
                ->where('report.metrics.sessions', '200')
                ->where('report.metrics.views', '310')
                ->where('report.metrics.key_events', '12')
                ->where('report.pages.0.value', 120)
                ->where('report.countries.0.label', 'Indonesia')
                ->where('report.channels.0.label', 'Organic Search')
                ->where('report.devices.0.label', 'mobile')
            );

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), 'oauth2.googleapis.com')) {
                return false;
            }

            $assertion = explode('.', (string) $request['assertion']);

            return count($assertion) === 3 && $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer';
        });

        Http::assertSent(fn ($request) => str_contains($request->url(), 'properties/123456789:batchRunReports')
            && $request->hasHeader('Authorization', 'Bearer fake-token')
            && count($request['requests']) === 5);
    }

    public function test_analytics_page_reports_api_failure_without_breaking(): void
    {
        $this->fakeServiceAccount();
        $this->fakeGoogleApis(403);
        $this->setPropertyId('123456789');

        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/settings/analytics')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Analytics')
                ->where('report.ok', false)
                ->missing('report.metrics')
                ->where('report.error', fn ($error) => is_string($error) && $error !== '')
            );
    }

    public function test_analytics_page_has_no_report_without_property_id_or_credentials(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/settings/analytics')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('report', null));

        $this->fakeServiceAccount();

        $this->actingAs($admin)->get('/admin/settings/analytics')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('report', null));
    }

    public function test_admin_can_save_property_id_and_non_numeric_is_rejected(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/settings/analytics', [
            'google_analytics_property_id' => '123456789',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('123456789', SiteSetting::where('key', 'google_analytics_property_id')->value('value'));

        $this->actingAs($admin)->post('/admin/settings/analytics', [
            'google_analytics_property_id' => 'G-ABC1234567',
        ])->assertSessionHasErrors('google_analytics_property_id');

        $this->assertSame('123456789', SiteSetting::where('key', 'google_analytics_property_id')->value('value'));
    }
}
