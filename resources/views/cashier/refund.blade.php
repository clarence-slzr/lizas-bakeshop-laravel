@extends('layouts.app')

@section('content')
    <div class="refund-container">

        <!-- ============ PAGE HEADER ============ -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Process Refund</h1>
                <p class="page-description">Search for a completed order and process customer refunds</p>
            </div>
        </div>

        <!-- ============ FLASH MESSAGES ============ -->
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

        <!-- ============ STATS CARDS ============ -->
        <div class="stats-grid">
            <div class="stat-card stat-today">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($todayRefunds) }}</span>
                    <span class="stat-label">Today's Refunds</span>
                </div>
            </div>

            <div class="stat-card stat-amount-today">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">₱{{ number_format($todayAmount, 2) }}</span>
                    <span class="stat-label">Today's Amount</span>
                </div>
            </div>

            <div class="stat-card stat-total">
                <div class="stat-icon">
                    <i class="fas fa-rotate-left"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($totalRefunds) }}</span>
                    <span class="stat-label">Total Refunds</span>
                </div>
            </div>

            <div class="stat-card stat-amount-total">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">₱{{ number_format($totalAmount, 2) }}</span>
                    <span class="stat-label">Total Amount</span>
                </div>
            </div>
        </div>

        <!-- ============ STEP 1: FIND ORDER ============ -->
            <div class="find-card">
                <div class="find-header">
                    <div class="find-header-icon"><i class="fas fa-magnifying-glass"></i></div>
                    <div>
                        <h3>Find Order</h3>
                        <p>Enter the order number to search</p>
                    </div>
                </div>

                {{-- GET request para hindi mag-refund agad --}}
                <form method="GET" action="{{ route('cashier.refund.search') }}" class="find-form">
                    <div class="find-form-group">
                        <label for="order_id">Order Number</label>
                        <div class="find-input-row">
                            <div class="find-input-wrapper">
                                <i class="fas fa-hashtag input-icon"></i>
                                <input type="text" name="order_id" id="order_id"
                                    placeholder="Example: 27 or 000027"
                                    value="{{ old('order_id') }}"
                                    required autocomplete="off" autofocus>
                            </div>
                            <button type="submit" class="btn-search">
                                <i class="fas fa-search"></i> Search Order
                            </button>
                        </div>
                        <div class="find-help">
                            <i class="fas fa-info-circle"></i>
                            <span>Only <strong>completed orders</strong> na hindi pa fully refunded ang pwedeng i-process.</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ============ STEP 2: ORDER DETAILS + REASON FORM ============ -->
            @if(isset($order) && $order)
                <div class="confirm-card">
                    <div class="confirm-header">
                        <div class="confirm-header-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3>Order Found — Confirm Refund</h3>
                            <p>Review the order details and provide a reason for the refund</p>
                        </div>
                    </div>

                    <div class="confirm-body">
                        {{-- ORDER DETAILS --}}
                        <div class="order-details-grid">
                            <div class="order-detail-item">
                                <span class="order-detail-label">Order No.</span>
                                <span class="order-detail-value">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Customer</span>
                                <span class="order-detail-value">{{ ucwords($order->customer_name) }}</span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Contact</span>
                                <span class="order-detail-value">{{ $order->contact_number ?? 'N/A' }}</span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Order Date</span>
                                <span class="order-detail-value">{{ $order->order_date->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Order Type</span>
                                <span class="order-detail-value">{{ ucfirst($order->order_type ?? 'Pick-up') }}</span>
                            </div>
                            <div class="order-detail-item highlight">
                                <span class="order-detail-label">Refund Amount</span>
                                <span class="order-detail-value amount">
                                    ₱{{ number_format($order->total_amount, 2) }}
                                </span>
                            </div>
                        </div>

                        {{-- ORDER ITEMS --}}
                        @if($order->items->count() > 0)
                            <div class="order-items-section">
                                <h4><i class="fas fa-box"></i> Order Items</h4>
                                <div class="order-items-list">
                                    @foreach($order->items as $item)
                                        <div class="order-item">
                                            <span class="item-name">{{ $item->product->name ?? 'Unknown Product' }}</span>
                                            <span class="item-qty">x{{ $item->quantity }}</span>
                                            <span class="item-subtotal">₱{{ number_format($item->subtotal, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- REFUND FORM --}}
                        <form method="POST" action="{{ route('cashier.refund.store') }}" class="confirm-form"
                            onsubmit="return confirm('Are you sure you want to refund Order #{{ $order->id }} for ₱{{ number_format($order->total_amount, 2) }}?');">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">

                            <div class="confirm-form-group">
                                <label for="reason">Reason for Refund <span class="required">*</span></label>
                                <textarea name="reason" id="reason" rows="3" required
                                    placeholder="Example: Customer changed mind, wrong order, product defect..."
                                    class="confirm-textarea">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <span class="form-error">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="confirm-actions">
                                <a href="{{ route('cashier.refund') }}" class="btn-cancel">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn-confirm">
                                    <i class="fas fa-check-circle"></i> Confirm Refund
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- ============ REFUND HISTORY ============ -->
            <div class="history-card">
                <div class="history-header">
                    <div class="history-header-left">
                        <div class="history-header-icon"><i class="fas fa-clock-rotate-left"></i></div>
                        <div>
                            <h3>Refund History</h3>
                            <p>Recent refund transactions</p>
                        </div>
                        <span class="history-badge">{{ $refunds->total() }} total</span>
                    </div>

                    <form method="GET" action="{{ route('cashier.refund') }}" class="history-search-form">
                        <div class="history-search">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" id="historySearch"
                                placeholder="Search customer, order #, or reason..."
                                value="{{ $search }}" autocomplete="off">
                            @if($search !== '')
                                <a href="{{ route('cashier.refund') }}" class="clear-history-search" title="Clear">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-wrapper">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th class="col-order">Order No.</th>
                                <th class="col-customer">Customer</th>
                                <th class="col-amount">Refund Amount</th>
                                <th class="col-reason">Reason</th>
                                <th class="col-date">Date</th>
                                <th class="col-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($refunds->count() > 0)
                                @foreach($refunds as $refund)
                                    <tr>
                                        <td class="col-order">
                                            <span class="order-number">#{{ str_pad($refund->order_id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td class="col-customer">
                                            <div class="customer-info">
                                                <div class="customer-avatar">
                                                    {{ strtoupper(substr($refund->order->customer_name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div class="customer-text">
                                                    <span class="customer-name">{{ ucwords($refund->order->customer_name ?? 'Unknown') }}</span>
                                                    @if(!empty($refund->order->contact_number))
                                                        <small class="customer-contact">{{ $refund->order->contact_number }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="col-amount">
                                            <span class="amount-value">₱{{ number_format($refund->refund_amount, 2) }}</span>
                                        </td>
                                        <td class="col-reason">
                                            <span class="reason-text">{{ $refund->reason ?? 'No reason provided' }}</span>
                                        </td>
                                        <td class="col-date">
                                            @if($refund->refund_date)
                                                <span class="date-primary">{{ \Carbon\Carbon::parse($refund->refund_date)->format('M d, Y') }}</span>
                                                <span class="date-time">{{ \Carbon\Carbon::parse($refund->refund_date)->format('h:i A') }}</span>
                                            @else
                                                <span class="date-primary">—</span>
                                            @endif
                                        </td>
                                        <td class="col-status">
                                            <span class="status-badge status-refunded">
                                                <i class="fas fa-rotate-left"></i>
                                                {{ ucfirst($refund->status ?? 'refunded') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="empty-row">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                @if($search !== '')
                                                    <i class="fas fa-search"></i>
                                                @else
                                                    <i class="fas fa-inbox"></i>
                                                @endif
                                            </div>
                                            @if($search !== '')
                                                <p>No refunds found for "<strong>{{ $search }}</strong>"</p>
                                                <small>Try a different search term</small>
                                                <div style="margin-top: 1rem;">
                                                    <a href="{{ route('cashier.refund') }}" class="btn-clear-search">
                                                        <i class="fas fa-times"></i> Clear Search
                                                    </a>
                                                </div>
                                            @else
                                                <p>No refunds yet</p>
                                                <small>Refunded orders will appear here</small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($refunds->hasPages())
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            Showing {{ $refunds->firstItem() }}-{{ $refunds->lastItem() }} of {{ $refunds->total() }} refunds
                        </div>
                        <div class="pagination-controls">
                            {{ $refunds->appends(['search' => $search])->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <script>
            // Debounced search para sa refund history
            (function () {
                const searchInput = document.getElementById('historySearch');
                if (!searchInput) return;

                let typingTimer;
                const delay = 600;

                searchInput.addEventListener('input', function () {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(function () {
                        searchInput.form.submit();
                    }, delay);
                });

                if (searchInput.value) {
                    searchInput.focus();
                    const val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                }
            })();
        </script>

        <style>
            .refund-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .page-header-left h1 {
                font-family: 'Playfair Display', serif;
                font-size: 1.75rem;
                font-weight: 600;
                color: #2C2B26;
                margin-bottom: 0.35rem;
            }

            .page-description { font-size: 0.85rem; color: #9E9D97; }

            .alert {
                padding: 0.875rem 1.25rem;
                border-radius: 0.5rem;
                font-size: 0.85rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .alert-success { background: #E8F0E3; color: #3E4A28; border: 1px solid #C5D4A8; }
            .alert-error { background: #FEF0ED; color: #A85444; border: 1px solid #E8C5BD; }

            /* STATS */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
            }

            @media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
            @media (max-width: 500px) { .stats-grid { grid-template-columns: 1fr; } }

            .stat-card {
                background: white;
                border: 1px solid #E3DCD0;
                border-radius: 0.75rem;
                padding: 1.25rem;
                display: flex;
                align-items: center;
                gap: 1rem;
                transition: all 0.25s ease;
                position: relative;
                overflow: hidden;
            }

            .stat-card::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0;
                height: 3px;
                background: #576238;
                opacity: 0;
                transition: opacity 0.25s ease;
            }

            .stat-card:hover { transform: translateY(-3px); border-color: #576238; box-shadow: 0 8px 24px rgba(87, 98, 56, 0.1); }
            .stat-card:hover::before { opacity: 1; }

            .stat-card.stat-today::before { background: #D4A054; }
            .stat-card.stat-amount-today::before { background: #C5705A; }
            .stat-card.stat-total::before { background: #7A8B4F; }
            .stat-card.stat-amount-total::before { background: #576238; }

            .stat-icon {
                width: 52px; height: 52px;
                border-radius: 0.75rem;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.35rem;
                flex-shrink: 0;
            }

            .stat-today .stat-icon { background: #FEF5E8; color: #D4A054; }
            .stat-amount-today .stat-icon { background: #FEF0ED; color: #C5705A; }
            .stat-total .stat-icon { background: #E8F0E3; color: #7A8B4F; }
            .stat-amount-total .stat-icon { background: #F0EADC; color: #576238; }

            .stat-content { flex: 1; min-width: 0; }

            .stat-value {
                font-size: 1.4rem;
                font-weight: 700;
                color: #2C2B26;
                display: block;
                line-height: 1.2;
                font-family: 'Inter', sans-serif;
            }

            .stat-label {
                font-size: 0.7rem;
                color: #9E9D97;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 500;
                display: block;
                margin-top: 0.25rem;
            }

            /* FIND ORDER CARD */
            .find-card {
                background: white;
                border: 1px solid #E3DCD0;
                border-radius: 0.75rem;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            }

            .find-header {
                background: linear-gradient(135deg, #FDF8F0 0%, #F7F0E4 100%);
                padding: 1.25rem;
                border-bottom: 1px solid #E3DCD0;
                display: flex;
                align-items: center;
                gap: 0.875rem;
            }

            .find-header-icon {
                width: 40px; height: 40px;
                background: #576238;
                border-radius: 0.5rem;
                display: flex; align-items: center; justify-content: center;
                color: white;
                font-size: 1rem;
                flex-shrink: 0;
            }

            .find-header h3 { font-size: 0.95rem; font-weight: 600; color: #2C2B26; margin: 0 0 0.15rem 0; }
            .find-header p { font-size: 0.75rem; color: #9E9D97; margin: 0; }

            .find-form { padding: 1.5rem; }

            .find-form-group label {
                display: block;
                font-size: 0.75rem;
                font-weight: 600;
                color: #2C2B26;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 0.5rem;
            }

            .find-input-row { display: flex; gap: 0.75rem; align-items: stretch; }

            @media (max-width: 640px) { .find-input-row { flex-direction: column; } }

            .find-input-wrapper { position: relative; flex: 1; }

            .input-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #9E9D97;
                font-size: 0.85rem;
                pointer-events: none;
            }

            .find-input-wrapper input {
                width: 100%;
                padding: 0.85rem 1rem 0.85rem 2.5rem;
                border: 1.5px solid #E3DCD0;
                border-radius: 0.5rem;
                font-size: 0.9rem;
                font-family: inherit;
                color: #2C2B26;
                background: white;
                transition: all 0.2s ease;
            }

            .find-input-wrapper input:focus {
                outline: none;
                border-color: #576238;
                box-shadow: 0 0 0 4px rgba(87, 98, 56, 0.1);
            }

            .find-input-wrapper input::placeholder { color: #C4C3BC; }

            .btn-search {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.85rem 1.75rem;
                background: #576238;
                color: white;
                border: none;
                border-radius: 0.5rem;
                font-size: 0.85rem;
                font-weight: 600;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.2s ease;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .btn-search:hover { background: #3E4A28; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(87, 98, 56, 0.3); }
            .btn-search:active { transform: translateY(0); }

            .find-help {
                margin-top: 0.875rem;
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
                font-size: 0.75rem;
                color: #9E9D97;
                line-height: 1.5;
            }

            .find-help i { color: #D4A054; margin-top: 0.15rem; flex-shrink: 0; }
            .find-help strong { color: #576238; }

            /* ============ CONFIRM CARD (STEP 2) ============ */
            .confirm-card {
                background: white;
                border: 2px solid #576238;
                border-radius: 0.75rem;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(87, 98, 56, 0.15);
                animation: confirmIn 0.3s ease;
            }

            @keyframes confirmIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .confirm-header {
                background: linear-gradient(135deg, #E8F0E3 0%, #F0F5EA 100%);
                padding: 1.25rem;
                border-bottom: 1px solid #C5D4A8;
                display: flex;
                align-items: center;
                gap: 0.875rem;
            }

            .confirm-header-icon {
                width: 44px; height: 44px;
                background: #576238;
                border-radius: 0.5rem;
                display: flex; align-items: center; justify-content: center;
                color: white;
                font-size: 1.2rem;
                flex-shrink: 0;
            }

            .confirm-header h3 { font-size: 1rem; font-weight: 700; color: #2C2B26; margin: 0 0 0.15rem 0; }
            .confirm-header p { font-size: 0.75rem; color: #576238; margin: 0; }

            .confirm-body { padding: 1.5rem; }

            /* ORDER DETAILS GRID */
            .order-details-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1rem;
                padding: 1.25rem;
                background: #FDF8F0;
                border-radius: 0.75rem;
                border: 1px solid #F0EADC;
                margin-bottom: 1.5rem;
            }

            @media (max-width: 768px) { .order-details-grid { grid-template-columns: repeat(2, 1fr); } }
            @media (max-width: 500px) { .order-details-grid { grid-template-columns: 1fr; } }

            .order-detail-item {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .order-detail-item.highlight {
                grid-column: span 3;
                background: white;
                padding: 0.75rem 1rem;
                border-radius: 0.5rem;
                border-left: 3px solid #C5705A;
            }

            @media (max-width: 768px) { .order-detail-item.highlight { grid-column: span 2; } }
            @media (max-width: 500px) { .order-detail-item.highlight { grid-column: span 1; } }

            .order-detail-label {
                font-size: 0.65rem;
                color: #9E9D97;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 600;
            }

            .order-detail-value {
                font-size: 0.9rem;
                font-weight: 600;
                color: #2C2B26;
                font-family: 'Inter', sans-serif;
            }

            .order-detail-item.highlight .order-detail-value.amount {
                font-size: 1.5rem;
                color: #C5705A;
                font-weight: 700;
            }

            /* ORDER ITEMS */
            .order-items-section { margin-bottom: 1.5rem; }

            .order-items-section h4 {
                font-size: 0.75rem;
                color: #2C2B26;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 600;
                margin-bottom: 0.75rem;
                display: flex;
                align-items: center;
                gap: 0.4rem;
            }

            .order-items-section h4 i { color: #576238; }

            .order-items-list {
                background: #FDF8F0;
                border: 1px solid #F0EADC;
                border-radius: 0.5rem;
                padding: 0.75rem;
                max-height: 200px;
                overflow-y: auto;
            }

            .order-item {
                display: flex;
                align-items: center;
                padding: 0.5rem 0.75rem;
                border-bottom: 1px solid #F0EADC;
                gap: 1rem;
            }

            .order-item:last-child { border-bottom: none; }

            .item-name {
                flex: 1;
                font-size: 0.85rem;
                color: #2C2B26;
                font-weight: 500;
            }

            .item-qty {
                font-size: 0.75rem;
                color: #9E9D97;
                background: white;
                padding: 0.15rem 0.5rem;
                border-radius: 2rem;
                font-weight: 600;
            }

            .item-subtotal {
                font-size: 0.85rem;
                font-weight: 700;
                color: #576238;
                font-family: 'Inter', sans-serif;
            }

            /* CONFIRM FORM */
            .confirm-form-group { margin-bottom: 1.25rem; }

            .confirm-form-group label {
                display: block;
                font-size: 0.75rem;
                font-weight: 600;
                color: #2C2B26;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 0.5rem;
            }

            .required { color: #C5705A; }

            .confirm-textarea {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 1.5px solid #E3DCD0;
                border-radius: 0.5rem;
                font-size: 0.9rem;
                font-family: inherit;
                color: #2C2B26;
                background: white;
                resize: vertical;
                transition: all 0.2s ease;
                min-height: 80px;
            }

            .confirm-textarea:focus {
                outline: none;
                border-color: #576238;
                box-shadow: 0 0 0 4px rgba(87, 98, 56, 0.1);
            }

            .confirm-textarea::placeholder { color: #C4C3BC; }

            .form-error {
                display: block;
                color: #C5705A;
                font-size: 0.75rem;
                margin-top: 0.4rem;
                display: flex;
                align-items: center;
                gap: 0.3rem;
            }

            .confirm-actions {
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
                padding-top: 1rem;
                border-top: 1px solid #F0EADC;
            }

            .btn-cancel {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: white;
                color: #6B6A65;
                border: 1px solid #E3DCD0;
                padding: 0.75rem 1.5rem;
                border-radius: 0.5rem;
                text-decoration: none;
                font-size: 0.85rem;
                font-weight: 600;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .btn-cancel:hover { background: #F0EADC; border-color: #576238; color: #576238; }

            .btn-confirm {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: #C5705A;
                color: white;
                border: none;
                padding: 0.75rem 1.75rem;
                border-radius: 0.5rem;
                font-size: 0.85rem;
                font-weight: 700;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .btn-confirm:hover { background: #A85444; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(197, 112, 90, 0.3); }
            .btn-confirm:active { transform: translateY(0); }

            /* HISTORY CARD */
            .history-card {
                background: white;
                border: 1px solid #E3DCD0;
                border-radius: 0.75rem;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            }

            .history-header {
                background: #FDF8F0;
                padding: 1.25rem;
                border-bottom: 1px solid #E3DCD0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .history-header-left { display: flex; align-items: center; gap: 0.875rem; }

            .history-header-icon {
                width: 40px; height: 40px;
                background: #F0EADC;
                border-radius: 0.5rem;
                display: flex; align-items: center; justify-content: center;
                color: #576238;
                font-size: 1rem;
                flex-shrink: 0;
            }

            .history-header h3 { font-size: 0.95rem; font-weight: 600; color: #2C2B26; margin: 0 0 0.15rem 0; }
            .history-header p { font-size: 0.75rem; color: #9E9D97; margin: 0; }

            .history-badge {
                background: #576238;
                color: white;
                padding: 0.25rem 0.7rem;
                border-radius: 2rem;
                font-size: 0.7rem;
                font-weight: 600;
                margin-left: 0.25rem;
            }

            .history-search-form { margin: 0; }

            .history-search { position: relative; display: flex; align-items: center; }

            .history-search i {
                position: absolute;
                left: 0.75rem;
                color: #9E9D97;
                font-size: 0.8rem;
                pointer-events: none;
            }

            .history-search input {
                padding: 0.55rem 2.5rem 0.55rem 2.25rem;
                border: 1px solid #E3DCD0;
                border-radius: 0.5rem;
                font-size: 0.8rem;
                font-family: inherit;
                width: 280px;
                color: #2C2B26;
                background: white;
                transition: all 0.2s ease;
            }

            .history-search input:focus {
                outline: none;
                border-color: #576238;
                box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
            }

            .history-search input::placeholder { color: #C4C3BC; }

            .clear-history-search {
                position: absolute;
                right: 0.6rem;
                color: #9E9D97;
                text-decoration: none;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.2s ease;
                font-size: 0.75rem;
            }

            .clear-history-search:hover { background: #FEF0ED; color: #C5705A; }

            /* TABLE */
            .table-wrapper { overflow-x: auto; }

            .history-table {
                width: 100%;
                border-collapse: collapse;
                font-family: 'Inter', sans-serif;
                font-size: 0.8125rem;
                line-height: 1.5;
            }

            .history-table thead tr {
                background: #FDF8F0;
                border-bottom: 1px solid #E3DCD0;
            }

            .history-table th {
                padding: 0.875rem 1.25rem;
                text-align: left;
                font-weight: 600;
                color: #576238;
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                white-space: nowrap;
            }

            .history-table td {
                padding: 1rem 1.25rem;
                text-align: left;
                color: #2C2B26;
                border-bottom: 1px solid #F0EADC;
                vertical-align: middle;
            }

            .history-table tbody tr { transition: background 0.15s ease; }
            .history-table tbody tr:hover { background: #FDF8F0; }
            .history-table tbody tr:last-child td { border-bottom: none; }

            .col-order { width: 110px; }
            .col-customer { width: auto; min-width: 200px; }
            .col-amount { width: 140px; }
            .col-reason { width: auto; min-width: 180px; }
            .col-date { width: 130px; }
            .col-status { width: 120px; }

            .order-number {
                font-weight: 600;
                color: #576238;
                font-family: 'SF Mono', 'Monaco', monospace;
                font-size: 0.8rem;
                background: #F0EADC;
                padding: 0.3rem 0.6rem;
                border-radius: 0.375rem;
                display: inline-block;
            }

            .customer-info { display: flex; align-items: center; gap: 0.75rem; }

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

            .customer-text { min-width: 0; }

            .customer-name {
                font-weight: 600;
                color: #2C2B26;
                display: block;
                font-size: 0.85rem;
            }

            .customer-contact {
                font-size: 0.7rem;
                color: #9E9D97;
                display: block;
                margin-top: 0.1rem;
            }

            .amount-value {
                font-weight: 700;
                color: #C5705A;
                font-size: 0.95rem;
                font-family: 'Inter', sans-serif;
            }

            .reason-text {
                font-size: 0.8rem;
                color: #6B6A65;
                font-style: italic;
                display: block;
                max-width: 300px;
                line-height: 1.4;
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

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.35rem 0.75rem;
                border-radius: 2rem;
                font-size: 0.7rem;
                font-weight: 600;
                white-space: nowrap;
            }

            .status-refunded {
                background: #FEF0ED;
                color: #C5705A;
                border: 1px solid #F8DCD4;
            }

            /* EMPTY STATE */
            .empty-row td { padding: 0 !important; }

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

            .empty-icon i { font-size: 2rem; color: #C4C3BC; }

            .empty-state p {
                color: #6B6A65;
                margin-bottom: 0.35rem;
                font-weight: 500;
                font-size: 0.9rem;
            }

            .empty-state small { color: #C4C3BC; font-size: 0.75rem; }

            .btn-clear-search {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                background: #576238;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                text-decoration: none;
                font-size: 0.75rem;
                font-weight: 600;
                transition: all 0.2s ease;
            }

            .btn-clear-search:hover { background: #3E4A28; transform: translateY(-1px); }

            /* PAGINATION */
            .pagination-wrapper {
                padding: 1rem 1.25rem;
                border-top: 1px solid #E3DCD0;
                background: #FDF8F0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .pagination-info { font-size: 0.75rem; color: #7A7A75; }
            .pagination-controls { display: flex; align-items: center; }

            /* RESPONSIVE */
            @media (max-width: 768px) {
                .history-header { flex-direction: column; align-items: stretch; }
                .history-search input { width: 100%; }
                .history-header-left { flex-wrap: wrap; }
            }
        </style>
@endsection