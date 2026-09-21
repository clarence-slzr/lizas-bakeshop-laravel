@extends('layouts.app')

@section('content')
    @php
        use App\Models\Product;

        $page_title = 'Point of Sale';
        $hide_page_title = true;

        $products = Product::where('is_available', 1)
            ->where('stock', '>', 0)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $groupedProducts = [];
        foreach ($products as $product) {
            $category = $product->category ?? 'Others';
            if (!isset($groupedProducts[$category])) {
                $groupedProducts[$category] = [];
            }
            $groupedProducts[$category][] = $product;
        }
    @endphp

    <div class="pos-wrapper">
        <div class="pos-container">
            <!-- ========== LEFT: PRODUCTS PANEL ========== -->
            <div class="products-panel">
                <div class="panel-header">
                    <div class="header-title">
                        <i class="fas fa-boxes-stacked"></i>
                        <h3>Products</h3>
                        <span class="header-count">{{ $products->count() }}</span>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="productSearch" placeholder="Search products..." class="product-search"
                            autocomplete="off">
                        <kbd class="search-hint">/</kbd>
                    </div>
                </div>

                <div class="category-tabs" id="categoryTabs">
                    <button class="cat-btn active" data-category="all">
                        <i class="fas fa-th"></i> All
                    </button>
                    @foreach(array_keys($groupedProducts) as $category)
                        <button class="cat-btn" data-category="{{ $category }}">
                            {{ $category }}
                        </button>
                    @endforeach
                </div>

                <div class="products-grid" id="productsGrid">
                    @foreach($products as $p)
                        <div class="product-card" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}"
                            data-stock="{{ $p->stock }}" data-category="{{ $p->category ?? 'Others' }}"
                            onclick="addToCart(this)" oncontextmenu="addMultipleToCart(event, this, 5)">
                            <div class="product-name">{{ $p->name }}</div>
                            <div class="product-price">₱{{ number_format($p->price, 2) }}</div>
                            <div class="product-stock {{ $p->stock < 10 ? 'stock-low' : '' }}">
                                @if($p->stock < 10)
                                    <i class="fas fa-exclamation-triangle"></i>
                                @endif
                                Stock: {{ $p->stock }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="no-results" id="noResults" style="display: none;">
                    <i class="fas fa-search"></i>
                    <p>No products found</p>
                    <small>Try a different search term</small>
                </div>
            </div>

            <!-- ========== RIGHT: CART PANEL ========== -->
            <div class="cart-panel">
                <div class="cart-header">
                    <div class="cart-header-left">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Current Order</h3>
                        <span class="cart-badge" id="cart-count">0 items</span>
                    </div>
                    <button class="clear-cart-btn" id="clearCartBtn" title="Clear all items (Ctrl+Backspace)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>

                <div class="cart-items" id="cartItems">
                    <div class="empty-cart-state" id="emptyCart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <p>No items added</p>
                        <small>Click on products to add to cart</small>
                    </div>
                    <div class="cart-table-wrapper" id="cartTableWrapper" style="display: none;">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th class="col-product">Product</th>
                                    <th class="col-price">Price</th>
                                    <th class="col-qty">Qty</th>
                                    <th class="col-subtotal">Subtotal</th>
                                    <th class="col-action"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value" id="cart-subtotal">₱0.00</span>
                    </div>
                    <div class="summary-row discount-row" id="discount-row" style="display: none;">
                        <span class="summary-label">Discount:</span>
                        <span class="summary-value discount-value" id="discount-amount">-₱0.00</span>
                    </div>
                    <div class="summary-row total-row-summary">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value total-value" id="cart-total">₱0.00</span>
                    </div>
                </div>

                <div class="cart-actions">
                    <!-- CUSTOMER INFO -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="fas fa-user"></i>
                            <span>Customer Info</span>
                        </div>
                        <input type="text" id="customer_name" placeholder="Full Name *" class="form-input"
                            autocomplete="off">
                        <input type="tel" id="contact_number" placeholder="Contact Number" class="form-input"
                            autocomplete="off">
                    </div>

                    <!-- ORDER TYPE -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="fas fa-truck"></i>
                            <span>Order Type</span>
                        </div>
                        <div class="order-type-buttons">
                            <button type="button" class="order-type-btn active" data-type="pickup">
                                <i class="fa-solid fa-shop"></i> Pick-up
                            </button>
                            <button type="button" class="order-type-btn" data-type="delivery">
                                <i class="fas fa-truck"></i> Delivery
                            </button>
                        </div>

                        <div id="deliveryDetails" style="display: none;">
                            <div class="delivery-field">
                                <label><i class="fas fa-location-dot"></i> Delivery Address <span
                                        class="required">*</span></label>
                                <textarea id="delivery_address" rows="2" placeholder="House number, street, barangay, city"
                                    class="form-input"></textarea>
                            </div>
                            <div class="delivery-field">
                                <label><i class="fas fa-flag"></i> Landmark (optional)</label>
                                <input type="text" id="landmark" placeholder="Example: Near church" class="form-input">
                            </div>
                        </div>
                    </div>

                    <!-- DISCOUNT -->
                    <div class="form-section discount-section">
                        <div class="form-section-header">
                            <i class="fas fa-tag"></i>
                            <span>Discount</span>
                            <button type="button" class="senior-btn" id="seniorBtn">
                                <i class="fas fa-user-tie"></i> Senior/PWD
                            </button>
                        </div>
                        <select id="discount_type" class="form-input">
                            <option value="none">No Discount</option>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₱)</option>
                        </select>
                        <div id="discount_value_div" style="display: none;">
                            <input type="number" id="discount_value" step="0.01" placeholder="Enter discount value"
                                class="form-input">
                        </div>
                    </div>

                    <!-- PAYMENT -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payment</span>
                        </div>
                        <div class="payment-row">
                            <span class="payment-label">Tendered:</span>
                            <input type="number" id="amount_tendered" placeholder="0.00" step="0.01"
                                class="form-input payment-input">
                        </div>
                        <div class="change-row">
                            <span class="change-label">Change:</span>
                            <strong class="change-amount" id="change_due">₱0.00</strong>
                        </div>
                    </div>

                    <!-- CHECKOUT -->
                    <button id="checkoutBtn" class="checkout-btn">
                        <i class="fas fa-check-circle"></i> Checkout (Enter)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== TOAST ========== -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // ============================================
        // STATE
        // ============================================
        let cart = [];
        let currentSubtotal = 0;
        let currentDiscount = 0;
        let currentTotal = 0;
        let currentOrderType = 'pickup';

        const CSRF_TOKEN = '{{ csrf_token() }}';
        const PLACE_ORDER_URL = '{{ route("cashier.place-order") }}';

        // ============================================
        // TOAST
        // ============================================
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle'
            };

            toast.innerHTML = `<i class="fas ${icons[type]}"></i> <span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // ============================================
        // ADD TO CART
        // ============================================
        function addToCart(card, quantity = 1) {
            const id = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const stock = parseInt(card.dataset.stock);

            const existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.qty + quantity <= stock) {
                    existing.qty += quantity;
                    showToast(`+${quantity} ${name}`, 'success');
                } else {
                    showToast('Not enough stock!', 'error');
                    return;
                }
            } else {
                if (quantity > stock) {
                    showToast('Not enough stock!', 'error');
                    return;
                }
                cart.push({ id, name, price, qty: quantity, stock });
                showToast(`Added ${name}`, 'success');
            }

            card.classList.add('added');
            setTimeout(() => card.classList.remove('added'), 300);

            updateCart();
        }

        function addMultipleToCart(e, card, qty) {
            e.preventDefault();
            addToCart(card, qty);
        }

        // ============================================
        // SEARCH & FILTER
        // ============================================
        const searchInput = document.getElementById('productSearch');
        const categoryBtns = document.querySelectorAll('.cat-btn');
        const productCards = document.querySelectorAll('.product-card');
        const noResults = document.getElementById('noResults');

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeCategory = document.querySelector('.cat-btn.active')?.dataset.category || 'all';
            let visibleCount = 0;

            productCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const category = card.dataset.category;
                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = activeCategory === 'all' || category === activeCategory;

                if (matchesSearch && matchesCategory) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        searchInput.addEventListener('input', filterProducts);

        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterProducts();
            });
        });

        // ============================================
        // ORDER TYPE
        // ============================================
        const pickupBtn = document.querySelector('.order-type-btn[data-type="pickup"]');
        const deliveryBtn = document.querySelector('.order-type-btn[data-type="delivery"]');
        const deliveryDetails = document.getElementById('deliveryDetails');

        function setOrderType(type) {
            currentOrderType = type;
            if (type === 'delivery') {
                pickupBtn.classList.remove('active');
                deliveryBtn.classList.add('active');
                deliveryDetails.style.display = 'block';
            } else {
                pickupBtn.classList.add('active');
                deliveryBtn.classList.remove('active');
                deliveryDetails.style.display = 'none';
            }
        }

        if (pickupBtn && deliveryBtn) {
            pickupBtn.addEventListener('click', () => setOrderType('pickup'));
            deliveryBtn.addEventListener('click', () => setOrderType('delivery'));
        }

        // ============================================
        // CLEAR CART
        // ============================================
        document.getElementById('clearCartBtn')?.addEventListener('click', () => {
            if (cart.length > 0 && confirm('Clear all items from cart?')) {
                cart = [];
                updateCart();
                showToast('Cart cleared', 'info');
            }
        });

        // ============================================
        // DISCOUNT
        // ============================================
        document.getElementById('seniorBtn')?.addEventListener('click', () => {
            const discountType = document.getElementById('discount_type');
            const discountValueDiv = document.getElementById('discount_value_div');
            const discountValue = document.getElementById('discount_value');

            discountType.value = 'percentage';
            discountValueDiv.style.display = 'block';
            discountValue.value = '20';
            calculateDiscount();
            showToast('Senior/PWD discount applied', 'success');
        });

        const discountType = document.getElementById('discount_type');
        const discountValueDiv = document.getElementById('discount_value_div');
        const discountValue = document.getElementById('discount_value');

        discountType.addEventListener('change', function () {
            if (this.value !== 'none') {
                discountValueDiv.style.display = 'block';
            } else {
                discountValueDiv.style.display = 'none';
                discountValue.value = '';
            }
            calculateDiscount();
        });

        discountValue.addEventListener('input', calculateDiscount);

        function calculateDiscount() {
            const subtotal = currentSubtotal;
            const type = discountType.value;
            const value = parseFloat(discountValue.value) || 0;

            if (type === 'percentage') {
                currentDiscount = subtotal * (value / 100);
            } else if (type === 'fixed') {
                currentDiscount = value;
            } else {
                currentDiscount = 0;
            }

            if (currentDiscount > subtotal) currentDiscount = subtotal;
            currentTotal = subtotal - currentDiscount;

            if (currentDiscount > 0) {
                document.getElementById('discount-row').style.display = 'flex';
                document.getElementById('discount-amount').textContent = `-₱${currentDiscount.toFixed(2)}`;
            } else {
                document.getElementById('discount-row').style.display = 'none';
            }
            document.getElementById('cart-total').textContent = `₱${currentTotal.toFixed(2)}`;
            calculateChange();
        }

        // ============================================
        // UPDATE CART
        // ============================================
        function updateCart() {
            const cartBody = document.getElementById('cart-body');
            const emptyCart = document.getElementById('emptyCart');
            const cartTableWrapper = document.getElementById('cartTableWrapper');

            currentSubtotal = 0;

            if (cart.length === 0) {
                emptyCart.style.display = 'block';
                cartTableWrapper.style.display = 'none';
                document.getElementById('cart-subtotal').textContent = '₱0.00';
                document.getElementById('cart-total').textContent = '₱0.00';
                document.getElementById('discount-row').style.display = 'none';
                document.getElementById('cart-count').textContent = '0 items';
                currentSubtotal = 0;
                currentTotal = 0;
                calculateChange();
                return;
            }

            emptyCart.style.display = 'none';
            cartTableWrapper.style.display = 'block';

            cartBody.innerHTML = '';
            cart.forEach((item, index) => {
                const subtotal = item.price * item.qty;
                currentSubtotal += subtotal;
                cartBody.innerHTML += `
                        <tr data-index="${index}">
                            <td class="col-product">${escapeHtml(item.name)}</td>
                            <td class="col-price">₱${item.price.toFixed(2)}</td>
                            <td class="col-qty">
                                <div class="qty-control">
                                    <button class="qty-btn qty-minus" data-index="${index}">−</button>
                                    <span class="qty-value">${item.qty}</span>
                                    <button class="qty-btn qty-plus" data-index="${index}">+</button>
                                </div>
                            </td>
                            <td class="col-subtotal">₱${subtotal.toFixed(2)}</td>
                            <td class="col-action">
                                <button class="remove-btn" data-index="${index}" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    `;
            });

            document.querySelectorAll('.qty-minus').forEach(btn => {
                btn.addEventListener('click', () => changeQty(parseInt(btn.dataset.index), -1));
            });
            document.querySelectorAll('.qty-plus').forEach(btn => {
                btn.addEventListener('click', () => changeQty(parseInt(btn.dataset.index), 1));
            });
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', () => removeItem(parseInt(btn.dataset.index)));
            });

            document.getElementById('cart-subtotal').textContent = `₱${currentSubtotal.toFixed(2)}`;
            document.getElementById('cart-count').textContent = `${cart.length} item${cart.length !== 1 ? 's' : ''}`;
            calculateDiscount();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function changeQty(index, delta) {
            const newQty = cart[index].qty + delta;
            if (newQty < 1) {
                removeItem(index);
            } else if (newQty <= cart[index].stock) {
                cart[index].qty = newQty;
                updateCart();
            } else {
                showToast('Not enough stock!', 'error');
            }
        }

        function removeItem(index) {
            const name = cart[index].name;
            cart.splice(index, 1);
            updateCart();
            showToast(`Removed ${name}`, 'info');
        }

        // ============================================
        // CHANGE CALC
        // ============================================
        function calculateChange() {
            const tendered = parseFloat(document.getElementById('amount_tendered').value) || 0;
            const change = tendered - currentTotal;
            const changeEl = document.getElementById('change_due');
            changeEl.textContent = `₱${change >= 0 ? change.toFixed(2) : '0.00'}`;

            if (change >= 0 && tendered > 0 && currentTotal > 0) {
                changeEl.classList.add('positive');
            } else {
                changeEl.classList.remove('positive');
            }
        }

        document.getElementById('amount_tendered').addEventListener('input', calculateChange);

        // ============================================
        // CHECKOUT
        // ============================================
        document.getElementById('checkoutBtn').addEventListener('click', async () => {
            const customer = document.getElementById('customer_name').value.trim();
            const contact = document.getElementById('contact_number').value.trim();
            const tendered = parseFloat(document.getElementById('amount_tendered').value) || 0;
            const orderType = currentOrderType;
            const deliveryAddress = document.getElementById('delivery_address')?.value.trim() || '';
            const landmark = document.getElementById('landmark')?.value.trim() || '';

            if (!customer) {
                showToast('Please enter customer name', 'error');
                document.getElementById('customer_name').focus();
                return;
            }

            if (orderType === 'delivery' && !deliveryAddress) {
                showToast('Please enter delivery address', 'error');
                document.getElementById('delivery_address').focus();
                return;
            }

            if (cart.length === 0) {
                showToast('Cart is empty', 'error');
                return;
            }

            if (tendered < currentTotal) {
                showToast('Insufficient payment', 'error');
                return;
            }

            const btn = document.getElementById('checkoutBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            try {
                const res = await fetch(PLACE_ORDER_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        customer_name: customer,
                        contact_number: contact,
                        items: cart,
                        total: currentTotal,
                        subtotal: currentSubtotal,
                        discount: currentDiscount,
                        tendered: tendered,
                        change: tendered - currentTotal,
                        order_type: orderType,
                        delivery_address: deliveryAddress,
                        landmark: landmark
                    })
                });

                const data = await res.json();

                if (data.success) {
                    showToast('Order placed successfully!', 'success');
                    cart = [];
                    updateCart();
                    document.getElementById('customer_name').value = '';
                    document.getElementById('contact_number').value = '';
                    document.getElementById('amount_tendered').value = '';
                    document.getElementById('discount_type').value = 'none';
                    document.getElementById('discount_value').value = '';
                    document.getElementById('discount_value_div').style.display = 'none';
                    document.getElementById('delivery_address').value = '';
                    document.getElementById('landmark').value = '';
                    setOrderType('pickup');

                    if (data.order_id) {
                        setTimeout(() => window.location.href = '{{ url("cashier/receipt") }}/' + data.order_id, 1000);
                    } else {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    showToast('Error: ' + data.error, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Checkout';
                }
            } catch (error) {
                showToast('Network error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Checkout';
            }
        });

        // ============================================
        // KEYBOARD SHORTCUTS
        // ============================================
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                searchInput.focus();
            }

            if (e.key === 'Escape') {
                if (document.activeElement === searchInput) {
                    searchInput.value = '';
                    filterProducts();
                    searchInput.blur();
                }
            }

            if (e.key === 'Enter' && document.activeElement.id === 'amount_tendered') {
                e.preventDefault();
                document.getElementById('checkoutBtn').click();
            }

            if (e.ctrlKey && e.key === 'Backspace') {
                e.preventDefault();
                document.getElementById('clearCartBtn').click();
            }
        });

        // ============================================
        // AUTO-FOCUS
        // ============================================
        document.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth > 900) {
                searchInput.focus();
            }
        });
    </script>

    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        /* ==================================================
               WRAPPER — kumukuha ng full height ng main content
               ================================================== */
        .pos-wrapper {
            margin: -1.5rem;
            /* cancel ang main content padding */
            padding: 1rem;
            /* sariling padding */
            height: calc(100vh - 56px);
            /* fallback: 100vh - top header height */
            height: calc(100dvh - 56px);
            /* modern: 100dvh - top header height */
            overflow: hidden;
            box-sizing: border-box;
            background: #F5F1EA;
        }

        /* ==================================================
               LAYOUT
               ================================================== */
        .pos-container {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 420px;
            gap: 1.25rem;
            height: 100%;
            overflow: hidden;
        }

        /* ==================================================
               PRODUCTS PANEL
               ================================================== */
        .products-panel {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            min-height: 0;
        }

        .panel-header {
            background: #FDF8F0;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .header-title i {
            color: #576238;
            font-size: 1.1rem;
        }

        .header-title h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .header-count {
            background: #F0EADC;
            color: #576238;
            padding: 0.15rem 0.55rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 320px;
            min-width: 180px;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9E9D97;
            font-size: 0.8rem;
            pointer-events: none;
        }

        .product-search {
            width: 100%;
            padding: 0.55rem 2.5rem 0.55rem 2.25rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-family: inherit;
            color: #2C2B26;
            background: white;
            transition: all 0.2s ease;
        }

        .product-search:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .product-search::placeholder {
            color: #C4C3BC;
        }

        .search-hint {
            position: absolute;
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            background: #F0EADC;
            color: #9E9D97;
            padding: 0.15rem 0.45rem;
            border-radius: 0.3rem;
            font-size: 0.65rem;
            font-family: 'SF Mono', monospace;
            font-weight: 600;
            border: 1px solid #E3DCD0;
            pointer-events: none;
        }

        .category-tabs {
            display: flex;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #F0EADC;
            background: white;
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            flex-shrink: 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch;
        }

        .category-tabs::-webkit-scrollbar {
            display: none;
        }

        .cat-btn {
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            padding: 0.4rem 0.95rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #576238;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-family: inherit;
        }

        .cat-btn:hover {
            background: #E3DCD0;
            transform: translateY(-1px);
        }

        .cat-btn.active {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .products-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
            gap: 0.75rem;
            padding: 1rem;
            overflow-y: auto;
            align-content: start;
            min-height: 0;
        }

        .products-grid::-webkit-scrollbar {
            width: 6px;
        }

        .products-grid::-webkit-scrollbar-track {
            background: #FDF8F0;
        }

        .products-grid::-webkit-scrollbar-thumb {
            background: #E3DCD0;
            border-radius: 3px;
        }

        .products-grid::-webkit-scrollbar-thumb:hover {
            background: #C4C3BC;
        }

        .product-card {
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
            border-radius: 0.6rem;
            padding: 0.875rem 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            user-select: none;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100px;
        }

        .product-card:hover {
            background: #576238;
            border-color: #576238;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.2);
        }

        .product-card:hover .product-name,
        .product-card:hover .product-price,
        .product-card:hover .product-stock {
            color: white;
        }

        .product-card:hover .product-stock.stock-low {
            color: #F5C88A;
        }

        .product-card.added {
            animation: addPulse 0.3s ease;
        }

        @keyframes addPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
                background: #7A8B4F;
            }

            100% {
                transform: scale(1);
            }
        }

        .product-name {
            font-weight: 600;
            font-size: 0.82rem;
            color: #2C2B26;
            margin-bottom: 0.4rem;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-weight: 700;
            font-size: 1rem;
            color: #576238;
            margin-bottom: 0.3rem;
            font-family: 'Playfair Display', serif;
        }

        .product-stock {
            font-size: 0.65rem;
            color: #9E9D97;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            font-weight: 500;
        }

        .product-stock.stock-low {
            color: #D4A054;
            font-weight: 600;
        }

        .no-results {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #9E9D97;
            padding: 2rem;
        }

        .no-results i {
            font-size: 3rem;
            color: #D4C9BD;
            margin-bottom: 0.75rem;
            display: block;
        }

        .no-results p {
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .no-results small {
            color: #C4C3BC;
            font-size: 0.75rem;
        }

        /* ==================================================
               CART PANEL
               ================================================== */
        .cart-panel {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }

        .cart-header {
            background: #FDF8F0;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .cart-header-left {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .cart-header-left i {
            color: #576238;
            font-size: 1rem;
        }

        .cart-header-left h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .cart-badge {
            background: #F0EADC;
            color: #576238;
            padding: 0.15rem 0.55rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .clear-cart-btn {
            background: none;
            border: none;
            color: #C5705A;
            cursor: pointer;
            padding: 0.35rem 0.55rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }

        .clear-cart-btn:hover {
            background: #FEF0ED;
        }

        .cart-items {
            flex: 1 1 auto;
            overflow-y: auto;
            min-height: 100px;
        }

        .cart-items::-webkit-scrollbar {
            width: 6px;
        }

        .cart-items::-webkit-scrollbar-track {
            background: #FDF8F0;
        }

        .cart-items::-webkit-scrollbar-thumb {
            background: #E3DCD0;
            border-radius: 3px;
        }

        .empty-cart-state {
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .empty-cart-icon {
            width: 72px;
            height: 72px;
            background: #F0EADC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .empty-cart-icon i {
            font-size: 1.75rem;
            color: #C4C3BC;
        }

        .empty-cart-state p {
            color: #6B6A65;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .empty-cart-state small {
            color: #C4C3BC;
            font-size: 0.75rem;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .cart-table th,
        .cart-table td {
            padding: 0.7rem 0.6rem;
            text-align: left;
            border-bottom: 1px solid #F0EADC;
        }

        .cart-table th {
            background: #FDF8F0;
            font-weight: 600;
            color: #576238;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .cart-table td {
            color: #2C2B26;
            vertical-align: middle;
        }

        .col-product {
            width: auto;
            font-weight: 500;
        }

        .col-price {
            width: 65px;
            font-size: 0.75rem;
            color: #9E9D97;
        }

        .col-qty {
            width: 95px;
        }

        .col-subtotal {
            width: 80px;
            font-weight: 600;
            color: #576238;
        }

        .col-action {
            width: 32px;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            justify-content: center;
        }

        .qty-btn {
            width: 22px;
            height: 22px;
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.3rem;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            color: #576238;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-family: inherit;
            line-height: 1;
        }

        .qty-btn:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .qty-value {
            font-weight: 600;
            min-width: 22px;
            text-align: center;
            font-size: 0.8rem;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #C5705A;
            cursor: pointer;
            padding: 0.2rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
            font-size: 0.75rem;
        }

        .remove-btn:hover {
            background: #FEF0ED;
        }

        .cart-summary {
            padding: 0.75rem 1rem;
            background: #FDF8F0;
            border-top: 1px solid #E3DCD0;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            flex-shrink: 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
        }

        .summary-label {
            color: #6B6A65;
            font-weight: 500;
        }

        .summary-value {
            font-weight: 600;
            color: #2C2B26;
        }

        .discount-value {
            color: #C5705A;
        }

        .total-row-summary {
            padding-top: 0.5rem;
            border-top: 1px dashed #E3DCD0;
            margin-top: 0.25rem;
        }

        .total-row-summary .summary-label {
            font-weight: 700;
            color: #2C2B26;
            font-size: 0.9rem;
        }

        .total-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #576238;
            font-family: 'Playfair Display', serif;
        }

        .cart-actions {
            padding: 0.875rem 1rem 1rem;
            border-top: 1px solid #E3DCD0;
            background: white;
            flex-shrink: 0;
            flex-grow: 1;
            overflow-y: auto;
            max-height: 50vh;
        }

        .cart-actions::-webkit-scrollbar {
            width: 6px;
        }

        .cart-actions::-webkit-scrollbar-thumb {
            background: #E3DCD0;
            border-radius: 3px;
        }

        .form-section {
            margin-bottom: 0.875rem;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .form-section-header {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.68rem;
            font-weight: 700;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .form-section-header i {
            color: #576238;
            font-size: 0.7rem;
        }

        .form-section-header .senior-btn {
            margin-left: auto;
        }

        .form-input {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.82rem;
            font-family: inherit;
            color: #2C2B26;
            background: white;
            transition: all 0.2s ease;
            margin-bottom: 0.4rem;
        }

        .form-input:last-child {
            margin-bottom: 0;
        }

        .form-input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .form-input::placeholder {
            color: #C4C3BC;
        }

        .order-type-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.4rem;
        }

        .order-type-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.55rem;
            background: #F0EADC;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.78rem;
            font-weight: 500;
            color: #576238;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .order-type-btn.active {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .order-type-btn:hover:not(.active) {
            background: #E3DCD0;
        }

        .delivery-field {
            margin-top: 0.5rem;
        }

        .delivery-field label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #6B6A65;
            margin-bottom: 0.3rem;
        }

        .required {
            color: #C5705A;
        }

        .delivery-field textarea,
        .delivery-field input {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.78rem;
            font-family: inherit;
            resize: vertical;
            color: #2C2B26;
        }

        .delivery-field textarea:focus,
        .delivery-field input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .senior-btn {
            background: #FEF5E8;
            border: 1px solid #F8E5C5;
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.62rem;
            font-weight: 600;
            color: #B8893A;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-family: inherit;
        }

        .senior-btn:hover {
            background: #D4A054;
            color: white;
            border-color: #D4A054;
        }

        .payment-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .payment-label {
            font-size: 0.78rem;
            color: #2C2B26;
            font-weight: 600;
            flex-shrink: 0;
        }

        .payment-input {
            flex: 1;
            margin-bottom: 0;
        }

        .change-row {
            margin-top: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0.75rem;
            background: #FDF8F0;
            border-radius: 0.5rem;
        }

        .change-label {
            font-size: 0.78rem;
            color: #6B6A65;
            font-weight: 500;
        }

        .change-amount {
            font-size: 1.05rem;
            color: #576238;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
        }

        .change-amount.positive {
            color: #4ADE80;
        }

        .checkout-btn {
            width: 100%;
            background: #576238;
            color: white;
            border: none;
            padding: 0.85rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.75rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: inherit;
        }

        .checkout-btn:hover {
            background: #3E4A28;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.3);
        }

        .checkout-btn:disabled {
            background: #9E9D97;
            cursor: not-allowed;
            transform: none;
        }

        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }

        .toast {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            min-width: 200px;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
            border-left: 4px solid #576238;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toast-success {
            border-left-color: #576238;
            color: #576238;
        }

        .toast-success i {
            color: #576238;
        }

        .toast-error {
            border-left-color: #C5705A;
            color: #C5705A;
        }

        .toast-error i {
            color: #C5705A;
        }

        .toast-info {
            border-left-color: #D4A054;
            color: #D4A054;
        }

        .toast-info i {
            color: #D4A054;
        }

        /* ==================================================
               RESPONSIVE
               ================================================== */

        @media (max-width: 1200px) {
            .pos-container {
                grid-template-columns: minmax(0, 1fr) 380px;
                gap: 1rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 0.65rem;
                padding: 0.875rem;
            }

            .product-name {
                font-size: 0.78rem;
            }

            .product-price {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 900px) {
            .pos-wrapper {
                height: auto;
                min-height: calc(100vh - 56px);
                min-height: calc(100dvh - 56px);
                overflow: visible;
                margin: -1.5rem;
                padding: 1rem;
            }

            .pos-container {
                grid-template-columns: 1fr;
                gap: 1rem;
                height: auto;
                overflow: visible;
            }

            .products-panel {
                max-height: 55vh;
                min-height: 400px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 0.6rem;
                padding: 0.875rem;
            }

            .cart-panel {
                min-height: auto;
                max-height: none;
            }

            .cart-items {
                max-height: 300px;
            }

            .cart-actions {
                max-height: none;
                overflow: visible;
            }

            .panel-header {
                padding: 0.75rem 0.875rem;
                gap: 0.6rem;
            }

            .header-title h3 {
                font-size: 0.88rem;
            }

            .search-box {
                max-width: 100%;
                flex: 1 0 100%;
                order: 3;
            }

            .category-tabs {
                padding: 0.6rem 0.875rem;
                gap: 0.4rem;
            }

            .cat-btn {
                padding: 0.35rem 0.8rem;
                font-size: 0.72rem;
            }
        }

        @media (max-width: 640px) {
            .pos-wrapper {
                margin: -1rem;
                padding: 0.75rem;
            }

            .pos-container {
                gap: 0.75rem;
            }

            .products-panel {
                max-height: 50vh;
                min-height: 320px;
                border-radius: 0.6rem;
            }

            .panel-header {
                padding: 0.65rem 0.75rem;
                gap: 0.5rem;
            }

            .header-title {
                gap: 0.4rem;
            }

            .header-title i {
                font-size: 0.95rem;
            }

            .header-title h3 {
                font-size: 0.82rem;
            }

            .header-count {
                font-size: 0.62rem;
                padding: 0.12rem 0.45rem;
            }

            .product-search {
                font-size: 0.75rem;
                padding: 0.5rem 2.25rem 0.5rem 2rem;
            }

            .search-hint {
                display: none;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(115px, 1fr));
                gap: 0.5rem;
                padding: 0.65rem;
            }

            .product-card {
                padding: 0.65rem 0.5rem;
                min-height: 90px;
                border-radius: 0.5rem;
            }

            .product-name {
                font-size: 0.72rem;
                line-height: 1.3;
                margin-bottom: 0.3rem;
            }

            .product-price {
                font-size: 0.85rem;
                margin-bottom: 0.2rem;
            }

            .product-stock {
                font-size: 0.58rem;
            }

            .cart-header {
                padding: 0.75rem 0.875rem;
            }

            .cart-header-left h3 {
                font-size: 0.82rem;
            }

            .cart-header-left i {
                font-size: 0.9rem;
            }

            .cart-badge {
                font-size: 0.6rem;
            }

            .empty-cart-state {
                padding: 2rem 1rem;
            }

            .empty-cart-icon {
                width: 60px;
                height: 60px;
            }

            .empty-cart-icon i {
                font-size: 1.4rem;
            }

            .cart-table th,
            .cart-table td {
                padding: 0.5rem 0.4rem;
                font-size: 0.72rem;
            }

            .cart-table th {
                font-size: 0.58rem;
            }

            .col-price {
                width: 55px;
                font-size: 0.7rem;
            }

            .col-qty {
                width: 80px;
            }

            .col-subtotal {
                width: 68px;
                font-size: 0.72rem;
            }

            .col-action {
                width: 26px;
            }

            .qty-btn {
                width: 20px;
                height: 20px;
                font-size: 0.75rem;
            }

            .qty-value {
                min-width: 18px;
                font-size: 0.72rem;
            }

            .cart-summary {
                padding: 0.6rem 0.875rem;
            }

            .summary-row {
                font-size: 0.75rem;
            }

            .total-value {
                font-size: 1.05rem;
            }

            .cart-actions {
                padding: 0.75rem 0.875rem 1rem;
            }

            .form-section {
                margin-bottom: 0.7rem;
            }

            .form-section-header {
                font-size: 0.62rem;
                margin-bottom: 0.4rem;
            }

            .form-input {
                font-size: 0.78rem;
                padding: 0.5rem 0.65rem;
                margin-bottom: 0.35rem;
            }

            .order-type-btn {
                font-size: 0.72rem;
                padding: 0.5rem;
            }

            .payment-label {
                font-size: 0.72rem;
            }

            .change-amount {
                font-size: 0.95rem;
            }

            .checkout-btn {
                font-size: 0.82rem;
                padding: 0.75rem;
                margin-top: 0.6rem;
            }

            .toast-container {
                top: 0.75rem;
                right: 0.75rem;
                left: 0.75rem;
            }

            .toast {
                min-width: auto;
                font-size: 0.75rem;
                padding: 0.65rem 0.85rem;
            }
        }

        @media (max-width: 400px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 0.4rem;
                padding: 0.5rem;
            }

            .product-card {
                padding: 0.55rem 0.4rem;
                min-height: 80px;
            }

            .product-name {
                font-size: 0.68rem;
            }

            .product-price {
                font-size: 0.78rem;
            }

            .product-stock {
                font-size: 0.55rem;
            }

            .cat-btn {
                padding: 0.3rem 0.7rem;
                font-size: 0.68rem;
            }

            .cart-table th,
            .cart-table td {
                padding: 0.4rem 0.3rem;
                font-size: 0.68rem;
            }

            .cart-table th {
                font-size: 0.55rem;
            }

            .col-price {
                width: 50px;
                font-size: 0.65rem;
            }

            .col-qty {
                width: 72px;
            }

            .col-subtotal {
                width: 60px;
                font-size: 0.68rem;
            }

            .qty-btn {
                width: 18px;
                height: 18px;
                font-size: 0.7rem;
            }

            .qty-value {
                min-width: 16px;
                font-size: 0.68rem;
            }
        }
    </style>
@endsection