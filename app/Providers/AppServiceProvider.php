<?php

namespace App\Providers;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\TripUser;
use App\Observers\TripDayCityObserver;
use App\Observers\TripDayEventObserver;
use App\Observers\TripDayObserver;
use App\Observers\TripObserver;
use App\Observers\TripUserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Trip::observe(TripObserver::class);
        TripDay::observe(TripDayObserver::class);
        TripDayEvent::observe(TripDayEventObserver::class);
        TripUser::observe(TripUserObserver::class);
        TripDayCity::observe(TripDayCityObserver::class);
    }
}
