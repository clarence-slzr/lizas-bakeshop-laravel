@extends('layouts.app')

@section('content')
    @php
        use App\Models\Order;

        $order_id = request('order_id') ?? (request()->route('id') ?? null);
        $order = Order::with(['items.product'])->findOrFail($order_id);
    @endphp

    <div class="receipt-container">
        <div class="receipt">
            <div class="receipt-header">
                <img src="/assets/images/liza-logo.jpg" alt="Logo" class="receipt-logo">
                <h2>Liza's Bakeshop</h2>
                <p>San Miguel, Bulacan</p>
                <p>Tel: 0912-345-6789</p>
                <hr>
                <p><strong>Order #: {{ $order->id }}</strong></p>
                <p>Date: {{ $order->order_date->format('M d, Y h:i A') }}</p>
                <p>Cashier: {{ auth()->user()->username }}</p>
                <hr>
            </div>

            <div class="receipt-body">
                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">
                                    @if($item->quantity > 0)
                                        ₱{{ number_format($item->subtotal / $item->quantity, 2) }}
                                    @else
                                        ₱0.00
                                    @endif
                                </td>
                                <td class="text-right">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" class="text-right"><strong>TOTAL:</strong></td>
                            <td class="text-right"><strong>₱{{ number_format($order->total_amount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="receipt-footer">
                <hr>
                <p>Thank you for your purchase!</p>
                <p>Please come again.</p>
                <hr>
                <button onclick="window.print()" class="print-btn">Print Receipt</button>
                <a href="{{ route('cashier.pos') }}" class="new-order-btn">New Order</a>
            </div>
        </div>
    </div>

    <style>
        .receipt-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            background: #f1f5f9;
            padding: 1rem;
        }

        .receipt {
            background: white;
            width: 400px;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .receipt-header {
            text-align: center;
        }

        .receipt-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .receipt-header h2 {
            margin: 10px 0 5px;
            color: #1e293b;
        }

        .receipt-header p {
            margin: 3px 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        hr {
            margin: 10px 0;
            border: none;
            border-top: 1px dashed #e2e8f0;
        }

        .receipt-table {
            width: 100%;
            font-size: 0.85rem;
        }

        .receipt-table th,
        .receipt-table td {
            padding: 6px;
            color: #334155;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            border-top: 1px solid #e2e8f0;
        }

        .receipt-footer {
            text-align: center;
            color: #64748b;
            font-size: 0.75rem;
        }

        .print-btn,
        .new-order-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .print-btn:hover,
        .new-order-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        @media print {

            .print-btn,
            .new-order-btn {
                display: none;
            }

            .receipt-container {
                background: white;
            }

            .receipt {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
@endsection