(function() {
    'use strict';

    const API_URL = window.API_URL || 'https://your-infinityfree-domain.rf.gd/api.php';
    const WHATSAPP_NUMBER = window.WHATSAPP_NUMBER || '2567XXXXXXXX';
    const IMAGEKIT_URL = window.IMAGEKIT_URL || 'https://ik.imagekit.io/your_imagekit_id';

    let currentProduct = null;
    let currentQty = 1;

    function getCart() {
        try {
            return JSON.parse(localStorage.getItem('pk_cart') || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('pk_cart', JSON.stringify(cart));
    }

    function updateCartCount() {
        const cart = getCart();
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        document.querySelectorAll('#cartCount').forEach(function(el) {
            el.textContent = count;
        });
    }

    function addToCart(id, name, price, image, qty) {
        const cart = getCart();
        const existing = cart.find(function(item) { return item.id === id; });
        if (existing) {
            existing.qty += qty;
        } else {
            cart.push({ id: id, name: name, price: price, image: image, qty: qty });
        }
        saveCart(cart);
        updateCartCount();
        alert('Added to cart!');
    }

    function updateQty(id, newQty) {
        const cart = getCart();
        const item = cart.find(function(i) { return i.id === id; });
        if (item) {
            item.qty = Math.max(1, Math.min(99, newQty));
            saveCart(cart);
            renderCart();
            updateCartCount();
        }
    }

    function removeFromCart(id) {
        const cart = getCart().filter(function(i) { return i.id !== id; });
        saveCart(cart);
        renderCart();
        updateCartCount();
    }

    function showLockdown() {
        document.querySelectorAll('#lockdownOverlay').forEach(function(el) { el.style.display = 'flex'; });
        document.querySelectorAll('#mainContent').forEach(function(el) { el.style.display = 'none'; });
        document.querySelectorAll('footer').forEach(function(el) { el.style.display = 'none'; });
        document.querySelectorAll('header').forEach(function(el) { el.style.display = 'none'; });
    }

    function showMain() {
        document.querySelectorAll('#lockdownOverlay').forEach(function(el) { el.style.display = 'none'; });
        document.querySelectorAll('#mainContent').forEach(function(el) { el.style.display = 'block'; });
        document.querySelectorAll('footer').forEach(function(el) { el.style.display = 'block'; });
        document.querySelectorAll('header').forEach(function(el) { el.style.display = 'block'; });
    }

    async function checkLicense() {
        try {
            const res = await fetch(API_URL + '?action=check_license&t=' + Date.now());
            const data = await res.json();
            if (data.license && data.license.status === 'expired') {
                showLockdown();
                return false;
            }
            showMain();
            return true;
        } catch (e) {
            console.error('License check failed', e);
            showLockdown();
            return false;
        }
    }

    async function fetchJSON(action) {
        const res = await fetch(API_URL + '?action=' + action + '&t=' + Date.now());
        if (!res.ok) throw new Error('API error');
        return res.json();
    }

    function getImageUrl(filename) {
        if (!filename) return 'https://via.placeholder.com/400?text=No+Image';
        if (filename.startsWith('http')) return filename;
        return IMAGEKIT_URL + '/' + filename + '?tr=w-400,q-80,f-webp';
    }

    function formatPrice(price) {
        return 'UGX ' + Number(price).toLocaleString();
    }

    async function loadCategories() {
        try {
            const response = await fetchJSON('get_categories');
            const categories = response.categories || [];
            const grid = document.getElementById('categoriesGrid');
            if (!grid) return;
            grid.innerHTML = categories.map(function(cat) {
                return '<div class="category-card" onclick="window.filterByCategory(' + cat.id + ')">' +
                    '<img src="' + getImageUrl(cat.image) + '" alt="' + cat.name + '" loading="lazy">' +
                    '<h3>' + cat.name + '</h3>' +
                '</div>';
            }).join('');
        } catch (e) {
            console.error('Failed to load categories', e);
        }
    }

    async function loadProducts(categoryId) {
        const url = categoryId ? API_URL + '?action=get_products&category_id=' + categoryId + '&t=' + Date.now() : API_URL + '?action=get_products&t=' + Date.now();
        try {
            const res = await fetch(url);
            const response = await res.json();
            const products = response.products || [];
            const grid = document.getElementById('productsGrid');
            if (!grid) return;
            grid.innerHTML = products.map(function(p) {
                const offerBadge = p.is_offer ? '<span class="offer-badge">OFFER</span>' : '';
                return '<div class="product-card">' + offerBadge +
                    '<a href="product.html?id=' + p.id + '" style="text-decoration:none;color:inherit;">' +
                        '<img src="' + getImageUrl(p.image) + '" alt="' + p.name + '" loading="lazy">' +
                        '<div class="product-info">' +
                            '<h3>' + p.name + '</h3>' +
                            '<div class="price">' + formatPrice(p.price) + '</div>' +
                            '<button class="btn" onclick="event.preventDefault(); window.location.href=\'product.html?id=' + p.id + '\'">View Product</button>' +
                        '</div>' +
                    '</a>' +
                '</div>';
            }).join('');
        } catch (e) {
            console.error('Failed to load products', e);
        }
    }

    async function loadOffers() {
        try {
            const response = await fetchJSON('get_offers');
            const products = response.offers || [];
            const track = document.getElementById('offersTrack');
            if (!track || !products.length) {
                const section = document.getElementById('offersSection');
                if (section) section.style.display = 'none';
                return;
            }
            track.innerHTML = products.map(function(p) {
                return '<div class="offer-card">' +
                    '<img src="' + getImageUrl(p.image) + '" alt="' + p.name + '" loading="lazy">' +
                    '<div class="offer-info">' +
                        '<span class="offer-badge">OFFER</span>' +
                        '<h4>' + p.name + '</h4>' +
                        '<div class="offer-price">' + formatPrice(p.price) + '</div>' +
                        '<a href="product.html?id=' + p.id + '" class="btn">View Offer</a>' +
                    '</div>' +
                '</div>';
            }).join('');
        } catch (e) {
            console.error('Failed to load offers', e);
        }
    }

    function renderCart() {
        const container = document.getElementById('cartContent');
        if (!container) return;
        const cart = getCart();
        if (!cart.length) {
            container.innerHTML = '<p style="text-align:center;padding:40px;color:#888;">Your cart is empty.</p>';
            return;
        }
        let total = 0;
        let html = cart.map(function(item) {
            const subtotal = item.price * item.qty;
            total += subtotal;
            return '<div class="cart-item">' +
                '<img src="' + getImageUrl(item.image) + '" alt="' + item.name + '">' +
                '<div class="item-info">' +
                    '<h4>' + item.name + '</h4>' +
                    '<p class="item-price">' + formatPrice(item.price) + '</p>' +
                    '<div class="qty-controls">' +
                        '<button onclick="window.updateCartQty(' + item.id + ', ' + (item.qty - 1) + ')">-</button>' +
                        '<span>' + item.qty + '</span>' +
                        '<button onclick="window.updateCartQty(' + item.id + ', ' + (item.qty + 1) + ')">+</button>' +
                    '</div>' +
                    '<p class="item-subtotal">Subtotal: ' + formatPrice(subtotal) + '</p>' +
                '</div>' +
                '<button class="remove-btn" onclick="window.removeCartItem(' + item.id + ')">Remove</button>' +
            '</div>';
        }).join('');
        html += '<div class="cart-summary">' +
            '<h3>Total: ' + formatPrice(total) + '</h3>' +
            '<div class="action-buttons">' +
                '<a href="https://wa.me/' + WHATSAPP_NUMBER + '?text=' + encodeURIComponent(generateCartWhatsApp()) + '" class="btn-order" target="_blank">Order on WhatsApp</a>' +
                '<a href="index.html" class="btn-continue">Continue Shopping</a>' +
            '</div>' +
        '</div>';
        container.innerHTML = html;
    }

    function generateCartWhatsApp() {
        const cart = getCart();
        if (!cart.length) return '';
        var msg = 'Hello PK Premium, I would like to order:\n';
        var total = 0;
        cart.forEach(function(item) {
            var sub = item.price * item.qty;
            total += sub;
            msg = msg + '- ' + item.name + ' x' + item.qty + ' = ' + formatPrice(sub) + '\n';
        });
        msg = msg + '\nTotal: ' + formatPrice(total);
        return msg;
    }

    function renderProductDetail() {
        const params = new URLSearchParams(window.location.search);
        const id = parseInt(params.get('id'), 10);
        if (!id) return;
        const container = document.getElementById('productDetail');
        if (!container) return;

        fetch(API_URL + '?action=get_product&id=' + id + '&t=' + Date.now())
            .then(function(res) { return res.json(); })
            .then(function(response) {
                const product = response.product;
                if (!product) {
                    container.innerHTML = '<p>Product not found.</p>';
                    return;
                }
                currentProduct = product;
                currentQty = 1;
                container.innerHTML = '<img src="' + getImageUrl(product.image) + '" alt="' + product.name + '" class="main-image">' +
                    '<div class="info">' +
                        '<span class="offer-badge">' + (product.is_offer ? 'OFFER' : (product.category_name || '')) + '</span>' +
                        '<h2>' + product.name + '</h2>' +
                        '<div class="price">' + formatPrice(product.price) + '</div>' +
                        '<p class="description">' + (product.description || '') + '</p>' +
                        '<div class="qty-selector">' +
                            '<button onclick="window.changeQty(-1)">-</button>' +
                            '<span id="qtyDisplay">1</span>' +
                            '<button onclick="window.changeQty(1)">+</button>' +
                        '</div>' +
                    '</div>';
            })
            .catch(function(err) {
                container.innerHTML = '<p>Failed to load product.</p>';
                console.error(err);
            });
    }

    function changeQty(delta) {
        currentQty = Math.max(1, Math.min(99, currentQty + delta));
        const display = document.getElementById('qtyDisplay');
        if (display) display.textContent = currentQty;
    }

    function initPage() {
        const path = window.location.pathname;
        if (path.indexOf('product.html') !== -1) {
            renderProductDetail();
            const addToCartBtn = document.getElementById('addToCartBtn');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function() {
                    if (!currentProduct) return;
                    addToCart(currentProduct.id, currentProduct.name, currentProduct.price, currentProduct.image, currentQty);
                });
            }
            const whatsappBtn = document.getElementById('whatsappBtn');
            if (whatsappBtn) {
                whatsappBtn.addEventListener('click', function() {
                    if (!currentProduct) return;
                    var msg = 'Hello PK Premium, I would like to order:\n- ' + currentProduct.name + ' x' + currentQty + ' = ' + formatPrice(currentProduct.price * currentQty) + '\nTotal: ' + formatPrice(currentProduct.price * currentQty);
                    window.open('https://wa.me/' + WHATSAPP_NUMBER + '?text=' + encodeURIComponent(msg), '_blank');
                });
            }
            const callBtn = document.getElementById('callBtn');
            if (callBtn) {
                callBtn.addEventListener('click', function() {
                    window.location.href = 'tel:+256703504504';
                });
            }
        } else if (path.indexOf('cart.html') !== -1) {
            renderCart();
        }
    }

    window.updateCartQty = function(id, qty) {
        if (qty < 1) {
            removeFromCart(id);
        } else {
            updateQty(id, qty);
        }
    };

    window.removeCartItem = function(id) {
        removeFromCart(id);
    };

    window.filterByCategory = function(categoryId) {
        loadProducts(categoryId);
        var section = document.getElementById('productsSection');
        if (section) section.scrollIntoView({ behavior: 'smooth' });
    };

    window.changeQty = changeQty;

    document.addEventListener('DOMContentLoaded', async function() {
        const active = await checkLicense();
        if (!active) return;
        updateCartCount();
        if (document.getElementById('categoriesGrid')) {
            loadCategories();
        }
        if (document.getElementById('productsGrid')) {
            loadProducts();
        }
        if (document.getElementById('offersTrack')) {
            loadOffers();
        }
        initPage();
    });
})();
