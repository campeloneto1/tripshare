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
}
