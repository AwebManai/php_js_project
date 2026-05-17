<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StyleStore - Clothes Shop</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-links a, .user-menu a {
            color: #1f2937;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.3s;
            padding: 6px 12px;
            border-radius: 4px;
        }

        .nav-links a {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            font-weight: 600;
        }

        .nav-links a:hover, .user-menu a:hover {
            opacity: 0.8;
        }

        .brand-link {
            text-decoration: none;
            color: #1f2937;
        }

        .user-info {
            color: white;
            font-size: 14px;
        }

        .user-menu a.btn-logout {
            background: #ff4444;
            cursor: pointer;
        }

        .user-menu a.btn-logout:hover {
            background: #cc0000;
        }

        .user-menu a {
            display: inline-block;
        }

        .cart-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .buy-btn {
            border: 1px solid #16a34a;
            background: #22c55e;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        .buy-btn:disabled {
            background: #9ca3af;
            border-color: #9ca3af;
            cursor: not-allowed;
        }

        .stock {
            color: #4b5563;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .stock.out {
            color: #dc2626;
            font-weight: 700;
        }

        /* Filter Section Styles */
        .filter-section {
            margin: 20px 0;
            padding: 16px;
            background: #f3f4f6;
            border-radius: 8px;
            margin-top: 16px;
            margin-bottom: 24px;
        }

        .filter-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            color: #1f2937;
            min-width: 200px;
            font-family: inherit;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .reset-btn {
            padding: 8px 16px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            font-size: 14px;
        }

        .reset-btn:hover {
            background: #dc2626;
        }

        /* Enhanced Product Card Styles */
        .product {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .product .content {
            padding: 12px;
            background: white;
        }

        .product .category-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .product .name {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .product .description {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product .price {
            font-size: 18px;
            font-weight: 700;
            color: #22c55e;
            margin-bottom: 8px;
        }

        .product button {
            width: 100%;
            padding: 8px;
            border: none;
            background: #22c55e;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .product button:hover:not(:disabled) {
            background: #16a34a;
        }

        .product button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .sale-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #fbbf24;
            color: #92400e;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            z-index: 10;
        }

        .sale-price-section {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
        }

        .original-price {
            font-size: 14px;
            color: #9ca3af;
            text-decoration: line-through;
        }

        .sale-discount {
            color: #dc2626;
            font-weight: 700;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select,
            .filter-group input {
                min-width: 100%;
            }

            .reset-btn {
                width: 100%;
            }
        }

        /* Quantity Picker Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .modal-close:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .modal-body {
            padding: 24px;
        }

        #modalProductName {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .quantity-controls {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .quantity-controls label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .quantity-selector {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .qty-btn {
            background: #e5e7eb;
            border: 1px solid #d1d5db;
            color: #374151;
            width: 40px;
            height: 40px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            transition: background 0.2s, border-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: #d1d5db;
            border-color: #9ca3af;
        }

        #quantityInput {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            text-align: center;
            font-weight: 600;
            color: #1f2937;
        }

        #quantityInput:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .stock-info {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            justify-content: flex-end;
        }

        .btn-cancel, .btn-confirm {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 14px;
        }

        .btn-cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-cancel:hover {
            background: #d1d5db;
        }

        .btn-confirm {
            background: #22c55e;
            color: white;
        }

        .btn-confirm:hover {
            background: #16a34a;
        }

        /* View Cart Button */
        .view-cart-btn {
            border: 1px solid #3b82f6;
            background: #3b82f6;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            transition: background 0.3s;
        }

        .view-cart-btn:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        /* Cart Modal Styles */
        .cart-modal {
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
        }

        .cart-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .empty-cart-message {
            text-align: center;
            color: #6b7280;
            font-size: 16px;
            padding: 40px 20px;
        }

        .cart-items-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            gap: 12px;
        }

        .cart-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .cart-item-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .cart-item-details {
            font-size: 12px;
            color: #6b7280;
        }

        .cart-item-qty-control {
            display: flex;
            align-items: center;
            gap: 6px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 4px;
        }

        .cart-item-qty-control button {
            background: none;
            border: none;
            color: #374151;
            cursor: pointer;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .cart-item-qty-control button:hover {
            background: #f3f4f6;
            border-radius: 2px;
        }

        .cart-item-qty {
            width: 30px;
            text-align: center;
            font-weight: 600;
            color: #1f2937;
            font-size: 13px;
        }

        .cart-item-price {
            font-weight: 600;
            color: #22c55e;
            min-width: 80px;
            text-align: right;
            font-size: 14px;
        }

        .cart-item-remove {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .cart-item-remove:hover {
            background: #fecaca;
        }

        .cart-summary {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #374151;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .cart-modal {
                width: 95%;
                max-height: 90vh;
            }

            .cart-item {
                flex-wrap: wrap;
            }

            .cart-item-price {
                width: 100%;
                text-align: left;
                margin-top: 8px;
            }
        }

        /* Product Details Modal Styles */
        .product-details-modal {
            width: 90%;
            max-width: 700px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .product-details-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .product-image-section {
            width: 100%;
            max-height: 300px;
            overflow: hidden;
            border-radius: 8px;
        }

        .product-image-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .product-details-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .product-details-category {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            width: fit-content;
        }

        .product-details-price-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-details-price {
            font-size: 28px;
            font-weight: 700;
            color: #22c55e;
        }

        .product-details-original-price {
            font-size: 20px;
            color: #9ca3af;
            text-decoration: line-through;
        }

        .product-details-discount {
            background: #fbbf24;
            color: #92400e;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
        }

        .product-details-description {
            font-size: 15px;
            color: #374151;
            line-height: 1.6;
            border: 1px solid #e5e7eb;
            padding: 16px;
            border-radius: 6px;
            background: #f9fafb;
        }

        .product-details-stock {
            font-size: 14px;
            font-weight: 600;
            padding: 12px;
            border-radius: 6px;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #22c55e;
        }

        .product-details-stock.out-of-stock {
            background: #fef2f2;
            color: #991b1b;
            border-color: #dc2626;
        }

        .product-details-footer {
            display: flex;
            gap: 12px;
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            justify-content: flex-end;
        }

        .btn-add-to-cart {
            background: #22c55e;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
            flex: 1;
        }

        .btn-add-to-cart:hover:not(:disabled) {
            background: #16a34a;
        }

        .btn-add-to-cart:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header>
        <div class="topbar">
            <a href="index.php" class="brand brand-link">StyleStore</a>
            <div style="display: flex; gap: 30px; align-items: center;">
                <div class="cart-wrap">
                    <div class="cart">Cart: <span id="cartCount">0</span> item(s)</div>
                    <button id="viewCartBtn" class="view-cart-btn" type="button">View Cart</button>
                    <button id="buyNowBtn" class="buy-btn" type="button" disabled>Buy Now</button>
                </div>
                <div id="authLinks">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-menu" style="display: flex; gap: 15px; align-items: center;">
                            <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</span>
                            <a href="purchases.php">My Purchases</a>
                            <a href="api/logout.php" class="btn-logout">Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="nav-links">
                            <a href="login.html">Log In</a>
                            <a href="signup.html">Sign Up</a>
                            <a href="admin_login.php">Admin Sign In</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main>
        <h1>Shop Clothes</h1>
        <div class="subtitle">Modern essentials for everyday style.</div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-container">
                <div class="filter-group">
                    <label for="categoryFilter">Filter by Category:</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="searchInput">Search:</label>
                    <input type="text" id="searchInput" placeholder="Search products..." />
                </div>
                <button id="resetFilters" class="reset-btn">Reset Filters</button>
            </div>
        </div>

        <div id="status" class="status">Loading products...</div>
        <section id="productGrid" class="grid"></section>
    </main>

    <!-- Product Details Modal -->
    <div id="productDetailsModal" class="modal" style="display: none;">
        <div class="modal-content product-details-modal">
            <div class="modal-header">
                <h2>Product Details</h2>
                <button class="modal-close" id="detailsModalClose">&times;</button>
            </div>
            <div class="modal-body product-details-body">
                <div class="product-image-section">
                    <img id="detailsProductImage" src="" alt="Product">
                </div>
                <div class="product-info-section">
                    <div class="product-details-category" id="detailsProductCategory"></div>
                    <h3 class="product-details-title" id="detailsProductTitle"></h3>
                    <div class="product-details-price-section" id="detailsPriceSection"></div>
                    <div class="product-details-description" id="detailsProductDescription"></div>
                    <div class="product-details-stock" id="detailsProductStock"></div>
                    <div class="quantity-controls">
                        <label for="detailsQuantityInput">Quantity to Buy:</label>
                        <div class="quantity-selector">
                            <button id="detailsDecreaseBtn" class="qty-btn">−</button>
                            <input type="number" id="detailsQuantityInput" min="1" max="100" value="1" />
                            <button id="detailsIncreaseBtn" class="qty-btn">+</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product-details-footer">
                <button id="detailsModalCancel" class="btn-cancel">Close</button>
                <button id="detailsAddToCartBtn" class="btn-add-to-cart" type="button">Add to Cart</button>
            </div>
        </div>
    </div>

    <!-- Quantity Picker Modal -->
    <div id="quantityModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Select Quantity</h2>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalProductName"></p>
                <div class="quantity-controls">
                    <label for="quantityInput">Quantity:</label>
                    <div class="quantity-selector">
                        <button id="decreaseBtn" class="qty-btn">−</button>
                        <input type="number" id="quantityInput" min="1" max="100" value="1" />
                        <button id="increaseBtn" class="qty-btn">+</button>
                    </div>
                    <p id="availableStock" class="stock-info"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button id="modalCancel" class="btn-cancel">Cancel</button>
                <button id="modalConfirm" class="btn-confirm">Add to Cart</button>
            </div>
        </div>
    </div>

    <!-- Cart Items View Modal -->
    <div id="cartModal" class="modal" style="display: none;">
        <div class="modal-content cart-modal">
            <div class="modal-header">
                <h2>Shopping Cart</h2>
                <button class="modal-close" id="cartModalClose">&times;</button>
            </div>
            <div class="modal-body cart-body">
                <div id="cartItemsList" class="cart-items-list">
                </div>
                <div id="emptyCartMessage" class="empty-cart-message">
                    Your cart is empty.
                </div>
            </div>
            <div id="cartSummary" class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotalPrice">$0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="totalPrice">$0.00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button id="cartModalClose2" class="btn-cancel">Continue Shopping</button>
                <button id="cartCheckoutBtn" class="btn-confirm" disabled>Proceed to Checkout</button>
            </div>
        </div>
    </div>

    <footer>© 2026 StyleStore. All rights reserved.</footer>

    <script>
        let cartCount = 0;
        const cart = {};
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        let allProducts = [];
        let allCategories = [];
        let currentFilter = {
            category: '',
            search: ''
        };
        let selectedProduct = null;

        function updateCartUi() {
            document.getElementById("cartCount").textContent = cartCount;
            document.getElementById("buyNowBtn").disabled = cartCount === 0;
            updateCartView();
        }

        function getProductPrice(productId) {
            const product = allProducts.find(p => String(p.id) === String(productId));
            if (!product) return 0;
            const basePrice = parseFloat(product.price) || 0;
            const discount = Number(product.discount_percentage) || 0;
            return basePrice * (1 - discount / 100);
        }

        function getProductName(productId) {
            const product = allProducts.find(p => String(p.id) === String(productId));
            return product ? product.name : 'Unknown Product';
        }

        function updateCartView() {
            const cartItemsList = document.getElementById("cartItemsList");
            const emptyMessage = document.getElementById("emptyCartMessage");
            const cartSummary = document.getElementById("cartSummary");
            const checkoutBtn = document.getElementById("cartCheckoutBtn");

            if (Object.keys(cart).length === 0) {
                cartItemsList.style.display = "none";
                emptyMessage.style.display = "block";
                cartSummary.style.display = "none";
                checkoutBtn.disabled = true;
                return;
            }

            cartItemsList.style.display = "flex";
            emptyMessage.style.display = "none";
            cartSummary.style.display = "block";
            checkoutBtn.disabled = false;

            cartItemsList.innerHTML = "";
            let subtotal = 0;

            Object.entries(cart).forEach(([productId, quantity]) => {
                const product = allProducts.find(p => String(p.id) === String(productId));
                const productName = getProductName(productId);
                const basePrice = product ? parseFloat(product.price) || 0 : 0;
                const discountPercent = product ? (Number(product.discount_percentage) || 0) : 0;
                const unitPrice = basePrice * (1 - discountPercent / 100);
                const lineTotal = unitPrice * quantity;
                subtotal += lineTotal;

                const itemEl = document.createElement("div");
                itemEl.className = "cart-item";
                itemEl.innerHTML = `
                    <div class="cart-item-info">
                        <div class="cart-item-name">${productName}</div>
                        <div class="cart-item-details">
                            ${discountPercent > 0 ? 
                                `<span style="text-decoration: line-through; color: #9ca3af;">$${basePrice.toFixed(2)}</span> 
                                 <span style="color: #dc2626; font-weight: 600;">$${unitPrice.toFixed(2)}</span> 
                                 <span style="color: #22c55e; font-weight: 600;">-${discountPercent.toFixed(1)}%</span>` :
                                `Price: $${unitPrice.toFixed(2)}`
                            }
                        </div>
                    </div>
                    <div class="cart-item-qty-control">
                        <button data-product="${productId}" class="qty-decrease">−</button>
                        <span class="cart-item-qty">${quantity}</span>
                        <button data-product="${productId}" class="qty-increase">+</button>
                    </div>
                    <div class="cart-item-price">$${lineTotal.toFixed(2)}</div>
                    <button data-product="${productId}" class="cart-item-remove">Remove</button>
                `;

                itemEl.querySelector(".qty-decrease").addEventListener("click", () => adjustQuantity(productId, -1));
                itemEl.querySelector(".qty-increase").addEventListener("click", () => adjustQuantity(productId, 1));
                itemEl.querySelector(".cart-item-remove").addEventListener("click", () => removeFromCart(productId));

                cartItemsList.appendChild(itemEl);
            });

            document.getElementById("subtotalPrice").textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById("totalPrice").textContent = `$${subtotal.toFixed(2)}`;
        }

        function openCartModal() {
            updateCartView();
            document.getElementById("cartModal").style.display = "flex";
        }

        function closeCartModal() {
            document.getElementById("cartModal").style.display = "none";
        }

        function adjustQuantity(productId, change) {
            const product = allProducts.find(p => String(p.id) === String(productId));
            if (!product) return;

            const currentQty = cart[productId] || 0;
            const newQty = currentQty + change;
            const maxAllowed = product.stock;

            if (newQty <= 0) {
                removeFromCart(productId);
                return;
            }

            if (newQty > maxAllowed) {
                alert(`Only ${maxAllowed} items available in stock.`);
                return;
            }

            const difference = newQty - currentQty;
            cart[productId] = newQty;
            cartCount += difference;
            updateCartUi();
        }

        function removeFromCart(productId) {
            if (!cart[productId]) return;
            cartCount -= cart[productId];
            delete cart[productId];
            updateCartUi();
        }

        function openQuantityModal(product) {
            selectedProduct = product;
            const modal = document.getElementById("quantityModal");
            const quantityInput = document.getElementById("quantityInput");
            const modalProductName = document.getElementById("modalProductName");
            const availableStock = document.getElementById("availableStock");

            const basePrice = parseFloat(product.price);
            const discountPercent = Number(product.discount_percentage) || 0;
            const salePrice = basePrice * (1 - discountPercent / 100);
            const hasSale = discountPercent > 0;

            quantityInput.value = 1;
            quantityInput.max = product.stock;
            
            let priceHTML = `Product: ${product.name} - `;
            if (hasSale) {
                priceHTML += `<span style="text-decoration: line-through; color: #9ca3af;">$${basePrice.toFixed(2)}</span> `;
                priceHTML += `<span style="color: #dc2626; font-weight: 600;">$${salePrice.toFixed(2)}</span> `;
                priceHTML += `<span style="color: #22c55e; font-weight: 600;">-${discountPercent.toFixed(1)}%</span>`;
            } else {
                priceHTML += `$${basePrice.toFixed(2)}`;
            }
            
            modalProductName.innerHTML = priceHTML;
            availableStock.textContent = `Available stock: ${product.stock}`;
            modal.style.display = "flex";
        }

        function closeQuantityModal() {
            document.getElementById("quantityModal").style.display = "none";
            selectedProduct = null;
        }

        function confirmAddToCart() {
            if (!selectedProduct) return;

            const quantity = parseInt(document.getElementById("quantityInput").value) || 1;
            const maxAllowed = selectedProduct.stock - (cart[selectedProduct.id] || 0);

            if (quantity > maxAllowed) {
                alert(`Only ${maxAllowed} item(s) available.`);
                return;
            }

            cart[selectedProduct.id] = (cart[selectedProduct.id] || 0) + quantity;
            cartCount += quantity;
            updateCartUi();
            closeQuantityModal();
        }

        function addToCart(product) {
            if (!isLoggedIn) {
                alert("Please log in first to buy products.");
                return;
            }

            const inCart = cart[product.id] || 0;
            if (inCart >= Number(product.stock)) {
                alert("No more stock available for this item.");
                return;
            }

            openQuantityModal(product);
        }

        function viewProductDetails(product) {
            if (!isLoggedIn) {
                alert("Please log in first to view details.");
                return;
            }

            selectedProduct = product;
            const modal = document.getElementById("productDetailsModal");
            const stock = Number(product.stock) || 0;
            const outOfStock = stock <= 0;
            const basePrice = parseFloat(product.price);
            const discountPercent = Number(product.discount_percentage) || 0;
            const salePrice = basePrice * (1 - discountPercent / 100);
            const hasSale = discountPercent > 0;

            // Set product image
            document.getElementById("detailsProductImage").src = product.image;
            document.getElementById("detailsProductImage").alt = product.name;

            // Set product category
            document.getElementById("detailsProductCategory").textContent = product.category || 'Uncategorized';

            // Set product title
            document.getElementById("detailsProductTitle").textContent = product.name;

            // Set product price
            const priceSection = document.getElementById("detailsPriceSection");
            if (hasSale) {
                priceSection.innerHTML = `
                    <span class="product-details-original-price">$${basePrice.toFixed(2)}</span>
                    <span class="product-details-price">$${salePrice.toFixed(2)}</span>
                    <span class="product-details-discount">-${discountPercent.toFixed(1)}%</span>
                `;
            } else {
                priceSection.innerHTML = `<span class="product-details-price">$${basePrice.toFixed(2)}</span>`;
            }

            // Set product description
            document.getElementById("detailsProductDescription").textContent = product.description || 'No description available';

            // Set stock info
            const stockElement = document.getElementById("detailsProductStock");
            if (outOfStock) {
                stockElement.className = "product-details-stock out-of-stock";
                stockElement.textContent = "Out of Stock";
            } else {
                stockElement.className = "product-details-stock";
                stockElement.textContent = `In stock: ${stock} item(s) available`;
            }

            // Set quantity input max value
            const quantityInput = document.getElementById("detailsQuantityInput");
            quantityInput.max = stock;
            quantityInput.value = 1;

            // Enable/disable add to cart button
            const addToCartBtn = document.getElementById("detailsAddToCartBtn");
            addToCartBtn.disabled = outOfStock;

            modal.style.display = "flex";
        }

        function closeProductDetailsModal() {
            document.getElementById("productDetailsModal").style.display = "none";
            selectedProduct = null;
        }

        function createProductCard(product) {
            const card = document.createElement("article");
            card.className = "product";

            const stock = Number(product.stock) || 0;
            const outOfStock = stock <= 0;
            const description = product.description || 'No description available';
            const category = product.category || 'Uncategorized';
            const hasSale = product.discount_percentage > 0;
            const discountPercent = Number(product.discount_percentage) || 0;
            const originalPrice = Number(product.price) || 0;
            const salePrice = originalPrice * (1 - discountPercent / 100);

            card.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                ${hasSale ? `<div class="sale-badge">-${discountPercent.toFixed(1)}%</div>` : ''}
                <div class="content">
                    <span class="category-badge">${category}</span>
                    <div class="name">${product.name}</div>
                    <div class="description">${description}</div>
                    ${hasSale ? `
                        <div class="sale-price-section">
                            <span class="original-price">$${originalPrice.toFixed(2)}</span>
                            <span class="sale-discount">$${salePrice.toFixed(2)}</span>
                        </div>
                    ` : `
                        <div class="price">$${originalPrice.toFixed(2)}</div>
                    `}
                    <div class="stock ${outOfStock ? 'out' : ''}">
                        ${outOfStock ? 'Out of Stock' : `In stock: ${stock}`}
                    </div>
                    <button type="button" ${outOfStock ? 'disabled' : ''}>View Details</button>
                </div>
            `;

            const button = card.querySelector("button");
            button.addEventListener("click", () => viewProductDetails(product));

            return card;
        }

        function populateCategoryFilter(categories) {
            const select = document.getElementById("categoryFilter");
            categories.forEach(category => {
                const option = document.createElement("option");
                option.value = category;
                option.textContent = category;
                select.appendChild(option);
            });
        }

        function filterProducts() {
            let filtered = allProducts;

            // Filter by category
            if (currentFilter.category) {
                filtered = filtered.filter(p => p.category === currentFilter.category);
            }

            // Filter by search
            if (currentFilter.search) {
                const search = currentFilter.search.toLowerCase();
                filtered = filtered.filter(p => 
                    p.name.toLowerCase().includes(search) || 
                    (p.description && p.description.toLowerCase().includes(search))
                );
            }

            displayProducts(filtered);
        }

        function displayProducts(products) {
            const gridEl = document.getElementById("productGrid");
            const statusEl = document.getElementById("status");
            
            gridEl.innerHTML = "";

            if (products.length === 0) {
                statusEl.textContent = "No products found.";
                return;
            }

            products.forEach((product) => {
                gridEl.appendChild(createProductCard(product));
            });

            statusEl.textContent = `Showing ${products.length} product(s).`;
        }

        async function checkout() {
            if (!isLoggedIn) {
                alert("Please log in first.");
                return;
            }

            if (cartCount === 0) {
                alert("Your cart is empty.");
                return;
            }

            const items = Object.entries(cart).map(([product_id, quantity]) => ({
                product_id: Number(product_id),
                quantity: Number(quantity)
            }));

            const buyBtn = document.getElementById("buyNowBtn");
            buyBtn.disabled = true;
            buyBtn.textContent = "Buying...";

            try {
                const response = await fetch("api/checkout.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ items })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Checkout failed");
                }

                alert("Purchase successful. Stock updated.");

                Object.keys(cart).forEach((key) => delete cart[key]);
                cartCount = 0;
                updateCartUi();
                closeCartModal();
                loadProducts();
            } catch (error) {
                alert(error.message || "Checkout failed.");
                updateCartUi();
            } finally {
                buyBtn.textContent = "Buy Now";
            }
        }

        async function loadProducts() {
            const statusEl = document.getElementById("status");

            try {
                const response = await fetch("api/products.php");
                if (!response.ok) {
                    throw new Error("Could not fetch products");
                }

                const data = await response.json();
                allProducts = data.products || [];
                allCategories = data.categories || [];

                populateCategoryFilter(allCategories);
                filterProducts();
            } catch (error) {
                statusEl.textContent = "Failed to load products.";
            }
        }

        // Event listeners for filtering
        document.getElementById("categoryFilter").addEventListener("change", (e) => {
            currentFilter.category = e.target.value;
            filterProducts();
        });

        document.getElementById("searchInput").addEventListener("input", (e) => {
            currentFilter.search = e.target.value;
            filterProducts();
        });

        document.getElementById("resetFilters").addEventListener("click", () => {
            currentFilter.category = '';
            currentFilter.search = '';
            document.getElementById("categoryFilter").value = '';
            document.getElementById("searchInput").value = '';
            filterProducts();
        });

        // Quantity Modal Event Listeners
        document.getElementById("modalClose").addEventListener("click", closeQuantityModal);
        document.getElementById("modalCancel").addEventListener("click", closeQuantityModal);
        document.getElementById("modalConfirm").addEventListener("click", confirmAddToCart);

        document.getElementById("decreaseBtn").addEventListener("click", () => {
            const input = document.getElementById("quantityInput");
            const val = Math.max(1, parseInt(input.value) - 1);
            input.value = val;
        });

        document.getElementById("increaseBtn").addEventListener("click", () => {
            const input = document.getElementById("quantityInput");
            const max = parseInt(input.max);
            const val = Math.min(max, parseInt(input.value) + 1);
            input.value = val;
        });

        // Close modal when clicking outside
        document.getElementById("quantityModal").addEventListener("click", (e) => {
            if (e.target.id === "quantityModal") {
                closeQuantityModal();
            }
        });

        // Cart Modal Event Listeners
        document.getElementById("viewCartBtn").addEventListener("click", openCartModal);
        document.getElementById("cartModalClose").addEventListener("click", closeCartModal);
        document.getElementById("cartModalClose2").addEventListener("click", closeCartModal);
        document.getElementById("cartCheckoutBtn").addEventListener("click", checkout);

        // Close cart modal when clicking outside
        document.getElementById("cartModal").addEventListener("click", (e) => {
            if (e.target.id === "cartModal") {
                closeCartModal();
            }
        });

        document.getElementById("buyNowBtn").addEventListener("click", checkout);
        
        // Product Details Modal Event Listeners
        document.getElementById("detailsModalClose").addEventListener("click", closeProductDetailsModal);
        document.getElementById("detailsModalCancel").addEventListener("click", closeProductDetailsModal);
        
        // Quantity controls for details modal
        document.getElementById("detailsDecreaseBtn").addEventListener("click", () => {
            const input = document.getElementById("detailsQuantityInput");
            const val = Math.max(1, parseInt(input.value) - 1);
            input.value = val;
        });

        document.getElementById("detailsIncreaseBtn").addEventListener("click", () => {
            const input = document.getElementById("detailsQuantityInput");
            const max = parseInt(input.max);
            const val = Math.min(max, parseInt(input.value) + 1);
            input.value = val;
        });

        // Add to cart from details modal
        document.getElementById("detailsAddToCartBtn").addEventListener("click", () => {
            if (!selectedProduct) return;

            const quantity = parseInt(document.getElementById("detailsQuantityInput").value) || 1;
            const stock = Number(selectedProduct.stock) || 0;
            const inCart = cart[selectedProduct.id] || 0;
            const maxAllowed = stock - inCart;

            if (quantity > maxAllowed) {
                alert(`Only ${maxAllowed} item(s) available.`);
                return;
            }

            cart[selectedProduct.id] = (cart[selectedProduct.id] || 0) + quantity;
            cartCount += quantity;
            updateCartUi();
            closeProductDetailsModal();
        });

        // Close details modal when clicking outside
        document.getElementById("productDetailsModal").addEventListener("click", (e) => {
            if (e.target.id === "productDetailsModal") {
                closeProductDetailsModal();
            }
        });

        updateCartUi();
        loadProducts();
    </script>
</body>
</html>
