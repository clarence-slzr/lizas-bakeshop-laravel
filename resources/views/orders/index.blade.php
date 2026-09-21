@extends('layouts.app')

@section('content')
    @php
        function buildURL($overrides = [])
        {
            $params = array_merge($_GET, $overrides);
            $params = array_filter($params, function ($v) {
                return $v !== null && $v !== '';
            });
            return 'orders?' . http_build_query($params);
        }
    @endphp

    <div class="orders-container">

        <!-- ========== PAGE HEADER ========== -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Orders Management</h1>
                <p class="page-description">View and manage all customer orders</p>
            </div>
        </div>

        <!-- ========== STATS CARDS ========== -->
        <div class="stats-row">
            <div class="stat-mini-card">
                <div class="stat-mini-icon"><i class="fas fa-receipt"></i></div>
                <div class="stat-mini-info">
                    <span class="stat-mini-value">{{ number_format(\App\Models\Order::count()) }}</span>
                    <span class="stat-mini-label">Total Orders</span>
                </div>
            </div>
            <div class="stat-mini-card warning">
                <div class="stat-mini-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-mini-info">
                    <span
                        class="stat-mini-value">{{ number_format(\App\Models\Order::where('status', 'pending')->count()) }}</span>
                    <span class="stat-mini-label">Pending</span>
                </div>
            </div>
            <div class="stat-mini-card success">
                <div class="stat-mini-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-mini-info">
                    <span
                        class="stat-mini-value">{{ number_format(\App\Models\Order::where('status', 'completed')->count()) }}</span>
                    <span class="stat-mini-label">Completed</span>
                </div>
            </div>
            <div class="stat-mini-card revenue">
                <div class="stat-mini-icon"><i class="fas fa-peso-sign"></i></div>
                <div class="stat-mini-info">
                    <span
                        class="stat-mini-value">₱{{ number_format(\App\Models\Order::where('status', 'completed')->sum('total_amount'), 2) }}</span>
                    <span class="stat-mini-label">Total Revenue</span>
                </div>
            </div>
        </div>

        <!-- ========== FLASH MESSAGES ========== -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif

        <!-- ========== ORDER TABLE CARD ========== -->
        <div class="data-card">
            <div class="card-header">
                <div class="header-left">
                    <h3><i class="fas fa-table"></i> All Orders</h3>
                    <span class="record-count">{{ $orders->total() }} orders</span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-order-no">Order No.</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-amount">Total Amount</th>
                            <th class="col-type">Order Type</th>
                            <th class="col-status">Status</th>
                            <th class="col-date">Date</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($orders->count() > 0)
                            @foreach($orders as $order)
                                <tr>
                                    <td class="col-order-no">
                                        <span class="order-number">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="col-customer">
                                        <span class="customer-name">{{ ucwords($order->customer_name) }}</span>
                                        @if(!empty($order->contact_number))
                                            <small class="customer-contact">{{ $order->contact_number }}</small>
                                        @endif
                                    </td>
                                    <td class="col-amount">
                                        <span class="amount-value">₱{{ number_format($order->total_amount, 2) }}</span>
                                    </td>
                                    <td class="col-type">
                                        <span class="type-badge type-{{ $order->order_type ?? 'pickup' }}">
                                            <i
                                                class="fas {{ ($order->order_type ?? 'pickup') == 'pickup' ? 'fa-store' : 'fa-truck' }}"></i>
                                            {{ ($order->order_type ?? 'pickup') == 'pickup' ? 'Pick-up' : 'Delivery' }}
                                        </span>
                                    </td>
                                    <td class="col-status">
                                        <span class="status-badge status-{{ $order->status }}">
                                            <i
                                                class="fas {{ $order->status == 'pending' ? 'fa-clock' : ($order->status == 'completed' ? 'fa-check-circle' : 'fa-times-circle') }}"></i>
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="col-date">
                                        <span class="date-primary">{{ $order->order_date->format('M d, Y') }}</span>
                                        <span class="date-time">{{ $order->order_date->format('h:i A') }}</span>
                                    </td>
                                    <td class="col-actions">
                                        <div class="action-group">
                                            {{-- VIEW button --}}
                                            <a href="{{ route('orders.show', $order->id) }}" class="action-btn action-view"
                                                title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($order->status === 'pending')
                                                {{-- COMPLETE button --}}
                                                <form method="POST" action="{{ route('orders.complete', $order->id) }}"
                                                    class="action-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="action-btn action-complete" title="Mark as Complete"
                                                        onclick="return confirm('Mark order #{{ $order->id }} as COMPLETED?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>

                                                {{-- CANCEL button --}}
                                                <form method="POST" action="{{ route('orders.cancel', $order->id) }}"
                                                    class="action-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="action-btn action-cancel" title="Cancel Order"
                                                        onclick="return confirm('Cancel order #{{ $order->id }}? Stock will be restored.')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="empty-row">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>No orders found</p>
                                        <small>Orders will appear here when customers place them</small>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($orders->hasPages())
                <div class="pagination">
                    <div class="pagination-info">
                        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                    </div>
                    <div class="pagination-links">
                        {{ $orders->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .orders-container {
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

        /* ============ ALERTS ============ */
        .alert {
            padding: 0.875rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #E8F0E3;
            color: #3E4A28;
            border: 1px solid #C5D4A8;
        }

        .alert-error {
            background: #FEF0ED;
            color: #A85444;
            border: 1px solid #E8C5BD;
        }

        .alert-info {
            background: #FEF5E8;
            color: #B8893A;
            border: 1px solid #E8D5A8;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 900px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        .stat-mini-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }

        .stat-mini-card:hover {
            transform: translateY(-2px);
            border-color: #576238;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.08);
        }

        .stat-mini-icon {
            width: 48px;
            height: 48px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .stat-mini-icon i {
            color: #576238;
        }

        .stat-mini-card.warning .stat-mini-icon {
            background: #FEF5E8;
        }

        .stat-mini-card.warning .stat-mini-icon i {
            color: #D4A054;
        }

        .stat-mini-card.warning .stat-mini-value {
            color: #D4A054;
        }

        .stat-mini-card.success .stat-mini-icon {
            background: #E8F0E3;
        }

        .stat-mini-card.success .stat-mini-icon i {
            color: #576238;
        }

        .stat-mini-card.success .stat-mini-value {
            color: #576238;
        }

        .stat-mini-info {
            flex: 1;
        }

        .stat-mini-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2C2B26;
            display: block;
            line-height: 1.3;
        }

        .stat-mini-label {
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

        .header-left h3 i {
            color: #576238;
            margin-right: 0.5rem;
        }

        .record-count {
            background: #F0EADC;
            color: #576238;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
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

        .col-order-no {
            width: 110px;
        }

        .col-customer {
            width: 180px;
        }

        .col-amount {
            width: 130px;
        }

        .col-type {
            width: 110px;
        }

        .col-status {
            width: 120px;
        }

        .col-date {
            width: 130px;
        }

        .col-actions {
            width: 160px;
        }

        .order-number {
            font-weight: 600;
            color: #576238;
            font-family: monospace;
            font-size: 0.85rem;
        }

        .customer-name {
            font-weight: 500;
            color: #2C2B26;
            display: block;
        }

        .customer-contact {
            font-size: 0.65rem;
            color: #9E9D97;
            display: block;
            margin-top: 0.125rem;
        }

        .amount-value {
            font-weight: 600;
            color: #576238;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .type-pickup,
        .type-delivery {
            background: #F0EADC;
            color: #576238;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-pending {
            background: #FEF5E8;
            color: #D4A054;
        }

        .status-completed {
            background: #E8F0E3;
            color: #576238;
        }

        .status-cancelled,
        .status-refunded {
            background: #FEF0ED;
            color: #C5705A;
        }

        .date-primary {
            display: block;
            font-size: 0.75rem;
            color: #2C2B26;
            font-weight: 500;
        }

        .date-time {
            display: block;
            font-size: 0.65rem;
            color: #9E9D97;
        }

        /* ============ ACTION BUTTONS ============ */
        .action-group {
            display: flex;
            gap: 0.375rem;
            align-items: center;
        }

        .action-form {
            display: inline;
            margin: 0;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            width: 32px;
            height: 32px;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
            background: transparent;
            padding: 0;
        }

        .action-view {
            background: #F0EADC;
            color: #576238;
            border-color: #E3DCD0;
        }

        .action-view:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .action-complete {
            background: #E8F0E3;
            color: #576238;
            border-color: #C5D4A8;
        }

        .action-complete:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .action-cancel {
            background: #FEF0ED;
            color: #C5705A;
            border-color: #E8C5BD;
        }

        .action-cancel:hover {
            background: #C5705A;
            color: white;
            border-color: #C5705A;
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
            margin-bottom: 0.5rem;
        }

        .empty-state small {
            color: #C4C3BC;
            font-size: 0.75rem;
        }

        .pagination {
            padding: 1rem 1.25rem;
            border-top: 1px solid #E3DCD0;
            background: #FDF8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .pagination-info {
            font-size: 0.75rem;
            color: #9E9D97;
        }

        .pagination-links {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
    </style>
@endsection