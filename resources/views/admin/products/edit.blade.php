@extends('layouts.app')

@section('content')
    @php
        $page_title = 'Edit Product';
        $hide_page_title = true;
    @endphp

    <div class="form-container">
        <div class="form-header">
            <div class="form-header-left">
                <a href="{{ route('admin.products.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <h1>Edit Product</h1>
                <p class="form-description">Update product information for {{ $product->name }}</p>
            </div>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" onclick="document.getElementById('successAlert').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST"
            class="product-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>

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
                            <div style="position: relative; display: flex; align-items: center;">
                                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9E9D97; font-size: 0.875rem; pointer-events: none; z-index: 2;">₱</span>
                                <input type="number" id="price" name="price" step="0.01" min="0"
                                    value="{{ old('price', $product->price) }}"
                                    required placeholder="0.00"
                                    style="padding-left: 32px !important; width: 100%;">
                            </div>
                            <small class="form-hint">Price in Philippine Peso (PHP)</small>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" min="0"
                                value="{{ old('stock', $product->stock) }}" placeholder="0">
                            <small class="form-hint">Current inventory count</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="" disabled {{ old('category', $product->category) ? '' : 'selected' }}>Select a category</option>
                            <option value="Breads" {{ old('category', $product->category) == 'Breads' ? 'selected' : '' }}>Breads</option>
                            <option value="Cakes" {{ old('category', $product->category) == 'Cakes' ? 'selected' : '' }}>Cakes</option>
                            <option value="Pastries" {{ old('category', $product->category) == 'Pastries' ? 'selected' : '' }}>Pastries</option>
                            <option value="Cookies" {{ old('category', $product->category) == 'Cookies' ? 'selected' : '' }}>Cookies</option>
                            <option value="Donuts" {{ old('category', $product->category) == 'Donuts' ? 'selected' : '' }}>Donuts</option>
                            <option value="Others" {{ old('category', $product->category) == 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                        <small class="form-hint">Select the product category for organization</small>
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image</label>
                        @if($product->image)
                            <div style="margin-bottom: 0.75rem;">
                                <img src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    style="width: 120px; height: 120px; object-fit: cover; border-radius: 0.5rem; border: 1px solid #E3DCD0;">
                                <small class="form-hint" style="display: block; margin-top: 0.5rem;">Current image</small>
                            </div>
                        @endif
                        <input type="file" id="image" name="image" accept="image/*">
                        <small class="form-hint">Upload a new product photo to replace the current one (JPG, PNG, max 2MB).</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Availability Settings</h3>

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

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <div class="info-content">
                            <strong>Note:</strong> Products marked as "Available" will appear in the POS system for cashiers to sell.
                            Best Sellers will appear on the landing page.
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <style>
        .form-container { max-width: 800px; margin: 0 auto; }
        .form-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; gap: 1rem; }
        .form-header-left { flex: 1; }
        .back-link {
            display: inline-flex; align-items: center; gap: 0.5rem; color: #576238;
            text-decoration: none; font-size: 0.8rem; margin-bottom: 0.75rem; transition: all 0.2s ease;
        }
        .back-link:hover { color: #3E4A28; transform: translateX(-2px); }
        .form-header h1 {
            font-family: 'Playfair Display', serif; font-size: 1.5rem;
            font-weight: 600; color: #2C2B26; margin-bottom: 0.25rem;
        }
        .form-description { color: #9E9D97; font-size: 0.85rem; }

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
        }

        .alert i {
            font-size: 1.1rem;
        }

        .alert-close {
            background: transparent;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: auto;
            opacity: 0.6;
            transition: opacity 0.2s ease;
            font-size: 0.9rem;
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

        .product-form { background: white; border: 1px solid #E3DCD0; border-radius: 0.75rem; overflow: hidden; }
        .form-grid { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.75rem; }
        .form-section { border-bottom: 1px solid #F0EADC; padding-bottom: 1.5rem; }
        .form-section:last-child { border-bottom: none; padding-bottom: 0; }
        .section-title {
            font-size: 0.9rem; font-weight: 600; color: #2C2B26; margin-bottom: 1.25rem;
            padding-bottom: 0.5rem; border-bottom: 2px solid #576238; display: inline-block;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 1.25rem; }
        .form-group:last-child { margin-bottom: 0; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.8rem; color: #2C2B26; }
        .required { color: #C5705A; margin-left: 0.25rem; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #E3DCD0;
            border-radius: 0.5rem; font-size: 0.875rem; font-family: 'Inter', sans-serif;
            transition: all 0.2s ease; background: white; resize: vertical;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #576238; box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }
        .form-hint { display: block; font-size: 0.7rem; color: #9E9D97; margin-top: 0.375rem; line-height: 1.4; }

        /* ============ TOGGLE ROW ============ */
        .toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #F0EADC;
        }

        .toggle-row:last-of-type { border-bottom: none; }

        .toggle-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .toggle-info strong { font-size: 0.85rem; color: #2C2B26; font-weight: 600; }
        .toggle-info small { font-size: 0.7rem; color: #9E9D97; font-weight: normal; }

        /* ============ TOGGLE SWITCH ============ */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
            cursor: pointer;
        }

        .toggle-switch input { opacity: 0; width: 0; height: 0; }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #E3DCD0;
            transition: 0.3s;
            border-radius: 24px;
        }

        .toggle-slider:before {
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

        .toggle-switch input:checked + .toggle-slider { background-color: #576238; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
        .toggle-switch input:focus + .toggle-slider { box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.2); }

        .info-box {
            background: #FDF8F0; border: 1px solid #E3DCD0; border-radius: 0.5rem;
            padding: 0.875rem; display: flex; align-items: flex-start; gap: 0.75rem; margin-top: 1rem;
        }
        .info-box i { color: #576238; font-size: 1rem; margin-top: 0.125rem; }
        .info-content { font-size: 0.8rem; color: #6B6A65; line-height: 1.5; }
        .info-content strong { color: #2C2B26; }
        .form-actions {
            background: #FDF8F0; padding: 1rem 1.5rem; border-top: 1px solid #E3DCD0;
            display: flex; gap: 1rem; justify-content: flex-end;
        }
        .btn-primary, .btn-secondary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-size: 0.85rem;
            font-weight: 500; cursor: pointer; transition: all 0.2s ease;
            border: none; text-decoration: none;
        }
        .btn-primary { background: #576238; color: white; }
        .btn-primary:hover { background: #3E4A28; transform: translateY(-1px); }
        .btn-secondary { background: white; color: #6B6A65; border: 1px solid #E3DCD0; }
        .btn-secondary:hover { background: #F0EADC; border-color: #576238; color: #576238; }
    </style>

    {{-- AUTO-HIDE SUCCESS MESSAGE AFTER 5 SECONDS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(function () {
                    successAlert.style.transition = 'opacity 0.5s ease';
                    successAlert.style.opacity = '0';
                    setTimeout(function () {
                        successAlert.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
@endsection