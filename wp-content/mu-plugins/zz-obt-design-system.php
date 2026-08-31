<?php
/*
Plugin Name: OBT Design System
Description: Loads the Organic Body Institute design system (tokens/base/components/animations CSS, GSAP/Lenis, fonts) and replaces Astra's default header/footer with the custom obi-header/obi-footer markup so the site matches the obt.startascension.com reference design.
*/

define('OBT_ASSET_URL', content_url('uploads/obt-design'));

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('obt-fonts', 'https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600&family=Open+Sans:wght@400;500;600&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&display=swap', [], null);

    wp_enqueue_style('obt-tokens', OBT_ASSET_URL . '/obt-tokens.css', [], '1.0');
    wp_enqueue_style('obt-base', OBT_ASSET_URL . '/obt-base.css', ['obt-tokens'], '1.0');
    wp_enqueue_style('obt-components', OBT_ASSET_URL . '/obt-components.css', ['obt-base'], '1.0');
    wp_enqueue_style('obt-animations-css', OBT_ASSET_URL . '/obt-animations.css', ['obt-components'], '1.0');

    wp_enqueue_script('gsap', OBT_ASSET_URL . '/vendor/gsap/gsap.min.js', [], '3.0', true);
    wp_enqueue_script('gsap-scrolltrigger', OBT_ASSET_URL . '/vendor/gsap/ScrollTrigger.min.js', ['gsap'], '3.0', true);
    wp_enqueue_script('lenis', OBT_ASSET_URL . '/vendor/lenis/lenis.min.js', [], '1.0', true);
    wp_enqueue_script('obi-animations', OBT_ASSET_URL . '/js/obi-animations.js', ['gsap', 'gsap-scrolltrigger', 'lenis'], '1.0', true);
    wp_enqueue_script('obi-hero-video', OBT_ASSET_URL . '/js/obi-hero-video.js', [], '1.3', true);
}, 20);

// Hide Astra's default header/footer - we render the custom obi-header/obi-footer instead.
add_action('wp', function () {
    remove_all_actions('astra_header');
    remove_all_actions('astra_footer');
});

function obt_nav_link($path, $label, $current_path) {
    $url = home_url($path);
    $is_current = ($current_path === $path) ? ' aria-current="page"' : '';
    return '<a href="' . esc_url($url) . '"' . $is_current . '>' . esc_html($label) . '</a>';
}

add_action('wp_body_open', function () {
    $current = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $current = $current === '' ? '' : '/' . $current . '/';
    $logo_reversed = OBT_ASSET_URL . '/obi-header-lockup-reversed.svg';
    $logo_primary = OBT_ASSET_URL . '/obi-header-lockup-primary.svg';
    ?>
    <header class="obi-header is-transparent-start">
      <div class="obi-header__inner">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="obi-header__logo" aria-label="Organic Body Institute — Home">
          <img class="obi-wordmark--light" src="<?php echo esc_url($logo_reversed); ?>" alt="Organic Body Institute">
          <img class="obi-wordmark--dark-svg" src="<?php echo esc_url($logo_primary); ?>" alt="Organic Body Institute">
        </a>
        <button class="obi-nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="obi-primary-nav">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <nav class="obi-nav" id="obi-primary-nav" aria-label="Primary">
          <?php
          echo obt_nav_link('/', 'Home', $current);
          echo obt_nav_link('/perspectives/', 'Perspectives', $current);
          echo obt_nav_link('/resources/', 'Resources', $current);
          echo obt_nav_link('/services/', 'Services', $current);
          echo obt_nav_link('/goods/', 'Goods', $current);
          echo obt_nav_link('/obi-collective/', 'OBI Collective', $current);
          ?>
        </nav>
        <div class="obi-header__right">
          <div class="obi-header__social">
            <a href="https://www.facebook.com/organicbodyinstitute" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 9h3V5.5h-3c-2.2 0-4 1.8-4 4V12H7v3.5h3V22h3.5v-6.5H17l.5-3.5h-4V9.5c0-.6.4-1 1-1z"/></svg></a>
            <a href="https://www.instagram.com/organicbodyinstitute" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg></a>
          </div>
          <a href="<?php echo esc_url(home_url('/cart/')); ?>" class="obi-cart-icon" aria-label="View cart"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 2-1.6L20.5 8H6"/><circle cx="10" cy="21" r="1"/><circle cx="17" cy="21" r="1"/></svg><span class="obi-cart-badge"></span> <span class="obi-visually-hidden">Cart</span></a>
        </div>
      </div>
    </header>
    <?php
});

add_action('wp_footer', function () {
    $wordmark = OBT_ASSET_URL . '/obi-wordmark-reversed.svg';
    ?>
    <footer class="obi-footer">
      <div class="obi-container">
        <div class="obi-footer__top">
          <div class="obi-reveal">
            <img src="<?php echo esc_url($wordmark); ?>" alt="Organic Body Institute" style="height:72px;margin-bottom:1.5rem;">
            <p style="opacity:.75;max-width:280px;">Actionable health within reach — practical, evidence-informed wellness for the whole woman.</p>
            <p style="opacity:.75;">1204 Harbor View Lane<br>Suite 210<br>Wilmington, NC 28401</p>
            <p style="opacity:.75;">hello@organicbodyinstitute.com<br>(910) 555-0142</p>
          </div>
          <div class="obi-reveal">
            <h4>Explore</h4>
            <ul>
              <li><a href="<?php echo esc_url(home_url('/perspectives/')); ?>">Perspectives</a></li>
              <li><a href="<?php echo esc_url(home_url('/resources/')); ?>">Resources</a></li>
              <li><a href="<?php echo esc_url(home_url('/goods/')); ?>">Goods</a></li>
              <li><a href="<?php echo esc_url(home_url('/obi-collective/')); ?>">OBI Collective</a></li>
            </ul>
          </div>
          <div class="obi-reveal">
            <h4>About</h4>
            <ul>
              <li><a href="<?php echo esc_url(home_url('/obi-collective/#jobs')); ?>">Careers</a></li>
              <li><a href="<?php echo esc_url(home_url('/obi-collective/#partners')); ?>">Partners &amp; Sponsors</a></li>
              <li><a href="<?php echo esc_url(home_url('/obi-collective/#donate')); ?>">Support Our Mission</a></li>
              <li><a href="#obi-contact">Contact Us</a></li>
            </ul>
          </div>
          <div class="obi-reveal">
            <h4>Stay In Touch</h4>
            <p style="opacity:.75;">Perspectives on hormone health, nutrition, movement and mindset — straight to your inbox, twice a month.</p>
            <form class="obi-newsletter-form" action="#" method="post">
              <label class="obi-visually-hidden" for="footer-newsletter-email">Email address</label>
              <input id="footer-newsletter-email" type="email" placeholder="Email address" required>
              <button type="submit">Join</button>
            </form>
          </div>
        </div>
      </div>
      <div class="obi-footer__pullquote obi-reveal">Actionable health, within reach.</div>
      <div class="obi-container">
        <div class="obi-footer__bottom">
          <span>&copy; 2026 Organic Body Institute. All rights reserved.</span>
          <div class="obi-footer__legal">
            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a>
            <a href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Use</a>
          </div>
          <div class="obi-footer__social">
            <a href="https://www.facebook.com/organicbodyinstitute" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 9h3V5.5h-3c-2.2 0-4 1.8-4 4V12H7v3.5h3V22h3.5v-6.5H17l.5-3.5h-4V9.5c0-.6.4-1 1-1z"/></svg></a>
            <a href="https://www.instagram.com/organicbodyinstitute" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg></a>
          </div>
        </div>
      </div>
    </footer>
    <?php
});

// Elementor's atomic-widget "attributes" transformer strips custom data-* attrs
// server-side, so the .obi-countup targets/suffixes can't be stored in Elementor
// JSON directly - inject them by DOM order before obi-animations.js runs its
// IntersectionObserver-based count-up.
add_action('wp_footer', function () {
    if (!is_page(17)) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var data = [['12000', '+'], ['48', ''], ['97', '%']];
        var els = document.querySelectorAll('.obi-countup');
        els.forEach(function (el, i) {
            if (data[i]) {
                el.dataset.target = data[i][0];
                el.dataset.suffix = data[i][1];
            }
        });
    });
    </script>
    <?php
}, 5);

// The .e-con framework class Elementor applies to every container element re-declares
// position/z-index/min-height/height/width/overflow/text-align/margin/padding via CSS
// custom properties, and elementor-frontend.css (plus each page's own local-N CSS) loads
// after obt-components.css in <head> - so at equal specificity Elementor's framework wins
// over the design-system classes for those properties. Printing this override stylesheet
// in wp_footer guarantees it is the literal last stylesheet on the page, beating both.
add_action('wp_footer', function () {
    echo '<link rel="stylesheet" href="' . esc_url(OBT_ASSET_URL . '/obt-elementor-overrides.css?ver=2.5') . '">' . "\n";
}, 4);

// Reference site's "Evidence-Informed + Actionable + ..." scrolling text marquee between
// the mission row and the tiles grid. Modeled as a legacy shortcode widget (like the
// WPForms/newsletter shortcode widgets already on this page) since building ~24 individual
// inline spans as atomic widgets isn't practical - this guarantees exact reference markup.
add_shortcode('obi_marquee', function () {
    $phrases = ['Evidence-Informed', 'Actionable', 'No Fear-Mongering', 'Women-Centered', 'Research-Backed', 'Same-Day Useful'];
    $set = '';
    foreach ($phrases as $p) {
        $set .= '<span class="obi-marquee__word">' . esc_html($p) . '</span><span class="obi-marquee__sep">+</span>';
    }
    // Duplicated so the CSS marquee (translateX(-50%) infinite) loops seamlessly.
    return '<div class="obi-marquee__track">' . $set . $set . '</div>';
});

// Reference site's 5-star rating icon set, repeated per testimonial card.
add_shortcode('obi_stars', function () {
    $star = '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2.5l2.9 6.3 6.9.7-5.2 4.7 1.5 6.8L12 17.7 5.9 21l1.5-6.8L2.2 9.5l6.9-.7z"/></svg>';
    return '<span class="obi-testimonial__stars" aria-hidden="true">' . str_repeat($star, 5) . '</span>';
});
