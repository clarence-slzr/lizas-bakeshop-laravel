@extends('layouts.app')

@section('content')
    @php
        // Itago yung default page title kasi may custom header tayo sa baba
        $page_title = 'Edit Product';
        $hide_page_title = true;
    @endphp

    <div class="form-container">
        <div class="form-header">
            <div class="form-header-left">
                {{-- Link pabalik sa products list --}}
                <a href="{{ route('admin.products.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <h1>Edit Product</h1>
                <p class="form-description">Update product information for {{ $product->name }}</p>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Validation Errors (Ipakita kung may mali sa form) --}}
        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul class="error-list">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST"
            class="product-form" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Sinasabi nito sa Laravel na Update operation ito, hindi Create --}}

            <div class="form-grid">
                
                {{-- SECTION 1: Basic Info --}}
                <div class="form-section">
                    <h2 class="section-title">Basic Information</h2>

                    <div class="form-group">
                        <label for="name">Product Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name', $product->name) }}" required autofocus
                            placeholder="e.g., Pandesal, Ube Cake, etc.">
                        <small class="form-hint">Enter the full product name as it appears to customers</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"
                            placeholder="Enter product description...">{{ old('description', $product->description) }}</textarea>
                        <small class="form-hint">Describe the product for customers to see on the product page</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price <span class="required">*</span></label>
                            {{-- Peso sign na naka-position sa loob ng input --}}
                            <div class="price-input-wrapper">
                                <span class="price-symbol">₱</span>
                                <input type="number" id="price" name="price" step="0.01" min="0"
                                    value="{{ old('price', $product->price) }}"
                                    required placeholder="0.00"
                                    class="price-input">
                            </div>
                            <small class="form-hint">Price in Philippine Peso (PHP)</small>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" min="0" step="1"
                                value="{{ old('stock', $product->stock) }}" placeholder="0">
                            <small class="form-hint">Current inventory count</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            {{-- Logic: Kung walang existing category, i-select yung blank option --}}
                            <option value="" disabled @if(!old('category', $product->category)) selected @endif>Select a category</option>
                            
                            {{-- I-highlight kung anong category currently naka-save --}}
                            <option value="Breads" @if(old('category', $product->category) == 'Breads') selected @endif>Breads</option>
                            <option value="Cakes" @if(old('category', $product->category) == 'Cakes') selected @endif>Cakes</option>
                            <option value="Pastries" @if(old('category', $product->category) == 'Pastries') selected @endif>Pastries</option>
                            <option value="Cookies" @if(old('category', $product->category) == 'Cookies') selected @endif>Cookies</option>
                            <option value="Donuts" @if(old('category', $product->category) == 'Donuts') selected @endif>Donuts</option>
                            <option value="Others" @if(old('category', $product->category) == 'Others') selected @endif>Others</option>
                        </select>
                        <small class="form-hint">Select the product category for organization</small>
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image</label>
                        {{-- Ipakita yung kasalukuyang image kung meron man --}}
                        @if($product->image)
                            <div class="current-image-wrapper">
                                <img src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    class="current-image-preview">
                                <small class="form-hint current-image-label">Current image</small>
                            </div>
                        @endif
                        <input type="file" id="image" name="image" accept="image/*">
                        <small class="form-hint">Upload a new product photo to replace the current one (JPG, PNG, max 2MB).</small>
                    </div>
                </div>

                {{-- SECTION 2: Availability Settings --}}
                <div class="form-section">
                    <h2 class="section-title">Availability Settings</h2>

                    {{-- Toggle: Available or Hidden --}}
                    <div class="toggle-row">
                        <div class="toggle-info">
                            <strong>Available for purchase</strong>
                            <small>When unchecked, this product will be hidden from POS</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_available" value="1"
                                {{ old('is_available', $product->is_available) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    {{-- Toggle: Best Seller status --}}
                    <div class="toggle-row">
                        <div class="toggle-info">
                            <strong>Mark as Best Seller</strong>
                            <small>Best sellers are featured on the landing page</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_best_seller" value="1"
                                {{ old('is_best_seller', $product->is_best_seller) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    {{-- Info box na paalala --}}
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <div class="info-content">
                            <strong>Note:</strong> Products marked as "Available" will appear in the POS system for cashiers to sell.
                            Best Sellers will appear on the landing page.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Buttons --}}
            <div class="form-actions">
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update Product
                </button>
            </div>
        </form>
    </div>

    <style>
        /* ============ FORM CONTAINER ============ */
        /* Nililimitahan ang lapad ng form at naka-center sa screen */
        .form-container { max-width: 800px; margin: 0 auto; }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.75rem;
            gap: 1rem;
        }

        .form-header-left { flex: 1; }

        /* Design ng Back button */
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

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .form-description {
            color: #9E9D97;
            font-size: 0.85rem;
        }

        /* ============ ALERT MESSAGES ============ */
        .alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
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
            align-items: flex-start;
        }

        .alert i {
            font-size: 1.1rem;
        }

        .alert-error > i {
            margin-top: 0.125rem;
        }

        .error-list {
            margin: 0;
            padding-left: 1.25rem;
        }

        /* Animation kapag lumabas yung alert */
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

        /* ============ FORM LAYOUT ============ */
        .product-form {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        /* Spacing ng bawat section sa form */
        .form-grid {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .form-section {
            border-bottom: 1px solid #F0EADC;
            padding-bottom: 1.5rem;
        }

        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #576238;
            display: inline-block;
        }

        /* Grid para magkatabi yung Price at Stock */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr; /* Magiging isang column kapag maliit ang screen */
            }
        }

        .form-group { margin-bottom: 1.25rem; }
        .form-group:last-child { margin-bottom: 0; }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.8rem;
            color: #2C2B26;
        }

        .required {
            color: #C5705A;
            margin-left: 0.25rem;
        }

        /* Default design para sa lahat ng inputs */
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: white;
            resize: vertical;
        }

        /* Highlight effect kapag clinick yung input */
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .form-hint {
            display: block;
            font-size: 0.7rem;
            color: #9E9D97;
            margin-top: 0.375rem;
            line-height: 1.4;
        }

        /* ============ PRICE INPUT ============ */
        .price-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .price-symbol {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9E9D97;
            font-size: 0.875rem;
            pointer-events: none;
            z-index: 2;
        }

        /* Para hindi matakpan ng peso sign yung number */
        .price-input {
            padding-left: 32px !important;
        }

        /* ============ CURRENT IMAGE PREVIEW ============ */
        .current-image-wrapper {
            margin-bottom: 0.75rem;
        }

        .current-image-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid #E3DCD0;
        }

        .current-image-label {
            display: block;
            margin-top: 0.5rem;
        }

        /* ============ TOGGLE ROW ============ */
        .toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #F0EADC;
        }

        .toggle-row:last-of-type {
            border-bottom: none;
        }

        .toggle-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .toggle-info strong {
            font-size: 0.85rem;
            color: #2C2B26;
            font-weight: 600;
        }

        .toggle-info small {
            font-size: 0.7rem;
            color: #9E9D97;
            font-weight: normal;
        }

        /* ============ TOGGLE SWITCH (Custom Checkbox) ============ */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
            cursor: pointer;
        }

        /* Itago yung default HTML checkbox */
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Background ng toggle */
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #E3DCD0;
            transition: 0.3s;
            border-radius: 24px;
        }

        /* Yung umiikot na bilog sa loob */
        .toggle-slider::before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }

        /* Kulay kapag ON yung toggle */
        .toggle-switch input:checked + .toggle-slider {
            background-color: #576238;
        }

        /* Paglipat ng bilog sa kanan kapag ON */
        .toggle-switch input:checked + .toggle-slider::before {
            transform: translateX(20px);
        }

        /* Highlight effect kapag focus */
        .toggle-switch input:focus + .toggle-slider {
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.2);
        }

        /* ============ INFO BOX ============ */
        .info-box {
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            padding: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .info-box i {
            color: #576238;
            font-size: 1rem;
            margin-top: 0.125rem;
        }

        .info-content {
            font-size: 0.8rem;
            color: #6B6A65;
            line-height: 1.5;
        }

        .info-content strong {
            color: #2C2B26;
        }

        /* ============ FORM ACTIONS ============ */
        .form-actions {
            background: #FDF8F0;
            padding: 1rem 1.5rem;
            border-top: 1px solid #E3DCD0;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: #576238;
            color: white;
        }

        .btn-primary:hover {
            background: #3E4A28;
            transform: translateY(-1px);
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
    </style>

    {{-- Auto-hide success message after 5 seconds --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(function () {
                    successAlert.style.transition = 'opacity 0.5s ease';
                    successAlert.style.opacity = '0';
                    // Tanggalin sa screen pagkatapos mag-fade out
                    setTimeout(function () {
                        successAlert.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
@endsection