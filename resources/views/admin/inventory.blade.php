@extends('layouts.app')

@section('content')
    @php
        // ============================================
        // HELPER: buildURL (closure — hindi global function)
        // ============================================
        $buildURL = function ($overrides = []) {
            $params = array_merge(request()->query(), $overrides);
            $params = array_filter($params, function ($v) {
                return $v !== null && $v !== '';
            });
            return route('admin.inventory') . '?' . http_build_query($params);
        };

        $success = session('success');
        $successCount = session('count', 0);
    @endphp

    <div class="inventory-container">

        {{-- ========== PAGE HEADER ========== --}}
        <div class="page-header no-print">
            <div class="page-header-left">
                <h1>Inventory Management</h1>
                <p class="page-description">Monitor and update your stock levels</p>
            </div>
            <div class="header-actions">
                {{-- ✅ MANAGE PRODUCTS — link sa Products page, hindi Add Product --}}
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">
                    <i class="fas fa-box"></i> Manage Products
                </a>
            </div>
        </div>

        {{-- ========== SUCCESS MESSAGES ========== --}}
        @if($success)
            <div class="alert alert-success no-print" id="successAlert">
                <i class="fas fa-check-circle"></i>
                @php
                    $msgs = [
                        'single_updated' => 'Product stock updated successfully!',
                        'stock_updated' => 'Product stock updated successfully!',
                        'bulk_updated' => "$successCount product(s) updated successfully!",
                        'bulk_adjusted' => "$successCount product(s) adjusted successfully!",
                    ];
                @endphp
                {{ $msgs[$success] ?? 'Action completed!' }}
                <button type="button" class="alert-close" onclick="document.getElementById('successAlert').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- ========== CRITICAL ALERT BANNER ========== --}}
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
                <a href="{{ $buildURL(['stock' => 'critical', 'page' => 1]) }}" class="btn-critical">View Critical Items</a>
            </div>
        @endif

        {{-- ========== STATS CARDS ========== --}}
        <div class="inventory-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <h3>Total Products</h3>
                    <p>{{ number_format($totalProductsCount) }}</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <h3>Low Stock</h3>
                    <p>{{ number_format($lowStockCount) }}</p>
                </div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <h3>Out of Stock</h3>
                    <p>{{ number_format($outOfStockCount) }}</p>
                </div>
            </div>
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
                <div class="stat-info">
                    <h3>Inventory Value</h3>
                    <p>₱{{ number_format($inventoryValue, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- ========== MAIN TABLE ========== --}}
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
                            <a href="{{ $buildURL(['search' => null]) }}" class="clear-search" title="Clear">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="search-btn" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ========== FILTER BAR ========== --}}
            <div class="filter-bar no-print">
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Filters:</label>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ $buildURL(['category' => null, 'page' => 1]) }}">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $buildURL(['category' => $cat, 'page' => 1]) }}" {{ $filterCategory === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ $buildURL(['stock' => null, 'page' => 1]) }}">All Stock Levels</option>
                        <option value="{{ $buildURL(['stock' => 'good', 'page' => 1]) }}" {{ $filterStock === 'good' ? 'selected' : '' }}>Good Stock (10+)</option>
                        <option value="{{ $buildURL(['stock' => 'low', 'page' => 1]) }}" {{ $filterStock === 'low' ? 'selected' : '' }}>Low Stock (1-9)</option>
                        <option value="{{ $buildURL(['stock' => 'critical', 'page' => 1]) }}" {{ $filterStock === 'critical' ? 'selected' : '' }}>Critical (&lt; 5)</option>
                        <option value="{{ $buildURL(['stock' => 'out', 'page' => 1]) }}" {{ $filterStock === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ $buildURL(['sort' => 'id_asc', 'page' => 1]) }}" {{ $sortBy === 'id_asc' ? 'selected' : '' }}>
                            Sort: ID ↑</option>
                        <option value="{{ $buildURL(['sort' => 'id_desc', 'page' => 1]) }}" {{ $sortBy === 'id_desc' ? 'selected' : '' }}>
                            Sort: ID ↓</option>
                        <option value="{{ $buildURL(['sort' => 'stock_asc', 'page' => 1]) }}" {{ $sortBy === 'stock_asc' ? 'selected' : '' }}>
                            Sort: Lowest Stock</option>
                        <option value="{{ $buildURL(['sort' => 'stock_desc', 'page' => 1]) }}" {{ $sortBy === 'stock_desc' ? 'selected' : '' }}>
                            Sort: Highest Stock</option>
                        <option value="{{ $buildURL(['sort' => 'name_asc', 'page' => 1]) }}" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>
                            Sort: Name A-Z</option>
                        <option value="{{ $buildURL(['sort' => 'name_desc', 'page' => 1]) }}" {{ $sortBy === 'name_desc' ? 'selected' : '' }}>
                            Sort: Name Z-A</option>
                        <option value="{{ $buildURL(['sort' => 'value_desc', 'page' => 1]) }}" {{ $sortBy === 'value_desc' ? 'selected' : '' }}>
                            Sort: Highest Value</option>
                        <option value="{{ $buildURL(['sort' => 'value_asc', 'page' => 1]) }}" {{ $sortBy === 'value_asc' ? 'selected' : '' }}>
                            Sort: Lowest Value</option>
                    </select>

                    @if($search || $filterCategory || $filterStock || $sortBy !== 'id_asc')
                        <a href="{{ route('admin.inventory') }}" class="btn-clear-filters">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    @endif
                </div>
            </div>

            {{-- ========== SINGLE UPDATE FORM (per row - hidden) ========== --}}
            <form method="POST" action="{{ route('admin.products.quick-stock') }}" id="singleForm" style="display:none;">
                @csrf
                <input type="hidden" name="id" id="singleProductId">
                <input type="hidden" name="stock" id="singleStockValue">
            </form>

            {{-- ========== BULK FORM ========== --}}
            <form method="POST" action="{{ route('admin.inventory.bulk-update') }}" id="bulkForm">
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
                                                <button type="button" class="qty-btn" title="Decrease"
                                                    onclick="adjustInput({{ $p->id }}, -1)">−</button>
                                                <input type="number" name="stock[{{ $p->id }}]" id="stock_{{ $p->id }}"
                                                    value="{{ $p->stock }}" min="0" class="stock-input">
                                                <button type="button" class="qty-btn" title="Increase"
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
                                            <div class="empty-icon">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                            <p>No products found</p>
                                            @if($search || $filterCategory || $filterStock)
                                                <a href="{{ route('admin.inventory') }}" class="btn-add-first">
                                                    <i class="fas fa-times"></i> Clear filters
                                                </a>
                                            @else
                                                <a href="{{ route('admin.products.index') }}" class="btn-add-first">
                                                    <i class="fas fa-box"></i> Go to Products
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- ========== BULK ACTIONS BAR ========== --}}
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

                {{-- ========== SAVE ALL BUTTON ========== --}}
                <div class="save-all-bar no-print">
                    <button type="submit" name="bulk_update" value="1" class="btn-save-all">
                        <i class="fas fa-save"></i> Save All Changes
                    </button>
                </div>
            </form>

            {{-- ========== PAGINATION (PROFESSIONAL) ========== --}}
            @if($products->hasPages())
                <div class="pagination-wrapper no-print">
                    <div class="pagination-info">
                        <i class="fas fa-list-ul"></i>
                        Showing <strong>{{ $products->firstItem() }}-{{ $products->lastItem() }}</strong> of
                        <strong>{{ $products->total() }}</strong> products
                    </div>

                    <nav class="custom-pagination" role="navigation" aria-label="Pagination Navigation">
                        <ul class="pagination-list">
                            @if ($products->onFirstPage())
                                <li class="pagination-item disabled">
                                    <span class="pagination-link"><i class="fas fa-chevron-left"></i></span>
                                </li>
                            @else
                                <li class="pagination-item">
                                    <a href="{{ $products->previousPageUrl() }}" class="pagination-link" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $pageNum => $url)
                                @if ($pageNum == $products->currentPage())
                                    <li class="pagination-item active">
                                        <span class="pagination-link">{{ $pageNum }}</span>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <a href="{{ $url }}" class="pagination-link">{{ $pageNum }}</a>
                                    </li>
                                @endif
                            @endforeach

                            @if ($products->hasMorePages())
                                <li class="pagination-item">
                                    <a href="{{ $products->nextPageUrl() }}" class="pagination-link" rel="next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="pagination-item disabled">
                                    <span class="pagination-link"><i class="fas fa-chevron-right"></i></span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    {{-- ========== JAVASCRIPT ========== --}}
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

            const form = document.getElementById('bulkForm');
            const hiddenMode = document.createElement('input');
            hiddenMode.type = 'hidden';
            hiddenMode.name = 'bulk_adjust_mode';
            hiddenMode.value = mode;
            form.appendChild(hiddenMode);

            const hiddenAmount = document.createElement('input');
            hiddenAmount.type = 'hidden';
            hiddenAmount.name = 'bulk_adjust_amount';
            hiddenAmount.value = amount;
            form.appendChild(hiddenAmount);

            const hiddenFlag = document.createElement('input');
            hiddenFlag.type = 'hidden';
            hiddenFlag.name = 'bulk_adjust';
            hiddenFlag.value = '1';
            form.appendChild(hiddenFlag);

            form.submit();
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        });

        // Auto hide success alert
        document.addEventListener('DOMContentLoaded', function () {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.transition = 'opacity 0.5s ease';
                    successAlert.style.opacity = '0';
                    setTimeout(() => successAlert.remove(), 500);
                }, 5000);
            }
        });
    </script>

    <style>
        .inventory-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* ============ PAGE HEADER ============ */
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
            margin: 0 0 0.25rem 0;
        }

        .page-description {
            font-size: 0.8rem;
            color: #9E9D97;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* ============ BUTTONS ============ */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #7A8B4F, #576238);
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
            line-height: 1;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.3);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            font-family: inherit;
            line-height: 1;
        }

        .btn-secondary:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
            transform: translateY(-1px);
        }

        /* ============ ALERTS ============ */
        .alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            position: relative;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: #E8F0E3;
            border: 1px solid #576238;
            color: #576238;
        }

        .alert-close {
            background: transparent;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: auto;
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }

        .alert-close:hover {
            opacity: 1;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============ CRITICAL BANNER ============ */
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
            transform: translateY(-1px);
        }

        /* ============ STATS CARDS ============ */
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
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.06);
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
            font-weight: 600;
        }

        .stat-info p {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0;
            font-family: 'Inter', sans-serif;
            line-height: 1.2;
        }

        /* ============ DATA CARD ============ */
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

        /* ============ SEARCH ============ */
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
            left: 0.875rem;
            color: #9E9D97;
            font-size: 0.8rem;
            pointer-events: none;
            z-index: 1;
        }

        .search-wrapper input {
            padding: 0.55rem 3rem 0.55rem 2.25rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            width: 280px;
            font-family: 'Inter', sans-serif;
            background: white;
            transition: all 0.2s ease;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .search-wrapper input::placeholder {
            color: #C4C3BC;
        }

        .clear-search {
            position: absolute;
            right: 2.75rem;
            color: #9E9D97;
            text-decoration: none;
            font-size: 0.75rem;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .clear-search:hover {
            color: #C5705A;
        }

        .search-btn {
            position: absolute;
            right: 0.25rem;
            background: #576238;
            color: white;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 0.375rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        .search-btn:hover {
            background: #3E4A28;
        }

        /* ============ FILTER BAR ============ */
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
            font-family: inherit;
            transition: all 0.2s;
        }

        .filter-select:hover {
            border-color: #576238;
        }

        .filter-select:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .btn-clear-filters {
            background: #FEF0ED;
            color: #C5705A;
            border: 1px solid #F8DCD4;
            padding: 0.4rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s ease;
            font-family: inherit;
            line-height: 1;
        }

        .btn-clear-filters:hover {
            background: #C5705A;
            color: white;
            border-color: #C5705A;
        }

        /* ============ BULK BAR ============ */
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
            font-family: inherit;
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
            font-family: inherit;
            transition: all 0.2s ease;
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
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: #F0EADC;
        }

        /* ============ SAVE ALL ============ */
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
            font-family: inherit;
        }

        .btn-save-all:hover {
            background: #3E4A28;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.2);
        }

        /* ============ TABLE ============ */
        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Inter', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.5;
            min-width: 900px;
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
            white-space: nowrap;
        }

        .data-table td {
            padding: 0.875rem 1rem;
            text-align: left;
            color: #2C2B26;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        .data-table tbody tr {
            transition: background 0.15s ease;
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
            font-family: 'Inter', sans-serif;
        }

        .value-text {
            font-weight: 600;
            color: #2C2B26;
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
        }

        /* ============ STOCK DISPLAY ============ */
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
            font-weight: 600;
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

        /* ============ STATUS BADGES ============ */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
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

        /* ============ UPDATE STOCK GROUP ============ */
        .update-stock-group {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .qty-btn {
            width: 30px;
            height: 32px;
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.95rem;
            color: #576238;
            font-weight: 700;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: inherit;
            padding: 0;
            line-height: 1;
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
            transition: all 0.2s ease;
        }

        .stock-input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
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
            font-family: inherit;
            padding: 0;
        }

        .btn-save-single:hover {
            background: #3E4A28;
            transform: scale(1.05);
        }

        /* ============ PAGINATION (PROFESSIONAL) ============ */
        .pagination-wrapper {
            padding: 1rem 1.25rem;
            border-top: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            background: #FDF8F0;
        }

        .pagination-info {
            font-size: 0.8rem;
            color: #6B6A65;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pagination-info i {
            color: #576238;
            font-size: 0.75rem;
        }

        .pagination-info strong {
            color: #2C2B26;
            font-weight: 600;
        }

        .custom-pagination {
            display: inline-block;
        }

        .pagination-list {
            display: flex;
            gap: 0.375rem;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .pagination-item {
            display: inline-block;
        }

        .pagination-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #576238;
            background: white;
            border: 1px solid #E3DCD0;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            line-height: 1;
        }

        .pagination-link:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(87, 98, 56, 0.1);
        }

        .pagination-item.active .pagination-link {
            background: #576238;
            border-color: #576238;
            color: white;
            font-weight: 700;
            cursor: default;
            box-shadow: 0 2px 8px rgba(87, 98, 56, 0.25);
        }

        .pagination-item.active .pagination-link:hover {
            transform: none;
            background: #576238;
            color: white;
        }

        .pagination-item.disabled .pagination-link {
            background: #F0EADC;
            color: #C4C3BC;
            border-color: #E3DCD0;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .pagination-item.disabled .pagination-link:hover {
            transform: none;
            background: #F0EADC;
            color: #C4C3BC;
            border-color: #E3DCD0;
            box-shadow: none;
        }

        .pagination-link i {
            font-size: 0.7rem;
        }

        /* ============ EMPTY STATE ============ */
        .empty-row td {
            padding: 0 !important;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            text-align: center;
            gap: 0.875rem;
        }

        .empty-icon {
            width: 60px;
            height: 60px;
            background: #F0EADC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-icon i {
            font-size: 1.5rem;
            color: #C4C3BC;
        }

        .empty-state p {
            color: #9E9D97;
            margin: 0;
            font-size: 0.85rem;
        }

        .btn-add-first {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #576238;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
            font-family: inherit;
            line-height: 1;
        }

        .btn-add-first:hover {
            background: #3E4A28;
            transform: translateY(-1px);
        }

        .btn-add-first i {
            font-size: 0.75rem;
        }

        /* ============ PRINT ============ */
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection