<?php

namespace App\Models;

use App\Traits\LogsTripHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Trip extends Model
{
    /** @use HasFactory<\Database\Factories\TripFactory> */
    use HasFactory, SoftDeletes, LogsTripHistory;

    protected $table = 'trips';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'is_public',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_public' => 'boolean',
    ];

    // Removido eager loading global - use scopes quando necessário

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'trips_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function days()
    {
        return $this->hasMany(TripDay::class)->orderBy('date', 'asc');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Carrega relações básicas
     */
    public function scopeWithRelations($query)
    {
        return $query->with([
            'user:id,name,username,email',
            'uploads' => fn($q) => $q->where('is_main', true),
        ]);
    }

    /**
     * Carrega relações completas incluindo days
     */
    public function scopeWithFullRelations($query)
    {
        return $query->withRelations()
            ->withCount(['users', 'days'])
            ->with([
                'days' => fn($q) => $q->with([
                    'cities' => fn($cq) => $cq->with('events')
                ])
            ]);
    }

    /**
     * Trips públicas
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Trips de um usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Trips que o usuário participa (owner ou membro)
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('users', fn($uq) => $uq->where('user_id', $userId));
        });
    }

    /**
     * Trips ativas (não deletadas e com datas futuras ou presentes)
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    /**
     * Ordena por data de início
     */
    public function scopeOrderByStartDate($query, $direction = 'asc')
    {
        return $query->orderBy('start_date', $direction);
    }

    /**
     * Retorna flags de estado da viagem
     */
    public function getFlagsAttribute(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'is_owner' => false,
                'is_admin' => false,
                'is_participant' => false,
                'is_visitant' => $this->is_public,
            ];
        }

        return [
            'is_owner' => $user->id === $this->user_id,
            'is_admin' => $this->users()->where('user_id', $user->id)->where('role', 'admin')->exists(),
            'is_participant' => $this->users()->where('user_id', $user->id)->where('role', 'participant')->exists(),
            'is_visitant' => $this->is_public && !$this->users()->where('user_id', $user->id)->exists() && $user->id !== $this->user_id,
        ];
    }

    /**
     * Retorna resumo de métricas da viagem
     */
    public function getSummaryAttribute(): array
    {
        $cacheKey = "trip_summary_{$this->id}";

        return Cache::remember($cacheKey, now()->addHours(1), function () {
            $allEvents = $this->days->flatMap(fn($day) =>
                $day->cities->flatMap(fn($city) => $city->events)
            );

            return [
                'total_days' => (int) $this->days->count(),
                'total_cities' => (int) $this->days->sum(fn($day) => $day->cities->count()),
                'total_events' => (int) $allEvents->count(),
                'total_value' => (float) $allEvents->sum('price'),
                'total_events_by_type' => [
                    'hotel' => $allEvents->where('type', 'hotel')->count(),
                    'restaurant' => $allEvents->where('type', 'restaurant')->count(),
                    'attraction' => $allEvents->where('type', 'attraction')->count(),
                    'transport' => $allEvents->where('type', 'transport')->count(),
                    'other' => $allEvents->where('type', 'other')->count(),
                ],
                'total_value_by_type' => [
                    'hotel' => (float) $allEvents->where('type', 'hotel')->sum('price'),
                    'restaurant' => (float) $allEvents->where('type', 'restaurant')->sum('price'),
                    'attraction' => (float) $allEvents->where('type', 'attraction')->sum('price'),
                    'transport' => (float) $allEvents->where('type', 'transport')->sum('price'),
                    'other' => (float) $allEvents->where('type', 'other')->sum('price'),
                ],
            ];
        });
    }

    /**
     * Limpa o cache do summary quando a viagem é modificada
     */
    public function clearSummaryCache(): void
    {
        Cache::forget("trip_summary_{$this->id}");
    }

    /**
     * Boot method para limpar cache automaticamente
     */
    protected static function booted(): void
    {
        static::updated(function (Trip $trip) {
            $trip->clearSummaryCache();
        });

        static::deleted(function (Trip $trip) {
            $trip->clearSummaryCache();
        });
    }
}
