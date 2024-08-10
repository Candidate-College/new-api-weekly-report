<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userSortingService;

    public function __construct(UserService $userSortingService)
    {
        $this->userSortingService = $userSortingService;
    }
    public function index()
    {
       $users = User::all();
       return UserResource::collection($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    public function getStaffOfSupervisor()
    {
        $supervisorId = Auth::id();
        $staff = User::where('supervisor_id', $supervisorId)
            ->select('id', 'profile_picture', 'first_name', 'last_name')
            ->get();
    
        if ($staff->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return UserResource::collection($staff);
    }

    public function getCLevelStaff(Request $request, $divisionId)
    {
        $cLevel = auth()->user();
        if (!$cLevel->CFlag) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
 
        $division = Division::findOrFail($divisionId);
        $users = User::where('division_id', $divisionId)->get();
        $sortedUsers = $this->userSortingService->sortUsersforClevel($users, $cLevel->id);
        $result = [
            'division_id' => $division->id,
            'division_name' => $division->name,
            'team_members' => $sortedUsers->map(function ($user) {
                return [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'profile_picture' => $user['profile_picture'],
                ];
            })
        ];
 
        return response()->json($result);
    }

}
