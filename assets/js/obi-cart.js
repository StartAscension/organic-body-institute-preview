/**
 * Organic Body Institute — client-side cart engine.
 * localStorage-backed (key: obi-cart-v1), no backend — this is the static
 * prototype's stand-in for WooCommerce cart/session. Swapping in real
 * WooCommerce later means replacing this file's storage layer; the DOM
 * hooks (data-cart-*, data-add-to-cart) stay the same.
 */
(function () {
  const STORAGE_KEY = 'obi-cart-v1';

  function formatPrice(n) {
    return '$' + n.toFixed(2);
  }

  function getCart() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      const cart = raw ? JSON.parse(raw) : [];
      return Array.isArray(cart) ? cart : [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(cart) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    } catch (e) {
      /* storage unavailable (private mode etc.) -- cart just won't persist */
    }
    document.dispatchEvent(new CustomEvent('obi-cart-updated', { detail: { cart } }));
  }

  function addToCart({ slug, title, price, image, type }, qty) {
    qty = qty || 1;
    const cart = getCart();
    const existing = cart.find((i) => i.slug === slug);
    if (existing) {
      existing.qty += qty;
    } else {
      cart.push({ slug, title, price: Number(price), image, type: type || 'physical', qty });
    }
    saveCart(cart);
  }

  function updateQty(slug, qty) {
    let cart = getCart();
    if (qty <= 0) {
      cart = cart.filter((i) => i.slug !== slug);
    } else {
      const item = cart.find((i) => i.slug === slug);
      if (item) item.qty = qty;
    }
    saveCart(cart);
  }

  function removeFromCart(slug) {
    saveCart(getCart().filter((i) => i.slug !== slug));
  }

  function clearCart() {
    saveCart([]);
  }

  function getSubtotal(cart) {
    cart = cart || getCart();
    return cart.reduce((sum, i) => sum + i.price * i.qty, 0);
  }

  function getCount(cart) {
    cart = cart || getCart();
    return cart.reduce((sum, i) => sum + i.qty, 0);
  }

  function hasPhysical(cart) {
    cart = cart || getCart();
    return cart.some((i) => i.type !== 'digital');
  }

  // ---------- DOM wiring (badge, drawer, add-to-cart buttons) ----------

  function renderBadge() {
    const count = getCount();
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
      el.textContent = count > 0 ? String(count) : '';
      el.classList.toggle('is-visible', count > 0);
    });
  }

  function renderInto(bodySelector, footerSelector, subtotalSelector, emptyMessage) {
    const body = document.querySelector(bodySelector);
    if (!body) return;
    const footer = footerSelector ? document.querySelector(footerSelector) : null;
    const cart = getCart();

    if (!cart.length) {
      body.innerHTML = `<div class="obi-empty-state"><p>${emptyMessage}</p><a href="/goods.html" class="obi-btn obi-btn--outline" style="margin-top:1.5rem;">Browse Goods</a></div>`;
      if (footer) footer.style.display = 'none';
      return;
    }

    if (footer) footer.style.display = '';
    body.innerHTML = cart
      .map(
        (item) => `
      <div class="obi-cart-line" data-slug="${item.slug}">
        <img src="${item.image}" alt="" class="obi-cart-line__img">
        <div class="obi-cart-line__info">
          <p class="obi-cart-line__title">${item.title}</p>
          <p class="obi-cart-line__price">${formatPrice(item.price)}${item.type === 'digital' ? ' <span class="obi-cart-line__tag">Digital</span>' : ''}</p>
          <div class="obi-cart-line__qty">
            <button type="button" class="obi-qty-btn" data-qty-dec aria-label="Decrease quantity">&minus;</button>
            <span aria-live="polite">${item.qty}</span>
            <button type="button" class="obi-qty-btn" data-qty-inc aria-label="Increase quantity">+</button>
          </div>
        </div>
        <button type="button" class="obi-cart-line__remove" data-remove aria-label="Remove ${item.title}">&times;</button>
      </div>`
      )
      .join('');

    if (subtotalSelector) {
      const subtotalEl = document.querySelector(subtotalSelector);
      if (subtotalEl) subtotalEl.textContent = formatPrice(getSubtotal(cart));
    }

    body.querySelectorAll('[data-slug]').forEach((row) => {
      const slug = row.dataset.slug;
      const item = cart.find((i) => i.slug === slug);
      row.querySelector('[data-qty-inc]').addEventListener('click', () => updateQty(slug, item.qty + 1));
      row.querySelector('[data-qty-dec]').addEventListener('click', () => updateQty(slug, item.qty - 1));
      row.querySelector('[data-remove]').addEventListener('click', () => removeFromCart(slug));
    });
  }

  function renderDrawer() {
    renderInto('[data-cart-drawer-body]', '[data-cart-drawer-footer]', '[data-cart-subtotal]',
      "Your bag is empty — browse Goods to add something you'll actually use.");
    renderInto('[data-cart-page-body]', '[data-cart-page-footer]', '[data-cart-page-subtotal]',
      'Your bag is empty — browse Goods above to add something.');
  }

  function openDrawer() {
    const drawer = document.querySelector('[data-cart-drawer]');
    if (!drawer) return;
    renderDrawer();
    drawer.classList.add('is-open');
    document.body.classList.add('obi-no-scroll');
    drawer.querySelector('[data-cart-close]')?.focus();
  }

  function closeDrawer() {
    const drawer = document.querySelector('[data-cart-drawer]');
    if (!drawer) return;
    drawer.classList.remove('is-open');
    document.body.classList.remove('obi-no-scroll');
  }

  function initButtons() {
    document.addEventListener('click', (e) => {
      const addBtn = e.target.closest('[data-add-to-cart]');
      if (addBtn) {
        e.preventDefault();
        const { slug, title, price, image, type } = addBtn.dataset;
        const qtyInput = document.querySelector('[data-qty-input]');
        const qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
        addToCart({ slug, title, price: Number(price), image, type }, qty);
        openDrawer();
        addBtn.classList.add('is-added');
        setTimeout(() => addBtn.classList.remove('is-added'), 1200);
        return;
      }
      if (e.target.closest('[data-cart-open]')) {
        e.preventDefault();
        openDrawer();
        return;
      }
      if (e.target.closest('[data-cart-close]') || e.target.closest('[data-cart-overlay]')) {
        e.preventDefault();
        closeDrawer();
        return;
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeDrawer();
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    renderBadge();
    renderDrawer();
    initButtons();
  });
  document.addEventListener('obi-cart-updated', () => {
    renderBadge();
    renderDrawer();
  });

  window.OBI_CART = {
    getCart, addToCart, updateQty, removeFromCart, clearCart,
    getSubtotal, getCount, hasPhysical, formatPrice, openDrawer, closeDrawer,
  };
})();
