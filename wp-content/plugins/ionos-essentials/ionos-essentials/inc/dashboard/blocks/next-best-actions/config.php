<?php

namespace ionos\essentials\dashboard\blocks\next_best_actions;

use const ionos\essentials\PLUGIN_DIR;

defined('ABSPATH') || exit();

require_once PLUGIN_DIR . '/ionos-essentials/inc/class-tenant.php';

use ionos\essentials\Tenant;
use function ionos\essentials\_is_plugin_active;
use function ionos\essentials\tenant\get_tenant_config;

$data = get_tenant_config();

$homepage = \get_option('page_on_front'); // returns "0" if no static front page is set
$edit_url = intval($homepage) === 0 ? \admin_url('edit.php?post_type=page') : admin_url(
  'post.php?post=' . $homepage . '&action=edit'
);
NBA::register('edit-and-complete', \__('Edit & Complete Your Website', 'ionos-essentials'), \__(
  'Add pages, text, and images, fine-tune your website with AI-powered tools or adjust colours and fonts.',
  'ionos-essentials'
), $edit_url, \__('Edit Website', 'ionos-essentials'), false, true, ['setup-ai']);

if (_is_plugin_active('extendify/extendify.php')) {
  NBA::register('help-center', \__('Discover Help Center', 'ionos-essentials'), \__(
    'Get instant support with Co-Pilot AI, explore our Knowledge Base, or take guided tours.',
    'ionos-essentials'
  ), '#', \__('Open Help Center', 'ionos-essentials'), false, true, ['after-setup']);
}

if (_is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
  NBA::register('contact-form', \__('Set Up Contact Form', 'ionos-essentials'), \__('Create a contact form to stay connected with your visitors.', 'ionos-essentials'), \admin_url('admin.php?page=wpcf7-new'), \__('Set Up Contact Form', 'ionos-essentials'), false, true, ['setup-ai']);
}

if (_is_plugin_active('woocommerce/woocommerce.php')) {
  $woo_onboarding_status = get_option('woocommerce_onboarding_profile');

  NBA::register('woocommerce', \__('Set Up Your WooCommerce Store', 'ionos-essentials'), \__('Launch your online store now with a guided setup wizard.', 'ionos-essentials'), \admin_url('admin.php?page=wc-admin&path=%2Fsetup-wizard'), \__('Start Setup', 'ionos-essentials'), isset($woo_onboarding_status['completed']) || isset($woo_onboarding_status['skipped']), false, ['setup-ai']);
}

NBA::register('select-theme', \__('Select a Theme', 'ionos-essentials'), \__(
  'Choose a theme that matches your website\'s purpose and your comfort level.',
  'ionos-essentials'
), \admin_url('themes.php'), \__('Select a Theme', 'ionos-essentials'), \wp_get_theme()
  ->get_stylesheet() !== 'extendable', false, ['setup-noai']);

NBA::register('create-page', \__('Create a Page', 'ionos-essentials'), \__('Create and publish a page and share your story with the world.', 'ionos-essentials'), \admin_url('post-new.php?post_type=page'), \__('Create Page', 'ionos-essentials'), false, true, ['setup-noai']);

if (null !== $data) {
  $connectdomain = $data['nba_links']['connectdomain'] ?? '';

  NBA::register('connect-domain', \__('Connect a Domain', 'ionos-essentials'), \__(
    'Connect your domain to your website to increase visibility and attract more visitors.',
    'ionos-essentials'
  ), $data['domain'] . $connectdomain, \__('Connect Domain', 'ionos-essentials'), false === strpos(home_url(), 'live-website.com') &&
    false          === strpos(home_url(), 'stretch.love')     &&
    false          === strpos(home_url(), 'stretch.monster'), false, ['setup-ai', 'setup-noai']);

  if (false !== strpos(home_url(), 'live-website.com') && (false !== strpos(home_url(), 'localhost'))) {
    $connectmail = $data['nba_links']['connectmail'] ?? '';

    NBA::register('email-account', \__('Set Up Email', 'ionos-essentials'), \__(
      'Set up your included email account and integrate it with your website.',
      'ionos-essentials'
    ), $data['domain'] . $connectmail, \__('Setup Email Account', 'ionos-essentials'), false, true, ['after-setup']);
  }
}

$tenant        = Tenant::get_slug();
$market        = strtolower(\get_option($tenant . '_market', 'de'));
if ('de' === $market && _is_plugin_active('woocommerce/woocommerce.php') && ! _is_plugin_active(
  'woocommerce-german-market-light/woocommerce-german-market-light.php'
)) {
  NBA::register('woocommerce-gml', \__('Legally compliant selling with German Market Light', 'ionos-essentials'), \__('Use the free extension for WooCommerce to operate your online store in Germany and Austria in a legally compliant manner.', 'ionos-essentials'), '#', \__('Install now', 'ionos-essentials'), _is_plugin_active(
    'woocommerce-german-market-light/WooCommerce-German-Market-Light.php'
  ), false, ['after-setup']);
}

if ('extendable' === get_stylesheet()) {
  NBA::register('social-media', \__('Social Media Setup', 'ionos-essentials'), \__(
    'Connect your social media profiles to your website and expand your online presence.',
    'ionos-essentials'
  ), \admin_url(
    'site-editor.php?postId=extendable%2F%2Ffooter&postType=\wp_template_part&focusMode=true&canvas=edit'
  ), \__('Connect Social Media', 'ionos-essentials'), false, true, ['after-setup']);

  $custom_logo_id           = get_theme_mod('custom_logo');
  $logo                     = \wp_get_attachment_image_src($custom_logo_id, 'full');
  $logo_src                 = $logo ? $logo[0] : '';
  $is_default_or_empty_logo = false !== strpos($logo_src, 'extendify-demo-logo.png') || '' === $logo_src;

  NBA::register('upload-logo', \__('Add Logo', 'ionos-essentials'), \__(
    'Ensure your website is branded with your unique logo for a professional look.',
    'ionos-essentials'
  ), \admin_url(
    'site-editor.php?postId=extendable%2F%2Fheader&postType=\wp_template_part&focusMode=true&canvas=edit&essentials-nba=true'
  ), \__('Add Logo', 'ionos-essentials'), ! $is_default_or_empty_logo, false, ['after-setup']);
}

NBA::register('favicon', \__('Add Favicon', 'ionos-essentials'), \__(
  'Add a favicon (site icon) to your website to enhance brand recognition and visibility.',
  'ionos-essentials'
), \admin_url('options-general.php'), \__('Add Favicon', 'ionos-essentials'), 0 < intval(\get_option('site_icon', 0)), false, ['after-setup']);

$contact_query = new \WP_Query([
  'post_type'      => 'page',
  'title'          => __('contact', 'extendify-local'),
  'posts_per_page' => 1,
  'fields'         => 'ids',
  'meta_query'     => [
    [
      'key'     => 'made_with_extendify_launch',
      'compare' => '1',
    ],
  ],
]);
$contact_post_id = ! empty($contact_query->posts) ? $contact_query->posts[0] : 0;
if ($contact_post_id) {
  NBA::register('personalize-business-data', \__('Personalize business data', 'ionos-essentials'), \__(
    'Add your business details, like a phone number, email, and address, to your website.',
    'ionos-essentials'
  ), \admin_url('post.php?post=' . $contact_post_id . '&action=edit'), \__('Personalize business data', 'ionos-essentials'), false, true, ['setup-ai']);
}

function has_legal_page_for_locale(&$post_id = null)
{
  $locale = \get_locale();

  // Check if locale is a variation of German or French
  if (strpos($locale, 'de') === 0) {
    $keyword = 'impressum';
  } elseif (strpos($locale, 'fr') === 0) {
    $keyword = 'mentions-legales';
  } else {
    return false;
  }

  $pages = \get_pages([
    'post_status' => 'publish',
  ]);

  foreach ($pages as $page) {
    $slug = strtolower($page->post_name);

    if (strpos($slug, $keyword) !== false) {
      $post_id = $page->ID;
      return true;
    }
  }

  return false;
}

$legal_post_id = null;

if (has_legal_page_for_locale($legal_post_id)) {
  NBA::register('extendify-imprint', __('Create Legal Notice', 'ionos-essentials'), __('Insert the necessary data for your company and your industry (or industries) into the legal notice (Impressum) template in order to operate your new website in a legally compliant manner.', 'ionos-essentials'), \admin_url('post.php?post=' . $legal_post_id . '&action=edit'), __('Edit now', 'ionos-essentials'), false, true, ['setup-ai']);
}

if ('extendable' === get_stylesheet() && \get_option('extendify_onboarding_completed')) {
  NBA::register('extendify-agent', \__('New AI Agent with Enhanced Capabilities', 'ionos-essentials'), \__('Our new AI Agent is here to change the way you edit your site! Simply point and click on elements to make changes and try the new capabilities, from font and style changes to rearranging content.', 'ionos-essentials'), \add_query_arg('ionos-highlight', 'chatbot', home_url()), \__('Try it', 'ionos-essentials'), false, true, ['always'], 'machine-learning', true);
}

NBA::register('tools-and-security', \__('\'Tools & Security\' area', 'ionos-essentials'), \__("All the features from your previous security plugin have now found their new home here. Plus, you'll find a new maintenance page function that you can switch on whenever you need it.", 'ionos-essentials'), '#tools', \__('Visit Tools & Security', 'ionos-essentials'), false, true, ['always'], 'megaphone', true);

if ('ionos' === Tenant::get_slug()) {
  NBA::register('survey', \__('Help us shape WordPress for you', 'ionos-essentials'), \__("We're always looking for ways to make your WordPress hosting experience even better. Please take a few minutes to fill out a quick online survey.", 'ionos-essentials'), get_survey_url(), \__('Take the survey', 'ionos-essentials'), false, true, ['always'], 'conversation', true);
}
