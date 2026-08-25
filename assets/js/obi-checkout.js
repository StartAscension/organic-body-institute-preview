/**
 * Checkout page logic — prototype only, no real payment processing.
 * Renders the order summary from the same localStorage cart obi-cart.js
 * manages, toggles shipping fields off for digital-only bags, and shows a
 * client-side "order placed" confirmation state on submit.
 */
(function () {
  const FLAT_SHIPPING = 6.5;
  const TAX_RATE = 0.07;

  function renderSummary() {
    // An order just placed intentionally empties the cart (see initSubmit) --
    // that's not the same as arriving with nothing to buy. Once the success
    // view is showing, stop reacting to cart-emptiness entirely so it can't
    // get clobbered back to the "empty bag" state by this same listener.
    const successEl = document.querySelector('[data-checkout-success]');
    if (successEl && successEl.style.display !== 'none') return true;

    const cart = window.OBI_CART.getCart();
    const linesEl = document.querySelector('[data-checkout-lines]');
    const emptyEl = document.querySelector('[data-checkout-empty]');
    const formWrapEl = document.querySelector('[data-checkout-form-wrap]');

    if (!cart.length) {
      if (emptyEl) emptyEl.style.display = '';
      if (formWrapEl) formWrapEl.style.display = 'none';
      return false;
    }
    if (emptyEl) emptyEl.style.display = 'none';
    if (formWrapEl) formWrapEl.style.display = '';

    linesEl.innerHTML = cart
      .map(
        (item) => `
      <div class="obi-checkout-line">
        <img src="${item.image}" alt="">
        <div class="obi-checkout-line__info">
          <p>${item.title}</p>
          <p class="obi-checkout-line__qty">Qty ${item.qty} &times; ${window.OBI_CART.formatPrice(item.price)}</p>
        </div>
      </div>`
      )
      .join('');

    const subtotal = window.OBI_CART.getSubtotal(cart);
    const isDigitalOnly = !window.OBI_CART.hasPhysical(cart);
    const shipping = isDigitalOnly ? 0 : FLAT_SHIPPING;
    const tax = Math.round(subtotal * TAX_RATE * 100) / 100;
    const total = subtotal + shipping + tax;

    document.querySelector('[data-checkout-subtotal]').textContent = window.OBI_CART.formatPrice(subtotal);
    document.querySelector('[data-checkout-shipping]').textContent = shipping === 0 ? 'Free' : window.OBI_CART.formatPrice(shipping);
    document.querySelector('[data-checkout-tax]').textContent = window.OBI_CART.formatPrice(tax);
    document.querySelector('[data-checkout-total]').textContent = window.OBI_CART.formatPrice(total);

    document.querySelectorAll('[data-shipping-field]').forEach((el) => {
      el.style.display = isDigitalOnly ? 'none' : '';
    });
    document.querySelectorAll('[data-shipping-required]').forEach((el) => {
      el.required = !isDigitalOnly;
    });
    const digitalNote = document.querySelector('[data-digital-only-note]');
    if (digitalNote) digitalNote.style.display = isDigitalOnly ? '' : 'none';

    return true;
  }

  function initPaymentToggle() {
    const options = document.querySelectorAll('[data-payment-options] .obi-payment-option');
    const cardFields = document.querySelector('[data-card-fields]');
    const paypalNote = document.querySelector('[data-paypal-note]');
    options.forEach((opt) => {
      const input = opt.querySelector('input');
      input.addEventListener('change', () => {
        options.forEach((o) => o.setAttribute('data-selected', 'false'));
        opt.setAttribute('data-selected', 'true');
        const isCard = input.value === 'card';
        if (cardFields) cardFields.style.display = isCard ? '' : 'none';
        if (paypalNote) paypalNote.style.display = isCard ? 'none' : '';
      });
    });
  }

  function initSubmit() {
    const form = document.querySelector('[data-checkout-form]');
    if (!form) return;
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      const orderNumber = 'OBI-' + Math.floor(100000 + Math.random() * 900000);
      document.querySelector('[data-order-number]').textContent = orderNumber;
      document.querySelector('[data-checkout-form-wrap]').style.display = 'none';
      document.querySelector('[data-checkout-success]').style.display = '';
      window.OBI_CART.clearCart();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('[data-checkout-root]')) return;
    const hasItems = renderSummary();
    if (hasItems) {
      initPaymentToggle();
      initSubmit();
    }
  });
  document.addEventListener('obi-cart-updated', renderSummary);
})();
