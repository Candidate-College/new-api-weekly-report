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

        /**
     * @OA\Get(
     *     path="/api/v1/supervisor/staff",
     *     summary="Melihat siapa saja staff dari supervisor",
     *     description="Menampilkan list siapa saja staff-staff dari seorang supervisor atau vice supervisor",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil menampilkan list staff dari supervisor.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", nullable=true, example="null"),
     *                 @OA\Property(property="instagram", type="string", nullable=true, example="null"),
     *                 @OA\Property(property="linkedin", type="string", nullable=true, example="null"),
     *                 @OA\Property(property="batch_no", type="string", nullable=true, example="null"),
     *                 @OA\Property(property="division", type="string", nullable=true, example="null"),
     *                 @OA\Property(property="supervisor_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="vice_supervisor_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="CFlag", type="boolean", nullable=true, example=null),
     *                 @OA\Property(property="SFlag", type="boolean", nullable=true, example=null),
     *                 @OA\Property(property="StFlag", type="boolean", nullable=true, example=null),
     *                 @OA\Property(property="profile_picture", type="string", format="uri", example="http://example.com/profile.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", nullable=true, example="null"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, example="null")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Data Not Found. Token tidak valid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token tidak valid.")
     *         )
     *     )
     * )
     */

    public function getStaffOfSupervisor()
    {
        $supervisorId = Auth::id();
        $staff = User::where('supervisor_id', $supervisorId)
        ->orWhere('vice_supervisor_id', $supervisorId)
        ->select('id', 'profile_picture', 'first_name', 'last_name')
        ->get();
    
    
        if ($staff->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return UserResource::collection($staff);
    }

        /**
     * @OA\Get(
     *     path="/api/v1/c-level/supervisor-staff/{divisionId}/list",
     *     summary="C-Level melihat supervisor dan staff di divisi dia",
     *     description="Melihat semua staff dan supervisor di divisi yang dia naungi",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="divisionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID divisi yang ingin dilihat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data supervisor dan staff berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="division_id", type="integer", example=1),
     *             @OA\Property(property="division_name", type="string", example="Web Development"),
     *             @OA\Property(
     *                 property="team_members",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="Elsie Lebsack"),
     *                     @OA\Property(property="role", type="string", example="Head"),
     *                     @OA\Property(property="profile_picture", type="string", format="uri", example="https://via.placeholder.com/640x480.png/0099ff?text=eum")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Token tidak valid atau tidak memiliki hak akses.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Terjadi kesalahan internal server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

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
