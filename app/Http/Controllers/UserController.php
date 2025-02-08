<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\Response;
use App\Models\CLevelDivision;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected UserService $userSortingService;

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
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
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
        $cLevel = Auth::user();


        $division = Division::findOrFail($divisionId);
        $users = User::where('division_id', $divisionId)->get();
        if (!empty($cLevel->id)) {
            $sortedUsers = $this->userSortingService->sortUsersforClevel($users, $cLevel->id);
        }
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

    /**
     * @OA\Get(
     *     path="/api/v1/division/staff-count",
     *     summary="Clevel melihat jumlah divisi dan jumlah staf",
     *     description="Endpoint ini mengembalikan jumlah divisi dan total staf yang ada di divisi-divisi tersebut untuk C-Level yang sedang login.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Jumlah divisi dan staf berhasil diambil.",
     *         @OA\JsonContent(
     *             @OA\Property(property="division_count", type="integer", example=3, description="Jumlah divisi yang dimiliki C-Level."),
     *             @OA\Property(property="total_staff_count", type="integer", example=25, description="Jumlah total staf yang ada di divisi-divisi C-Level.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: Token tidak valid atau tidak ada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized: Token tidak valid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error: Terjadi kesalahan di server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error: Terjadi kesalahan di server.")
     *         )
     *     )
     * )
     */

    public function getDivisionAndStaffCount(Request $request)
    {
        $userId = Auth::id();
        $cLevelDivisions = CLevelDivision::where('c_level_id', $userId)->pluck('division_id');

        $divisionCount = $cLevelDivisions->count();

        $staffCount = User::whereIn('division_id', $cLevelDivisions)->count();

        if ($divisionCount == 0 || $staffCount == 0) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'division_count' => $divisionCount,
            'total_staff_count' => $staffCount,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/c-level/division/{divisionId}/staff-list",
     *     summary="Clevel melihat daftar staff dalam suatu divisi",
     *     description="Endpoint ini mengembalikan daftar staff dalam suatu divisi yang hanya dapat diakses oleh C-Level yang menaungi divisi tersebut.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="divisionId",
     *         in="path",
     *         required=true,
     *         description="ID divisi yang ingin diambil datanya",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar staff berhasil diambil.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=15),
     *                 @OA\Property(property="name", type="string", example="Backend Developer"),
     *                 @OA\Property(property="abbreviation", type="string", example="BE"),
     *                 @OA\Property(property="members", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=60),
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                         @OA\Property(property="division_id", type="integer", example=1),
     *                         @OA\Property(property="HFlag", type="boolean", example=false, description="Menandakan apakah user adalah Head"),
     *                         @OA\Property(property="ChFlag", type="boolean", example=false, description="Menandakan apakah user adalah Co-Head"),
     *                         @OA\Property(property="StFlag", type="boolean", example=true, description="Menandakan apakah user adalah Staff")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized: User tidak memiliki akses ke divisi ini.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized: You are not authorized to access this division")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division tidak ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Division not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error: Terjadi kesalahan di server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error: Terjadi kesalahan di server.")
     *         )
     *     )
     * )
     */

    public function getStaffByDivision($divisionId)
    {
        $user = Auth::user();

        $division = Division::with([
            'members' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'division_id', 'HFlag', 'ChFlag', 'StFlag')
                    ->orderByRaw('HFlag DESC, ChFlag DESC, first_name ASC');
            }
        ])->find($divisionId);

        if (!$division) {
            return response()->json([
                'message' => 'Division not found'
            ], 404);
        }

        $isAuthorized = CLevelDivision::where('c_level_id', $user->c_level_id)
            ->where('division_id', $divisionId)
            ->exists();

        if (!$isAuthorized) {
            return response()->json([
                'message' => 'Unauthorized: You are not authorized to access this division'
            ], 403);
        }

        return response()->json([
            "data" => $division
        ]);
    }
}
