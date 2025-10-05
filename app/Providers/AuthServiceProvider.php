<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\TripDayEvent;
use App\Policies\TripDayEventPolicy;
use App\Models\TripDayCity;
use App\Models\TripUser;
use App\Models\User;
use App\Models\UserFollow;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\TripDayCityPolicy;
use App\Policies\TripDayPolicy;
use App\Policies\TripPolicy;
use App\Policies\TripUserPolicy;
use App\Policies\UserFollowPolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Trip::class        => TripPolicy::class,
        TripDay::class     => TripDayPolicy::class,
        TripDayEvent::class => TripDayEventPolicy::class,
        TripDayCity::class  => TripDayCityPolicy::class,
        TripUser::class     => TripUserPolicy::class,
        User::class        =>  UserPolicy::class,
        Role::class        =>  RolePolicy::class,
        Permission::class  =>  PermissionPolicy::class,
        UserFollow::class => UserFollowPolicy::class,

    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
