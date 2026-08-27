/**
 * Powers the two dynamic single-item pages: product.html?slug=X and
 * post.html?slug=X. Reads window.OBI_PRODUCTS / window.OBI_POSTS (see
 * obi-data.js) and the ?slug= query param, then populates the DOM. This is
 * the static-prototype stand-in for what WordPress template routing would
 * otherwise do server-side.
 */
(function () {
  function getSlug() {
    return new URLSearchParams(window.location.search).get('slug');
  }

  function renderProductCard(p) {
    return `
      <article class="obi-card obi-reveal">
        <a href="product.html?slug=${p.slug}" class="obi-hover-zoom obi-product-card__img-wrap">
          <img src="${p.image}" alt="${p.title}" loading="lazy">
        </a>
        <h3><a href="product.html?slug=${p.slug}">${p.title}</a></h3>
        <p class="obi-product-card__price">${window.OBI_CART.formatPrice(p.price)}</p>
        <button type="button" class="obi-btn obi-btn--outline obi-add-to-cart" style="width:100%;justify-content:center;"
          data-add-to-cart data-slug="${p.slug}" data-title="${p.title}" data-price="${p.price}" data-image="${p.image}" data-type="${p.type}">Add to Bag</button>
      </article>`;
  }

  function renderPostCard(p) {
    return `
      <article class="obi-card obi-reveal">
        <a href="post.html?slug=${p.slug}" class="obi-hover-zoom obi-card__img-wrap">
          <img src="${p.image}" alt="${p.title}" loading="lazy">
        </a>
        <span class="obi-eyebrow">${p.category}</span>
        <h3><a href="post.html?slug=${p.slug}">${p.title}</a></h3>
        <p>${p.excerpt}</p>
        <div class="obi-card__meta">${p.meta}</div>
      </article>`;
  }

  function initProductPage() {
    const root = document.querySelector('[data-product-root]');
    if (!root) return;
    const slug = getSlug();
    const product = (window.OBI_PRODUCTS || []).find((p) => p.slug === slug);

    if (!product) {
      root.style.display = 'none';
      document.querySelector('[data-product-not-found]').style.display = '';
      return;
    }

    document.title = `${product.title} — Goods | Organic Body Institute`;
    document.querySelector('[data-product-breadcrumb]').textContent = product.title;
    document.querySelector('[data-product-image]').src = product.image;
    document.querySelector('[data-product-image]').alt = product.title;
    document.querySelector('[data-product-title]').textContent = product.title;
    document.querySelector('[data-product-price]').textContent = window.OBI_CART.formatPrice(product.price);
    document.querySelector('[data-product-description]').innerHTML = product.descriptionHtml;

    if (product.badge) {
      document.querySelector('[data-product-badge]').textContent = product.badge;
      document.querySelector('[data-product-badge-wrap]').style.display = 'inline-flex';
    }

    const addBtn = document.querySelector('[data-add-to-cart]');
    addBtn.dataset.slug = product.slug;
    addBtn.dataset.title = product.title;
    addBtn.dataset.price = product.price;
    addBtn.dataset.image = product.image;
    addBtn.dataset.type = product.type;

    const qtyInput = document.querySelector('[data-qty-input]');
    document.querySelector('[data-qty-plus]').addEventListener('click', () => {
      qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) + 1);
    });
    document.querySelector('[data-qty-minus]').addEventListener('click', () => {
      qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) - 1);
    });

    const related = (window.OBI_PRODUCTS || []).filter((p) => p.slug !== product.slug).slice(0, 3);
    document.querySelector('[data-related-products]').innerHTML = related.map(renderProductCard).join('');
  }

  function initPostPage() {
    const root = document.querySelector('[data-post-root]');
    if (!root) return;
    const slug = getSlug();
    const post = (window.OBI_POSTS || []).find((p) => p.slug === slug);
    const contentSections = document.querySelectorAll('main > section');

    if (!post) {
      root.style.display = 'none';
      contentSections.forEach((s) => (s.style.display = 'none'));
      document.querySelector('[data-post-not-found]').style.display = '';
      return;
    }

    document.title = `${post.title} — Perspectives | Organic Body Institute`;
    document.querySelector('[data-post-category]').textContent = post.category;
    document.querySelector('[data-post-title]').textContent = post.title;
    document.querySelector('[data-post-meta]').textContent = `${post.meta} · ${post.category}`;
    document.querySelector('[data-post-image]').src = post.image;
    document.querySelector('[data-post-image]').alt = post.title;
    document.querySelector('[data-post-body]').innerHTML = post.bodyHtml;

    const related = (window.OBI_POSTS || []).filter((p) => p.slug !== post.slug).slice(0, 3);
    document.querySelector('[data-related-posts]').innerHTML = related.map(renderPostCard).join('');
  }

  document.addEventListener('DOMContentLoaded', () => {
    initProductPage();
    initPostPage();
  });
})();
