<?php

namespace App\Observers;

use App\Jobs\SendTransportReminder;
use App\Jobs\SendTripCheckInReminder;
use App\Models\TripUser;
use App\Models\TripHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

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

        // Agenda lembretes se tiver informações de transporte
        $this->scheduleTransportReminders($tripUser);
    }

    public function updated(TripUser $tripUser): void
    {
        $changes = [];
        $descriptions = [];
        $transportChanged = false;

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

            // Detecta mudanças nos dados de transporte
            if (in_array($key, ['transport_type', 'transport_datetime'])) {
                $transportChanged = true;
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

        // Reagenda lembretes se dados de transporte mudaram
        if ($transportChanged) {
            // Cancela jobs antigos antes de criar novos
            $this->cancelOldReminders($tripUser);
            $this->scheduleTransportReminders($tripUser);
        }
    }

    public function deleted(TripUser $tripUser): void
    {
        $userName = $tripUser->user->name;

        // Cancela lembretes pendentes quando remove participante
        $this->cancelOldReminders($tripUser);

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

    /**
     * Cancela lembretes antigos do participante
     */
    private function cancelOldReminders(TripUser $tripUser): void
    {
        // Cancela job de check-in se existir
        if ($tripUser->checkin_reminder_job_id) {
            try {
                Queue::deleteJobById($tripUser->checkin_reminder_job_id);
            } catch (\Exception $e) {
                // Job já pode ter sido executado ou não existe mais
            }
        }

        // Cancela job de transporte se existir
        if ($tripUser->transport_reminder_job_id) {
            try {
                Queue::deleteJobById($tripUser->transport_reminder_job_id);
            } catch (\Exception $e) {
                // Job já pode ter sido executado ou não existe mais
            }
        }

        // Limpa os IDs dos jobs cancelados
        $tripUser->checkin_reminder_job_id = null;
        $tripUser->transport_reminder_job_id = null;
        $tripUser->saveQuietly(); // Save sem disparar observers novamente
    }

    /**
     * Agenda lembretes de transporte para o participante
     */
    private function scheduleTransportReminders(TripUser $tripUser): void
    {
        // Carrega a trip se não estiver carregada
        if (!$tripUser->relationLoaded('trip')) {
            $tripUser->load('trip');
        }

        // Carrega o user se não estiver carregado
        if (!$tripUser->relationLoaded('user')) {
            $tripUser->load('user');
        }

        // Agenda check-in 3 dias antes APENAS para quem vai de avião
        if ($tripUser->transport_type === 'plane' && $tripUser->transport_datetime) {
            $transportDate = Carbon::parse($tripUser->transport_datetime);
            $threeDaysBeforeTransport = $transportDate->copy()->subDays(3);

            if ($threeDaysBeforeTransport->isFuture()) {
                $job = SendTripCheckInReminder::dispatch($tripUser->trip, $tripUser->user, 'before_transport')
                    ->delay($threeDaysBeforeTransport);

                // Salva o ID do job
                $tripUser->checkin_reminder_job_id = $job->id ?? null;
            }
        }

        // Agenda lembrete 2 horas antes do transporte para TODOS que têm tipo e horário definidos
        if ($tripUser->transport_type && $tripUser->transport_datetime) {
            $transportDate = Carbon::parse($tripUser->transport_datetime);
            $twoHoursBeforeTransport = $transportDate->copy()->subHours(2);

            if ($twoHoursBeforeTransport->isFuture()) {
                $job = SendTransportReminder::dispatch($tripUser->trip, $tripUser->user, $tripUser->transport_type, $tripUser->transport_datetime)
                    ->delay($twoHoursBeforeTransport);

                // Salva o ID do job
                $tripUser->transport_reminder_job_id = $job->id ?? null;
            }
        }

        // Salva os IDs dos jobs (se houver algum)
        if ($tripUser->isDirty(['checkin_reminder_job_id', 'transport_reminder_job_id'])) {
            $tripUser->saveQuietly(); // Save sem disparar observers novamente
        }
    }
}
