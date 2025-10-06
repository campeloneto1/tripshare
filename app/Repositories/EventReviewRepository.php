<?php

namespace App\Repositories;

use App\Models\EventReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EventReviewRepository
{
    /**
     * Retorna a query base com relacionamentos.
     */
    public function baseQuery(): Builder
    {
        return EventReview::query()->with(['user', 'event']);
    }

    /**
     * Lista reviews com filtros e paginação.
     */
    public function all(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = $this->baseQuery();

        if (!empty($filters['search'])) {
            $this->filterSearch($query, $filters['search']);
        }

        if (!empty($filters['xid'])) {
            $query->where('xid', $filters['xid']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['trip_day_event_id'])) {
            $query->where('trip_day_event_id', $filters['trip_day_event_id']);
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        $query->orderBy('created_at', 'desc');

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    /**
     * Busca uma EventReview pelo ID.
     */
    public function find(int $id): ?EventReview
    {
        return $this->baseQuery()->find($id);
    }

    /**
     * Cria uma nova EventReview.
     */
    public function create(array $data): EventReview
    {
        return EventReview::create($data);
    }

    /**
     * Atualiza uma EventReview existente.
     */
    public function update(EventReview $eventReview, array $data): EventReview
    {
        $eventReview->update($data);
        return $eventReview->fresh(['user', 'event']);
    }

    /**
     * Exclui uma EventReview.
     */
    public function delete(EventReview $eventReview): bool
    {
        return $eventReview->delete();
    }

    /**
     * Calcula média de rating por XID.
     */
    public function averageByXid(string $xid): ?float
    {
        return EventReview::where('xid', $xid)->avg('rating');
    }

    /**
     * Busca review do usuário para um evento específico.
     */
    public function findByUserAndEvent(int $userId, ?int $eventId): ?EventReview
    {
        return EventReview::where('user_id', $userId)
            ->where('trip_day_event_id', $eventId)
            ->first();
    }

    /**
     * Aplica filtro de busca por comentário ou XID.
     */
    protected function filterSearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('comment', 'like', "%{$search}%")
              ->orWhere('xid', 'like', "%{$search}%")
              ->orWhereHas('user', function ($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%");
              });
        });
    }
}
