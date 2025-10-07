<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Role;
use App\Models\Trip;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Trip $trip;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->userRole = Role::factory()->create(['name' => 'User']);
        $this->user = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->trip = Trip::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_unauthenticated_user_cannot_upload(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->postJson('/api/v1/uploads', [
            'file' => $file,
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_can_upload_single_file(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 600, 400);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
                'type' => 'image',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'path', 'original_name', 'type'],
            ]);

        $this->assertDatabaseHas('uploads', [
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
            'original_name' => 'photo.jpg',
            'type' => 'image',
        ]);

        // Verify file was stored
        $upload = Upload::where('uploadable_id', $this->trip->id)->first();
        Storage::disk('public')->assertExists($upload->path);
    }

    public function test_can_upload_multiple_files(): void
    {
        $files = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
            UploadedFile::fake()->image('photo3.jpg'),
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'files' => $files,
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
                'type' => 'image',
            ]);

        $response->assertStatus(201)
            ->assertJsonCount(3, 'data');

        $this->assertEquals(3, Upload::where('uploadable_id', $this->trip->id)->count());
    }

    public function test_first_file_in_multiple_upload_becomes_main(): void
    {
        $files = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
        ];

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'files' => $files,
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
            ]);

        $uploads = Upload::where('uploadable_id', $this->trip->id)
            ->orderBy('order')
            ->get();

        $this->assertTrue($uploads->first()->is_main);
        $this->assertFalse($uploads->last()->is_main);
    }

    public function test_can_upload_with_is_main_flag(): void
    {
        $file = UploadedFile::fake()->image('main.jpg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
                'is_main' => true,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('uploads', [
            'uploadable_id' => $this->trip->id,
            'is_main' => true,
        ]);
    }

    public function test_can_upload_to_different_uploadable_types(): void
    {
        $place = Place::factory()->create();

        $file = UploadedFile::fake()->image('place.jpg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_type' => Place::class,
                'uploadable_id' => $place->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('uploads', [
            'uploadable_type' => Place::class,
            'uploadable_id' => $place->id,
        ]);
    }

    public function test_can_list_uploads_for_uploadable(): void
    {
        Upload::factory()->count(3)->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/uploads?uploadable_type=" . Trip::class . "&uploadable_id={$this->trip->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_view_single_upload(): void
    {
        $upload = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/uploads/{$upload->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $upload->id]);
    }

    public function test_can_update_upload_metadata(): void
    {
        $upload = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
            'order' => 0,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/uploads/{$upload->id}", [
                'order' => 5,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('uploads', [
            'id' => $upload->id,
            'order' => 5,
        ]);
    }

    public function test_can_set_upload_as_main(): void
    {
        $upload1 = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
            'is_main' => true,
        ]);

        $upload2 = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
            'is_main' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/uploads/{$upload2->id}", [
                'is_main' => true,
            ]);

        $response->assertStatus(200);

        // upload2 should now be main
        $this->assertTrue($upload2->fresh()->is_main);

        // upload1 should no longer be main
        $this->assertFalse($upload1->fresh()->is_main);
    }

    public function test_can_delete_upload(): void
    {
        $file = UploadedFile::fake()->image('delete-me.jpg');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
            ]);

        $upload = Upload::where('uploadable_id', $this->trip->id)->first();
        $path = $upload->path;

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/uploads/{$upload->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('uploads', ['id' => $upload->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_can_reorder_uploads(): void
    {
        $upload1 = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
            'order' => 0,
        ]);

        $upload2 = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
            'order' => 1,
        ]);

        $upload3 = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $this->trip->id,
            'order' => 2,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads/reorder', [
                'upload_ids' => [$upload3->id, $upload1->id, $upload2->id],
            ]);

        $response->assertStatus(200);

        $this->assertEquals(0, $upload3->fresh()->order);
        $this->assertEquals(1, $upload1->fresh()->order);
        $this->assertEquals(2, $upload2->fresh()->order);
    }

    public function test_validation_error_when_uploading_without_file(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Nenhum arquivo enviado.']);
    }

    public function test_validation_error_when_missing_uploadable_type(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_id' => $this->trip->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_validation_error_when_missing_uploadable_id(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_type' => Trip::class,
            ]);

        $response->assertStatus(422);
    }

    public function test_reorder_requires_array_of_upload_ids(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads/reorder', [
                'upload_ids' => 'not-an-array',
            ]);

        $response->assertStatus(422);
    }

    public function test_stores_original_filename(): void
    {
        $file = UploadedFile::fake()->image('my-vacation-photo.jpg');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
            ]);

        $this->assertDatabaseHas('uploads', [
            'uploadable_id' => $this->trip->id,
            'original_name' => 'my-vacation-photo.jpg',
        ]);
    }

    public function test_stores_file_size(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg')->size(1024); // 1MB

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/uploads', [
                'file' => $file,
                'uploadable_type' => Trip::class,
                'uploadable_id' => $this->trip->id,
            ]);

        $upload = Upload::where('uploadable_id', $this->trip->id)->first();

        $this->assertNotNull($upload->size);
        $this->assertGreaterThan(0, $upload->size);
    }

    public function test_empty_list_when_no_uploadable_params(): void
    {
        Upload::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/uploads');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
