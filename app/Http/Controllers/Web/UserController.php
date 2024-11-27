<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Get the search query and role filter from the request
        $searchQuery = $request->input('search');
        $roleFilter = $request->input('role_filter'); // New input for role filter

        // Dynamically set the number of items per page (default to 5 if not specified)
        $perPage = $request->input('perPage', 5); // Use input 'perPage', default to 5

        // Fetch users based on the search query, role filter, and paginate
        $users = User::when($searchQuery, function ($query, $searchQuery) {
                // Search across first_name, last_name, and email
                return $query->where(function ($query) use ($searchQuery) {
                    $query->where('first_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%');
                });
            })
            ->when($roleFilter, function ($query, $roleFilter) {
                // Filter based on selected role
                if ($roleFilter == 'CLevel') {
                    $query->where('CFlag', 1);
                } elseif ($roleFilter == 'Supervisor') {
                    $query->where('Sflag', 1);
                } elseif ($roleFilter == 'Staff') {
                    $query->where('StFlag', 1);
                }
            })
            ->paginate($perPage);  // Use the dynamic perPage value

        // Return the view with the necessary data
        return view('admin.users.user', compact('users', 'searchQuery', 'roleFilter'));
    }


    public function updateFlags(Request $request)
    {
        // Validate the input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:CLevel,Supervisor,Staff',
        ]);

        $user = User::find($request->user_id);

        // Update the appropriate flag based on the selected role
        $user->CFlag = $request->role == 'CLevel' ? 1 : 0;
        $user->Sflag = $request->role == 'Supervisor' ? 1 : 0;
        $user->StFlag = $request->role == 'Staff' ? 1 : 0;

        $user->save();

        // Redirect back to the same page using query string
        $queryString = $request->input('redirect', '');
        return redirect()->route('admin.users.user', $queryString)->with('success', 'User role updated successfully!');
    }



    public function dashboard() {
        return view('admin.dashboard');
    }

}
