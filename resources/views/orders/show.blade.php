@extends('layouts.app')

@section('content')
    @php
        use App\Models\Order;

        $page_title = 'Order Details';
        $hide_page_title = true;

        // $order is passed from controller
    @endphp

    <div class="order-details-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <a href="{{ route('orders.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                <h1>Order Details</h1>
                <p class="page-description">View complete order information</p>
            </div>
            <div class="order-number">
                <span class="order-badge">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>

        <!-- Order Information Cards -->
        <div class="info-grid">
            <!-- Customer Information Card -->
            <div class="info-card">
                <div class="card-title">
                    <i class="fas fa-user"></i>
                    <h3>Customer Information</h3>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value">{{ ucwords($order->customer_name) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact Number:</span>
                        <span class="info-value">{{ $order->contact_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Type:</span>
                        <span class="info-value">
                            <span class="type-badge type-{{ $order->order_type ?? 'pickup' }}">
                                <i
                                    class="fas {{ ($order->order_type ?? 'pickup') == 'pickup' ? 'fa-store' : 'fa-truck' }}"></i>
                                {{ ucfirst($order->order_type ?? 'Pick-up') }}
                            </span>
                        </span>
                    </div>
                    @if(($order->order_type ?? 'pickup') == 'delivery' && !empty($order->delivery_address))
                        <div class="info-row">
                            <span class="info-label">Delivery Address:</span>
                            <span class="info-value">{!! nl2br(e($order->delivery_address)) !!}</span>
                        </div>
                    @endif
                    @if(!empty($order->landmark))
                        <div class="info-row">
                            <span class="info-label">Landmark:</span>
                            <span class="info-value">{{ $order->landmark }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Information Card -->
            <div class="info-card">
                <div class="card-title">
                    <i class="fas fa-receipt"></i>
                    <h3>Order Information</h3>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value">{{ $order->order_date->format('F j, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Time:</span>
                        <span class="info-value">{{ $order->order_date->format('g:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $order->status }}">
                                <i
                                    class="fas {{ $order->status == 'pending' ? 'fa-clock' : ($order->status == 'completed' ? 'fa-check-circle' : 'fa-times-circle') }}"></i>
                                {{ ucfirst($order->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">Cash</span>
                    </div>
                    @if($order->status == 'completed')
                        <div class="info-row">
                            <span class="info-label">Completed Date:</span>
                            <span
                                class="info-value">{{ $order->updated_at ? $order->updated_at->format('F j, Y g:i A') : $order->order_date->format('F j, Y g:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PAYMENT INFORMATION CARD -->
            <div class="info-card">
                <div class="card-title">
                    <i class="fa-solid fa-money-check"></i>
                    <h3>Payment Information</h3>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <span class="info-label">Subtotal:</span>
                        <span class="info-value">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Discount:</span>
                        <span class="info-value">₱{{ number_format($order->discount ?? 0, 2) }}</span>
                    </div>
                    <div class="info-row total-row">
                        <span class="info-label">Grand Total:</span>
                        <span class="info-value grand-total">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Table -->
        <div class="data-card">
            <div class="card-header">
                <div class="header-left">
                    <h3><i class="fas fa-shopping-cart"></i> Order Items</h3>
                    <span class="record-count">{{ $order->items->count() }} items</span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-product">Product Name</th>
                            <th class="col-qty">Quantity</th>
                            <th class="col-price">Unit Price</th>
                            <th class="col-subtotal">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($order->items->count() > 0)
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="col-product">
                                        <span class="product-name">{{ $item->product->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="col-qty">
                                        <span class="qty-value">{{ number_format($item->quantity) }}</span>
                                        <span class="qty-unit">pcs</span>
                                    </td>
                                    <td class="col-price">
                                        <span class="price-value">
                                            @if($item->quantity > 0)
                                                ₱{{ number_format($item->subtotal / $item->quantity, 2) }}
                                            @else
                                                ₱0.00
                                            @endif
                                        </span>
                                    </td>
                                    <td class="col-subtotal">
                                        <span class="subtotal-value">₱{{ number_format($item->subtotal, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="empty-row">
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <p>No items found for this order</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="footer-row">
                            <td colspan="3" class="total-label">Grand Total</td>
                            <td class="total-value">₱{{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Action Buttons (if pending) -->
        @if($order->status == 'pending' && auth()->user()->isAdmin())
            <div class="action-buttons">
                <form method="POST" action="{{ route('orders.update', $order->id) }}" style="display:inline;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-complete" onclick="return confirm('Mark this order as completed?')">
                        <i class="fas fa-check-circle"></i> Mark as Completed
                    </button>
                </form>
                <a href="{{ route('orders.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        @endif
    </div>

    <style>
        .order-details-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .page-header-left {
            flex: 1;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #576238;
            text-decoration: none;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .back-link:hover {
            color: #3E4A28;
            transform: translateX(-2px);
        }

        .page-header h1 {
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

        .order-number {
            background: #FDF8F0;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .order-badge {
            font-size: 1.1rem;
            font-weight: 700;
            color: #576238;
            font-family: monospace;
            letter-spacing: 1px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 1024px) {
            .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .card-title {
            background: #FDF8F0;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            color: #576238;
            font-size: 1rem;
        }

        .card-title h3 {
            font-size: 0.85rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .card-content {
            padding: 1rem;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            width: 120px;
            font-weight: 500;
            color: #9E9D97;
            flex-shrink: 0;
        }

        .info-value {
            flex: 1;
            color: #2C2B26;
        }

        .total-row {
            border-top: 1px solid #F0EADC;
            margin-top: 0.5rem;
            padding-top: 0.75rem;
        }

        .grand-total {
            font-weight: 700;
            font-size: 1rem;
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

        .footer-row {
            background: #FDF8F0;
            border-top: 1px solid #E3DCD0;
        }

        .footer-row td {
            padding: 0.875rem 1rem;
            font-weight: 600;
        }

        .total-label {
            text-align: right;
            font-size: 0.85rem;
            color: #2C2B26;
        }

        .total-value {
            font-weight: 700;
            font-size: 1rem;
            color: #576238;
        }

        .col-product {
            width: auto;
        }

        .col-qty {
            width: 100px;
        }

        .col-price {
            width: 120px;
        }

        .col-subtotal {
            width: 130px;
        }

        .product-name {
            font-weight: 500;
            color: #2C2B26;
        }

        .qty-value {
            font-weight: 600;
            color: #576238;
        }

        .qty-unit {
            font-size: 0.65rem;
            color: #9E9D97;
            margin-left: 0.25rem;
        }

        .price-value {
            font-weight: 500;
            color: #2C2B26;
        }

        .subtotal-value {
            font-weight: 600;
            color: #576238;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }

        .btn-complete {
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
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-complete:hover {
            background: #3E4A28;
            transform: translateY(-1px);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: #6B6A65;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #E3DCD0;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
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
        }
    </style>
@endsection