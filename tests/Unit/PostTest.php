<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_be_created(): void
    {
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'Meu primeiro post!',
        ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'Meu primeiro post!',
        ]);
    }

    public function test_post_belongs_to_user(): void
    {
        $post = Post::factory()->create();

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($post->user_id, $post->user->id);
    }

    public function test_post_can_belong_to_trip(): void
    {
        $trip = Trip::factory()->create();
        $post = Post::factory()->create([
            'trip_id' => $trip->id,
        ]);

        $this->assertInstanceOf(Trip::class, $post->trip);
        $this->assertEquals($trip->id, $post->trip->id);
    }

    public function test_post_can_be_a_share(): void
    {
        $originalPost = Post::factory()->create();
        $sharedPost = Post::factory()->create([
            'shared_post_id' => $originalPost->id,
        ]);

        $this->assertInstanceOf(Post::class, $sharedPost->sharedPost);
        $this->assertEquals($originalPost->id, $sharedPost->sharedPost->id);
        $this->assertTrue($sharedPost->is_shared);
    }

    public function test_post_has_many_comments(): void
    {
        $post = Post::factory()->create();

        PostComment::factory()->count(3)->create([
            'post_id' => $post->id,
        ]);

        $this->assertEquals(3, $post->comments()->count());
    }

    public function test_post_has_many_likes(): void
    {
        $post = Post::factory()->create();

        PostLike::factory()->count(5)->create([
            'post_id' => $post->id,
        ]);

        $this->assertEquals(5, $post->likes()->count());
    }

    public function test_post_scope_for_user(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['user_id' => $user->id]);
        Post::factory()->count(2)->create();

        $userPosts = Post::forUser($user->id)->get();

        $this->assertEquals(3, $userPosts->count());
    }

    public function test_post_scope_for_trip(): void
    {
        $trip = Trip::factory()->create();
        Post::factory()->count(2)->create(['trip_id' => $trip->id]);
        Post::factory()->count(3)->create();

        $tripPosts = Post::forTrip($trip->id)->get();

        $this->assertEquals(2, $tripPosts->count());
    }

    public function test_post_type_attribute(): void
    {
        $regularPost = Post::factory()->create(['trip_id' => null, 'shared_post_id' => null]);
        $tripPost = Post::factory()->create(['trip_id' => Trip::factory()->create()->id]);
        $sharedPost = Post::factory()->create(['shared_post_id' => Post::factory()->create()->id]);

        $this->assertEquals('regular', $regularPost->type);
        $this->assertEquals('trip', $tripPost->type);
        $this->assertEquals('shared', $sharedPost->type);
    }

    public function test_post_can_be_updated(): void
    {
        $post = Post::factory()->create([
            'content' => 'Original content',
        ]);

        $post->update([
            'content' => 'Updated content',
        ]);

        $this->assertEquals('Updated content', $post->fresh()->content);
    }

    public function test_post_content_can_be_updated(): void
    {
        $post = Post::factory()->create([
            'content' => 'First version',
        ]);

        $post->update(['content' => 'Second version']);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content' => 'Second version',
        ]);
    }

    public function test_post_photos_can_be_updated(): void
    {
        $post = Post::factory()->create([
            'photos' => json_encode(['photo1.jpg']),
        ]);

        $post->update([
            'photos' => json_encode(['photo1.jpg', 'photo2.jpg', 'photo3.jpg']),
        ]);

        $photos = json_decode($post->fresh()->photos, true);
        $this->assertCount(3, $photos);
    }

    public function test_post_can_be_deleted(): void
    {
        $post = Post::factory()->create();
        $postId = $post->id;

        $post->delete();

        $this->assertDatabaseMissing('posts', ['id' => $postId]);
    }

    public function test_deleting_post_deletes_comments(): void
    {
        $post = Post::factory()->create();

        $comment1 = PostComment::factory()->create(['post_id' => $post->id]);
        $comment2 = PostComment::factory()->create(['post_id' => $post->id]);

        $post->delete();

        $this->assertEquals(0, PostComment::where('post_id', $post->id)->count());
    }

    public function test_deleting_post_deletes_likes(): void
    {
        $post = Post::factory()->create();

        PostLike::factory()->count(5)->create(['post_id' => $post->id]);

        $this->assertEquals(5, $post->likes()->count());

        $post->delete();

        $this->assertEquals(0, PostLike::where('post_id', $post->id)->count());
    }

    public function test_deleting_shared_post_does_not_delete_original(): void
    {
        $originalPost = Post::factory()->create(['content' => 'Original']);
        $sharedPost = Post::factory()->create(['shared_post_id' => $originalPost->id]);

        $sharedPost->delete();

        $this->assertDatabaseHas('posts', ['id' => $originalPost->id]);
        $this->assertDatabaseMissing('posts', ['id' => $sharedPost->id]);
    }
}
