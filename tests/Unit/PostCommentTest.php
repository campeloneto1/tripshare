<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_comment_can_be_created(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Ótimo post!',
        ]);

        $this->assertDatabaseHas('posts_comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Ótimo post!',
        ]);
    }

    public function test_post_comment_belongs_to_post(): void
    {
        $comment = PostComment::factory()->create();

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($comment->post_id, $comment->post->id);
    }

    public function test_post_comment_belongs_to_user(): void
    {
        $comment = PostComment::factory()->create();

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($comment->user_id, $comment->user->id);
    }

    public function test_user_can_comment_multiple_times(): void
    {
        $user = User::factory()->create();

        PostComment::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals(5, PostComment::where('user_id', $user->id)->count());
    }
}
