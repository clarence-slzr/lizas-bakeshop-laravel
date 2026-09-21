@extends('layouts.app')

@section('content')
    @php
        use App\Models\User;

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filterRole = isset($_GET['role']) ? trim($_GET['role']) : '';

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'LIKE', "%$search%")
                    ->orWhere('full_name', 'LIKE', "%$search%");
            });
        }
        if ($filterRole !== '' && in_array($filterRole, ['admin', 'cashier'])) {
            $query->where('role', $filterRole);
        }

        $totalCount = $query->count();
        $totalPages = ceil($totalCount / $limit);

        $users = $query->orderBy('id', 'asc')->skip($offset)->take($limit)->get();

        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $cashierCount = User::where('role', 'cashier')->count();

        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';

        function buildURL($overrides = [])
        {
            $params = array_merge($_GET, $overrides);
            $params = array_filter($params, function ($v) {
                return $v !== null && $v !== '';
            });
            return 'users?' . http_build_query($params);
        }
    @endphp

    <div class="users-container">

        <!-- ========== PAGE HEADER ========== -->
        <div class="page-header">
            <div>
                <h1>Manage Users</h1>
                <p class="page-subtitle">Manage your bakeshop's user accounts and roles</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                <i class="fa-solid fa-user-plus"></i> Add New User
            </a>
        </div>

        <!-- ========== SUCCESS MESSAGE ========== -->
        @if($success == 'added')
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                User has been added successfully.
            </div>
        @elseif($success == 'updated')
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                User has been updated successfully.
            </div>
        @elseif($success == 'deleted')
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                User has been deleted successfully.
            </div>
        @endif

        <!-- ========== ERROR MESSAGE ========== -->
        @if($error)
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                @if($error == 'has_orders')
                    Cannot delete this user because they have existing orders in the system.
                @elseif($error == 'admin_protected')
                    Cannot delete administrator account.
                @elseif($error == 'not_found')
                    User not found.
                @else
                    An error occurred while processing your request.
                @endif
            </div>
        @endif

        <!-- ========== STATISTICS CARDS ========== -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $totalUsers }}</span>
                    <span class="stat-label">Total Users</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon admin">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $adminCount }}</span>
                    <span class="stat-label">Administrators</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cashier">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $cashierCount }}</span>
                    <span class="stat-label">Cashiers</span>
                </div>
            </div>
        </div>

        <!-- ========== USER TABLE ========== -->
        <div class="data-card">

            <!-- Card Header -->
            <div class="card-header">
                <div class="header-left">
                    <h3>User Accounts</h3>
                    <span class="record-count">{{ number_format($totalCount) }} total</span>
                </div>
                <form method="GET" action="{{ route('admin.users.index') }}" class="search-form">
                    @if($filterRole)
                        <input type="hidden" name="role" value="{{ $filterRole }}">
                    @endif
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search user..." value="{{ $search }}">
                        @if($search !== '')
                            <a href="{{ buildURL(['search' => null]) }}" class="clear-btn">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <span class="filter-label">
                    <i class="fas fa-filter"></i> Filter:
                </span>
                <a href="{{ buildURL(['role' => null, 'page' => 1]) }}"
                    class="filter-chip {{ $filterRole === '' ? 'active' : '' }}">
                    All Roles
                </a>
                <a href="{{ buildURL(['role' => 'admin', 'page' => 1]) }}"
                    class="filter-chip {{ $filterRole === 'admin' ? 'active' : '' }}">
                    <i class="fas fa-crown"></i> Administrator
                </a>
                <a href="{{ buildURL(['role' => 'cashier', 'page' => 1]) }}"
                    class="filter-chip {{ $filterRole === 'cashier' ? 'active' : '' }}">
                    <i class="fas fa-cash-register"></i> Cashier
                </a>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-username">Username</th>
                            <th class="col-fullname">Full Name</th>
                            <th class="col-role">Role</th>
                            <th class="col-created">Created At</th>
                            <th class="col-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($users->count() > 0)
                            @foreach($users as $u)
                                <tr>
                                    <td class="col-id">
                                        <span class="user-id">#{{ str_pad($u->id, 3, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="col-username">
                                        <span class="username">{{ $u->username }}</span>
                                    </td>
                                    <td class="col-fullname">
                                        <span class="fullname">{{ $u->full_name ?? $u->username }}</span>
                                    </td>
                                    <td class="col-role">
                                        <span class="role-badge {{ $u->role == 'admin' ? 'role-admin' : 'role-cashier' }}">
                                            <i class="fas {{ $u->role == 'admin' ? 'fa-crown' : 'fa-cash-register' }}"></i>
                                            {{ $u->role == 'admin' ? 'Administrator' : 'Cashier' }}
                                        </span>
                                    </td>
                                    <td class="col-created">
                                        <span class="created-date">{{ date('M d, Y', strtotime($u->created_at)) }}</span>
                                        <span class="created-time">{{ date('h:i A', strtotime($u->created_at)) }}</span>
                                    </td>
                                    <td class="col-actions">
                                        <a href="{{ route('admin.users.edit', $u->id) }}" class="action-btn action-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="empty-row">
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-users-slash"></i>
                                        @if($search || $filterRole)
                                            <p>No users match your search</p>
                                            <a href="{{ route('admin.users.index') }}" class="link-clear">Clear filters</a>
                                        @else
                                            <p>No users found</p>
                                            <a href="{{ route('admin.users.create') }}" class="btn-add-first">Add your first
                                                user</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($totalPages > 1)
                <div class="pagination">
                    <span class="pagination-info">
                        Showing {{ $offset + 1 }}–{{ min($offset + $limit, $totalCount) }} of {{ $totalCount }}
                    </span>
                    <div class="pagination-links">
                        @if($page > 1)
                            <a href="{{ buildURL(['page' => $page - 1]) }}" class="page-link">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif

                        @php
                            $start_page = max(1, $page - 2);
                            $end_page = min($totalPages, $page + 2);
                        @endphp

                        @for($i = $start_page; $i <= $end_page; $i++)
                            @if($i == $page)
                                <span class="page-current">{{ $i }}</span>
                            @else
                                <a href="{{ buildURL(['page' => $i]) }}" class="page-link">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($page < $totalPages)
                            <a href="{{ buildURL(['page' => $page + 1]) }}" class="page-link">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .users-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 0.8rem;
            color: #9E9D97;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #576238;
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .btn-primary:hover {
            background: #3E4A28;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
        }

        .alert-success {
            background: #E8F0E3;
            border: 1px solid #576238;
            color: #576238;
        }

        .alert-error {
            background: #FEF0ED;
            border: 1px solid #C5705A;
            color: #C5705A;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #576238;
            flex-shrink: 0;
        }

        .stat-icon.admin {
            background: #FEF5E8;
            color: #D4A054;
        }

        .stat-icon.cashier {
            background: #E8F0E3;
            color: #576238;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #2C2B26;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.65rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .card-header {
            background: #FDF8F0;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-left h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .record-count {
            background: #F0EADC;
            color: #576238;
            padding: 0.2rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .search-form {
            margin: 0;
        }

        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-wrapper i.fa-search {
            position: absolute;
            left: 0.75rem;
            color: #9E9D97;
            font-size: 0.8rem;
        }

        .search-wrapper input {
            padding: 0.5rem 2rem 0.5rem 2.2rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            width: 240px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s ease;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: #576238;
        }

        .clear-btn {
            position: absolute;
            right: 0.6rem;
            color: #9E9D97;
            text-decoration: none;
            font-size: 0.75rem;
        }

        .clear-btn:hover {
            color: #C5705A;
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
            flex-wrap: wrap;
        }

        .filter-label {
            font-size: 0.75rem;
            color: #7A7A75;
            font-weight: 500;
            margin-right: 0.25rem;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.85rem;
            border-radius: 2rem;
            font-size: 0.72rem;
            font-weight: 500;
            color: #6B6A65;
            background: white;
            border: 1px solid #E3DCD0;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .filter-chip:hover {
            border-color: #576238;
            color: #576238;
        }

        .filter-chip.active {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .filter-chip i {
            font-size: 0.65rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Inter', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .data-table thead tr {
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
        }

        .data-table th {
            padding: 0.875rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #576238;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 0.875rem 1rem;
            text-align: left;
            color: #2C2B26;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background: #FDF8F0;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .col-id {
            width: 70px;
        }

        .col-username {
            width: 150px;
        }

        .col-fullname {
            width: auto;
        }

        .col-role {
            width: 150px;
        }

        .col-created {
            width: 140px;
        }

        .col-actions {
            width: 100px;
        }

        .user-id {
            font-weight: 600;
            color: #576238;
            font-family: monospace;
            font-size: 0.8rem;
        }

        .username {
            font-weight: 500;
            color: #2C2B26;
        }

        .fullname {
            color: #2C2B26;
        }

        .created-date {
            display: block;
            font-size: 0.75rem;
            color: #2C2B26;
        }

        .created-time {
            display: block;
            font-size: 0.65rem;
            color: #9E9D97;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.3rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .role-admin {
            background: #FEF5E8;
            color: #D4A054;
        }

        .role-cashier {
            background: #E8F0E3;
            color: #576238;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.7rem;
            border-radius: 0.375rem;
            font-size: 0.72rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .action-edit {
            background: #F0EADC;
            color: #576238;
        }

        .action-edit:hover {
            background: #576238;
            color: white;
        }

        .empty-row td {
            padding: 0 !important;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #D4C9BD;
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state p {
            color: #9E9D97;
            margin-bottom: 0.75rem;
        }

        .link-clear {
            color: #576238;
            text-decoration: underline;
            font-size: 0.8rem;
        }

        .btn-add-first {
            display: inline-block;
            background: #576238;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .btn-add-first:hover {
            background: #3E4A28;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: #FDF8F0;
            border-top: 1px solid #E3DCD0;
            flex-wrap: wrap;
        }

        .pagination-info {
            font-size: 0.75rem;
            color: #9E9D97;
        }

        .pagination-links {
            display: flex;
            gap: 0.4rem;
            align-items: center;
        }

        .page-link {
            background: white;
            color: #576238;
            padding: 0.35rem 0.7rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-size: 0.75rem;
            border: 1px solid #E3DCD0;
            transition: all 0.2s ease;
        }

        .page-link:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .page-current {
            background: #576238;
            color: white;
            padding: 0.35rem 0.7rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
@endsection