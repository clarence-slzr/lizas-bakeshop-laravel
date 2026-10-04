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
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" onclick="document.getElementById('flashAlert').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" id="flashError">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close" onclick="document.getElementById('flashError').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info" id="flashInfo">
                <i class="fas fa-info-circle"></i>
                <span>{{ session('info') }}</span>
                <button type="button" class="alert-close" onclick="document.getElementById('flashInfo').remove()">
                    <i class="fas fa-times"></i>
                </button>
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

            {{-- Filters Bar --}}
            <div class="filters-bar">
                <form method="GET" action="{{ route('orders.index') }}" class="search-form">
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                    @if(request('date'))<input type="hidden" name="date" value="{{ request('date') }}">@endif
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" placeholder="Search by order no., customer, or phone..."
                            value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('orders.index', array_merge(request()->except('search'))) }}" class="clear-search"
                                title="Clear">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <div class="filter-buttons">
                    <a href="{{ route('orders.index', array_merge(request()->except('status', 'page'))) }}"
                        class="filter-btn {{ !request('status') ? 'active' : '' }}">
                        All
                    </a>
                    <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
                        class="filter-btn {{ request('status') === 'pending' ? 'active' : '' }}">
                        Pending
                    </a>
                    <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'completed'])) }}"
                        class="filter-btn {{ request('status') === 'completed' ? 'active' : '' }}">
                        Completed
                    </a>
                    <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'cancelled'])) }}"
                        class="filter-btn {{ request('status') === 'cancelled' ? 'active' : '' }}">
                        Cancelled
                    </a>
                    <a href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => 'refunded'])) }}"
                        class="filter-btn {{ request('status') === 'refunded' ? 'active' : '' }}">
                        Refunded
                    </a>
                </div>

                <div class="date-filter">
                    <select name="date" onchange="location.href=this.value" class="filter-select">
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('date', 'page'), ['date' => null])) }}"
                            {{ !request('date') ? 'selected' : '' }}>All Time</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'today'])) }}"
                            {{ request('date') === 'today' ? 'selected' : '' }}>Today</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'week'])) }}"
                            {{ request('date') === 'week' ? 'selected' : '' }}>This Week</option>
                        <option
                            value="{{ route('orders.index', array_merge(request()->except('page'), ['date' => 'month'])) }}"
                            {{ request('date') === 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-order">Order No.</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-amount">Total Amount</th>
                            <th class="col-type">Order Type</th>
                            <th class="col-status">Status</th>
                            <th class="col-date">Date</th>
                            <th class="col-actions">Actions</th>
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
                                    'pending' => 'fa-hourglass-half',
                                    'completed' => 'fa-check-circle',
                                    'cancelled' => 'fa-times-circle',
                                    'refunded' => 'fa-rotate-left',
                                    default => 'fa-hourglass-half',
                                };
                            @endphp
                            <tr>
                                <td class="col-order">
                                    <a href="{{ route('orders.show', $order->id) }}" class="order-link">
                                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
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
                                <td class="col-amount">
                                    <span class="amount">₱{{ number_format($order->total_amount, 2) }}</span>
                                </td>
                                <td class="col-type">
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
                                <td class="col-status">
                                    <span class="status-badge {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }}"></i>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="col-date">
                                    <div class="date-cell">
                                        <span
                                            class="date">{{ $order->order_date ? $order->order_date->format('M d, Y') : 'N/A' }}</span>
                                        <span
                                            class="time">{{ $order->order_date ? $order->order_date->format('h:i A') : '' }}</span>
                                    </div>
                                </td>
                                <td class="col-actions">
                                    <div class="action-buttons">
                                        <a href="{{ route('orders.show', $order->id) }}" class="action-btn action-view"
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($status === 'pending')
                                            <button type="button" class="action-btn action-cancel"
                                                onclick="openCancelModal({{ $order->id }}, '{{ addslashes($order->customer_name) }}', '{{ number_format($order->total_amount, 2) }}')"
                                                title="Cancel Order">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <p>
                                            @if(request('status') === 'cancelled')
                                                No cancelled orders
                                            @elseif(request('status') === 'pending')
                                                No pending orders
                                            @elseif(request('status') === 'completed')
                                                No completed orders
                                            @elseif(request('status') === 'refunded')
                                                No refunded orders
                                            @elseif(request('search'))
                                                No results for "{{ request('search') }}"
                                            @else
                                                No orders yet
                                            @endif
                                        </p>
                                        @if(request()->hasAny(['status', 'search', 'date']))
                                            <a href="{{ route('orders.index') }}" class="btn-clear-filter">
                                                <i class="fas fa-times"></i> Clear Filters
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============ PROFESSIONAL PAGINATION ============ --}}
            @if($orders->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <i class="fas fa-list-ul"></i>
                        Showing <strong>{{ $orders->firstItem() }}-{{ $orders->lastItem() }}</strong> of
                        <strong>{{ $orders->total() }}</strong> orders
                    </div>

                    <nav class="custom-pagination" role="navigation" aria-label="Pagination Navigation">
                        <ul class="pagination-list">
                            {{-- Previous --}}
                            @if ($orders->onFirstPage())
                                <li class="pagination-item disabled">
                                    <span class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="pagination-item">
                                    <a href="{{ $orders->previousPageUrl() }}" class="pagination-link" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach ($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                                @if ($page == $orders->currentPage())
                                    <li class="pagination-item active">
                                        <span class="pagination-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if ($orders->hasMorePages())
                                <li class="pagination-item">
                                    <a href="{{ $orders->nextPageUrl() }}" class="pagination-link" rel="next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="pagination-item disabled">
                                    <span class="pagination-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif

            {{-- ============================================ --}}
            {{-- DYNAMIC FOOTER — Base sa Filter --}}
            {{-- ============================================ --}}
            @php
                $currentStatus = request('status');
                $currentDate = request('date');
                $hasFilter = $currentStatus || $currentDate || request('search');
            @endphp

            <div class="card-footer">
                @if(!$hasFilter)
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-peso-sign"></i> Total Revenue:
                        </span>
                        <span class="footer-value">₱{{ number_format($stats['revenue'] ?? 0, 2) }}</span>
                    </div>
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-calculator"></i> Average Order:
                        </span>
                        <span
                            class="footer-value">₱{{ number_format(($stats['revenue'] ?? 0) / max($stats['completed'] ?? 1, 1), 2) }}</span>
                    </div>

                @elseif($currentStatus === 'completed')
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-check-circle" style="color: #576238;"></i> Completed Revenue:
                        </span>
                        <span class="footer-value">₱{{ number_format($stats['revenue'] ?? 0, 2) }}</span>
                    </div>

                @elseif($currentStatus === 'pending')
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-hourglass-half" style="color: #D4A054;"></i> Pending Orders:
                        </span>
                        <span class="footer-value">{{ $stats['pending'] ?? 0 }} orders</span>
                    </div>

                @elseif($currentStatus === 'cancelled')
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-times-circle" style="color: #C5705A;"></i> Cancelled Orders:
                        </span>
                        <span class="footer-value">{{ $stats['cancelled'] ?? 0 }} orders</span>
                    </div>

                @elseif($currentStatus === 'refunded')
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-rotate-left" style="color: #8B7A6B;"></i> Refunded Orders:
                        </span>
                        <span class="footer-value">{{ $stats['refunded'] ?? 0 }} orders</span>
                    </div>

                @elseif($currentDate)
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-calendar"></i> Filtered Orders:
                        </span>
                        <span class="footer-value">{{ $orders->total() }} orders</span>
                    </div>

                @elseif(request('search'))
                    <div class="footer-stat">
                        <span class="footer-label">
                            <i class="fas fa-search"></i> Search Results:
                        </span>
                        <span class="footer-value">{{ $orders->total() }} orders</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============ CANCEL ORDER MODAL ============ --}}
    <div class="modal-overlay" id="cancelModal" onclick="if(event.target===this) closeCancelModal()">
        <div class="modal-box">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-exclamation-triangle" style="color: #C5705A;"></i>
                    Cancel Order
                </h3>
                <button type="button" class="modal-close" onclick="closeCancelModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" action="" id="cancelForm">
                @csrf
                @method('PATCH')

                <div class="modal-body">
                    <div class="modal-warning">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            Ang pag-cancel ng order ay <strong>hindi na maibabalik</strong>. Ang stock ng mga item ay
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
                        <label for="cancel_reason">
                            Reason for Cancellation <span class="required">*</span>
                        </label>
                        <textarea name="cancel_reason" id="cancel_reason" rows="3" required
                            placeholder="Example: Customer changed mind, duplicate order, out of stock..."
                            class="modal-textarea"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeCancelModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-confirm-cancel">
                        <i class="fas fa-times-circle"></i> Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(orderId, customerName, amount) {
            document.getElementById('cancelOrderId').textContent = '#' + String(orderId).padStart(5, '0');
            document.getElementById('cancelCustomerName').textContent = customerName;
            document.getElementById('cancelAmount').textContent = '₱' + amount;

            const form = document.getElementById('cancelForm');
            form.action = '{{ url("orders") }}/' + orderId + '/cancel';

            document.getElementById('cancel_reason').value = '';
            document.getElementById('cancelModal').classList.add('active');

            setTimeout(() => document.getElementById('cancel_reason').focus(), 100);
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.remove('active');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCancelModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            ['flashAlert', 'flashError', 'flashInfo'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    setTimeout(() => {
                        el.style.transition = 'opacity 0.5s ease';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 500);
                    }, 5000);
                }
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
            font-size: 1.75rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0 0 0.25rem 0;
        }

        .page-description {
            font-size: 0.85rem;
            color: #9E9D97;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* ============ BUTTONS ============ */
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
            border: none;
            cursor: pointer;
            font-family: inherit;
            line-height: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7A8B4F, #576238);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
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
            animation: slideDown 0.3s ease;
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

        .alert-info {
            background: #E3EDF5;
            border: 1px solid #3E6B8C;
            color: #3E6B8C;
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
            position: relative;
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
            padding: 1.25rem;
            border-bottom: 1px solid #F0EADC;
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

        /* ============ FILTERS BAR ============ */
        .filters-bar {
            padding: 1rem 1.25rem;
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-form {
            flex: 1;
            min-width: 280px;
            max-width: 400px;
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
            font-size: 0.85rem;
            pointer-events: none;
        }

        .search-wrapper input {
            width: 100%;
            padding: 0.55rem 4rem 0.55rem 2.5rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
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
            right: 3rem;
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
            width: 32px;
            height: 32px;
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

        .filter-buttons {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.875rem;
            border: 1px solid #E3DCD0;
            background: white;
            color: #6B6A65;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            line-height: 1;
        }

        .filter-btn:hover {
            border-color: #576238;
            color: #576238;
        }

        .filter-btn.active {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .date-filter {
            margin-left: auto;
        }

        .filter-select {
            padding: 0.45rem 0.875rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-family: inherit;
            background: white;
            cursor: pointer;
            color: #2C2B26;
        }

        .filter-select:focus {
            outline: none;
            border-color: #576238;
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
            min-width: 900px;
        }

        .data-table thead tr {
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
        }

        .data-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #576238;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .data-table td {
            padding: 1rem;
            color: #2C2B26;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        .data-table tbody tr {
            transition: background 0.15s ease;
        }

        .data-table tbody tr:hover {
            background: #FDFBF7;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .col-order {
            width: 110px;
        }

        .col-customer {
            width: auto;
            min-width: 180px;
        }

        .col-amount {
            width: 130px;
        }

        .col-type {
            width: 120px;
        }

        .col-status {
            width: 130px;
        }

        .col-date {
            width: 140px;
        }

        .col-actions {
            width: 100px;
            text-align: center;
        }

        .order-link {
            color: #576238;
            font-weight: 600;
            text-decoration: none;
            font-family: 'SF Mono', 'Monaco', monospace;
            font-size: 0.8rem;
            background: #F0EADC;
            padding: 0.3rem 0.6rem;
            border-radius: 0.375rem;
            display: inline-block;
            transition: all 0.2s;
        }

        .order-link:hover {
            background: #576238;
            color: white;
        }

        .customer-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .customer-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #7A8B4F, #576238);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .customer-text {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .customer-name {
            display: block;
            font-weight: 500;
            color: #2C2B26;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .customer-phone {
            display: block;
            font-size: 0.7rem;
            color: #9E9D97;
        }

        .amount {
            font-weight: 700;
            color: #2C2B26;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
            white-space: nowrap;
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
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
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

        .date-cell {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .date-cell .date {
            font-weight: 500;
            color: #2C2B26;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .date-cell .time {
            font-size: 0.7rem;
            color: #9E9D97;
        }

        .action-buttons {
            display: flex;
            gap: 0.35rem;
            justify-content: center;
            align-items: center;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 0.5rem;
            border: 1px solid #E3DCD0;
            background: white;
            color: #6B6A65;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            cursor: pointer;
            font-family: inherit;
            padding: 0;
        }

        .action-view:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .action-cancel {
            color: #C5705A;
            border-color: #F8DCD4;
        }

        .action-cancel:hover {
            background: #C5705A;
            color: white;
            border-color: #C5705A;
        }

        /* ============ EMPTY STATE (FIXED) ============ */
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
            margin-bottom: 0.25rem;
        }

        .empty-icon i {
            font-size: 1.5rem;
            color: #C4C3BC;
        }

        .empty-state p {
            color: #9E9D97;
            font-size: 0.85rem;
            margin: 0;
            line-height: 1.5;
        }

        .btn-clear-filter {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #FEF0ED;
            color: #C5705A;
            border: 1px solid #F8DCD4;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            font-family: inherit;
            line-height: 1;
        }

        .btn-clear-filter:hover {
            background: #C5705A;
            color: white;
            border-color: #C5705A;
            transform: translateY(-1px);
        }

        .btn-clear-filter i {
            font-size: 0.7rem;
        }

        /* ============ PAGINATION (PROFESSIONAL) ============ */
        .pagination-wrapper {
            padding: 1.25rem 1.5rem;
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

        /* ============ CARD FOOTER ============ */
        .card-footer {
            padding: 1rem 1.25rem;
            background: #FDF8F0;
            border-top: 1px solid #E3DCD0;
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .footer-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .footer-label {
            color: #6B6A65;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .footer-label i {
            font-size: 0.75rem;
        }

        .footer-value {
            color: #576238;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
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
            resize: vertical;
            transition: all 0.2s ease;
            min-height: 80px;
            box-sizing: border-box;
        }

        .modal-textarea:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 4px rgba(87, 98, 56, 0.1);
        }

        .modal-textarea::placeholder {
            color: #C4C3BC;
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

        @media (max-width: 768px) {
            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form {
                max-width: 100%;
                min-width: 0;
            }

            .date-filter {
                margin-left: 0;
            }

            .filter-buttons {
                justify-content: flex-start;
            }

            .card-footer {
                justify-content: space-between;
                gap: 1rem;
            }

            .pagination-wrapper {
                flex-direction: column;
                align-items: stretch;
            }

            .custom-pagination {
                display: flex;
                justify-content: center;
            }
        }
    </style>
@endsection