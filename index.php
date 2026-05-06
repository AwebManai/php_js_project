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
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.3s;
            padding: 6px 12px;
            border-radius: 4px;
        }

        .nav-links a:hover, .user-menu a:hover {
            opacity: 0.8;
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
    </style>
</head>
<body>
    <header>
        <div class="topbar">
            <div class="brand">StyleStore</div>
            <div style="display: flex; gap: 30px; align-items: center;">
                <div class="cart">Cart: <span id="cartCount">0</span> item(s)</div>
                <div id="authLinks">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-menu" style="display: flex; gap: 15px; align-items: center;">
                            <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</span>
                            <a href="api/logout.php" class="btn-logout">Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="nav-links">
                            <a href="signup.html">Sign Up</a>
                            <a href="login.html">Log In</a>
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

        function addToCart() {
            cartCount++;
            document.getElementById("cartCount").textContent = cartCount;
        }

        function createProductCard(product) {
            const card = document.createElement("article");
            card.className = "product";

            card.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                <div class="content">
                    <div class="name">${product.name}</div>
                    <div class="price">$${Number(product.price).toFixed(2)}</div>
                    <button type="button">Add to Cart</button>
                </div>
            `;

            const button = card.querySelector("button");
            button.addEventListener("click", addToCart);

            return card;
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
                    ? `Showing ${products.length} product(s) from backend.`
                    : "No products found.";
            } catch (error) {
                statusEl.textContent = "Failed to load products from backend.";
            }
        }

        loadProducts();
    </script>
</body>
</html>
