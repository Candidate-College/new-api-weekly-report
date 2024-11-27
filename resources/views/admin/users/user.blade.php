@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="container">
    <h1 class="mb-4">
        Users Management
        <a href="{{ route('admin.users.user') }}" class="btn btn-warning ms-3">Refresh Table</a>
    </h1>

    <!-- Success Message as Toast -->
    @if (session('success'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
            <div role="alert" aria-live="assertive" aria-atomic="true" class="toast show" data-autohide="true" data-delay="3000">
                <div class="toast-header bg-success text-white">
                    <strong class="me-auto">Success</strong>
                    <!-- Close button aligned to the right -->
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.users.user') }}" class="mb-3">
        <div class="row mb-3">
            <!-- Search Input -->
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama, email">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </div>
    
            <!-- Dropdown to Filter by Role -->
            <div class="col-md-6">
                <div class="input-group">
                    <select name="role_filter" class="form-control">
                        <option value="">Select Role</option>
                        <option value="CLevel" {{ request('role_filter') == 'CLevel' ? 'selected' : '' }}>C-Level</option>
                        <option value="Supervisor" {{ request('role_filter') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="Staff" {{ request('role_filter') == 'Staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                    <button class="btn btn-secondary" type="submit">Filter</button>
                </div>
            </div>
        </div>
    </form>    

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                <tr>
                    <td>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->CFlag == 1 && $user->Sflag == 1 && $user->StFlag == 1)
                            <span class="badge bg-danger">Admin</span>
                        @elseif($user->CFlag == 1)
                            <span class="badge bg-primary">C-Level</span>
                        @elseif($user->Sflag == 1)
                            <span class="badge bg-success">Supervisor</span>
                        @elseif($user->StFlag == 1)
                            <span class="badge bg-secondary">Staff</span>
                        @else
                            <span class="badge bg-light">No Role Assigned</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#userModal" 
                                data-name="{{ $user->first_name }} {{ $user->last_name }}" 
                                data-email="{{ $user->email }}"
                                data-role="{{
                                    $user->CFlag == 1 ? 'C-Level' : 
                                    ($user->Sflag == 1 ? 'Supervisor' : 
                                    ($user->StFlag == 1 ? 'Staff' : 'No Role Assigned'))
                                }}"
                                data-user-id="{{ $user->id }}">
                            View Details
                        </button>

                        <!-- Hide Change Role Button for Admins -->
                        @if(!($user->CFlag == 1 && $user->Sflag == 1 && $user->StFlag == 1))
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changeRoleModal" 
                                data-user-id="{{ $user->id }}"
                                data-current-role="{{
                                    $user->CFlag == 1 ? 'C-Level' : 
                                    ($user->Sflag == 1 ? 'Supervisor' : 
                                    ($user->StFlag == 1 ? 'Staff' : 'No Role Assigned'))
                                }}">
                            Change Role
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div>
        {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Modal for User Details -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="userName">Name: </p>
                <p id="userEmail">Email: </p>
                <p id="userRole">Role: </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Change Role -->
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-labelledby="changeRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeRoleModalLabel">Change User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.updateFlags') }}" method="POST" id="changeRoleForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="userId" name="user_id">
                    <input type="hidden" name="redirect" value="{{ request()->getQueryString() }}"> <!-- Pass query string -->
                    <div class="mb-3">
                        <label for="role" class="form-label">Select Role</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" id="roleCLevel" value="CLevel">
                            <label class="form-check-label" for="roleCLevel">C-Level</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" id="roleSupervisor" value="Supervisor">
                            <label class="form-check-label" for="roleSupervisor">Supervisor</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" id="roleStaff" value="Staff">
                            <label class="form-check-label" for="roleStaff">Staff</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const userModal = document.getElementById('userModal');
    userModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const name = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const role = button.getAttribute('data-role');

        const modalBody = userModal.querySelector('.modal-body');
        modalBody.querySelector('#userName').textContent = 'Name: ' + name;
        modalBody.querySelector('#userEmail').textContent = 'Email: ' + email;
        modalBody.querySelector('#userRole').textContent = 'Role: ' + role;
    });

    const changeRoleModal = document.getElementById('changeRoleModal');
    changeRoleModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-user-id');
        const currentRole = button.getAttribute('data-current-role');

        document.getElementById('userId').value = userId;

        if (currentRole === 'C-Level') {
            document.getElementById('roleCLevel').checked = true;
        } else if (currentRole === 'Supervisor') {
            document.getElementById('roleSupervisor').checked = true;
        } else if (currentRole === 'Staff') {
            document.getElementById('roleStaff').checked = true;
        }
    });

    // Dynamically append the query string to the form action
    const form = document.getElementById('changeRoleForm');
    const queryString = window.location.search;
    form.action = form.action + (queryString ? `?${queryString.substring(1)}` : '');
</script>
@endsection
