/**
 * Organic Body Institute — house animation system.
 * GSAP + ScrollTrigger hooked to a fixed vocabulary of Elementor-portable CSS
 * classes (obi-reveal, obi-reveal-group, obi-hover-zoom, obi-hover-crossfade)
 * so the pattern stays reusable through future content edits.
 */
(function () {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initSmoothScroll() {
    // Buttery inertial scroll (Lenis) -- skipped entirely for
    // prefers-reduced-motion so those users get plain native scroll, not a
    // "reduced" version of an effect they asked to avoid. Driven off GSAP's
    // own ticker (rather than its own rAF loop) and synced to ScrollTrigger
    // per Lenis's documented GSAP integration, so scroll-reveal triggers
    // stay accurate to the smoothed scroll position instead of the raw one.
    if (reduceMotion || typeof Lenis === 'undefined') return;
    const lenis = new Lenis({ duration: 1.1, smoothWheel: true });
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
      lenis.on('scroll', ScrollTrigger.update);
      gsap.ticker.add((time) => lenis.raf(time * 1000));
      gsap.ticker.lagSmoothing(0);
    } else {
      function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
      }
      requestAnimationFrame(raf);
    }
    window.__obiLenis = lenis;

    // Lenis drives its own smooth wheel/touch scrolling, so the native CSS
    // `scroll-behavior: smooth` (base.css, kept as the no-JS/no-Lenis
    // fallback) has to be switched off here -- otherwise a same-page anchor
    // jump triggers the browser's native smooth-scroll AND Lenis's virtual
    // scroll at once, and the two fight each other into a stuttering scroll.
    document.documentElement.style.scrollBehavior = 'auto';
    const headerH = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--header-h')) || 0;
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link || link.getAttribute('href').length < 2) return;
      const target = document.querySelector(link.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      lenis.scrollTo(target, { offset: -headerH });
    });
  }

  function initReveal() {
    if (reduceMotion || typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
      document.querySelectorAll('.obi-reveal, .obi-reveal-group').forEach((el) => el.classList.add('is-revealed'));
      return;
    }
    gsap.registerPlugin(ScrollTrigger);

    document.querySelectorAll('.obi-reveal').forEach((el) => {
      ScrollTrigger.create({
        trigger: el,
        start: 'top 90%',
        once: true,
        onEnter: () => el.classList.add('is-revealed'),
      });
    });

    document.querySelectorAll('.obi-reveal-group').forEach((group) => {
      Array.from(group.children).forEach((child, i) => {
        child.style.transitionDelay = i * 0.1 + 's';
      });
      ScrollTrigger.create({
        trigger: group,
        start: 'top 90%',
        once: true,
        onEnter: () => group.classList.add('is-revealed'),
      });
    });
  }

  function initHeader() {
    const header = document.querySelector('.obi-header');
    if (!header) return;
    const onScroll = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 20);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  function initNavToggle() {
    const toggle = document.querySelector('.obi-nav-toggle');
    const nav = document.querySelector('.obi-nav');
    if (!toggle || !nav) return;
    toggle.addEventListener('click', () => {
      const isOpen = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach((a) =>
      a.addEventListener('click', () => {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      })
    );
  }

  function initCountUp() {
    const stats = document.querySelectorAll('.obi-countup');
    if (!stats.length) return;
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const el = entry.target;
          const target = parseFloat(el.dataset.target || el.textContent);
          const suffix = el.dataset.suffix || '';
          if (reduceMotion || typeof gsap === 'undefined') {
            el.textContent = target.toLocaleString() + suffix;
          } else {
            const obj = { val: 0 };
            gsap.to(obj, {
              val: target,
              duration: 1.8,
              ease: 'power2.out',
              onUpdate: () => {
                el.textContent = Math.round(obj.val).toLocaleString() + suffix;
              },
            });
          }
          io.unobserve(el);
        });
      },
      { threshold: 0.6 }
    );
    stats.forEach((el) => io.observe(el));
  }

  function initHeroRotator() {
    // Works with either <video> or <img> hero slides (placeholder builds use
    // stills with a CSS Ken Burns drift; dropping in real footage later is a
    // matter of swapping <img class="obi-hero__media"> for <video ...> with
    // no JS changes needed).
    const hero = document.querySelector('[data-hero-rotator]');
    if (!hero) return;
    const slides = Array.from(hero.querySelectorAll('.obi-hero__media'));
    if (!slides.length) return;
    const isVideo = (el) => el.tagName === 'VIDEO';
    const toggleBtn = hero.querySelector('[data-hero-toggle]');
    const prevBtn = hero.querySelector('[data-hero-prev]');
    const nextBtn = hero.querySelector('[data-hero-next]');
    let current = 0;
    let playing = !reduceMotion;
    let autoTimer = null;

    function show(index) {
      slides.forEach((el, i) => {
        if (i === index) {
          el.classList.add('is-active');
          if (isVideo(el)) {
            el.currentTime = 0;
            if (playing) el.play().catch(() => {});
          }
        } else {
          el.classList.remove('is-active');
          if (isVideo(el)) el.pause();
        }
      });
      current = index;
    }

    function next() {
      show((current + 1) % slides.length);
    }

    slides.forEach((el, i) => {
      if (isVideo(el)) el.addEventListener('ended', () => show((i + 1) % slides.length));
    });

    function restartAutoAdvance() {
      if (autoTimer) clearInterval(autoTimer);
      if (!slides.some(isVideo) && playing && slides.length > 1) {
        autoTimer = setInterval(next, 7000);
      }
    }

    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        playing = !playing;
        const active = slides[current];
        if (playing) {
          if (isVideo(active)) active.play().catch(() => {});
          toggleBtn.setAttribute('aria-label', 'Pause background video');
          toggleBtn.dataset.state = 'playing';
        } else {
          if (isVideo(active)) active.pause();
          toggleBtn.setAttribute('aria-label', 'Play background video');
          toggleBtn.dataset.state = 'paused';
        }
        restartAutoAdvance();
      });
    }
    if (nextBtn) nextBtn.addEventListener('click', () => { next(); restartAutoAdvance(); });
    if (prevBtn) prevBtn.addEventListener('click', () => { show((current - 1 + slides.length) % slides.length); restartAutoAdvance(); });

    show(0);
    if (reduceMotion && isVideo(slides[0])) slides[0].pause();
    restartAutoAdvance();
  }

  function initFilters() {
    const filterBar = document.querySelector('[data-filter-bar]');
    if (!filterBar) return;
    const pills = Array.from(filterBar.querySelectorAll('.obi-filter-pill'));
    const cards = Array.from(document.querySelectorAll('[data-filter-item]'));
    const countEl = document.querySelector('[data-filter-count]');

    function apply(category) {
      let shown = 0;
      cards.forEach((card) => {
        const cats = (card.dataset.categories || '').split(',').map((s) => s.trim());
        const match = category === 'all' || cats.includes(category);
        card.classList.toggle('is-visible', match);
        card.style.display = match ? '' : 'none';
        if (match) shown += 1;
      });
      if (countEl) countEl.textContent = shown + (shown === 1 ? ' article' : ' articles');
    }

    pills.forEach((pill) => {
      pill.addEventListener('click', () => {
        pills.forEach((p) => p.setAttribute('aria-pressed', 'false'));
        pill.setAttribute('aria-pressed', 'true');
        apply(pill.dataset.category);
      });
    });

    apply('all');
  }

  function initDonateWidget() {
    const widget = document.querySelector('.obi-donate-widget');
    if (!widget) return;
    widget.querySelectorAll('.obi-donate-toggle button').forEach((btn) =>
      btn.addEventListener('click', () => {
        widget.querySelectorAll('.obi-donate-toggle button').forEach((b) => b.setAttribute('aria-pressed', 'false'));
        btn.setAttribute('aria-pressed', 'true');
      })
    );
    widget.querySelectorAll('.obi-donate-amounts button').forEach((btn) =>
      btn.addEventListener('click', () => {
        widget.querySelectorAll('.obi-donate-amounts button').forEach((b) => b.setAttribute('aria-pressed', 'false'));
        btn.setAttribute('aria-pressed', 'true');
      })
    );
  }

  document.addEventListener('DOMContentLoaded', () => {
    initSmoothScroll();
    initReveal();
    initHeader();
    initNavToggle();
    initCountUp();
    initHeroRotator();
    initFilters();
    initDonateWidget();
  });
})();
