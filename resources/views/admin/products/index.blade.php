@extends('layouts.app')

@section('content')
    @php
        use App\Models\Product;

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filterCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
        $filterStock = isset($_GET['stock']) ? trim($_GET['stock']) : '';

        $query = Product::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('category', 'LIKE', "%$search%")
                    ->orWhere('id', 'LIKE', "%$search%");
            });
        }

        if ($filterCategory !== '') {
            $query->where('category', $filterCategory);
        }

        if ($filterStock === 'low') {
            $query->where('stock', '<', 10)->where('stock', '>', 0);
        } elseif ($filterStock === 'out') {
            $query->where('stock', 0);
        } elseif ($filterStock === 'good') {
            $query->where('stock', '>=', 10);
        }

        $totalCount = $query->count();
        $totalPages = ceil($totalCount / $limit);
        $products = $query->orderBy('id', 'asc')->skip($offset)->take($limit)->get();

        $categories = Product::whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category', 'asc')->pluck('category');

        $statTotal = Product::count();
        $statActive = Product::where('is_available', 1)->count();
        $statLowStock = Product::where('stock', '<', 10)->where('stock', '>', 0)->count();
        $statOutStock = Product::where('stock', 0)->count();

        $success = $_GET['success'] ?? '';

        function buildURL($overrides = [])
        {
            $params = array_merge($_GET, $overrides);
            return 'products?' . http_build_query($params);
        }
    @endphp

    <div class="products-container">
        <!-- PAGE HEADER -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Manage Products</h1>
                <p class="page-description">Add, edit, and manage your bakeshop's inventory</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.products.create') }}" class="btn-primary">
                    <i class="fa-solid fa-circle-plus"></i> Add New Product
                </a>
            </div>
        </div>

        <!-- STATS BAR -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-icon stat-total"><i class="fas fa-box"></i></div>
                <div>
                    <span class="stat-value">{{ number_format($statTotal) }}</span>
                    <span class="stat-label">Total Products</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon stat-active"><i class="fas fa-check-circle"></i></div>
                <div>
                    <span class="stat-value">{{ number_format($statActive) }}</span>
                    <span class="stat-label">Active</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon stat-low"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <span class="stat-value">{{ number_format($statLowStock) }}</span>
                    <span class="stat-label">Low Stock</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon stat-out"><i class="fas fa-times-circle"></i></div>
                <div>
                    <span class="stat-value">{{ number_format($statOutStock) }}</span>
                    <span class="stat-label">Out of Stock</span>
                </div>
            </div>
        </div>

        <!-- SUCCESS MESSAGE -->
        @if($success)
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                @php
                    $messages = [
                        'added' => 'Product has been added successfully!',
                        'updated' => 'Product has been updated successfully!',
                        'deleted' => 'Product has been deleted successfully!',
                        'toggled' => 'Product availability updated!',
                        'stock_updated' => 'Stock updated successfully!',
                        'bulk_deleted' => 'Selected products have been deleted!',
                        'bulk_activated' => 'Selected products are now active!',
                        'bulk_deactivated' => 'Selected products are now inactive!'
                    ];
                @endphp
                {{ $messages[$success] ?? 'Action completed successfully!' }}
            </div>
        @endif

        <!-- PRODUCTS CARD -->
        <div class="data-card">
            <div class="card-header">
                <div class="header-left">
                    <h3>Products List</h3>
                    <span class="record-count">
                        {{ $products->count() }} of {{ $totalCount }}
                        {{ ($search || $filterCategory || $filterStock) ? ' (filtered)' : '' }}
                    </span>
                </div>

                <form method="GET" action="{{ route('admin.products.index') }}" class="search-form" id="searchForm">
                    @if($filterCategory)<input type="hidden" name="category" value="{{ $filterCategory }}">@endif
                    @if($filterStock)<input type="hidden" name="stock" value="{{ $filterStock }}">@endif

                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Search products... (Press /)"
                            value="{{ $search }}">
                        @if($search !== '')
                            <a href="{{ buildURL(['search' => null]) }}" class="clear-search">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </form>
            </div>

            <!-- FILTER BAR -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Filters:</label>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ buildURL(['category' => null, 'page' => 1]) }}">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ buildURL(['category' => $cat, 'page' => 1]) }}" {{ $filterCategory === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ buildURL(['stock' => null, 'page' => 1]) }}">All Stock Levels</option>
                        <option value="{{ buildURL(['stock' => 'good', 'page' => 1]) }}" {{ $filterStock === 'good' ? 'selected' : '' }}>Good Stock (10+)</option>
                        <option value="{{ buildURL(['stock' => 'low', 'page' => 1]) }}" {{ $filterStock === 'low' ? 'selected' : '' }}>Low Stock (1-9)</option>
                        <option value="{{ buildURL(['stock' => 'out', 'page' => 1]) }}" {{ $filterStock === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>

                    @if($search || $filterCategory || $filterStock)
                        <a href="{{ route('admin.products.index') }}" class="btn-clear-filters">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    @endif
                </div>
            </div>

            <!-- BULK ACTIONS FORM -->
            <form method="POST" action="" id="bulkForm">
                @csrf
                <div class="bulk-actions" id="bulkActions" style="display: none;">
                    <span class="bulk-info">
                        <i class="fas fa-check-square"></i>
                        <strong id="selectedCount">0</strong> selected
                    </span>
                    <select name="bulk_action" class="bulk-select">
                        <option value="">-- Bulk Action --</option>
                        <option value="activate">Activate Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="btn-apply" onclick="return confirmBulkAction()">Apply</button>
                    <button type="button" class="btn-cancel" onclick="clearSelection()">Cancel</button>
                </div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <th class="col-id">ID</th>
                                <th class="col-product">Product Name</th>
                                <th class="col-price">Price</th>
                                <th class="col-category">Category</th>
                                <th class="col-stock">Stock</th>
                                <th class="col-status">Status</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($products->count() > 0)
                                @foreach($products as $p)
                                    <tr>
                                        <td class="col-checkbox">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $p->id }}" class="row-checkbox"
                                                onchange="updateBulkActions()">
                                        </td>
                                        <td class="col-id">#{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td class="col-product">
                                            <span class="product-name">{{ $p->name }}</span>
                                        </td>
                                        <td class="col-price">
                                            <span class="price-value">₱{{ number_format($p->price, 2) }}</span>
                                        </td>
                                        <td class="col-category">
                                            <span class="category-badge">{{ $p->category }}</span>
                                        </td>
                                        <td class="col-stock">
                                            @if($p->stock <= 0)
                                                <span class="stock-badge stock-out">Out of Stock</span>
                                            @elseif($p->stock < 10)
                                                <span class="stock-badge stock-low">{{ $p->stock }} left</span>
                                            @else
                                                <span class="stock-badge stock-good">{{ $p->stock }} units</span>
                                            @endif
                                            <button type="button" class="quick-stock-btn"
                                                onclick="openStockModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->stock }})"
                                                title="Quick update stock">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        </td>
                                        <td class="col-status">
                                            <a href="{{ route('admin.products.toggle', $p->id) ?? '#' }}"
                                                class="status-toggle {{ $p->is_available ? 'status-active' : 'status-inactive' }}">
                                                {{ $p->is_available ? 'Active' : 'Inactive' }}
                                            </a>
                                        </td>
                                        <td class="col-actions">
                                            {{-- ACTION DROPDOWN --}}
                                            <div class="action-dropdown">
                                                <button type="button" class="action-trigger"
                                                    onclick="toggleActionMenu(event, {{ $p->id }})" title="Actions">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <div class="action-menu" id="action-menu-{{ $p->id }}">
                                                    <a href="{{ route('admin.products.edit', $p->id) }}" class="action-menu-item">
                                                        <i class="fas fa-edit"></i>
                                                        <span>Edit Product</span>
                                                    </a>
                                                    <form action="{{ route('admin.products.destroy', $p->id) }}" method="POST"
                                                        style="margin: 0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-menu-item action-menu-danger"
                                                            onclick="return confirm('Delete {{ addslashes($p->name) }}? Cannot be undone.')">
                                                            <i class="fas fa-trash-alt"></i>
                                                            <span>Delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="empty-row">
                                    <td colspan="8">
                                        <div class="empty-state">
                                            @if($search || $filterCategory || $filterStock)
                                                <i class="fas fa-filter"></i>
                                                <p>No products match your filters</p>
                                                <a href="{{ route('admin.products.index') }}" class="clear-search-link">Clear all
                                                    filters</a>
                                            @else
                                                <i class="fas fa-box-open"></i>
                                                <p>No products yet</p>
                                                <a href="{{ route('admin.products.create') }}" class="btn-add-first">Add your first
                                                    product</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- PAGINATION -->
            @if($totalPages > 1)
                <div class="pagination">
                    <div class="pagination-info">
                        Showing {{ $offset + 1 }}-{{ min($offset + $limit, $totalCount) }} of {{ $totalCount }} products
                    </div>
                    <div class="pagination-controls">
                        @if($page > 1)
                            <a href="{{ buildURL(['page' => 1]) }}" class="page-btn"><i class="fas fa-angle-double-left"></i></a>
                            <a href="{{ buildURL(['page' => $page - 1]) }}" class="page-btn"><i class="fas fa-angle-left"></i></a>
                        @endif

                        @php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                        @endphp

                        @for($i = $start; $i <= $end; $i++)
                            <a href="{{ buildURL(['page' => $i]) }}" class="page-btn {{ $i == $page ? 'active' : '' }}">{{ $i }}</a>
                        @endfor

                        @if($page < $totalPages)
                            <a href="{{ buildURL(['page' => $page + 1]) }}" class="page-btn"><i class="fas fa-angle-right"></i></a>
                            <a href="{{ buildURL(['page' => $totalPages]) }}" class="page-btn"><i
                                    class="fas fa-angle-double-right"></i></a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- STOCK UPDATE MODAL -->
    <div class="modal-overlay" id="stockModal" onclick="if(event.target===this) closeStockModal()">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fas fa-boxes"></i> Quick Stock Update</h3>
                <button class="modal-close" onclick="closeStockModal()"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.products.quick-stock') ?? '#' }}">
                @csrf
                <input type="hidden" name="quick_stock_id" id="stockId">
                <div class="modal-body">
                    <p class="modal-product-name" id="stockProductName"></p>
                    <label class="modal-label">New Stock Quantity:</label>
                    <input type="number" name="quick_stock_value" id="stockValue" min="0" class="modal-input" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeStockModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
            if (e.key === 'Escape') {
                if (document.activeElement.id === 'searchInput') document.activeElement.blur();
                closeStockModal();
                // Close any open action menu
                document.querySelectorAll('.action-menu').forEach(menu => menu.classList.remove('open'));
            }
        });

        function toggleSelectAll(checkbox) {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checkbox.checked);
            updateBulkActions();
        }

        function updateBulkActions() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            const total = document.querySelectorAll('.row-checkbox').length;
            const bulkBar = document.getElementById('bulkActions');
            const countSpan = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAll');

            if (countSpan) countSpan.textContent = checked;
            if (bulkBar) bulkBar.style.display = checked > 0 ? 'flex' : 'none';
            if (selectAll) selectAll.checked = checked > 0 && checked === total;
        }

        function clearSelection() {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
            const selectAll = document.getElementById('selectAll');
            if (selectAll) selectAll.checked = false;
            updateBulkActions();
        }

        function confirmBulkAction() {
            const action = document.querySelector('.bulk-select').value;
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            if (!action) { alert('Please select a bulk action.'); return false; }
            if (action === 'delete') return confirm(`Delete ${count} product(s)? Cannot be undone.`);
            return confirm(`Apply "${action}" to ${count} product(s)?`);
        }

        function openStockModal(id, name, currentStock) {
            document.getElementById('stockId').value = id;
            document.getElementById('stockProductName').textContent = name;
            document.getElementById('stockValue').value = currentStock;
            document.getElementById('stockModal').classList.add('active');
            setTimeout(() => document.getElementById('stockValue').focus(), 100);
        }

        function closeStockModal() {
            document.getElementById('stockModal').classList.remove('active');
        }

        // ============================================
        // ACTION DROPDOWN (Edit + Delete)
        // ============================================
        function toggleActionMenu(event, productId) {
            event.stopPropagation();

            // Close all other open menus
            document.querySelectorAll('.action-menu').forEach(menu => {
                if (menu.id !== 'action-menu-' + productId) {
                    menu.classList.remove('open');
                }
            });

            // Toggle current menu
            const menu = document.getElementById('action-menu-' + productId);
            if (menu) {
                menu.classList.toggle('open');
            }
        }

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.action-dropdown')) {
                document.querySelectorAll('.action-menu').forEach(menu => {
                    menu.classList.remove('open');
                });
            }
        });
    </script>

    <style>
        .products-container {
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

        .page-header-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .page-description {
            font-size: 0.8rem;
            color: #9E9D97;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-primary {
            background: #576238;
            color: white;
        }

        .btn-primary:hover {
            background: #3E4A28;
            transform: translateY(-1px);
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-item {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }

        .stat-item:hover {
            transform: translateY(-2px);
            border-color: #576238;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .stat-total {
            background: #F0EADC;
            color: #576238;
        }

        .stat-active {
            background: #E8F0E3;
            color: #576238;
        }

        .stat-low {
            background: #FEF5E8;
            color: #D4A054;
        }

        .stat-out {
            background: #FEF0ED;
            color: #C5705A;
        }

        .stat-value {
            display: block;
            font-size: 1.25rem;
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
            padding: 0.25rem 0.75rem;
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

        .search-icon {
            position: absolute;
            left: 0.75rem;
            color: #9E9D97;
            font-size: 0.8rem;
        }

        .search-wrapper input {
            padding: 0.5rem 2rem 0.5rem 2rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem 0 0 0.5rem;
            font-size: 0.8rem;
            width: 280px;
            font-family: 'Inter', sans-serif;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: #576238;
        }

        .clear-search {
            position: absolute;
            right: 5rem;
            color: #9E9D97;
            text-decoration: none;
            font-size: 0.8rem;
            padding: 0.25rem;
        }

        .clear-search:hover {
            color: #C5705A;
        }

        .search-btn {
            background: #576238;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .search-btn:hover {
            background: #3E4A28;
        }

        .filter-bar {
            padding: 0.75rem 1.25rem;
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-group label {
            font-size: 0.75rem;
            color: #7A7A75;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .filter-select {
            padding: 0.4rem 0.75rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            background: white;
            color: #2C2B26;
            cursor: pointer;
        }

        .filter-select:hover {
            border-color: #576238;
        }

        .btn-clear-filters {
            background: #FEF0ED;
            color: #C5705A;
            padding: 0.4rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-clear-filters:hover {
            background: #C5705A;
            color: white;
        }

        .bulk-actions {
            background: #E8F0E3;
            border-bottom: 1px solid #576238;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .bulk-info {
            font-size: 0.8rem;
            color: #576238;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .bulk-select {
            padding: 0.4rem 0.75rem;
            border: 1px solid #576238;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            background: white;
        }

        .btn-apply {
            background: #576238;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
        }

        .btn-apply:hover {
            background: #3E4A28;
        }

        .btn-cancel {
            background: white;
            color: #7A7A75;
            border: 1px solid #E3DCD0;
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
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

        .col-checkbox {
            width: 40px;
            text-align: center;
        }

        .col-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #576238;
        }

        .col-id {
            width: 80px;
        }

        .col-price {
            width: 100px;
        }

        .col-category {
            width: 130px;
        }

        .col-stock {
            width: 160px;
        }

        .col-status {
            width: 100px;
        }

        .col-actions {
            width: 70px;
            text-align: center;
        }

        .product-name {
            font-weight: 500;
            color: #2C2B26;
        }

        .price-value {
            font-weight: 600;
            color: #576238;
        }

        .category-badge {
            display: inline-block;
            background: #F0EADC;
            padding: 0.25rem 0.625rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            color: #576238;
            font-weight: 500;
        }

        .stock-badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .stock-good {
            background: #E8F0E3;
            color: #576238;
        }

        .stock-low {
            background: #FEF5E8;
            color: #D4A054;
        }

        .stock-out {
            background: #FEF0ED;
            color: #C5705A;
        }

        .quick-stock-btn {
            background: transparent;
            border: 1px solid #E3DCD0;
            color: #9E9D97;
            width: 22px;
            height: 22px;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.6rem;
            margin-left: 0.3rem;
            transition: all 0.2s ease;
        }

        .quick-stock-btn:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .status-toggle {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .status-active {
            background: #E8F0E3;
            color: #576238;
        }

        .status-active:hover {
            background: #576238;
            color: white;
        }

        .status-inactive {
            background: #F0EADC;
            color: #9E9D97;
        }

        .status-inactive:hover {
            background: #9E9D97;
            color: white;
        }

        /* ============ ACTION DROPDOWN ============ */
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-trigger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.375rem;
            color: #576238;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            padding: 0;
        }

        .action-trigger:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .action-menu {
            position: absolute;
            top: calc(100% + 0.35rem);
            right: 0;
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            padding: 0.35rem;
            min-width: 160px;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-4px);
            transition: all 0.15s ease;
        }

        .action-menu.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .action-menu-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            width: 100%;
            padding: 0.55rem 0.7rem;
            border-radius: 0.375rem;
            color: #2C2B26;
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 500;
            background: transparent;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-align: left;
            transition: all 0.15s ease;
        }

        .action-menu-item i {
            width: 16px;
            text-align: center;
            font-size: 0.8rem;
            color: #576238;
        }

        .action-menu-item:hover {
            background: #F0EADC;
        }

        .action-menu-item:hover i {
            color: #576238;
        }

        .action-menu-danger {
            color: #C5705A;
        }

        .action-menu-danger i {
            color: #C5705A;
        }

        .action-menu-danger:hover {
            background: #FEF0ED;
        }

        .action-menu-danger:hover i {
            color: #A85444;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            border-top: 1px solid #E3DCD0;
            background: #FDF8F0;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .pagination-info {
            font-size: 0.75rem;
            color: #7A7A75;
        }

        .pagination-controls {
            display: flex;
            gap: 0.25rem;
        }

        .page-btn {
            min-width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.5rem;
            border: 1px solid #E3DCD0;
            background: white;
            color: #2C2B26;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .page-btn:hover {
            background: #F0EADC;
            border-color: #576238;
        }

        .page-btn.active {
            background: #576238;
            color: white;
            border-color: #576238;
            font-weight: 600;
        }

        .empty-row td {
            padding: 0 !important;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #D4C9BD;
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state p {
            color: #9E9D97;
            margin-bottom: 0.75rem;
        }

        .empty-state .clear-search-link,
        .empty-state .btn-add-first {
            display: inline-block;
            margin-top: 0.5rem;
            color: #576238;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .empty-state .btn-add-first {
            background: #576238;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(44, 43, 38, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 0.75rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalIn 0.2s ease;
        }

        @keyframes modalIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 0.95rem;
            color: #2C2B26;
            margin: 0;
        }

        .modal-header h3 i {
            color: #576238;
            margin-right: 0.4rem;
        }

        .modal-close {
            background: transparent;
            border: none;
            color: #9E9D97;
            cursor: pointer;
            font-size: 1rem;
            padding: 0.25rem;
        }

        .modal-close:hover {
            color: #C5705A;
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-product-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #576238;
            margin-bottom: 1rem;
            padding: 0.5rem 0.75rem;
            background: #F0EADC;
            border-radius: 0.375rem;
        }

        .modal-label {
            display: block;
            font-size: 0.75rem;
            color: #7A7A75;
            margin-bottom: 0.4rem;
        }

        .modal-input {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }

        .modal-input:focus {
            outline: none;
            border-color: #576238;
        }

        .modal-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #E3DCD0;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
    </style>
@endsection