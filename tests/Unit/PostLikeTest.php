<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_like_can_be_created(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $like = PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('posts_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_post_like_belongs_to_post(): void
    {
        $like = PostLike::factory()->create();

        $this->assertInstanceOf(Post::class, $like->post);
        $this->assertEquals($like->post_id, $like->post->id);
    }

    public function test_post_like_belongs_to_user(): void
    {
        $like = PostLike::factory()->create();

        $this->assertInstanceOf(User::class, $like->user);
        $this->assertEquals($like->user_id, $like->user->id);
    }

    public function test_user_can_like_multiple_posts(): void
    {
        $user = User::factory()->create();

        PostLike::factory()->count(10)->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals(10, PostLike::where('user_id', $user->id)->count());
    }

    public function test_post_can_have_multiple_likes(): void
    {
        $post = Post::factory()->create();

        PostLike::factory()->count(15)->create([
            'post_id' => $post->id,
        ]);

        $this->assertEquals(15, $post->likes()->count());
    }

    public function test_post_like_can_be_deleted_unlike(): void
    {
        $like = PostLike::factory()->create();
        $likeId = $like->id;

        $like->delete();

        $this->assertDatabaseMissing('posts_likes', ['id' => $likeId]);
    }

    public function test_user_can_unlike_post(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $like = PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals(1, $post->likes()->count());

        $like->delete();

        $this->assertEquals(0, $post->fresh()->likes()->count());
    }

    public function test_deleting_like_decreases_post_like_count(): void
    {
        $post = Post::factory()->create();

        PostLike::factory()->count(10)->create(['post_id' => $post->id]);

        $this->assertEquals(10, $post->likes()->count());

        $firstLike = $post->likes()->first();
        $firstLike->delete();

        $this->assertEquals(9, $post->fresh()->likes()->count());
    }

    public function test_deleting_post_deletes_all_likes(): void
    {
        $post = Post::factory()->create();

        $like1 = PostLike::factory()->create(['post_id' => $post->id]);
        $like2 = PostLike::factory()->create(['post_id' => $post->id]);
        $like3 = PostLike::factory()->create(['post_id' => $post->id]);

        $post->delete();

        $this->assertDatabaseMissing('posts_likes', ['id' => $like1->id]);
        $this->assertDatabaseMissing('posts_likes', ['id' => $like2->id]);
        $this->assertDatabaseMissing('posts_likes', ['id' => $like3->id]);
    }

    public function test_user_cannot_like_same_post_twice(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }
}
