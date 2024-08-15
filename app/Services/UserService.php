<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    private const STAFF_ROLE = 'Staff';
    private const HEAD_ROLE = 'Head';
    private const CO_HEAD_ROLE = 'Co-Head';

    private const SORT_ORDER_STAFF = 3;
    private const SORT_ORDER_HEAD = 1;
    private const SORT_ORDER_CO_HEAD = 2;

    public function sortUsersforClevel($users, $cLevelId)
    {
        $staffMap = User::where('StFlag', true)
            ->select('id', 'supervisor_id', 'vice_supervisor_id')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user];
            });

        return $users->map(function ($user) use ($cLevelId, $staffMap) {
            if ($user->supervisor_id == $cLevelId || $user->vice_supervisor_id == $cLevelId) {
                if ($staffMap->contains('supervisor_id', $user->id)) {
                    return $this->formatUser($user, self::HEAD_ROLE, self::SORT_ORDER_HEAD);
                }

                if ($staffMap->contains('vice_supervisor_id', $user->id)) {
                    return $this->formatUser($user, self::CO_HEAD_ROLE, self::SORT_ORDER_CO_HEAD);
                }
            }

            if ($user->StFlag) {
                return $this->formatUser($user, self::STAFF_ROLE, self::SORT_ORDER_STAFF);
            }

            return null;
        })
        ->filter()
        ->sortBy('sort_order')
        ->values();
    }

    private function formatUser($user, $role, $sortOrder)
    {
        return [
            'id' => $user->id,
            'name' => "{$user->first_name} {$user->last_name}",
            'role' => $role,
            'sort_order' => $sortOrder,
            'profile_picture' => $user->profile_picture,
        ];
    }
}
