@extends('layouts.app')

@section('content')
    <div class="product-show-container">

        {{-- ============================================ --}}
        {{-- FLASH MESSAGES (Kung meron man) --}}
        {{-- ============================================ --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- ============================================ --}}
        {{-- PRODUCT DETAILS GRID --}}
        {{-- ============================================ --}}
        <div class="product-grid">
            
            {{-- LEFT: PRODUCT IMAGE --}}
            <div class="product-image-card">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-main-img">
                @else
                    {{-- Fallback kung walang image --}}
                    <div class="no-image">
                        <img src="{{ asset('images/liza-logo.png') }}" alt="Liza's Bakeshop" style="width: 150px; opacity: 0.5;">
                    </div>
                @endif
            </div>

            {{-- RIGHT: PRODUCT INFO --}}
            <div class="product-info">
                <span class="product-label">PRODUCT DETAILS</span>
                <h1 class="product-title">{{ $product->name }}</h1>

                <div class="product-badges">
                    <span class="badge badge-best">
                        <i class="fas fa-crown"></i> BEST SELLER
                    </span>
                    <span class="badge badge-category">
                        <i class="fas fa-tag"></i> {{ strtoupper($product->category ?? 'GENERAL') }}
                    </span>
                    @if($product->stock > 0)
                        <span class="badge badge-stock">
                            <i class="fas fa-check-circle"></i> IN STOCK ({{ $product->stock }})
                        </span>
                    @else
                        <span class="badge badge-out">
                            <i class="fas fa-times-circle"></i> OUT OF STOCK
                        </span>
                    @endif
                </div>

                <div class="product-price">₱{{ number_format($product->price, 2) }}</div>

                <p class="product-description">
                    {{-- Dynamic description kung meron sa DB, else default --}}
                    {{ $product->description ?? "mas masarap ka ba sa Biscocho ko? facts or bluff" }}
                </p>

                {{-- HOW TO ORDER SECTION --}}
                <div class="how-to-order-card">
                    <h3><i class="fas fa-info-circle"></i> How to Order</h3>
                    <ul class="order-list">
                        <li>
                            <i class="fas fa-shopping-bag"></i>
                            <span>Visit us at our store or contact us to place your order.</span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Tecson St. Brgy. Poblacion, San Miguel, Bulacan</span>
                        </li>
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <span>0917 514 3444</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Mon-Sun: 7AM - 7PM</span>
                        </li>
                    </ul>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="product-actions">
                    <a href="{{ route('landing') }}#contact" class="btn-contact">
                        <i class="fas fa-envelope"></i> Contact Us
                    </a>
                    <a href="{{ url()->previous() }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Best Sellers
                    </a>
                </div>

            </div>
        </div>
    </div>

    <style>
        .product-show-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* ============ MAIN GRID ============ */
        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        @media (max-width: 900px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        /* ============ IMAGE CARD ============ */
        .product-image-card {
            background: #FDF8F0; /* Cream background */
            border: 1px solid #E3DCD0;
            border-radius: 1.5rem;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: sticky;
            top: 2rem;
        }

        .product-main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            text-align: center;
        }

        /* ============ PRODUCT INFO ============ */
        .product-info {
            display: flex;
            flex-direction: column;
        }

        .product-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #D4A054;
            letter-spacing: 1.5px;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .product-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0 0 1rem 0;
            line-height: 1.2;
        }

        /* ============ BADGES ============ */
        .product-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.875rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .badge-best {
            background: #FEF5E8;
            color: #B8893A;
            border: 1px solid #F8E5C5;
        }

        .badge-category {
            background: #F0EADC;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
        }

        .badge-stock {
            background: #E8F0E3;
            color: #3E4A28;
            border: 1px solid #C5D4A8;
        }

        .badge-out {
            background: #FEF0ED;
            color: #A85444;
            border: 1px solid #E8C5BD;
        }

        .product-price {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #576238;
            margin-bottom: 1.5rem;
        }

        .product-description {
            font-size: 1rem;
            color: #6B6A65;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-style: italic;
        }

        /* ============ HOW TO ORDER CARD ============ */
        .how-to-order-card {
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .how-to-order-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .how-to-order-card h3 i {
            color: #576238;
        }

        .order-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }

        .order-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: #4A4945;
            line-height: 1.5;
        }

        .order-list li i {
            color: #9E9D97;
            width: 16px;
            text-align: center;
            margin-top: 0.15rem;
        }

        /* ============ BUTTONS ============ */
        .product-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-contact,
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-contact {
            background: #576238;
            color: white;
            border: none;
            flex: 1;
        }

        .btn-contact:hover {
            background: #3E4A28;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.3);
        }

        .btn-back {
            background: white;
            color: #2C2B26;
            border: 1px solid #E3DCD0;
            flex: 1;
        }

        .btn-back:hover {
            background: #FDF8F0;
            border-color: #576238;
            color: #576238;
        }

        @media (max-width: 480px) {
            .product-actions {
                flex-direction: column;
            }
        }
    </style>
@endsection