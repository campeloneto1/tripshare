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

    public function test_post_comment_can_be_updated(): void
    {
        $comment = PostComment::factory()->create([
            'content' => 'Original comment',
        ]);

        $comment->update([
            'content' => 'Updated comment',
        ]);

        $this->assertEquals('Updated comment', $comment->fresh()->content);
    }

    public function test_post_comment_content_can_be_edited(): void
    {
        $comment = PostComment::factory()->create([
            'content' => 'First version',
        ]);

        $comment->update(['content' => 'Edited version']);

        $this->assertDatabaseHas('posts_comments', [
            'id' => $comment->id,
            'content' => 'Edited version',
        ]);
    }

    public function test_post_comment_can_be_deleted(): void
    {
        $comment = PostComment::factory()->create();
        $commentId = $comment->id;

        $comment->delete();

        $this->assertDatabaseMissing('posts_comments', ['id' => $commentId]);
    }

    public function test_deleting_comment_decreases_post_comment_count(): void
    {
        $post = Post::factory()->create();

        PostComment::factory()->count(3)->create(['post_id' => $post->id]);

        $this->assertEquals(3, $post->comments()->count());

        $firstComment = $post->comments()->first();
        $firstComment->delete();

        $this->assertEquals(2, $post->fresh()->comments()->count());
    }

    public function test_deleting_post_deletes_all_comments(): void
    {
        $post = Post::factory()->create();

        $comment1 = PostComment::factory()->create(['post_id' => $post->id]);
        $comment2 = PostComment::factory()->create(['post_id' => $post->id]);

        $post->delete();

        $this->assertDatabaseMissing('posts_comments', ['id' => $comment1->id]);
        $this->assertDatabaseMissing('posts_comments', ['id' => $comment2->id]);
    }
}
