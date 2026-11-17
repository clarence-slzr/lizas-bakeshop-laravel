@extends('layouts.app')

@section('content')
    <div class="orders-container">

        {{-- ============ PAGE HEADER ============ --}}
        <div class="page-header">
            <div class="page-header-left">
                <h1>Orders Management</h1>
                <p class="page-description">View and manage all customer orders</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('orders.index') }}" class="btn-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh
                </a>
            </div>
        </div>

        {{-- ============ FLASH MESSAGES ============ --}}
        @if(session('success'))
            <div class="alert alert-success" id="flashAlert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="alert-close" data-dismiss="flashAlert"><i class="fas fa-times"></i></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" id="flashError">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="alert-close" data-dismiss="flashError"><i class="fas fa-times"></i></button>
            </div>
        @endif

        {{-- ============ STATS CARDS ============ --}}
        <div class="stats-grid">
            <a href="{{ route('orders.index') }}" class="stat-card {{ !request('status') ? 'stat-active' : '' }}">
                <div class="stat-icon stat-icon-total"><i class="fas fa-receipt"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['total'] ?? 0 }}</span>
                    <span class="stat-label">Total Orders</span>
                </div>
            </a>

            <a href="{{ route('orders.index', ['status' => 'pending']) }}"
                class="stat-card {{ request('status') === 'pending' ? 'stat-active' : '' }}">
                <div class="stat-icon stat-icon-pending"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['pending'] ?? 0 }}</span>
                    <span class="stat-label">Pending</span>
                </div>
            </a>

            <a href="{{ route('orders.index', ['status' => 'completed']) }}"
                class="stat-card {{ request('status') === 'completed' ? 'stat-active' : '' }}">
                <div class="stat-icon stat-icon-completed"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['completed'] ?? 0 }}</span>
                    <span class="stat-label">Completed</span>
                </div>
            </a>

            <a href="{{ route('orders.index', ['status' => 'cancelled']) }}"
                class="stat-card {{ request('status') === 'cancelled' ? 'stat-active' : '' }}">
                <div class="stat-icon stat-icon-cancelled"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-value">{{ $stats['cancelled'] ?? 0 }}</span>
                    <span class="stat-label">Cancelled</span>
                </div>
            </a>
        </div>

        {{-- ============ ORDERS CARD ============ --}}
        <div class="data-card">
            {{-- Card Header --}}
            <div class="card-header">
                <div class="header-left">
                    <h3><i class="fas fa-list"></i> All Orders</h3>
                    <span class="record-count">{{ $orders->total() }} orders</span>
                </div>
            </div>

            {{-- Status Filter Tabs --}}
            <div class="filter-tabs">
                <a href="{{ route('orders.index', array_merge(request()->except('status', 'page'))) }}"
                    class="filter-tab {{ !request('status') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> All
                    <span class="count">{{ $stats['total'] }}</span>
                </a>
                <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'completed'])) }}"
                    class="filter-tab {{ request('status') === 'completed' ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i> Completed
                    <span class="count">{{ $stats['completed'] }}</span>
                </a>
                <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
                    class="filter-tab {{ request('status') === 'pending' ? 'active' : '' }}">
                    <i class="fas fa-clock"></i> Pending
                    <span class="count">{{ $stats['pending'] }}</span>
                </a>
                <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'cancelled'])) }}"
                    class="filter-tab {{ request('status') === 'cancelled' ? 'active' : '' }}">
                    <i class="fas fa-times-circle"></i> Cancelled
                    <span class="count">{{ $stats['cancelled'] }}</span>
                </a>
                <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'refunded'])) }}"
                    class="filter-tab {{ request('status') === 'refunded' ? 'active' : '' }}">
                    <i class="fas fa-rotate-left"></i> Refunded
                    <span class="count">{{ $stats['refunded'] }}</span>
                </a>
            </div>

            {{-- Advanced Filter Bar (Date & Sort) --}}
            <div class="filter-bar">
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Filters:</label>

                    {{-- Date Filter --}}
                    <select class="filter-select" onchange="location.href=this.value">
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('date', 'start_date', 'end_date', 'page'))) }}"
                            {{ !request('date') && !request('start_date') ? 'selected' : '' }}>All Dates</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'today'])) }}"
                            {{ request('date') === 'today' ? 'selected' : '' }}>Today</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'yesterday'])) }}"
                            {{ request('date') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'week'])) }}"
                            {{ request('date') === 'week' ? 'selected' : '' }}>Last 7 Days</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'month'])) }}"
                            {{ request('date') === 'month' ? 'selected' : '' }}>This Month</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'last_month'])) }}"
                            {{ request('date') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'year'])) }}"
                            {{ request('date') === 'year' ? 'selected' : '' }}>This Year</option>
                    </select>

                    {{-- Sort (Placeholder) --}}
                    <select class="filter-select">
                        <option>Newest First</option>
                    </select>

                    {{-- Clear Filters Button --}}
                    @if(request('status') || request('date') || request('search') || request('start_date'))
                        <a href="{{ route('orders.index') }}" class="btn-clear-filters">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    @endif
                </div>
            </div>

            {{-- Table --}}
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-order">Order No.</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-amount text-center">Total Amount</th>
                            <th class="col-type text-center">Type</th>
                            <th class="col-status text-center">Status</th>
                            <th class="col-date text-center">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $status = $order->status ?? 'pending';
                                $statusClass = match ($status) {
                                    'pending' => 'status-pending',
                                    'completed' => 'status-completed',
                                    'cancelled' => 'status-cancelled',
                                    'refunded' => 'status-refunded',
                                    default => 'status-pending',
                                };
                                $statusIcon = match ($status) {
                                    'pending' => 'fa-clock',
                                    'completed' => 'fa-check-circle',
                                    'cancelled' => 'fa-times-circle',
                                    'refunded' => 'fa-rotate-left',
                                    default => 'fa-clock',
                                };
                            @endphp
                            <tr class="clickable-row" data-href="{{ route('orders.show', $order->id) }}">
                                <td class="col-order">
                                    <span class="order-link">
                                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="col-customer">
                                    <div class="customer-cell">
                                        <div class="customer-avatar">
                                            {{ strtoupper(substr($order->customer_name ?? 'G', 0, 1)) }}
                                        </div>
                                        <div class="customer-text">
                                            <span class="customer-name">{{ $order->customer_name ?? 'Guest' }}</span>
                                            <span class="customer-phone">{{ $order->contact_number ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-amount text-center">
                                    <span class="amount">₱{{ number_format($order->total_amount, 2) }}</span>
                                </td>
                                <td class="col-type text-center">
                                    @if(($order->order_type ?? 'pickup') === 'delivery')
                                        <span class="type-badge type-delivery">
                                            <i class="fas fa-truck"></i> Delivery
                                        </span>
                                    @else
                                        <span class="type-badge type-pickup">
                                            <i class="fas fa-store"></i> Pick-up
                                        </span>
                                    @endif
                                </td>
                                <td class="col-status text-center">
                                    <span class="status-badge {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }}"></i> {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="col-date text-center">
                                    <span class="date-primary">{{ $order->order_date->format('M d, Y') }}</span>
                                    <span class="date-time">{{ $order->order_date->format('h:i A') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fas fa-receipt"></i></div>
                                        <p>No orders found</p>
                                        @if(request()->hasAny(['status', 'search', 'date', 'start_date']))
                                            <a href="{{ route('orders.index') }}" class="link-clear">Clear filters</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============ CUSTOM PAGINATION ============ --}}
            @if($orders->hasPages())
                <div class="pagination">
                    <div class="pagination-info">
                        Showing <strong>{{ $orders->firstItem() }}</strong> to <strong>{{ $orders->lastItem() }}</strong> of
                        <strong>{{ $orders->total() }}</strong> results
                    </div>
                    <div class="pagination-links">
                        {{-- Previous Button --}}
                        @if ($orders->onFirstPage())
                            <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $orders->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left"></i></a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                            @if ($page == $orders->currentPage())
                                <span class="page-btn active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($orders->hasMorePages())
                            <a href="{{ $orders->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ============ MODALS (Complete & Cancel) ============ --}}
    <!-- Complete Order Modal -->
    <div class="modal-overlay" id="completeModal" onclick="if(event.target===this) closeCompleteModal()">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="color: #576238;"></i> Complete Order</h3>
                <button type="button" class="modal-close" onclick="closeCompleteModal()"><i
                        class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="" id="completeForm">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p style="margin-bottom: 1rem; color: #6B6A65;">Sigurado ka bang kumpleto na ang order na ito?</p>
                    <div class="cancel-order-info" style="background: #E8F0E3; border-color: #C5D4A8;">
                        <div class="cancel-info-item">
                            <span class="cancel-info-label">Order No.</span>
                            <span class="cancel-info-value" id="completeOrderId">—</span>
                        </div>
                        <div class="cancel-info-item">
                            <span class="cancel-info-label">Customer</span>
                            <span class="cancel-info-value" id="completeCustomerName">—</span>
                        </div>
                        <div class="cancel-info-item">
                            <span class="cancel-info-label">Total Amount</span>
                            <span class="cancel-info-value" style="color: #576238;" id="completeAmount">—</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeCompleteModal()">Cancel</button>
                    <button type="submit" class="btn-confirm-complete"><i class="fas fa-check"></i> Yes, Complete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal-overlay" id="cancelModal" onclick="if(event.target===this) closeCancelModal()">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #C5705A;"></i> Cancel Order</h3>
                <button type="button" class="modal-close" onclick="closeCancelModal()"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="" id="cancelForm">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="modal-warning">
                        <i class="fas fa-info-circle"></i>
                        <div>Ang pag-cancel ng order ay <strong>hindi na maibabalik</strong>. Ang stock ay
                            <strong>ibabalik</strong> sa inventory.
                        </div>
                    </div>
                    <div class="cancel-order-info">
                        <div class="cancel-info-item">
                            <span class="cancel-info-label">Order No.</span>
                            <span class="cancel-info-value" id="cancelOrderId">—</span>
                        </div>
                        <div class="cancel-info-item">
                            <span class="cancel-info-label">Customer</span>
                            <span class="cancel-info-value" id="cancelCustomerName">—</span>
                        </div>
                        <div class="cancel-info-item">
                            <span class="cancel-info-label">Total Amount</span>
                            <span class="cancel-info-value cancel-amount" id="cancelAmount">—</span>
                        </div>
                    </div>
                    <div class="modal-form-group">
                        <label>Reason for Cancellation <span class="required">*</span></label>
                        <textarea name="cancel_reason" id="cancel_reason" rows="3" required
                            placeholder="Example: Customer changed mind..." class="modal-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeCancelModal()">Cancel</button>
                    <button type="submit" class="btn-confirm-cancel"><i class="fas fa-times-circle"></i> Confirm
                        Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ========== CLICKABLE ROWS ==========
        document.querySelectorAll('.clickable-row').forEach(function (row) {
            row.addEventListener('click', function () {
                window.location.href = this.dataset.href;
            });
        });

        // Complete Modal Functions
        function openCompleteModal(orderId, customerName, amount) {
            document.getElementById('completeOrderId').textContent = '#' + String(orderId).padStart(5, '0');
            document.getElementById('completeCustomerName').textContent = customerName;
            document.getElementById('completeAmount').textContent = '₱' + amount;
            document.getElementById('completeForm').action = '{{ url("orders") }}/' + orderId + '/complete';
            document.getElementById('completeModal').classList.add('active');
        }
        function closeCompleteModal() {
            document.getElementById('completeModal').classList.remove('active');
        }

        // Cancel Modal Functions
        function openCancelModal(orderId, customerName, amount) {
            document.getElementById('cancelOrderId').textContent = '#' + String(orderId).padStart(5, '0');
            document.getElementById('cancelCustomerName').textContent = customerName;
            document.getElementById('cancelAmount').textContent = '₱' + amount;
            document.getElementById('cancelForm').action = '{{ url("orders") }}/' + orderId + '/cancel';
            document.getElementById('cancel_reason').value = '';
            document.getElementById('cancelModal').classList.add('active');
            setTimeout(() => document.getElementById('cancel_reason').focus(), 100);
        }
        function closeCancelModal() {
            document.getElementById('cancelModal').classList.remove('active');
        }

        // ESC Key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCompleteModal();
                closeCancelModal();
            }
        });

        // Handle alert dismiss
        document.querySelectorAll('[data-dismiss]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const target = document.getElementById(this.dataset.dismiss);
                if (target) target.remove();
            });
        });
    </script>

    <style>
        /* ============ CONTAINER ============ */
        .orders-container {
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
            border: 1px solid #E3DCD0;
            background: white;
            color: #6B6A65;
            cursor: pointer;
            font-family: inherit;
            line-height: 1;
        }

        .btn-secondary:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
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

        .alert-close {
            background: transparent;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: auto;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* ============ STATS GRID ============ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            border-color: #576238;
        }

        .stat-card.stat-active {
            border-color: #576238;
            background: #FDFBF7;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .stat-icon-total {
            background: #F0EADC;
            color: #576238;
        }

        .stat-icon-pending {
            background: #FEF5E8;
            color: #D4A054;
        }

        .stat-icon-completed {
            background: #E8F0E3;
            color: #576238;
        }

        .stat-icon-cancelled {
            background: #FEF0ED;
            color: #C5705A;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2C2B26;
            line-height: 1.2;
            font-family: 'Inter', sans-serif;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
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
            padding: 1.25rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-left h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-left h3 i {
            color: #576238;
        }

        .record-count {
            background: #F0EADC;
            color: #576238;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* ============ FILTER TABS ============ */
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            background: white;
            border-bottom: 1px solid #E3DCD0;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
        }

        .filter-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .filter-tab:hover {
            border-color: #576238;
            color: #576238;
        }

        .filter-tab.active {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .filter-tab .count {
            background: #F0EADC;
            color: #576238;
            padding: 0.1rem 0.45rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .filter-tab.active .count {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        /* ============ FILTER BAR ============ */
        .filter-bar {
            padding: 0.75rem 1.25rem;
            background: white;
            border-bottom: 1px solid #F0EADC;
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
            padding: 0.4rem 0.7rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.375rem;
            font-size: 0.72rem;
            background: white;
            color: #2C2B26;
            cursor: pointer;
            transition: border-color 0.2s ease;
            font-family: inherit;
        }

        .filter-select:hover {
            border-color: #576238;
        }

        .filter-select:focus {
            outline: none;
            border-color: #576238;
        }

        .btn-clear-filters {
            background: #FEF0ED;
            color: #C5705A;
            padding: 0.4rem 0.7rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .btn-clear-filters:hover {
            background: #C5705A;
            color: white;
        }

        /* ============ TABLE ============ */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            table-layout: fixed;
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

        /* ✅ Center alignment utility */
        .data-table th.text-center,
        .data-table td.text-center {
            text-align: center !important;
        }

        .data-table td {
            padding: 1rem 1rem;
            color: #2C2B26;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        /* ✅ Clickable Row Styles */
        .clickable-row {
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .clickable-row:hover {
            background: #FDFBF7;
        }

        /* ✅ Prevent text selection sa buong row kapag dina-drag */
        .clickable-row td {
            user-select: none;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ✅ Responsive Column Widths */
        .col-order {
            width: 12%;
        }

        .col-customer {
            width: 34%;
        }

        .col-amount {
            width: 14%;
        }

        .col-type {
            width: 14%;
        }

        .col-status {
            width: 14%;
        }

        .col-date {
            width: 12%;
        }

        /* ✅ Ginawang parang badge yung Order No. pero hindi na link */
        .order-link {
            color: #576238;
            font-weight: 600;
            font-family: 'SF Mono', monospace;
            font-size: 0.8rem;
            background: #F0EADC;
            padding: 0.3rem 0.6rem;
            border-radius: 0.375rem;
            display: inline-block;
        }

        .customer-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .customer-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7A8B4F, #576238);
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .customer-text {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
            min-width: 0;
        }

        .customer-name {
            font-weight: 600;
            color: #2C2B26;
            display: block;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .customer-phone {
            font-size: 0.7rem;
            color: #9E9D97;
            display: block;
        }

        .amount {
            font-weight: 700;
            color: #2C2B26;
            font-size: 0.9rem;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
            white-space: nowrap;
            min-width: 90px;
        }

        .type-pickup {
            background: #F0EADC;
            color: #576238;
        }

        .type-delivery {
            background: #E3EDF5;
            color: #3E6B8C;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
            min-width: 100px;
        }

        .status-completed {
            background: #E8F0E3;
            color: #576238;
        }

        .status-pending {
            background: #FEF5E8;
            color: #D4A054;
        }

        .status-cancelled {
            background: #FEF0ED;
            color: #C5705A;
        }

        .status-refunded {
            background: #F0EADC;
            color: #8B7A6B;
        }

        .date-primary {
            display: block;
            font-size: 0.78rem;
            color: #2C2B26;
            font-weight: 500;
        }

        .date-time {
            display: block;
            font-size: 0.68rem;
            color: #9E9D97;
            margin-top: 0.1rem;
        }

        /* ============ EMPTY STATE ============ */
        .empty-row td {
            padding: 0 !important;
        }

        .empty-state {
            text-align: center;
            padding: 3.5rem 2rem;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #F0EADC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .empty-icon i {
            font-size: 2rem;
            color: #C4C3BC;
        }

        .empty-state p {
            color: #6B6A65;
            margin-bottom: 0.35rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .link-clear {
            color: #576238;
            text-decoration: underline;
            font-size: 0.8rem;
        }

        /* ============ CUSTOM PAGINATION ============ */
        .pagination {
            padding: 1.25rem;
            border-top: 1px solid #E3DCD0;
            background: #FDF8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .pagination-info {
            font-size: 0.8rem;
            color: #6B6A65;
        }

        .pagination-info strong {
            color: #2C2B26;
            font-weight: 600;
        }

        .pagination-links {
            display: flex;
            gap: 0.375rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #576238;
            background: white;
            border: 1px solid #E3DCD0;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .page-btn:hover:not(.disabled) {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(87, 98, 56, 0.1);
        }

        .page-btn.active {
            background: #576238;
            color: white;
            border-color: #576238;
            font-weight: 700;
            cursor: default;
            box-shadow: 0 2px 8px rgba(87, 98, 56, 0.25);
        }

        .page-btn.disabled {
            background: #F0EADC;
            color: #C4C3BC;
            border-color: #E3DCD0;
            cursor: not-allowed;
            opacity: 0.6;
            pointer-events: none;
        }

        /* ============ MODAL ============ */
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
            max-width: 480px;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
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

        .modal-warning {
            background: #FEF0ED;
            border: 1px solid #F8DCD4;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            color: #A85444;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
        }

        .modal-warning i {
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .modal-warning strong {
            color: #C5705A;
        }

        .cancel-order-info {
            background: #FDF8F0;
            border: 1px solid #F0EADC;
            border-radius: 0.5rem;
            padding: 0.875rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .cancel-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .cancel-info-label {
            font-size: 0.7rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .cancel-info-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #2C2B26;
            font-family: 'Inter', sans-serif;
        }

        .cancel-info-value.cancel-amount {
            color: #C5705A;
            font-size: 0.95rem;
        }

        .modal-form-group {
            margin-top: 1rem;
        }

        .modal-form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #2C2B26;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .required {
            color: #C5705A;
        }

        .modal-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: inherit;
            color: #2C2B26;
            background: white;
            transition: all 0.2s ease;
            box-sizing: border-box;
            min-height: 80px;
            resize: vertical;
        }

        .modal-textarea:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 4px rgba(87, 98, 56, 0.1);
        }

        .modal-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #E3DCD0;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            background: #FDF8F0;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
            padding: 0.6rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
        }

        .btn-confirm-complete {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #576238;
            color: white;
            border: none;
            padding: 0.6rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .btn-confirm-complete:hover {
            background: #3E4A28;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.3);
        }

        .btn-confirm-cancel {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #C5705A;
            color: white;
            border: none;
            padding: 0.6rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .btn-confirm-cancel:hover {
            background: #A85444;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(197, 112, 90, 0.3);
        }
    </style>
@endsection