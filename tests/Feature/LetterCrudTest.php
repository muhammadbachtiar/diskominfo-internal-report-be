<?php

namespace Tests\Feature;

use Infra\User\Models\User;
use Domain\Letter\Services\GeminiLetterAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Infra\Letter\Models\Classification;
use Infra\Letter\Models\Letter;
use Infra\Shared\Models\Unit;
use Tests\TestCase;

class LetterCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
        
        // Create necessary data
        $this->classification = Classification::create(['name' => 'Biasa']);
        $this->unit = Unit::create(['name' => 'IT Department']);
        
        Storage::fake('s3');
    }

    public function test_can_analyze_letter()
    {
        $mock = $this->mock(GeminiLetterAnalysisService::class);
        $mock->shouldReceive('analyze')
            ->once()
            ->andReturn([
                'pengirim' => 'Setda',
                'tahun' => '2024',
                'nomor_surat' => '123/ABC/2024',
                'perihal' => 'Undangan Rapat',
                'sifat' => 'Biasa',
                'deskripsi' => 'Rapat koordinasi mingguan.'
            ]);

        $file = UploadedFile::fake()->create('letter.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/v1/letters/analyze', [
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('pengirim', 'Setda');
    }

    public function test_can_store_incoming_letter()
    {
        $file = UploadedFile::fake()->image('incoming.jpg');

        $response = $this->postJson('/api/v1/letters/incoming', [
            'letter_number' => 'SURAT-001',
            'sender_receiver' => 'Dinas Pendidikan',
            'date_of_letter' => '2024-01-13',
            'year' => 2024,
            'subject' => 'Surat Pemberitahuan',
            'classification_id' => $this->classification->id,
            'unit_id' => $this->unit->id,
            'description' => 'Ringkasan isi surat',
            'file' => $file,
            'metadata_ai' => ['original_data' => 'raw output']
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('letters', [
            'letter_number' => 'SURAT-001',
            'type' => 'incoming'
        ]);
        
        $letter = Letter::where('letter_number', 'SURAT-001')->first();
        Storage::disk('s3')->assertExists($letter->file_url);
    }

    public function test_can_list_letters_with_filters()
    {
        Letter::create([
            'type' => 'incoming',
            'letter_number' => 'IN-001',
            'sender_receiver' => 'Sender A',
            'date_of_letter' => '2024-01-01',
            'year' => 2024,
            'subject' => 'Subject A',
            'classification_id' => $this->classification->id,
            'unit_id' => $this->unit->id,
            'file_url' => 'path/to/file',
            'created_by' => $this->user->id
        ]);

        $response = $this->getJson('/api/v1/letters?type=incoming&year=2024');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
