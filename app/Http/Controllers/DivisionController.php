<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Http\Resources\GeneralApiResource;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="Documentation for API v1",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 * )
 */

/**
 * @OA\Tag(
 *     name="Divisions",
 *     description="Division management"
 * )
 */
class DivisionController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/v1/divisions",
     *     summary="Get a list of all divisions",
     *     tags={"Divisions"},
     *     @OA\Response(
     *         response=200,
     *         description="List of divisions",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Divisions fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Human Resources"),
     *                     @OA\Property(property="abbreviation", type="string", example="HR"),
     *                     @OA\Property(property="responsibility", type="string", example="Manage employee relations"),
     *                     @OA\Property(property="description", type="string", example="Handles HR-related tasks.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    
    public function index()
    {
        $divisions = Division::all();

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'Divisions fetched successfully',
            'data' => $divisions
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/divisions",
     *     summary="Create a new division",
     *     tags={"Divisions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Finance"),
     *             @OA\Property(property="abbreviation", type="string", example="FIN"),
     *             @OA\Property(property="responsibility", type="string", example="Manage finances"),
     *             @OA\Property(property="description", type="string", example="Finance division handles budgeting and accounting.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Division created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Division created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Finance")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:10',
            'responsibility' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $division = Division::create($validated);

        return new GeneralApiResource([
            'status_code' => 201,
            'success' => true,
            'message' => 'Division created successfully',
            'data' => $division
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/divisions/{id}",
     *     summary="Get a specific division by ID",
     *     tags={"Divisions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the division",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Division fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Division fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Human Resources")
     *             )
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $division = Division::findOrFail($id);

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'Division fetched successfully',
            'data' => $division
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/divisions/{id}",
     *     summary="Update a division by ID",
     *     tags={"Divisions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the division",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Finance"),
     *             @OA\Property(property="abbreviation", type="string", example="FIN"),
     *             @OA\Property(property="responsibility", type="string", example="Manage finances"),
     *             @OA\Property(property="description", type="string", example="Updated description for the Finance division.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Division updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Division updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Finance")
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:10',
            'responsibility' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $division = Division::findOrFail($id);
        $division->update($validated);

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'Division updated successfully',
            'data' => $division
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/divisions/{id}",
     *     summary="Delete a division by ID",
     *     tags={"Divisions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the division",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Division deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Division deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();

        return new GeneralApiResource([
            'status_code' => 200,
            'success' => true,
            'message' => 'Division deleted successfully',
            'data' => null
        ]);
    }
}
