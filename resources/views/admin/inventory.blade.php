@extends('layouts.app')

@section('content')
    @php
        use App\Models\Product;
        use Illuminate\Support\Facades\DB;

        $page_title = 'Inventory';
        $hide_page_title = true;

        // ============================================
        // FILTERS & SORTING
        // ============================================
        $search = trim($_GET['search'] ?? '');
        $filterCategory = trim($_GET['category'] ?? '');
        $filterStock = trim($_GET['stock'] ?? '');
        $sortBy = $_GET['sort'] ?? 'id_asc';

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
        } elseif ($filterStock === 'critical') {
            $query->where('stock', '<', 5);
        }

        $sortOptions = [
            'id_asc' => ['id', 'ASC'],
            'id_desc' => ['id', 'DESC'],
            'name_asc' => ['name', 'ASC'],
            'name_desc' => ['name', 'DESC'],
            'stock_asc' => ['stock', 'ASC'],
            'stock_desc' => ['stock', 'DESC'],
            'value_asc' => [DB::raw('(price * stock)'), 'ASC'],
            'value_desc' => [DB::raw('(price * stock)'), 'DESC']
        ];
        $sortOpt = $sortOptions[$sortBy] ?? ['id', 'ASC'];

        // ============================================
        // PAGINATION
        // ============================================
        $perPage = 15;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $offset = ($page - 1) * $perPage;

        $totalProducts = $query->count();
        $totalPages = ceil($totalProducts / $perPage);

        $products = $query->orderBy($sortOpt[0], $sortOpt[1])->skip($offset)->take($perPage)->get();

        // ============================================
        // CATEGORIES FOR FILTER
        // ============================================
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category', 'asc')
            ->pluck('category');

        // ============================================
        // STATISTICS
        // ============================================
        $allProducts = Product::all();
        $totalProductsCount = $allProducts->count();
        $lowStockCount = $allProducts->filter(fn($p) => $p->stock < 10 && $p->stock > 0)->count();
        $outOfStockCount = $allProducts->filter(fn($p) => $p->stock == 0)->count();
        $criticalCount = $allProducts->filter(fn($p) => $p->stock < 5 && $p->stock > 0)->count();
        $totalValue = $allProducts->sum(fn($p) => $p->price * $p->stock);

        // ============================================
        // HELPER: buildURL
        // ============================================
        function buildURL($overrides = [])
        {
            $params = array_merge($_GET, $overrides);
            $params = array_filter($params, function ($v) {
                return $v !== null && $v !== '';
            });
            return 'inventory?' . http_build_query($params);
        }

        $success = $_GET['success'] ?? '';
        $successCount = $_GET['count'] ?? 0;
    @endphp

    <div class="inventory-container">

        <!-- ========== PAGE HEADER ========== -->
        <div class="page-header no-print">
            <div class="page-header-left">
                <h1>Inventory Management</h1>
                <p class="page-description">Monitor and update your stock levels</p>
            </div>
        </div>

        <!-- ========== SUCCESS MESSAGES ========== -->
        @if($success)
            <div class="alert alert-success no-print">
                <i class="fas fa-check-circle"></i>
                @php
                    $msgs = [
                        'single_updated' => 'Product stock updated successfully!',
                        'bulk_updated' => "$successCount product(s) updated successfully!",
                        'bulk_adjusted' => "$successCount product(s) adjusted successfully!"
                    ];
                @endphp
                {{ $msgs[$success] ?? 'Action completed!' }}
            </div>
        @endif

        <!-- ========== CRITICAL ALERT BANNER ========== -->
        @if($criticalCount > 0 || $outOfStockCount > 0)
            <div class="critical-banner no-print">
                <div class="critical-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="critical-text">
                    <strong>Attention Needed!</strong>
                    @if($outOfStockCount > 0)
                        <span>{{ $outOfStockCount }} product(s) are <strong>out of stock</strong>.</span>
                    @endif
                    @if($criticalCount > 0)
                        <span>{{ $criticalCount }} product(s) have <strong>critically low stock</strong> (below 5).</span>
                    @endif
                </div>
                <a href="{{ buildURL(['stock' => 'critical', 'page' => 1]) }}" class="btn-critical">View Critical Items</a>
            </div>
        @endif

        <!-- ========== STATS CARDS ========== -->
        <div class="inventory-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <h3>Total Products</h3>
                    <p>{{ $totalProductsCount }}</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <h3>Low Stock</h3>
                    <p>{{ $lowStockCount }}</p>
                </div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <h3>Out of Stock</h3>
                    <p>{{ $outOfStockCount }}</p>
                </div>
            </div>
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
                <div class="stat-info">
                    <h3>Inventory Value</h3>
                    <p>₱{{ number_format($totalValue, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- ========== MAIN TABLE ========== -->
        <div class="data-card">
            <div class="card-header">
                <div class="header-left">
                    <h3><i class="fas fa-table"></i> Products Inventory</h3>
                    <span class="record-count">
                        {{ $products->count() }} of {{ $totalProducts }}
                        {{ ($search || $filterCategory || $filterStock) ? ' (filtered)' : '' }}
                    </span>
                </div>

                <form method="GET" action="{{ route('admin.inventory') }}" class="search-form no-print">
                    @if($filterCategory)<input type="hidden" name="category" value="{{ $filterCategory }}">@endif
                    @if($filterStock)<input type="hidden" name="stock" value="{{ $filterStock }}">@endif
                    @if($sortBy)<input type="hidden" name="sort" value="{{ $sortBy }}">@endif
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Search products..."
                            value="{{ $search }}">
                        @if($search !== '')
                            <a href="{{ buildURL(['search' => null]) }}" class="clear-search"><i class="fas fa-times"></i></a>
                        @endif
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </form>
            </div>

            <!-- ========== FILTER BAR ========== -->
            <div class="filter-bar no-print">
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
                        <option value="{{ buildURL(['stock' => 'critical', 'page' => 1]) }}" {{ $filterStock === 'critical' ? 'selected' : '' }}>Critical (< 5)</option>
                        <option value="{{ buildURL(['stock' => 'out', 'page' => 1]) }}" {{ $filterStock === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ buildURL(['sort' => 'id_asc']) }}" {{ $sortBy === 'id_asc' ? 'selected' : '' }}>
                            Sort: ID ↑</option>
                        <option value="{{ buildURL(['sort' => 'stock_asc']) }}" {{ $sortBy === 'stock_asc' ? 'selected' : '' }}>Sort: Lowest Stock</option>
                        <option value="{{ buildURL(['sort' => 'stock_desc']) }}" {{ $sortBy === 'stock_desc' ? 'selected' : '' }}>Sort: Highest Stock</option>
                        <option value="{{ buildURL(['sort' => 'name_asc']) }}" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>
                            Sort: Name A-Z</option>
                        <option value="{{ buildURL(['sort' => 'value_desc']) }}" {{ $sortBy === 'value_desc' ? 'selected' : '' }}>Sort: Highest Value</option>
                    </select>

                    @if($search || $filterCategory || $filterStock || $sortBy !== 'id_asc')
                        <a href="{{ route('admin.inventory') }}" class="btn-clear-filters">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    @endif
                </div>
            </div>

            <!-- ========== SINGLE UPDATE FORM (per row - hidden) ========== -->
            <form method="POST" action="{{ route('admin.products.quick-stock') }}" id="singleForm" style="display:none;">
                @csrf
                <input type="hidden" name="quick_stock_id" id="singleProductId">
                <input type="hidden" name="quick_stock_value" id="singleStockValue">
            </form>

            <!-- ========== BULK FORM ========== -->
            <form method="POST" action="{{ route('admin.inventory.bulk-update') ?? '#' }}" id="bulkForm">
                @csrf
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-checkbox no-print">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <th class="col-id">ID</th>
                                <th class="col-product">Product Name</th>
                                <th class="col-category">Category</th>
                                <th class="col-price">Price</th>
                                <th class="col-stock">Current Stock</th>
                                <th class="col-value">Stock Value</th>
                                <th class="col-status">Status</th>
                                <th class="col-update no-print">Update Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($products->count() > 0)
                                @foreach($products as $p)
                                    @php
                                        $stockValue = $p->price * $p->stock;
                                        $stockPercent = min(($p->stock / 50) * 100, 100);
                                        $barColor = $p->stock == 0 ? '#C5705A' : ($p->stock < 10 ? '#D4A054' : '#576238');
                                    @endphp
                                    <tr class="{{ $p->stock == 0 ? 'row-danger' : ($p->stock < 5 ? 'row-warning' : '') }}">
                                        <td class="col-checkbox no-print">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $p->id }}" class="row-checkbox"
                                                onchange="updateBulkActions()">
                                        </td>
                                        <td class="col-id">#{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td class="col-product">
                                            <span class="product-name">{{ $p->name }}</span>
                                        </td>
                                        <td class="col-category">
                                            <span class="category-badge">{{ $p->category }}</span>
                                        </td>
                                        <td class="col-price">
                                            <span class="price-value">₱{{ number_format($p->price, 2) }}</span>
                                        </td>
                                        <td class="col-stock">
                                            <div class="stock-display">
                                                <span
                                                    class="stock-badge {{ $p->stock == 0 ? 'stock-out' : ($p->stock < 10 ? 'stock-low' : 'stock-good') }}">
                                                    {{ $p->stock }} units
                                                </span>
                                                <div class="stock-bar-bg">
                                                    <div class="stock-bar-fill"
                                                        style="width: {{ $stockPercent }}%; background: {{ $barColor }};"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="col-value">
                                            <span class="value-text">₱{{ number_format($stockValue, 2) }}</span>
                                        </td>
                                        <td class="col-status">
                                            @if($p->stock <= 0)
                                                <span class="status-badge status-out">Out of Stock</span>
                                            @elseif($p->stock < 10)
                                                <span class="status-badge status-low">Low Stock</span>
                                            @else
                                                <span class="status-badge status-good">In Stock</span>
                                            @endif
                                        </td>
                                        <td class="col-update no-print">
                                            <div class="update-stock-group">
                                                <button type="button" class="qty-btn"
                                                    onclick="adjustInput({{ $p->id }}, -1)">−</button>
                                                <input type="number" name="stock[{{ $p->id }}]" id="stock_{{ $p->id }}"
                                                    value="{{ $p->stock }}" min="0" class="stock-input">
                                                <button type="button" class="qty-btn"
                                                    onclick="adjustInput({{ $p->id }}, 1)">+</button>
                                                <button type="button" class="btn-save-single" onclick="saveSingle({{ $p->id }})"
                                                    title="Save this row">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="empty-row">
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <i class="fas fa-box-open"></i>
                                            <p>No products found</p>
                                            @if($search || $filterCategory || $filterStock)
                                                <a href="{{ route('admin.inventory') }}" class="btn-add-first">Clear filters</a>
                                            @else
                                                <a href="{{ route('admin.products.create') }}" class="btn-add-first">Add first
                                                    product</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- ========== BULK ACTIONS BAR (Bottom) ========== -->
                <div class="bulk-adjust-bar no-print" id="bulkAdjustBar" style="display: none;">
                    <div class="bulk-info">
                        <i class="fas fa-check-square"></i>
                        <strong id="selectedCount">0</strong> product(s) selected
                    </div>
                    <div class="bulk-controls">
                        <span>Quick Adjust:</span>
                        <select class="bulk-select" id="adjustMode">
                            <option value="add">Add (+)</option>
                            <option value="subtract">Subtract (−)</option>
                        </select>
                        <input type="number" id="adjustAmount" value="10" min="1" class="bulk-amount">
                        <button type="button" class="btn-apply" onclick="applyBulkAdjust()">
                            <i class="fas fa-check"></i> Apply to Selected
                        </button>
                        <button type="button" class="btn-cancel" onclick="clearSelection()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>

                <!-- ========== SAVE ALL BUTTON ========== -->
                <div class="save-all-bar no-print">
                    <button type="submit" name="bulk_update" value="1" class="btn-save-all">
                        <i class="fas fa-save"></i> Save All Changes
                    </button>
                </div>
            </form>

            <!-- ========== PAGINATION ========== -->
            @if($totalPages > 1)
                <div class="pagination no-print">
                    <div class="pagination-info">
                        Showing {{ $offset + 1 }}-{{ min($offset + $perPage, $totalProducts) }} of {{ $totalProducts }} products
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

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        function saveSingle(id) {
            const input = document.getElementById('stock_' + id);
            const value = parseInt(input.value) || 0;

            if (value < 0) {
                alert('Stock cannot be negative.');
                return;
            }

            document.getElementById('singleProductId').value = id;
            document.getElementById('singleStockValue').value = value;
            document.getElementById('singleForm').submit();
        }

        function adjustInput(id, delta) {
            const input = document.getElementById('stock_' + id);
            let val = parseInt(input.value) || 0;
            val = Math.max(0, val + delta);
            input.value = val;
        }

        function toggleSelectAll(checkbox) {
            document.querySelectorAll('.row-checkbox').forEach(function (cb) {
                cb.checked = checkbox.checked;
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            const total = document.querySelectorAll('.row-checkbox').length;
            const bar = document.getElementById('bulkAdjustBar');
            const countSpan = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAll');

            if (countSpan) countSpan.textContent = checked;
            if (bar) bar.style.display = checked > 0 ? 'flex' : 'none';
            if (selectAll) selectAll.checked = checked > 0 && checked === total;
        }

        function clearSelection() {
            document.querySelectorAll('.row-checkbox').forEach(function (cb) {
                cb.checked = false;
            });
            document.getElementById('selectAll').checked = false;
            updateBulkActions();
        }

        function applyBulkAdjust() {
            const mode = document.getElementById('adjustMode').value;
            const amount = parseInt(document.getElementById('adjustAmount').value);
            const count = document.querySelectorAll('.row-checkbox:checked').length;

            if (amount < 1) {
                alert('Amount must be at least 1.');
                return;
            }
            if (!confirm((mode === 'add' ? 'Add ' : 'Subtract ') + amount + ' to ' + count + ' product(s)?')) return;

            document.getElementById('adjustModeHidden').value = mode;
            document.getElementById('adjustAmountHidden').value = amount;

            const form = document.getElementById('bulkForm');
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'bulk_adjust';
            hidden.value = '1';
            form.appendChild(hidden);
            form.submit();
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        });
    </script>

    <style>
        /* COPY LAHAT NG CSS MULA SA OLD FILE */
        .inventory-container {
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

        .critical-banner {
            background: linear-gradient(135deg, #FEF0ED, #FEF5E8);
            border: 1px solid #C5705A;
            border-left: 4px solid #C5705A;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .critical-icon {
            width: 44px;
            height: 44px;
            background: white;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #C5705A;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .critical-text {
            flex: 1;
            font-size: 0.8rem;
            color: #2C2B26;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .critical-text strong {
            color: #C5705A;
        }

        .btn-critical {
            background: #C5705A;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-critical:hover {
            background: #A85844;
        }

        .inventory-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 900px) {
            .inventory-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            .inventory-stats {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: white;
            padding: 1.25rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid #E3DCD0;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: #576238;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .stat-icon i {
            color: #576238;
        }

        .stat-card.warning .stat-icon {
            background: #FEF5E8;
        }

        .stat-card.warning .stat-icon i {
            color: #D4A054;
        }

        .stat-card.danger .stat-icon {
            background: #FEF0ED;
        }

        .stat-card.danger .stat-icon i {
            color: #C5705A;
        }

        .stat-card.total .stat-icon {
            background: #E8F0E3;
        }

        .stat-info h3 {
            font-size: 0.7rem;
            color: #9E9D97;
            margin: 0 0 0.25rem 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0;
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

        .header-left h3 i {
            color: #576238;
            margin-right: 0.4rem;
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
            padding: 0.5rem 2rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem 0 0 0.5rem;
            font-size: 0.8rem;
            width: 260px;
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

        .bulk-adjust-bar {
            background: #E8F0E3;
            border-top: 1px solid #576238;
            border-bottom: 1px solid #576238;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
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

        .bulk-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #576238;
            flex-wrap: wrap;
        }

        .bulk-select,
        .bulk-amount {
            padding: 0.4rem 0.6rem;
            border: 1px solid #576238;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            background: white;
        }

        .bulk-amount {
            width: 70px;
            text-align: center;
        }

        .btn-apply {
            background: #576238;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-weight: 500;
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

        .btn-cancel:hover {
            background: #F0EADC;
        }

        .save-all-bar {
            padding: 1rem 1.25rem;
            background: #FDF8F0;
            border-top: 1px solid #E3DCD0;
            display: flex;
            justify-content: flex-end;
        }

        .btn-save-all {
            background: #576238;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-save-all:hover {
            background: #3E4A28;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.2);
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

        .row-danger {
            background: #FFF8F6;
        }

        .row-danger:hover {
            background: #FEF0ED !important;
        }

        .row-warning {
            background: #FFFCF5;
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
            width: 70px;
        }

        .col-price {
            width: 100px;
        }

        .col-category {
            width: 110px;
        }

        .col-stock {
            width: 150px;
        }

        .col-value {
            width: 110px;
        }

        .col-status {
            width: 110px;
        }

        .col-update {
            width: 190px;
        }

        .product-name {
            font-weight: 500;
            color: #2C2B26;
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

        .price-value {
            font-weight: 600;
            color: #576238;
        }

        .value-text {
            font-weight: 600;
            color: #2C2B26;
            font-size: 0.8rem;
        }

        .stock-display {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .stock-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
            width: fit-content;
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

        .stock-bar-bg {
            width: 100%;
            height: 4px;
            background: #F0EADC;
            border-radius: 4px;
            overflow: hidden;
        }

        .stock-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-good {
            background: #E8F0E3;
            color: #576238;
        }

        .status-low {
            background: #FEF5E8;
            color: #D4A054;
        }

        .status-out {
            background: #FEF0ED;
            color: #C5705A;
        }

        .update-stock-group {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .qty-btn {
            width: 28px;
            height: 32px;
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.9rem;
            color: #576238;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .stock-input {
            width: 65px;
            padding: 0.4rem 0.5rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            text-align: center;
            font-family: 'Inter', sans-serif;
        }

        .stock-input:focus {
            outline: none;
            border-color: #576238;
        }

        .btn-save-single {
            background: #576238;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-left: 0.2rem;
        }

        .btn-save-single:hover {
            background: #3E4A28;
            transform: scale(1.05);
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

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection