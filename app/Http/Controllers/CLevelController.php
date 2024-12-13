<?php

namespace App\Http\Controllers;

use App\Models\CLevel;
use App\Http\Resources\GeneralApiResource;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="API documentation for CLevel management",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 * )
 */

/**
 * @OA\Tag(
 *     name="CLevels",
 *     description="CLevel management"
 * )
 */
class CLevelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/clevels",
     *     summary="Get a list of all CLevels",
     *     tags={"CLevels"},
     *     @OA\Response(
     *         response=200,
     *         description="List of CLevels",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="CLevels fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Chief Executive Officer"),
     *                     @OA\Property(property="abbreviation", type="string", example="CEO"),
     *                     @OA\Property(property="responsibility", type="string", example="Manage employee relations"),
     *                     @OA\Property(property="description", type="string", example="This is CEO")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $clevels = CLevel::all();

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'CLevels fetched successfully',
            'data' => $clevels
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clevels",
     *     summary="Create a new CLevel",
     *     tags={"CLevels"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Chief Executive Officer"),
     *             @OA\Property(property="abbreviation", type="string", example="CEO"),
     *             @OA\Property(property="responsibilities", type="string", example="Oversee the overall strategic direction of the company."),
     *             @OA\Property(property="description", type="string", example="The CEO is the highest-ranking executive in a company.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="CLevel created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="CLevel created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Chief Executive Officer")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:10',
            'responsibilities' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $clevel = CLevel::create($validated);

        return new GeneralApiResource([
            'status_code' => 201,
            'success' => true,
            'message' => 'CLevel created successfully',
            'data' => $clevel
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clevels/{id}",
     *     summary="Get a specific CLevel by ID",
     *     tags={"CLevels"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CLevel",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CLevel fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="CLevel fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Chief Executive Officer")
     *             )
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $clevel = CLevel::findOrFail($id);

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'CLevel fetched successfully',
            'data' => $clevel
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/clevels/{id}",
     *     summary="Update a CLevel by ID",
     *     tags={"CLevels"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CLevel",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Chief Executive Officer"),
     *             @OA\Property(property="abbreviation", type="string", example="CEO"),
     *             @OA\Property(property="responsibilities", type="string", example="Oversee the company’s strategies."),
     *             @OA\Property(property="description", type="string", example="Updated description of the CEO role.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CLevel updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="CLevel updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Chief Executive Officer")
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:10',
            'responsibilities' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $clevel = CLevel::findOrFail($id);
        $clevel->update($validated);

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'CLevel updated successfully',
            'data' => $clevel
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/clevels/{id}",
     *     summary="Delete a CLevel by ID",
     *     tags={"CLevels"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CLevel",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CLevel deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="CLevel deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $clevel = CLevel::findOrFail($id);
        $clevel->delete();

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'CLevel deleted successfully',
            'data' => null
        ]);
    }

        /**
         * @OA\Get(
         *     path="/api/v1/clevels/{id}/with-division",
         *     summary="Get CLevel with its associated Division",
         *     tags={"CLevels"},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         description="ID of the CLevel",
         *         required=true,
         *         @OA\Schema(type="integer", example=1)
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="CLevel with its Division fetched successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="status_code", type="integer", example=200),
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="CLevel with Division fetched successfully"),
         *             @OA\Property(
         *                 property="data",
         *                 type="object",
         *                 @OA\Property(property="id", type="integer", example=1),
         *                 @OA\Property(property="title", type="string", example="Chief Executive Officer"),
         *                 @OA\Property(property="division", type="object",
         *                     @OA\Property(property="id", type="integer", example=1),
         *                     @OA\Property(property="name", type="string", example="Management")
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="CLevel not found",
         *         @OA\JsonContent(
         *             @OA\Property(property="status_code", type="integer", example=404),
         *             @OA\Property(property="success", type="boolean", example=false),
         *             @OA\Property(property="message", type="string", example="CLevel not found")
         *         )
         *     )
         * )
         */
        public function cLevelWithItsDivision($id)
        {
            $clevel = CLevel::with('divisions')->findOrFail($id);

            // Return the data using the GeneralApiResource
            return new GeneralApiResource([
                'status_code' => 200,
                'success' => true,
                'message' => 'CLevel with Division fetched successfully',
                'data' => $clevel
            ]);
        }

}
