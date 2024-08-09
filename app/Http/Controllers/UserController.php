<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
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

        return UserResource::collection($staff);
    }
    
    public function getCLevelStaff()
    {
        $cLevelId = Auth::id();

        // Get supervisors under the C-Level
        $supervisors = User::where('supervisor_id', $cLevelId)
            ->select('id', 'profile_picture', 'first_name', 'last_name')
            ->with(['staff' => function($query) {
                $query->select('id', 'profile_picture', 'first_name', 'last_name', 'supervisor_id');
            }])
            ->get();

        return response()->json([
            'c_level_id' => $cLevelId,
            'supervisors' => $supervisors->map(function ($supervisor) {
                return [
                    'supervisor' => new UserResource($supervisor),
                    'staff' => UserResource::collection($supervisor->staff)
                    
                ];
            })
        ]);
    }

}
