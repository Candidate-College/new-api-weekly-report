<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CLevel;
use App\Models\Division;
use App\Http\Resources\GeneralApiResource;

class AccountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user/data",
     *     summary="Retrieve authenticated user data",
     *     description="Returns data of the authenticated user, including role, profile details, and optional CLevel or Division information.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved user data.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User data retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="role", type="string", example="Administrator"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2023-12-01T12:00:00Z"),
     *                 @OA\Property(property="profile_picture", type="string", format="uri", example="http://example.com/profile.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-01T12:00:00Z"),
     *                 @OA\Property(property="c_level_data", type="object", nullable=true,
     *                     description="Additional data for C-Level users, if applicable."
     *                 ),
     *                 @OA\Property(property="division_data", type="object", nullable=true,
     *                     description="Division-specific data for members of a C-Level, if applicable."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while retrieving user data.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred.")
     *         )
     *     )
     * )
     */

    public function getUserData(Request $request)
    {
        $user = Auth::user();

        // Determine role based on flags
        $role = $this->getUserRoles($user);

        // Fetch CLevel and Division data if applicable
        $cLevelData = $this->getCLevelData($user);
        $divisionDataForMemberOfCLevel = $this->getDivisionDataForMemberOfCLevel($user);

        // Construct response data
        $response = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $role,
            'email_verified_at' => $user->email_verified_at,
            'profile_picture' => $user->profile_picture,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'c_level_data' => $cLevelData,
            'division_data' => $divisionDataForMemberOfCLevel,
        ];

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'User data retrieved successfully',
            'data' => $response,
        ]);
    }
    
    private function getUserRoles($user)
    {
        if ($user->HFlag) return 'Head';
        if ($user->ChFlag) return 'Co-Head';
        if ($user->CFlag) return 'C-Level';
        if ($user->SFlag) return 'Supervisor';
        if ($user->StFlag) return 'Staff';

        return 'Guest'; // Default role if no flags are set
    }

    private function getCLevelData($user)
    {
        if (!$user->CFlag || !$user->c_level_id) {
            return null;
        }

        $cLevel = CLevel::with('divisions')->find($user->c_level_id);

        if (!$cLevel) {
            return null;
        }

        return [
            'id' => $cLevel->id,
            'name' => $cLevel->name,
            'abbreviation' => $cLevel->abbreviation,
            'description' => $cLevel->description,
            'responsibility' => $cLevel->responsibility,
            'divisions' => $cLevel->divisions->map(function ($division) {
                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'abbreviation' => $division->abbreviation,
                    'responsibility' => $division->responsibility,
                    'description' => $division->description,
                    'created_at' => $division->created_at,
                    'updated_at' => $division->updated_at,
                ];
            }),
        ];
    }

    private function getDivisionDataForMemberOfCLevel($user)
    {
        if (!($user->HFlag || $user->ChFlag || $user->StFlag) || !$user->division_id) {
            return null;
        }

        // Find the division with the CLevel division relation
        $division = Division::with('cLevelDivisions.cLevel')->find($user->division_id);

        if (!$division) {
            return null;
        }

        return [
            'id' => $division->id,
            'name' => $division->name,
            'abbreviation' => $division->abbreviation,
            'responsibility' => $division->responsibility,
            'description' => $division->description,
            'created_at' => $division->created_at,
            'updated_at' => $division->updated_at,
            'c_level' => $division->cLevelDivisions->map(function ($cLevelDivision) {
                return [
                    'id' => $cLevelDivision->cLevel->id,
                    'name' => $cLevelDivision->cLevel->name,
                    'abbreviation' => $cLevelDivision->cLevel->abbreviation,
                ];
            }),
        ];
    }

    public function cLevelWithItsDivision($id)
    {
        $cLevel = CLevel::with('divisions')->findOrFail($id);

        $response = [
            'id' => $cLevel->id,
            'name' => $cLevel->name,
            'description' => $cLevel->description,
            'responsibility' => $cLevel->responsibility,
            'divisions' => $cLevel->divisions->map(function ($division) {
                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'abbreviation' => $division->abbreviation,
                    'responsibility' => $division->responsibility,
                    'description' => $division->description,
                    'created_at' => $division->created_at,
                    'updated_at' => $division->updated_at,
                ];
            }),
        ];

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'CLevel with Division fetched successfully',
            'data' => $response,
        ]);
    }

    
}
