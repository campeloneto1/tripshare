<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\User;
use App\Models\Role;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserService::class);
    }

    public function test_user_can_be_created(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => Hash::make('password'),
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);
    }

    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $updated = $this->service->update($user, [
            'name' => 'Updated Name',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_user_password_is_hashed_on_update(): void
    {
        $user = User::factory()->create();

        $updated = $this->service->update($user, [
            'password' => 'newpassword',
        ]);

        $this->assertTrue(Hash::check('newpassword', $updated->password));
    }

    public function test_user_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();

        $result = $this->service->delete($user);

        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_user_can_be_restored(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $result = $this->service->restore($user);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_can_be_force_deleted(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();

        $result = $this->service->forceDelete($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_user_has_many_trips(): void
    {
        $user = User::factory()->create();

        Trip::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertEquals(3, $user->trips()->count());
    }

    public function test_user_belongs_to_many_trips_as_participant(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create();

        $trip->users()->attach($user->id, ['role' => 'participant']);

        $this->assertEquals(1, $user->tripsParticipating()->count());
    }

    public function test_user_has_role_relationship(): void
    {
        $role = Role::factory()->create(['name' => 'User']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals('User', $user->role->name);
    }

    public function test_user_has_permission_method_works(): void
    {
        $role = Role::factory()->create(['name' => 'Admin']);
        $permission = \App\Models\Permission::factory()->create(['name' => 'delete_trips']);
        $role->permissions()->attach($permission->id);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasPermission('delete_trips'));
        $this->assertFalse($user->hasPermission('nonexistent_permission'));
    }

    public function test_user_is_admin_method_works(): void
    {
        $adminRole = Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        $userRole = Role::factory()->create(['id' => 2, 'name' => 'User']);

        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $regularUser = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertTrue($admin->is_admin());
        $this->assertFalse($regularUser->is_admin());
    }

    public function test_user_is_public_field_defaults_correctly(): void
    {
        $publicUser = User::factory()->create(['is_public' => true]);
        $privateUser = User::factory()->create(['is_public' => false]);

        $this->assertTrue($publicUser->is_public);
        $this->assertFalse($privateUser->is_public);
    }

    public function test_user_avatar_url_returns_default_when_no_upload(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $avatarUrl = $user->avatar_url;

        $this->assertStringContainsString('ui-avatars.com', $avatarUrl);
        $this->assertStringContainsString('John+Doe', $avatarUrl);
    }

    public function test_user_followers_relationship_only_returns_accepted(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => true]);

        $this->actingAs($follower);

        // Cria follow aceito
        app(\App\Services\UserFollowService::class)->store([
            'following_id' => $following->id,
        ]);

        $followers = $following->followers;

        $this->assertEquals(1, $followers->count());
        $this->assertEquals($follower->id, $followers->first()->id);
    }

    public function test_user_following_relationship_only_returns_accepted(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => true]);

        $this->actingAs($follower);

        app(\App\Services\UserFollowService::class)->store([
            'following_id' => $following->id,
        ]);

        $followingUsers = $follower->following;

        $this->assertEquals(1, $followingUsers->count());
        $this->assertEquals($following->id, $followingUsers->first()->id);
    }

    public function test_user_find_respects_privacy_for_trips(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        // Trip pública
        Trip::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        // Trip privada
        Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->actingAs($viewer);

        $foundUser = $this->service->find($owner->id);

        // Deve ver apenas a trip pública
        $this->assertEquals(1, $foundUser->trips->count());
    }

    public function test_user_can_see_all_own_trips(): void
    {
        $owner = User::factory()->create();

        Trip::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->actingAs($owner);

        $foundUser = $this->service->find($owner->id);

        // Deve ver todas as próprias trips
        $this->assertEquals(2, $foundUser->trips->count());
    }
}
