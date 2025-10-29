<?php

namespace Tests\Feature;

use Domain\Asset\Services\AttachAssetToReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Infra\Asset\Models\Asset;
use Infra\Report\Models\Report;
use Infra\Roles\Models\Roles;
use Infra\Shared\Models\Unit;
use Infra\User\Models\User;
use Tests\TestCase;

class ReportAssetAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_report_assets(): void
    {
        [$user, $unit] = $this->actingAsAdminUser();
        $report = $this->createReport($user, $unit);
        $asset = $this->createAsset($unit);

        AttachAssetToReportService::resolve()->execute((string) $asset->id, (string) $report->id, 'For documentation');

        $response = $this->getJson("/api/v1/reports/{$report->id}/assets");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.asset.id', (string) $asset->id)
            ->assertJsonPath('data.0.note', 'For documentation');

        $this->assertNotNull($response->json('data.0.attached_at'));
    }

    public function test_can_detach_asset_from_report(): void
    {
        [$user, $unit] = $this->actingAsAdminUser();
        $report = $this->createReport($user, $unit);
        $asset = $this->createAsset($unit);

        AttachAssetToReportService::resolve()->execute((string) $asset->id, (string) $report->id, 'Temporary use');

        $response = $this->deleteJson("/api/v1/reports/{$report->id}/assets/{$asset->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('report_assets', [
            'report_id' => $report->id,
            'asset_id' => $asset->id,
        ]);
    }

    public function test_detach_returns_not_found_when_not_attached(): void
    {
        [$user, $unit] = $this->actingAsAdminUser();
        $report = $this->createReport($user, $unit);
        $asset = $this->createAsset($unit);

        $response = $this->deleteJson("/api/v1/reports/{$report->id}/assets/{$asset->id}");

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    private function actingAsAdminUser(): array
    {
        $unit = Unit::create([
            'name' => 'Bidang A',
            'code' => 'BID-' . Str::upper(Str::random(4)),
        ]);

        $user = User::factory()->create(['unit_id' => $unit->id]);
        $role = Roles::firstOrCreate(['nama' => 'admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->actingAs($user, 'api');

        return [$user, $unit];
    }

    private function createReport(User $user, Unit $unit): Report
    {
        return Report::create([
            'number' => 'RPT-' . Str::upper(Str::random(6)),
            'title' => 'Routine Inspection',
            'description' => 'Generated for testing',
            'unit_id' => $unit->id,
            'created_by' => $user->id,
            'lat' => -6.2,
            'lng' => 106.8,
            'accuracy' => 5,
        ]);
    }

    private function createAsset(Unit $unit): Asset
    {
        return Asset::create([
            'name' => 'Camera',
            'code' => 'AST-' . Str::upper(Str::random(5)),
            'unit_id' => $unit->id,
        ]);
    }
}
