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
    </style>
</head>
<body>
    <header>
        <div class="topbar">
            <a href="index.php" class="brand brand-link">StyleStore</a>
            <div style="display: flex; gap: 30px; align-items: center;">
                <div class="cart-wrap">
                    <div class="cart">Cart: <span id="cartCount">0</span> item(s)</div>
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

        <div id="status" class="status">Loading products...</div>
        <section id="productGrid" class="grid"></section>
    </main>

    <footer>© 2026 StyleStore. All rights reserved.</footer>

    <script>
        let cartCount = 0;
        const cart = {};
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        function updateCartUi() {
            document.getElementById("cartCount").textContent = cartCount;
            document.getElementById("buyNowBtn").disabled = cartCount === 0;
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

            cart[product.id] = inCart + 1;
            cartCount++;
            updateCartUi();
        }

        function createProductCard(product) {
            const card = document.createElement("article");
            card.className = "product";

            const stock = Number(product.stock) || 0;
            const outOfStock = stock <= 0;

            card.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                <div class="content">
                    <div class="name">${product.name}</div>
                    <div class="price">$${Number(product.price).toFixed(2)}</div>
                    <div class="stock ${outOfStock ? "out" : ""}">
                        ${outOfStock ? "Out of stock" : `In stock: ${stock}`}
                    </div>
                    <button type="button" ${outOfStock ? "disabled" : ""}>Add to Cart</button>
                </div>
            `;

            const button = card.querySelector("button");
            button.addEventListener("click", () => addToCart(product));

            return card;
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
            const gridEl = document.getElementById("productGrid");

            try {
                const response = await fetch("api/products.php");
                if (!response.ok) {
                    throw new Error("Could not fetch products");
                }

                const products = await response.json();
                gridEl.innerHTML = "";

                products.forEach((product) => {
                    gridEl.appendChild(createProductCard(product));
                });

                statusEl.textContent = products.length
                    ? `Showing ${products.length} product(s).`
                    : "No products found.";
            } catch (error) {
                statusEl.textContent = "Failed to load products.";
            }
        }

        document.getElementById("buyNowBtn").addEventListener("click", checkout);
        updateCartUi();
        loadProducts();
    </script>
</body>
</html>
