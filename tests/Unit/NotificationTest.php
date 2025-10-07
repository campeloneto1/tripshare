<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use App\Models\VoteQuestion;
use App\Notifications\FollowAcceptedNotification;
use App\Notifications\FollowRequestNotification;
use App\Notifications\VoteQuestionCreatedNotification;
use App\Services\UserFollowService;
use App\Services\VoteQuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_request_notification_is_sent_when_following_private_user(): void
    {
        Notification::fake();

        $follower = User::factory()->create();
        $privateUser = User::factory()->create(['is_public' => false]);

        $this->actingAs($follower);

        $service = app(UserFollowService::class);
        $service->store(['following_id' => $privateUser->id]);

        Notification::assertSentTo(
            $privateUser,
            FollowRequestNotification::class,
            function ($notification, $channels) use ($follower) {
                return $notification->follower->id === $follower->id;
            }
        );
    }

    public function test_follow_accepted_notification_is_sent_when_following_public_user(): void
    {
        Notification::fake();

        $follower = User::factory()->create();
        $publicUser = User::factory()->create(['is_public' => true]);

        $this->actingAs($follower);

        $service = app(UserFollowService::class);
        $service->store(['following_id' => $publicUser->id]);

        Notification::assertSentTo(
            $follower,
            FollowAcceptedNotification::class,
            function ($notification, $channels) use ($publicUser) {
                return $notification->following->id === $publicUser->id;
            }
        );
    }

    public function test_follow_accepted_notification_is_sent_when_accepting_follow_request(): void
    {
        Notification::fake();

        $follower = User::factory()->create();
        $privateUser = User::factory()->create(['is_public' => false]);

        $this->actingAs($follower);

        $service = app(UserFollowService::class);
        $userFollow = $service->store(['following_id' => $privateUser->id]);

        // Clear notifications from initial follow request
        Notification::assertSentTo($privateUser, FollowRequestNotification::class);

        // Accept the follow
        $service->update($userFollow, [
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        Notification::assertSentTo(
            $follower,
            FollowAcceptedNotification::class
        );
    }

    public function test_vote_question_created_notification_is_sent_to_trip_participants(): void
    {
        Notification::fake();

        $userRole = Role::factory()->create(['name' => 'User']);
        $owner = User::factory()->create(['role_id' => $userRole->id]);
        $participant1 = User::factory()->create(['role_id' => $userRole->id]);
        $participant2 = User::factory()->create(['role_id' => $userRole->id]);

        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->users()->attach($participant1->id, ['role' => 'participant']);
        $trip->users()->attach($participant2->id, ['role' => 'participant']);

        $this->actingAs($owner);

        $service = app(VoteQuestionService::class);
        $service->store([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        // Should notify both participants but not the creator
        Notification::assertSentTo(
            [$participant1, $participant2],
            VoteQuestionCreatedNotification::class
        );

        Notification::assertNotSentTo($owner, VoteQuestionCreatedNotification::class);
    }

    public function test_vote_question_notification_is_not_sent_if_no_participants(): void
    {
        Notification::fake();

        $userRole = Role::factory()->create(['name' => 'User']);
        $owner = User::factory()->create(['role_id' => $userRole->id]);

        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $this->actingAs($owner);

        $service = app(VoteQuestionService::class);
        $service->store([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        // No notifications should be sent as there are no participants
        Notification::assertNothingSent();
    }

    public function test_vote_question_notification_excludes_creator(): void
    {
        Notification::fake();

        $userRole = Role::factory()->create(['name' => 'User']);
        $admin = User::factory()->create(['role_id' => $userRole->id]);
        $owner = User::factory()->create(['role_id' => $userRole->id]);

        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $this->actingAs($admin); // Admin creates the question

        $service = app(VoteQuestionService::class);
        $service->store([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        // Should notify owner but not the admin who created it
        Notification::assertSentTo($owner, VoteQuestionCreatedNotification::class);
        Notification::assertNotSentTo($admin, VoteQuestionCreatedNotification::class);
    }

    public function test_follow_request_notification_contains_correct_data(): void
    {
        $follower = User::factory()->create(['name' => 'John Doe']);
        $privateUser = User::factory()->create(['is_public' => false]);

        $notification = new FollowRequestNotification($follower);

        $this->assertEquals($follower->id, $notification->follower->id);
        $this->assertEquals('John Doe', $notification->follower->name);
    }

    public function test_follow_accepted_notification_contains_correct_data(): void
    {
        $following = User::factory()->create(['name' => 'Jane Smith']);

        $notification = new FollowAcceptedNotification($following);

        $this->assertEquals($following->id, $notification->following->id);
        $this->assertEquals('Jane Smith', $notification->following->name);
    }

    public function test_vote_question_created_notification_contains_correct_data(): void
    {
        $creator = User::factory()->create(['name' => 'Creator User']);
        $question = VoteQuestion::factory()->create(['title' => 'Test Question']);

        $notification = new VoteQuestionCreatedNotification($question, $creator);

        $this->assertEquals($question->id, $notification->voteQuestion->id);
        $this->assertEquals('Test Question', $notification->voteQuestion->title);
        $this->assertEquals($creator->id, $notification->creator->id);
    }
}
