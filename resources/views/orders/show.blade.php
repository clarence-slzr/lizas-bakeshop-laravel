@extends('layouts.app')

@section('content')
    <div class="order-detail-container">

        {{-- ============================================ --}}
        {{-- FLASH MESSAGES --}}
        {{-- ============================================ --}}
        @if(session('success'))
            <div class="flash-message flash-success" id="flashSuccess">
                <div class="flash-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="flash-content">
                    <div class="flash-title">Success!</div>
                    <div class="flash-text">{{ session('success') }}</div>
                </div>
                <button type="button" class="flash-close" onclick="document.getElementById('flashSuccess').remove()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="flash-progress"></div>
            </div>
        @endif

        @if(session('error'))
            <div class="flash-message flash-error" id="flashError">
                <div class="flash-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="flash-content">
                    <div class="flash-title">Error!</div>
                    <div class="flash-text">{{ session('error') }}</div>
                </div>
                <button type="button" class="flash-close" onclick="document.getElementById('flashError').remove()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="flash-progress"></div>
            </div>
        @endif

        @if(session('info'))
            <div class="flash-message flash-info" id="flashInfo">
                <div class="flash-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="flash-content">
                    <div class="flash-title">Info</div>
                    <div class="flash-text">{{ session('info') }}</div>
                </div>
                <button type="button" class="flash-close" onclick="document.getElementById('flashInfo').remove()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="flash-progress"></div>
            </div>
        @endif

        {{-- ============================================ --}}
        {{-- BACK BUTTON --}}
        {{-- ============================================ --}}
        <div class="page-nav">
            <a href="{{ route('orders.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>

        {{-- ============================================ --}}
        {{-- ORDER HEADER --}}
        {{-- ============================================ --}}
        <div class="order-header">
            <div class="order-header-left">
                <h1>Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h1>
                <p>Placed on {{ $order->order_date->format('F j, Y \a\t g:i A') }}</p>
            </div>
            <div class="order-status">
                @php
                    $statusClass = match ($order->status) {
                        'pending' => 'status-pending',
                        'completed' => 'status-completed',
                        'cancelled' => 'status-cancelled',
                        'refunded' => 'status-refunded',
                        default => 'status-pending',
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">
                    <i class="fas fa-circle"></i> {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- ORDER INFO GRID --}}
        {{-- ============================================ --}}
        <div class="order-info-grid">
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-user"></i></div>
                <div class="info-content">
                    <span class="info-label">Customer</span>
                    <span class="info-value">{{ ucwords($order->customer_name) }}</span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <div class="info-content">
                    <span class="info-label">Contact</span>
                    <span class="info-value">{{ $order->contact_number ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon"><i class="fas fa-truck"></i></div>
                <div class="info-content">
                    <span class="info-label">Order Type</span>
                    <span class="info-value">{{ ucfirst($order->order_type ?? 'Pick-up') }}</span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="info-content">
                    <span class="info-label">Total Amount</span>
                    <span class="info-value highlight">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- DELIVERY DETAILS --}}
        @if(($order->order_type ?? 'pickup') === 'delivery' && $order->delivery_address)
            <div class="delivery-card">
                <div class="delivery-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="delivery-content">
                    <span class="delivery-label">Delivery Address</span>
                    <span class="delivery-value">{{ $order->delivery_address }}</span>
                    @if($order->landmark)
                        <span class="delivery-landmark">
                            <i class="fas fa-flag"></i> Landmark: {{ $order->landmark }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

        {{-- ============================================ --}}
        {{-- ORDER ITEMS --}}
        {{-- ============================================ --}}
        <div class="order-items-card">
            <div class="card-header">
                <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
                <span class="items-count">{{ $order->items->count() }}
                    item{{ $order->items->count() > 1 ? 's' : '' }}</span>
            </div>
            <div class="table-wrapper">
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name ?? 'Unknown Product' }}</strong>
                                </td>
                                <td>₱{{ number_format($item->price, 2) }}</td>
                                <td>
                                    <span class="qty-badge">{{ $item->quantity }}</span>
                                </td>
                                <td class="subtotal-cell">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: 700;">Total:</td>
                            <td style="font-weight: 700; color: #576238; font-size: 1.1rem;">
                                ₱{{ number_format($order->total_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- ACTION BUTTONS --}}
        {{-- ============================================ --}}
        <div class="order-actions">
            @if($order->status === 'pending')
                <form method="POST" action="{{ route('orders.complete', $order->id) }}" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-complete" onclick="return confirm('Mark this order as completed?')">
                        <i class="fas fa-check-circle"></i> Mark as Complete
                    </button>
                </form>

                <form method="POST" action="{{ route('orders.cancel', $order->id) }}" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-cancel"
                        onclick="return confirm('Cancel this order? Stock will be restored.')">
                        <i class="fas fa-times-circle"></i> Cancel Order
                    </button>
                </form>
            @endif

            <a href="{{ route('orders.index') }}" class="btn-secondary">
                <i class="fas fa-list"></i> Back to Orders
            </a>
        </div>

    </div>

    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function (msg) {
                setTimeout(function () {
                    msg.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(-10px)';
                    setTimeout(function () {
                        msg.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>

    <style>
        /* ============ CONTAINER ============ */
        .order-detail-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* ============ FLASH MESSAGES ============ */
        .flash-message {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            position: relative;
            overflow: hidden;
            animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .flash-success {
            background: linear-gradient(135deg, #E8F0E3 0%, #D8E5CF 100%);
            border: 1px solid #576238;
            color: #3E4A28;
        }

        .flash-error {
            background: linear-gradient(135deg, #FEF0ED 0%, #FCE8E6 100%);
            border: 1px solid #C5705A;
            color: #A85444;
        }

        .flash-info {
            background: linear-gradient(135deg, #E3EDF5 0%, #D6E4EF 100%);
            border: 1px solid #3E6B8C;
            color: #3E6B8C;
        }

        .flash-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .flash-success .flash-icon {
            color: #576238;
        }

        .flash-error .flash-icon {
            color: #C5705A;
        }

        .flash-info .flash-icon {
            color: #3E6B8C;
        }

        .flash-content {
            flex: 1;
            min-width: 0;
        }

        .flash-title {
            font-size: 0.85rem;
            font-weight: 800;
            margin-bottom: 0.15rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .flash-text {
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .flash-close {
            background: transparent;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            opacity: 0.6;
            flex-shrink: 0;
        }

        .flash-close:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.05);
        }

        .flash-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.4;
            animation: progressBar 5s linear forwards;
        }

        @keyframes progressBar {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        /* ============ BACK BUTTON ============ */
        .page-nav {
            margin-bottom: 0.5rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
            padding: 0.55rem 1.1rem;
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
            transform: translateX(-2px);
        }

        .btn-back i {
            transition: transform 0.2s ease;
        }

        .btn-back:hover i {
            transform: translateX(-3px);
        }

        /* ============ HEADER ============ */
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .order-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0 0 0.25rem 0;
        }

        .order-header p {
            color: #9E9D97;
            font-size: 0.85rem;
            margin: 0;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .status-badge i {
            font-size: 0.5rem;
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

        /* ============ ORDER INFO GRID ============ */
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .order-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .order-info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            border-color: #576238;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.08);
        }

        .info-icon {
            width: 44px;
            height: 44px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #576238;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .info-content {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .info-label {
            font-size: 0.65rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .info-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            font-family: 'Inter', sans-serif;
        }

        .info-value.highlight {
            font-size: 1.1rem;
            font-weight: 700;
            color: #576238;
        }

        /* ============ DELIVERY CARD ============ */
        .delivery-card {
            background: linear-gradient(135deg, #FEF5E8 0%, #FDF8F0 100%);
            border: 1px solid #F8E5C5;
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .delivery-icon {
            width: 44px;
            height: 44px;
            background: white;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #D4A054;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .delivery-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .delivery-label {
            font-size: 0.65rem;
            color: #B8893A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .delivery-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
        }

        .delivery-landmark {
            font-size: 0.75rem;
            color: #9E9D97;
            font-style: italic;
            margin-top: 0.25rem;
        }

        .delivery-landmark i {
            color: #D4A054;
            margin-right: 0.25rem;
        }

        /* ============ ORDER ITEMS CARD ============ */
        .order-items-card {
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
        }

        .card-header h3 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0;
        }

        .card-header h3 i {
            color: #576238;
            margin-right: 0.4rem;
        }

        .items-count {
            background: #F0EADC;
            color: #576238;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .order-items-table th,
        .order-items-table td {
            padding: 0.875rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid #F0EADC;
        }

        .order-items-table th {
            background: #FDF8F0;
            color: #576238;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .order-items-table tbody tr {
            transition: background 0.2s ease;
        }

        .order-items-table tbody tr:hover {
            background: #FDF8F0;
        }

        .qty-badge {
            display: inline-block;
            background: #F0EADC;
            color: #576238;
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .subtotal-cell {
            font-weight: 700;
            color: #576238;
            font-family: 'Inter', sans-serif;
        }

        .order-items-table tfoot td {
            background: #FDF8F0;
            padding: 1rem 1.25rem;
            border-bottom: none;
        }

        /* ============ ACTION BUTTONS ============ */
        .order-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid #E3DCD0;
        }

        .btn-complete,
        .btn-cancel,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-family: inherit;
            text-decoration: none;
        }

        .btn-complete {
            background: #576238;
            color: white;
        }

        .btn-complete:hover {
            background: #3E4A28;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.3);
        }

        .btn-cancel {
            background: #C5705A;
            color: white;
        }

        .btn-cancel:hover {
            background: #A85444;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(197, 112, 90, 0.3);
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

        @media (max-width: 640px) {
            .order-actions {
                flex-direction: column;
            }

            .btn-complete,
            .btn-cancel,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection