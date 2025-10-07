<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventReviewRequest;
use App\Http\Requests\UpdateEventReviewRequest;
use App\Http\Resources\EventReviewResource;
use App\Models\EventReview;
use App\Services\EventReviewService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class EventReviewController extends Controller
{
    use AuthorizesRequests;
     public function __construct(private EventReviewService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', EventReview::class);
            $filters = $request->only(['limit', 'search', 'xid', 'user_id', 'trip_day_event_id', 'rating']);
            $eventsReviews = $this->service->list($filters);
            return EventReviewResource::collection($eventsReviews);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(EventReview $eventReview)
    {
        try {
            $this->authorize('view', $eventReview);
            $eventReview = $this->service->find($eventReview->id);
            if (!$eventReview) return response()->json(['error' => 'Review não encontrada'], 404);
            return EventReviewResource::make($eventReview);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreEventReviewRequest $request)
    {
        try {
            $data = $request->validated();
            $this->authorize('create', [EventReview::class, $data['trip_day_event_id']]);
            $eventReview = $this->service->store($data);
            return response()->json([
                "message" => "Review cadastrada com sucesso",
                "data" => EventReviewResource::make($eventReview)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateEventReviewRequest $request, EventReview $eventReview)
    {
        try {
            $this->authorize('update', $eventReview);
            $eventReview = $this->service->update($eventReview, $request->validated());
            return response()->json([
                "message" => "Review atualizada com sucesso",
                "data" => EventReviewResource::make($eventReview)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(EventReview $eventReview)
    {
        try {
            $this->authorize('delete', $eventReview);
            $this->service->delete($eventReview);
            return response()->json([
                "message" => "Review excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
