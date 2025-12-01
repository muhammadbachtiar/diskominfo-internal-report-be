<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Report\Models\Report;
use Infra\Roles\Models\Roles;
use Infra\Shared\Models\Unit;
use Infra\User\Models\User;
use Tests\TestCase;

class ReportFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_submit_report(): void
    {
        $unit = Unit::create(['id' => (string) \Ramsey\Uuid\Uuid::uuid7(), 'name' => 'Bidang A', 'code' => 'BIDA']);
        $user = User::factory()->create(['unit_id' => $unit->id]);
        $adminRole = Roles::firstOrCreate(['nama' => 'admin']);
        $user->roles()->syncWithoutDetaching([$adminRole->id]);
        $this->actingAs($user, 'api');

        $res = $this->postJson('/api/v1/reports', [
            'title' => 'Test',
            'lat' => -3.7,
            'lng' => 103.8,
            'accuracy' => 10,
        ]);
        $res->assertStatus(201);

        $id = $res->json('data.id');
        $report = Report::find($id);
        $this->assertEquals('draft', $report->status);
    }
}
