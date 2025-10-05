<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\UserFollow;
use App\Notifications\FollowAcceptedNotification;
use App\Notifications\FollowRequestNotification;
use App\Repositories\UserFollowRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserFollowService
{
    public function __construct(
        private UserFollowRepository $repository,
        private UserRepository $userRepository
        ) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?UserFollow
    {
        $return = $this->repository->find($id);
        return $return;
    }

    public function store(array $data): UserFollow
    {
        return DB::transaction(function () use ($data) {
            $data['follower_id'] = Auth::id();

            // Verifica se já existe um follow ativo
            $existing = UserFollow::where('follower_id', $data['follower_id'])
                ->where('following_id', $data['following_id'])
                ->first();

            if ($existing) {
                throw new \InvalidArgumentException('Você já está seguindo este usuário.');
            }

            $following = $this->userRepository->find($data['following_id']);
            $follower = Auth::user();

            if($following->is_public){
                $data['status'] = 'accepted';
                $data['accepted_at'] = now();

                // Notifica que foi aceito automaticamente
                $follower->notify(new FollowAcceptedNotification($following));
            } else {
                // Notifica que recebeu pedido de follow
                $following->notify(new FollowRequestNotification($follower));
            }

            return $this->repository->create($data);
        });
    }

    public function update(UserFollow $userFollow, array $data): UserFollow
    {
        return DB::transaction(function () use ($userFollow, $data) {
            $updated = $this->repository->update($userFollow, $data);

            // Se o status mudou para accepted, notifica o seguidor
            if (isset($data['status']) && $data['status'] === 'accepted' && $userFollow->status !== 'accepted') {
                $follower = $this->userRepository->find($userFollow->follower_id);
                $following = $this->userRepository->find($userFollow->following_id);

                $follower->notify(new FollowAcceptedNotification($following));
            }

            return $updated;
        });
    }

    public function delete(UserFollow $userFollow): bool
    {
        return DB::transaction(fn() => $this->repository->delete($userFollow));
    }

    public function listByUser(int $userId)
    {
        $filters = ['follower_id' => $userId];
        return $this->repository->all($filters);
    }
}
