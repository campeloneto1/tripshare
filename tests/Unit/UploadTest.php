<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\Trip;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_can_be_created(): void
    {
        $user = User::factory()->create();

        $upload = Upload::create([
            'uploadable_type' => User::class,
            'uploadable_id' => $user->id,
            'path' => 'uploads/avatar.jpg',
            'original_name' => 'avatar.jpg',
            'type' => 'image/jpeg',
            'size' => 1024,
            'is_main' => true,
        ]);

        $this->assertDatabaseHas('uploads', [
            'uploadable_type' => User::class,
            'uploadable_id' => $user->id,
            'path' => 'uploads/avatar.jpg',
        ]);
    }

    public function test_upload_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $upload = Upload::factory()->create([
            'uploadable_type' => User::class,
            'uploadable_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $upload->uploadable);
        $this->assertEquals($user->id, $upload->uploadable->id);
    }

    public function test_upload_belongs_to_post(): void
    {
        $post = Post::factory()->create();
        $upload = Upload::factory()->create([
            'uploadable_type' => Post::class,
            'uploadable_id' => $post->id,
        ]);

        $this->assertInstanceOf(Post::class, $upload->uploadable);
        $this->assertEquals($post->id, $upload->uploadable->id);
    }

    public function test_upload_belongs_to_trip(): void
    {
        $trip = Trip::factory()->create();
        $upload = Upload::factory()->create([
            'uploadable_type' => Trip::class,
            'uploadable_id' => $trip->id,
        ]);

        $this->assertInstanceOf(Trip::class, $upload->uploadable);
        $this->assertEquals($trip->id, $upload->uploadable->id);
    }

    public function test_upload_can_be_updated(): void
    {
        $upload = Upload::factory()->create(['is_main' => false]);

        $upload->update(['is_main' => true]);

        $this->assertTrue($upload->fresh()->is_main);
    }

    public function test_upload_can_be_deleted(): void
    {
        $upload = Upload::factory()->create();
        $uploadId = $upload->id;

        $upload->delete();

        $this->assertDatabaseMissing('uploads', ['id' => $uploadId]);
    }

    public function test_upload_url_attribute_returns_correct_url(): void
    {
        $upload = Upload::factory()->create(['path' => 'uploads/test.jpg']);

        $url = $upload->url;

        $this->assertStringContainsString('storage/uploads/test.jpg', $url);
    }

    public function test_upload_is_main_casts_to_boolean(): void
    {
        $upload = Upload::factory()->create(['is_main' => 1]);

        $this->assertTrue($upload->is_main);
        $this->assertIsBool($upload->is_main);
    }

    public function test_upload_order_casts_to_integer(): void
    {
        $upload = Upload::factory()->create(['order' => '5']);

        $this->assertEquals(5, $upload->order);
        $this->assertIsInt($upload->order);
    }

    public function test_upload_size_casts_to_integer(): void
    {
        $upload = Upload::factory()->create(['size' => '2048']);

        $this->assertEquals(2048, $upload->size);
        $this->assertIsInt($upload->size);
    }

    public function test_model_can_have_multiple_uploads(): void
    {
        $post = Post::factory()->create();

        Upload::factory()->count(3)->create([
            'uploadable_type' => Post::class,
            'uploadable_id' => $post->id,
        ]);

        $uploads = Upload::where('uploadable_type', Post::class)
            ->where('uploadable_id', $post->id)
            ->get();

        $this->assertEquals(3, $uploads->count());
    }

    public function test_upload_can_be_ordered(): void
    {
        $post = Post::factory()->create();

        Upload::factory()->create([
            'uploadable_type' => Post::class,
            'uploadable_id' => $post->id,
            'order' => 1,
        ]);

        Upload::factory()->create([
            'uploadable_type' => Post::class,
            'uploadable_id' => $post->id,
            'order' => 2,
        ]);

        $uploads = Upload::where('uploadable_type', Post::class)
            ->where('uploadable_id', $post->id)
            ->orderBy('order')
            ->get();

        $this->assertEquals(1, $uploads->first()->order);
        $this->assertEquals(2, $uploads->last()->order);
    }
}
