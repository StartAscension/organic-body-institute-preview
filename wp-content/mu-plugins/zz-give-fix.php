<?php
/*
Plugin Name: Give Onboarding Wizard Fix
Description: Disables Give's Onboarding Wizard admin_menu hook, which fatals in this hosting environment (give_onboarding option resolves incorrectly during admin_menu). Give's donation forms, shortcodes, and other admin pages are unaffected.
*/
// The entire Give Onboarding module (first-run setup wizard + its REST routes) is broken
// in this hosting environment: SettingsRepositoryFactory::make('give_onboarding') resolves
// to false instead of an array during admin_menu/admin_init/rest_api_init here (works fine
// on the original source server). None of this is core donation functionality - donation
// forms, shortcodes, and the rest of Give's admin pages are unaffected by disabling it.
add_filter('give_disable_hook-admin_menu:Give\Onboarding\Wizard\Page@add_page', '__return_true');
add_filter('give_disable_hook-admin_init:Give\Onboarding\Wizard\Page@redirect', '__return_true');
add_filter('give_disable_hook-admin_init:Give\Onboarding\Wizard\Page@setup_wizard', '__return_true');
add_filter('give_disable_hook-admin_enqueue_scripts:Give\Onboarding\Wizard\Page@enqueue_scripts', '__return_true');
add_filter('give_disable_hook-admin_menu:Give\Onboarding\Wizard\FormPreview@add_page', '__return_true');
add_filter('give_disable_hook-admin_init:Give\Onboarding\Wizard\FormPreview@setup_form_preview', '__return_true');
add_filter('give_disable_hook-rest_api_init:Give\Onboarding\Routes\FormRoute@registerRoute', '__return_true');
add_filter('give_disable_hook-rest_api_init:Give\Onboarding\Routes\LocationRoute@registerRoute', '__return_true');
add_filter('give_disable_hook-rest_api_init:Give\Onboarding\Routes\AddonsRoute@registerRoute', '__return_true');
add_filter('give_disable_hook-rest_api_init:Give\Onboarding\Routes\CurrencyRoute@registerRoute', '__return_true');
add_filter('give_disable_hook-rest_api_init:Give\Onboarding\Routes\FeaturesRoute@registerRoute', '__return_true');
add_filter('give_disable_hook-rest_api_init:Give\Onboarding\Routes\SettingsRoute@registerRoute', '__return_true');
add_filter('give_disable_hook-admin_init:Give\Onboarding\Setup\Handlers\AdminNoticeHandler@maybeHandle', '__return_true');
add_filter('give_disable_hook-admin_init:Give\Onboarding\Setup\Handlers\TopLevelMenuRedirect@maybeHandle', '__return_true');
add_filter('give_disable_hook-admin_menu:Give\Onboarding\Setup\Page@add_page', '__return_true');
add_filter('give_disable_hook-admin_enqueue_scripts:Give\Onboarding\Setup\Page@enqueue_scripts', '__return_true');
