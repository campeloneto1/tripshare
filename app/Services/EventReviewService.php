<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\EventReview;
use App\Repositories\EventReviewRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventReviewService
{
    public function __construct(private EventReviewRepository $repository) {}

    public function list(array $filters): LengthAwarePaginator|Collection
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?EventReview
    {
        return $this->repository->find($id);
    }

    public function store(array $data): EventReview
    {
        return DB::transaction(function () use ($data) {
            // Valida se já existe review do usuário para este evento
            if (isset($data['user_id']) && isset($data['trip_day_event_id'])) {
                $existingReview = $this->repository->findByUserAndEvent(
                    $data['user_id'],
                    $data['trip_day_event_id']
                );

                if ($existingReview) {
                    throw new \InvalidArgumentException('Você já avaliou este evento.');
                }
            }

            return $this->repository->create($data);
        });
    }

    public function update(EventReview $eventReview, array $data): EventReview
    {
        return DB::transaction(function () use ($eventReview, $data) {
            return $this->repository->update($eventReview, $data);
        });
    }

    public function delete(EventReview $eventReview): bool
    {
        return DB::transaction(fn() => $this->repository->delete($eventReview));
    }

    /**
     * Calcula a média de rating por XID.
     */
    public function getAverageByXid(string $xid): ?float
    {
        return $this->repository->averageByXid($xid);
    }
}
