@extends('layouts.app')

@section('content')
    <style>
        .product-detail-page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 2rem 4rem;
        }

        .page-nav {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 2rem;
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
            font-family: inherit;
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

        .product-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        @media (max-width: 768px) {
            .product-detail-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        .product-detail-image {
            background: #F5F1E8;
            border: 1px solid #E3DCD0;
            border-radius: 1rem;
            overflow: hidden;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-detail-image .placeholder-logo {
            width: 150px;
            height: 150px;
            object-fit: contain;
            opacity: 0.7;
        }

        .product-detail-info {
            display: flex;
            flex-direction: column;
        }

        .product-detail-info .eyebrow {
            color: #D4A054;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            display: inline-block;
        }

        .product-detail-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .product-detail-badges {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .detail-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .detail-badge.badge-best {
            background: #D4A054;
            color: white;
        }

        .detail-badge.badge-stock {
            background: #E8F0E3;
            color: #576238;
        }

        .detail-badge.badge-out {
            background: #FEF0ED;
            color: #C5705A;
        }

        .detail-badge.badge-category {
            background: #F0EADC;
            color: #576238;
        }

        .product-detail-price {
            font-size: 2rem;
            font-weight: 700;
            color: #576238;
            margin-bottom: 1.5rem;
            font-family: 'Inter', sans-serif;
        }

        .product-detail-description {
            font-size: 0.95rem;
            color: #6B6A65;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #E3DCD0;
        }

        .how-to-order {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .how-to-order h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #2C2B26;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .how-to-order h3 i {
            color: #576238;
        }

        .how-to-order-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
            color: #6B6A65;
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }

        .how-to-order-item:last-child {
            margin-bottom: 0;
        }

        .how-to-order-item i {
            color: #576238;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .product-detail-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-contact {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #576238;
            color: white;
            padding: 0.85rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            font-family: inherit;
            border: none;
            cursor: pointer;
        }

        .btn-contact:hover {
            background: #3E4A28;
            transform: translateY(-2px);
        }

        .btn-back-detail {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: #576238;
            padding: 0.85rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid #576238;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-back-detail:hover {
            background: #576238;
            color: white;
            transform: translateY(-2px);
        }

        .related-section {
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid #E3DCD0;
        }

        .related-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .related-section p {
            color: #6B6A65;
            text-align: center;
            margin-bottom: 2rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .related-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .related-grid {
                grid-template-columns: 1fr;
            }
        }

        .related-card {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .related-card:hover {
            transform: translateY(-4px);
            border-color: #576238;
            box-shadow: 0 12px 24px rgba(87, 98, 56, 0.12);
        }

        .related-card-image {
            height: 150px;
            background: #F5F1E8;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .related-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .related-card-info {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .related-card-info h4 {
            font-family: 'Playfair Display', serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.35rem;
            line-height: 1.3;
        }

        .related-card-price {
            font-size: 1rem;
            font-weight: 700;
            color: #576238;
            font-family: 'Inter', sans-serif;
        }
    </style>

    <div class="product-detail-page">

        {{-- BACK BUTTON --}}
        <div class="page-nav">
            <a href="{{ route('landing') }}#products" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Best Sellers
            </a>
        </div>

        <div class="product-detail-grid">
            {{-- PRODUCT IMAGE --}}
            <div class="product-detail-image">
                @if($product->image && file_exists(storage_path('app/public/' . $product->image)))
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                @else
                    <img src="{{ asset('images/liza-logo.png') }}" alt="{{ $product->name }}" class="placeholder-logo"
                        onerror="this.src='{{ asset('assets/images/liza-logo.jpg') }}'">
                @endif
            </div>

            {{-- PRODUCT INFO --}}
            <div class="product-detail-info">
                <span class="eyebrow">Product Details</span>
                <h1>{{ $product->name }}</h1>

                {{-- BADGES --}}
                <div class="product-detail-badges">
                    @if($product->is_best_seller)
                        <span class="detail-badge badge-best">
                            <i class="fas fa-fire"></i> Best Seller
                        </span>
                    @endif
                    @if($product->category)
                        <span class="detail-badge badge-category">
                            <i class="fas fa-tag"></i> {{ $product->category }}
                        </span>
                    @endif
                    @if($product->is_available && $product->stock > 0)
                        <span class="detail-badge badge-stock">
                            <i class="fas fa-check-circle"></i> In Stock ({{ $product->stock }})
                        </span>
                    @else
                        <span class="detail-badge badge-out">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    @endif
                </div>

                {{-- PRICE --}}
                <div class="product-detail-price">₱{{ number_format($product->price, 2) }}</div>

                {{-- DESCRIPTION --}}
                @if($product->description)
                    <p class="product-detail-description">
                        {{ $product->description }}
                    </p>
                @else
                    <p class="product-detail-description" style="font-style: italic; color: #9E9D97;">
                        No description available for this product.
                    </p>
                @endif

                {{-- HOW TO ORDER --}}
                <div class="how-to-order">
                    <h3>
                        <i class="fas fa-info-circle"></i> How to Order
                    </h3>
                    <div class="how-to-order-item">
                        <i class="fas fa-store"></i>
                        <span>Visit us at our store or contact us to place your order.</span>
                    </div>
                    <div class="how-to-order-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Tecson St. Brgy. Poblacion, San Miguel, Bulacan</span>
                    </div>
                    <div class="how-to-order-item">
                        <i class="fas fa-phone"></i>
                        <span>0917 514 3444</span>
                    </div>
                    <div class="how-to-order-item">
                        <i class="fas fa-clock"></i>
                        <span>Mon-Sun: 7AM - 7PM</span>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="product-detail-actions">
                    <a href="{{ route('landing') }}#contact" class="btn-contact">
                        <i class="fas fa-envelope"></i> Contact Us
                    </a>
                    <a href="{{ route('landing') }}#products" class="btn-back-detail">
                        <i class="fas fa-arrow-left"></i> Back to Best Sellers
                    </a>
                </div>
            </div>
        </div>

        {{-- RELATED PRODUCTS --}}
        @if(isset($related) && $related->count() > 0)
            <div class="related-section">
                <h2>You Might Also Like</h2>
                <p>Other products from the same category</p>

                <div class="related-grid">
                    @foreach($related as $item)
                        <a href="{{ route('products.show', $item->id) }}" class="related-card">
                            <div class="related-card-image">
                                @if($item->image && file_exists(storage_path('app/public/' . $item->image)))
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                                @else
                                    <img src="{{ asset('images/liza-logo.png') }}" alt="{{ $item->name }}"
                                        style="object-fit: contain; padding: 1.5rem; opacity: 0.7;"
                                        onerror="this.src='{{ asset('assets/images/liza-logo.jpg') }}'">
                                @endif
                            </div>
                            <div class="related-card-info">
                                <h4>{{ $item->name }}</h4>
                                <span class="related-card-price">₱{{ number_format($item->price, 2) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection