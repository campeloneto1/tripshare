<?php

namespace App\Observers;

use App\Models\TripUser;
use App\Models\TripHistory;

class TripUserObserver
{
    public function created(TripUser $tripUser): void
    {
        $userName = $tripUser->user->name;
        $roleNames = [
            'owner' => 'proprietário',
            'editor' => 'editor',
            'viewer' => 'visualizador',
        ];
        $roleName = $roleNames[$tripUser->role] ?? $tripUser->role;

        TripHistory::create([
            'trip_id' => $tripUser->trip_id,
            'user_id' => auth()->id(),
            'action' => 'person_added',
            'model_type' => TripUser::class,
            'model_id' => $tripUser->id,
            'description' => "{$userName} foi adicionado como {$roleName}",
        ]);
    }

    public function updated(TripUser $tripUser): void
    {
        $changes = [];
        $descriptions = [];

        foreach ($tripUser->getDirty() as $key => $value) {
            $original = $tripUser->getOriginal($key);
            $changes[$key] = [
                'old' => $original,
                'new' => $value,
            ];

            if ($key === 'role') {
                $roleNames = [
                    'owner' => 'proprietário',
                    'editor' => 'editor',
                    'viewer' => 'visualizador',
                ];
                $oldRole = $roleNames[$original] ?? $original;
                $newRole = $roleNames[$value] ?? $value;
                $descriptions[] = "permissão alterada de {$oldRole} para {$newRole}";
            }
        }

        if (!empty($changes)) {
            $userName = $tripUser->user->name;
            TripHistory::create([
                'trip_id' => $tripUser->trip_id,
                'user_id' => auth()->id(),
                'action' => 'person_updated',
                'model_type' => TripUser::class,
                'model_id' => $tripUser->id,
                'changes' => $changes,
                'description' => "{$userName}: " . implode(', ', $descriptions),
            ]);
        }
    }

    public function deleted(TripUser $tripUser): void
    {
        $userName = $tripUser->user->name;

        TripHistory::create([
            'trip_id' => $tripUser->trip_id,
            'user_id' => auth()->id(),
            'action' => 'person_removed',
            'model_type' => TripUser::class,
            'model_id' => $tripUser->id,
            'description' => "{$userName} foi removido da trip",
        ]);
    }

    public function restored(TripUser $tripUser): void
    {
        //
    }

    public function forceDeleted(TripUser $tripUser): void
    {
        //
    }
}
