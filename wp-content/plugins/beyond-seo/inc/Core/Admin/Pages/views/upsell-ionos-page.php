<?php
/**
 * IONOS-Specific Upsell Page View Template
 * 
 * This is a view file that contains the complete HTML/CSS template for the IONOS upsell page.
 * It expects variables to be passed from the parent scope.
 * 
 * Expected Variables:
 * @var string $headerBgColor - Background color for the header section
 * @var string $logoUrl - URL to the logo image
 * @var array $currentPageContent - Array containing title, description, faqTitle, and faqs
 * @var array $plans - Array of plan configurations with pricing and features
 * @var array $assistantTabs - Tabs (visibility/reputation/social), each with 3 feature cards
 * @var array $faqSection - Array containing FAQ title and items
 * @var int $currentPlanLevel - Current subscription level (0=Free, 1=Standard, 2=Advanced, 3=Social)
 * 
 * @package RankingCoach
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// IONOS-specific data preparation
$currentPlanLevel = \RankingCoach\Inc\Core\Helpers\CoreHelper::getCurrentPlanLevel();
$headerBgColor = '#021B41';
$logoUrl = plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/icons/upsell-logo-small.png';

// Shared FAQ set shown across all plan levels
$commonFaqs = [
	[
		'question' => __('1. Which subscription is suitable for small businesses?', 'beyond-seo'),
		'answer' => __("All of our plans are built for small and medium-sized businesses. Just pick the one that matches your goal, and you're ready to go.", 'beyond-seo')
	],
	[
		'question' => __('2. Do I need any technical skills?', 'beyond-seo'),
		'answer' => __("Not at all. You don't need any prior knowledge or experience, and with agents handling many of your marketing tasks, promoting your business is easier than ever.", 'beyond-seo')
	],
	[
		'question' => __('3. Can I cancel my plan?', 'beyond-seo'),
		'answer' => __('Yes. You can find all the details about subscription management and cancellation through your provider.', 'beyond-seo')
	]
];

// Page content definitions
$pageContent = [
	'standard' => [
		'title' => __('AI agents that handle your marketing', 'beyond-seo'),
		'description' => __("Select the AI assistant that best fits your needs and start growing your online presence, reputation, or social media performance.", 'beyond-seo'),
		'faqTitle' => __('FAQs', 'beyond-seo'),
		'faqs' => $commonFaqs
	],
	'advanced' => [
		'title' => __('Trust is the most important factor in a purchase. Advanced helps you get trusted by clients.', 'beyond-seo'),
		'description' => __('Customers trust what others say online about your business. With AI-powered features, you can reply to reviews right away, get more customers to share their feedback, and learn what people really think with sentiment analysis. All these features and more work together to help you build trust and stand out online.', 'beyond-seo'),
		'faqTitle' => __('FAQs', 'beyond-seo'),
		'faqs' => $commonFaqs
	],
	'social' => [
		'title' => __('60% of businesses that grew last year relied on social media. Use the social add-on to join this year`s growing businesses.', 'beyond-seo'),
		'description' => __('Show up where your clients are, share content that captures attention, and keep the conversation going. Plan, post, and boost to build trust, grow engagement, and turn followers into loyal customers', 'beyond-seo'),
		'faqTitle' => __('FAQs', 'beyond-seo'),
		'faqs' => $commonFaqs
	],
	'allUnlocked' => [
		'title' => __('60% of businesses that grew last year relied on social media. Use the social add-on to join this year`s growing businesses.', 'beyond-seo'),
		'description' => __('Show up where your clients are, share content that captures attention, and keep the conversation going. Plan, post, and boost to build trust, grow engagement, and turn followers into loyal customers', 'beyond-seo'),
		'faqTitle' => __('FAQs', 'beyond-seo'),
		'faqs' => []
	]
];

// Plans definitions
$plans = [
	// VISIBILITY
	'standard' => [
		'plan' => 'momentum_search',
		'packet' => 'momentum_search',
		'name' => __('AI Search Manager', 'beyond-seo'),
		'subtitle' => __('Be found where customers search', 'beyond-seo'),
		'price' => '', // hidden on UI
		'period' => '',
		'isCurrent' => false,
		'isRecommended' => false,
		'features' => [
			__('AI Presence agent', 'beyond-seo'),
			__('Create a Google Ads campaign with AI', 'beyond-seo'),
			__('Publish to GBP, Facebook & major online directories', 'beyond-seo'),
			__('Monitor your visibility across major AI models', 'beyond-seo'),
			__('Get expert guidance from AI Advisors', 'beyond-seo'),
			__('Google Analytics integration (GA4)', 'beyond-seo'),
			__('SEO tasks with video tutorials', 'beyond-seo'),
			__('AI text SEO', 'beyond-seo'),
			__('AI URL optimiser', 'beyond-seo'),
			__('Detailed SEO insights', 'beyond-seo'),
			__('Brand & competitor monitoring feed', 'beyond-seo'),
		],
		'upgradeButton' => [
			'show' => true,
			'text' => __('Upgrade', 'beyond-seo'),
			'link' => admin_url('admin.php?page=rankingcoach-upsell&step=upsell&planSelected=momentum_search'),
		],
		'hasTermsAndConditions' => false,
		'termsLink' => '',
		'privacyLink' => '',
	],
	// REPUTATION
	'advanced' => [
		'plan' => 'momentum_reputation',
		'packet' => 'momentum_reputation',
		'name' => __('AI Reputation Manager', 'beyond-seo'),
		'subtitle' => __('Grow and protect your reputation', 'beyond-seo'),
		'price' => '', // hidden on UI
		'period' => '',
		'isCurrent' => false,
		'isRecommended' => false,
		'features' => [
			__('AI Reputation agent', 'beyond-seo'),
			__('Manage all your reviews in one place', 'beyond-seo'),
			__('AI auto-reply to reviews', 'beyond-seo'),
			__('AI reputation sentiment analysis', 'beyond-seo'),
			__("Generate AI replies that match your brand's voice", 'beyond-seo'),
			__('Collect & display reviews on your website with widgets', 'beyond-seo'),
			__('Get more reviews via email & printable materials', 'beyond-seo'),
			__('Publish to Google, Facebook & major online directories', 'beyond-seo'),
			__('Get expert guidance from AI Advisors', 'beyond-seo'),
			__('See how you compare to local competitors with AI', 'beyond-seo'),
			__('Detailed Reputation insights', 'beyond-seo'),
			__('Get instant alerts for new reviews', 'beyond-seo'),
		],
		'upgradeButton' => [
			'show' => true,
			'text' => __('Upgrade', 'beyond-seo'),
			'link' => admin_url('admin.php?page=rankingcoach-upsell&step=upsell&planSelected=momentum_reputation'),
		],
		'hasTermsAndConditions' => false,
		'termsLink' => '',
		'privacyLink' => '',
	],
	// SOCIAL
	'social' => [
		'plan' => 'momentum_marketing',
		'packet' => 'momentum_marketing',
		'name' => __('AI Marketing Manager', 'beyond-seo'),
		'subtitle' => __('Create content effortlessly', 'beyond-seo'),
		'price' => '', // hidden on UI
		'period' => '',
		'isCurrent' => false,
		'isRecommended' => false,
		'features' => [
			__('AI Social agent', 'beyond-seo'),
			__('Create & publish posts on multiple channels with AI', 'beyond-seo'),
			__('Create entire series of posts with AI', 'beyond-seo'),
			__('Generate text & images with AI', 'beyond-seo'),
			__('Schedule posts in advance', 'beyond-seo'),
			__('Plan everything in calendar', 'beyond-seo'),
			__('Get expert guidance from AI Advisors', 'beyond-seo'),
			__('Track link performance', 'beyond-seo'),
			__('View & respond to post comments', 'beyond-seo'),
			__('Detailed Social Media insights', 'beyond-seo'),
		],
		'upgradeButton' => [
			'show' => true,
			'text' => __('Upgrade', 'beyond-seo'),
			'link' => admin_url('admin.php?page=rankingcoach-upsell&step=upsell&planSelected=momentum_marketing'),
		],
		'hasTermsAndConditions' => false,
		'termsLink' => '',
		'privacyLink' => '',
	],
];

// Current page content selection
$currentPageContent = match ($currentPlanLevel) {
	0 => $pageContent['standard'],
	1 => $pageContent['advanced'],
	2 => $pageContent['social'],
	default => $pageContent['allUnlocked']
};

// Assistant tabs — each tab shows 3 feature cards (illustration + text), in a
// swipeable/tabbed carousel. Card layout alternates left/right by index.
$assistantTabs = [
	'visibility' => [
		'label' => __('AI Search Manager', 'beyond-seo'),
		'cards' => [
			[
				'title' => __('Rank higher in AI search results', 'beyond-seo'),
				'description' => __('Follow step-by-step SEO video tutorials. Optimise your pages with AI, connect Google Analytics, track keyword rankings against competitors, and access performance reports anytime. No SEO experience needed.', 'beyond-seo'),
				'image' => 'assets/svg/rank-higher-in.svg',
			],
			[
				'title' => __('Your agent makes sure customers find you online', 'beyond-seo'),
				'description' => __("The Presence agent scans online directories such as Google Business Profile, Facebook or Waze and instantly spots what's missing or incomplete. Then it automatically publishes your profile on all major directories. Simply approve it, and the agent will handle the rest.", 'beyond-seo'),
				'image' => 'assets/svg/your-agent-makes.svg',
			],
			[
				'title' => __('See how AI models recommend your business', 'beyond-seo'),
				'description' => __('Understand how platforms like ChatGPT, Gemini, and Perplexity see your brand. Get clear, actionable recommendations to improve your visibility and boost your chances of being found by potential customers.', 'beyond-seo'),
				'image' => 'assets/svg/see-how-ai.svg',
			],
		],
	],
	'reputation' => [
		'label' => __('AI Reputation Manager', 'beyond-seo'),
		'cards' => [
			[
				'title' => __('All your reviews in one place', 'beyond-seo'),
				'description' => __('See and manage reviews from Google, Facebook, and other platforms in a single location. Stay on top of feedback and respond faster, all from one place.', 'beyond-seo'),
				'image' => 'assets/svg/all-your-reviews.svg',
			],
			[
				'title' => __('Your agent protects your reputation around the clock', 'beyond-seo'),
				'description' => __('No more missed reviews or uncertainty about how to reply. The Reputation agent responds in your voice, reads the emotion behind every review, and flags exactly what needs your attention. Then it suggests the best next steps to protect and improve your reputation.', 'beyond-seo'),
				'image' => 'assets/svg/your-agent-protects.svg',
			],
			[
				'title' => __('Get more reviews effortlessly', 'beyond-seo'),
				'description' => __('Make it simple for customers to leave reviews. Send requests by email, or display printed materials with QR codes at your location so customers can leave a review in seconds.', 'beyond-seo'),
				'image' => 'assets/svg/get-more-reviews.svg',
			],
		],
	],
	'social' => [
		'label' => __('AI Marketing Manager', 'beyond-seo'),
		'cards' => [
			[
				'title' => __('Write once and post everywhere', 'beyond-seo'),
				'description' => __("No more copying and pasting the same content on different platforms. Write your post once and publish it across all major social media channels with a single click. Your content is automatically adapted to each platform's requirements.", 'beyond-seo'),
				'image' => 'assets/svg/write-once-and.svg',
			],
			[
				'title' => __('The agent helps your business stay active on social media', 'beyond-seo'),
				'description' => __("We know posting regularly can be hard. That's why the Social agent makes it simple. It can create, schedule, and publish posts for you, whether you want a single post or an entire series. Just approve them, and the agent takes care of the rest.", 'beyond-seo'),
				'image' => 'assets/svg/the-agent-helps.svg',
			],
			[
				'title' => __('Turn your idea into a complete series in seconds', 'beyond-seo'),
				'description' => __('Building a series of posts that truly connects, matches your vision, and goes out on time can be a headache. We make it easy. Just describe your idea, and our AI writes every post, picks the best times to publish, and ties it all together into one clear, cohesive story.', 'beyond-seo'),
				'image' => 'assets/svg/turn-your-idea.svg',
			],
		],
	],
];

// FAQ section
$faqSection = [
	'title' => $currentPageContent['faqTitle'] ?? '',
	'items' => $currentPageContent['faqs'] ?? []
];

?>
<div class='wrap rankingcoach-upsell-page'>
    <div class='rankingcoach-upsell-header' style="background-color: <?php echo esc_attr($headerBgColor); ?>;">
        <div class='rankingcoach-upsell-logo'>
            <img src="<?php echo esc_url($logoUrl); ?>" alt='RankingCoach Logo'>
        </div>
    </div>

    <div class='rankingcoach-upsell-content'>
        <h2 class='rankingcoach-upsell-title'><?php echo esc_html($currentPageContent['title']); ?></h2>

        <p class="rankingcoach-upsell-description">
            <?php echo esc_html($currentPageContent['description']); ?>
        </p>

        <?php
        // Display current plan message for all subscription levels
        $currentPlanDisplayName = '';
        switch ($currentPlanLevel) {
            case 0:
                $currentPlanDisplayName = __('Radar Free', 'beyond-seo');
                break;
            case 1:
                $currentPlanDisplayName = __('Standard', 'beyond-seo');
                break;
            case 2:
                $currentPlanDisplayName = __('Advanced', 'beyond-seo');
                break;
            case 3:
                $currentPlanDisplayName = __('Social', 'beyond-seo');
                break;
        }
        // Hide the "currently using" banner once the customer owns any plan packet.
        $hasOwnedPackets = !empty(\RankingCoach\Inc\Core\Helpers\CoreHelper::getActiveSubscriptions());
        if (!empty($currentPlanDisplayName) && !$hasOwnedPackets) : ?>
            <div class="rankingcoach-free-plan-message">
                <?php echo sprintf(
                    /* translators: %s is the plan name (e.g., Free, Standard, Advanced, Pro) */
                        esc_html__('You\'re currently using the %s plan', 'beyond-seo'),
                    '<strong>' . esc_html($currentPlanDisplayName) . '</strong>'
                ); ?>
            </div>
        <?php endif; ?>

        <div class="rankingcoach-pricing-plans">
                <?php if (empty($plans)) : ?>
                <div class="rankingcoach-no-plans">
                    <p><?php echo esc_html__('No plans available at the moment. Please try again later.', 'beyond-seo'); ?></p>
                </div>
            <?php else :
                // Dynamic plans display
                $planKeys = array_keys($plans);
                foreach ($planKeys as $index => $key) :
                    $plan = $plans[$key];

                    // Packet-based ownership: a customer may own several packets at the
                    // same time (e.g. momentum_search, momentum_reputation). All plans are
                    // always shown — owned ones are marked and disabled rather than skipped.
                    $isOwned = !empty($plan['packet'])
                        && \RankingCoach\Inc\Core\Helpers\CoreHelper::hasActiveSubscription($plan['packet']);

                    // Greyed-out feature styling is not applied for owned plans here;
                    // ownership is conveyed via the label and disabled button instead.
                    $shouldBeGreyedOut = false;

                    // Mark owned plans as "current"
                    $plan['isCurrent'] = $isOwned;

                    // Only offer the upgrade button for plans the customer does not yet own
                    if (isset($plan['upgradeButton'])) {
                        $plan['upgradeButton']['show'] = !$isOwned;
                    }

                    // Determine CSS classes for the plan
                    $planClasses = [];
                    if ($isOwned) {
                        $planClasses[] = 'current-plan';
                    }
            ?>
                <div class="rankingcoach-plan <?php echo esc_attr(implode(' ', $planClasses)); ?>">

                        <div class="plan-header <?php echo esc_attr($shouldBeGreyedOut ? 'greyed-out' : ''); ?>">
                            <?php
                            // Force the last word (e.g. "Assistant") onto its own line so every
                            // card title spans two lines consistently.
                            $planName = $plan['name'] ?? '';
                            $lastSpace = strrpos($planName, ' ');
                            if ($lastSpace !== false) {
                                $planNameHtml = esc_html(substr($planName, 0, $lastSpace)) . '<br>' . esc_html(substr($planName, $lastSpace + 1));
                            } else {
                                $planNameHtml = esc_html($planName);
                            }
                            ?>
                            <h3 class="plan-name"><?php echo wp_kses($planNameHtml, ['br' => []]); ?></h3>
                            <?php if (!empty($plan['subtitle'])) : ?>
                                <p class="plan-subtitle"><?php echo esc_html($plan['subtitle']); ?></p>
                            <?php endif; ?>
                        </div>

                    <?php if ($isOwned) : ?>
                            <div class="plan-cta">
                                <button type="button" class="button upgrade-button disabled" disabled aria-disabled="true">
                                    <?php esc_html_e('You currently have this plan', 'beyond-seo'); ?>
                                </button>
                            </div>
                        <?php elseif (isset($plan['upgradeButton']) && isset($plan['upgradeButton']['show']) && $plan['upgradeButton']['show']) : ?>
                                <div class="plan-cta">
                                    <a href="<?php echo esc_url($plan['upgradeButton']['link'] ?? '#'); ?>" class="button button-primary upgrade-button">
                                        <?php echo esc_html($plan['upgradeButton']['text'] ?? __('Upgrade', 'beyond-seo')); ?>
                                    </a>
                                </div>
                        <?php endif; ?>

                        <div class="plan-features">
                            <ul>
                                <?php if (isset($plan['features']) && is_array($plan['features'])) : ?>
                                    <?php $visibleFeatures = 4; ?>
                                    <?php foreach ($plan['features'] as $index => $feature) : ?>
                                        <li class="<?php echo esc_attr(($shouldBeGreyedOut ? 'greyed-out-text' : '')); ?> <?php echo ($index >= $visibleFeatures) ? 'feature-hidden' : ''; ?>">
                                            <span class="checkmark <?php echo esc_attr(($shouldBeGreyedOut ? 'greyed-out-checkmark' : '')); ?>">✓</span>
                                            <?php echo esc_html($feature); ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            <?php if (isset($plan['features']) && is_array($plan['features']) && count($plan['features']) > $visibleFeatures) : ?>
                                <div class="show-more-container">
                                    <button class="show-more-btn" type="button">
                                        <span class="show-more-text"><?php esc_html_e('Show more', 'beyond-seo'); ?></span>
                                        <span class="show-more-icon" aria-hidden="true">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 9l7 7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Assistant Tabs (swipeable) -->
        <?php $tabKeys = array_keys($assistantTabs); ?>
        <div class="rankingcoach-assistant-tabs" data-active-index="0">
            <div class="assistant-tabs-nav" role="tablist">
                <?php foreach ($tabKeys as $tabIndex => $tabKey) :
                    $tab = $assistantTabs[$tabKey]; ?>
                    <button
                        type="button"
                        class="assistant-tab <?php echo $tabIndex === 0 ? 'active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo $tabIndex === 0 ? 'true' : 'false'; ?>"
                        data-index="<?php echo esc_attr($tabIndex); ?>"
                    >
                        <span class="assistant-tab-label"><?php echo esc_html($tab['label']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="assistant-tabs-viewport">
                <div class="assistant-tabs-track">
                    <?php foreach ($tabKeys as $tabIndex => $tabKey) :
                        $tab = $assistantTabs[$tabKey]; ?>
                        <div
                            class="assistant-tab-panel <?php echo $tabIndex === 0 ? 'active' : ''; ?>"
                            role="tabpanel"
                            data-index="<?php echo esc_attr($tabIndex); ?>"
                            <?php echo $tabIndex === 0 ? '' : 'aria-hidden="true"'; ?>
                        >
                            <div class="rankingcoach-feature-cards">
                                <?php foreach ($tab['cards'] as $cardIndex => $card) :
                                    $layoutClass = ($cardIndex % 2 === 0) ? 'image-left' : 'image-right'; ?>
                                    <div class="feature-card <?php echo esc_attr($layoutClass); ?>">
                                        <div class="feature-content">
                                            <h3 class="feature-title"><?php echo esc_html($card['title'] ?? ''); ?></h3>
                                            <p class="feature-description">
                                                <?php echo esc_html($card['description'] ?? ''); ?>
                                            </p>
                                        </div>
                                        <div class="feature-image">
                                            <img src="<?php echo esc_url(plugin_dir_url( dirname( __DIR__ ) ) . ($card['image'] ?? '')); ?>" alt="<?php echo esc_attr($card['title'] ?? ''); ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- End of rankingcoach-assistant-tabs -->

        <!-- FAQ Section -->
        <div class="rankingcoach-faq-section">
            <h2 class="faq-section-title"><?php echo esc_html($faqSection['title']); ?></h2>
            <div class="faq-items-container">
                <?php if (!empty($faqSection['items'])) : ?>
                    <?php foreach ($faqSection['items'] as $faq) : ?>
                        <div class="faq-item">
                            <h3 class="faq-question"><?php echo esc_html($faq['question']); ?></h3>
                            <p class="faq-answer"><?php echo esc_html($faq['answer']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- End of rankingcoach-faq-section -->
    </div>

    <style>
        /* Font Face Declarations */
        @font-face {
            font-family: 'OpenSans';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/OpenSans-Regular.ttf'); ?>') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'OpenSans';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/OpenSans-Light.ttf'); ?>') format('truetype');
            font-weight: 300;
            font-style: normal;
        }

        @font-face {
            font-family: 'OpenSans';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/OpenSans-Semibold.ttf'); ?>') format('truetype');
            font-weight: 600;
            font-style: normal;
        }

        @font-face {
            font-family: 'OpenSans';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/OpenSans-Bold.ttf'); ?>') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: 'Overpass';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/Overpass-Regular.ttf'); ?>') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Overpass';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/Overpass-Light.ttf'); ?>') format('truetype');
            font-weight: 300;
            font-style: normal;
        }

        @font-face {
            font-family: 'Overpass';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/Overpass-Bold.ttf'); ?>') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: 'Overpass';
            src: url('<?php echo esc_url(plugin_dir_url(RANKINGCOACH_FILE) . 'inc/Core/Admin/assets/fonts/Overpass-ExtraBold.ttf'); ?>') format('truetype');
            font-weight: 800;
            font-style: normal;
        }

        body {
            background: white;
            font-family: 'OpenSans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
        }
        .rankingcoach-upsell-page {
            max-width: none;
            margin: -20px -20px 0 -20px;
            background-color: white;
        }

        .rankingcoach-upsell-header {
            width: 100%;
            height: 72px;
            padding-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 112px;
            position: relative;
        }

        .rankingcoach-upsell-logo {
            max-width: 1200px;
            height: 100%;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
        }

        .rankingcoach-upsell-logo img {
            max-height: 24px;
            width: auto;
            object-fit: contain;
        }

        .rankingcoach-upsell-content {
            text-align: center;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 60px;
        }

    .rankingcoach-upsell-title,
    .rankingcoach-upsell-subtitle {
        font-family: 'Overpass', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
        font-size: 32px;
        font-weight: 600;
        line-height: 1.1;
        margin-top: 0;
        margin-bottom: 30px;
        color: #333;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }

    .rankingcoach-upsell-description {
        font-size: 16px;
        line-height: 1.6;
        color: #666;
        margin-bottom: 10px;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }				.rankingcoach-pricing-plans {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .rankingcoach-plan {
            background: #fff;
            border: 1px solid #C9D3E2;
            border-radius: 20px;
            flex: 1;
            min-width: 280px;
            max-width: 340px;
            display: flex;
            flex-direction: column;
            position: relative;
            padding: 36px 32px 28px;
            transition: transform 0.2s ease-in-out;
        }

        .plan-header {
            text-align: left;
        }

        .plan-name {
            font-family: 'Overpass', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            color: #0B2A63;
            /* Reserve two lines so every card's title/subtitle/button align,
               even when a title fits on a single line */
            min-height: 2.4em;
        }

        .plan-subtitle {
            margin: 12px 0 0;
            font-size: 16px;
            line-height: 1.4;
            color: #0B2A63;
        }

        .plan-header.greyed-out .plan-name,
        .plan-header.greyed-out .plan-subtitle {
            color: #8A97AC;
        }

        .plan-pricing {
            display: none; /* Hide pricing section */
        }

        .current-plan-label {
            color: #B6C1D3;
            padding: 24px 0 0;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
        }

        .plan-cta {
            padding: 28px 0 8px;
            text-align: center;
        }

        .upgrade-button {
            display: inline-block;
            min-width: 180px;
            height: auto !important;
            line-height: 1.4 !important;
            padding: 8px 28px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            border-radius: 999px !important;
            background-color: #5CC4E2 !important;
            border-color: #5CC4E2 !important;
            color: #0B2A63 !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        .upgrade-button:hover {
            background-color: #4BB6D6 !important;
            border-color: #4BB6D6 !important;
            color: #0B2A63 !important;
        }

        .upgrade-button.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        .current-plan .upgrade-button {
            background-color: #B7C2D6 !important;
            border-color: #B7C2D6 !important;
            color: #fff !important;
        }

        .plan-features {
            padding: 24px 0 0;
            flex-grow: 1;
        }

        .plan-features ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .plan-features li {
            margin-bottom: 18px;
            padding-left: 30px;
            position: relative;
            font-size: 16px;
            line-height: 1.4;
            color: #0B2A63;
        }

        .plan-features .checkmark {
            position: absolute;
            left: 0;
            top: 0;
            color: #43B02A;
            font-weight: bold;
        }

        .plan-features li.greyed-out-text {
            color: #8A97AC;
        }

        .plan-features .greyed-out-checkmark {
            color: #B7C2D6;
        }

        .feature-hidden {
            display: none;
        }

        .show-more-container {
            text-align: center;
            margin-top: 8px;
        }

        .show-more-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 600;
            color: #0B2A63;
            padding: 8px;
        }

        .show-more-btn .show-more-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 0;
            transition: transform 0.2s ease;
        }

        .show-more-btn .show-more-icon svg {
            display: block;
        }

        .show-more-btn.expanded .show-more-icon {
            transform: rotate(180deg);
        }

        .plan-terms {
            padding: 10px 20px;
            font-size: 12px;
            text-align: left;
            line-height: 1.4;
            color: #666;
        }

        .plan-terms input {
            margin-right: 5px;
        }

        .plan-terms a {
            color: #0073aa;
        }

        .terms-error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
            text-align: left;
        }

        .wp-admin #wpcontent {
            padding-left: 0;
        }

        .wp-admin .notice {
            display: none;
        }

        /* Assistant Tabs (swipeable) */
        .rankingcoach-assistant-tabs {
            max-width: 900px;
            margin: 100px auto 0;
            padding: 0 20px;
        }

        .assistant-tabs-nav {
            position: relative;
            display: flex;
            justify-content: center;
            gap: 56px;
            border-bottom: 1px solid #D9E0EA;
        }

        .assistant-tab {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 4px 16px;
            font-family: 'Overpass', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            font-size: 18px;
            font-weight: 600;
            line-height: 1.2;
            color: #1A2B4A;
            white-space: nowrap;
            transition: color 0.25s ease;
        }

        /* Underline indicator for the active tab (pure CSS, no JS measuring) */
        .assistant-tab::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -2px;
            height: 2px;
            background-color: #2E6DE6;
            border-radius: 4px;
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .assistant-tab:hover {
            color: #2E6DE6;
        }

        .assistant-tab.active {
            color: #2E6DE6;
        }

        .assistant-tab.active::after {
            transform: scaleX(1);
        }

        .assistant-tabs-viewport {
            overflow: hidden;
            margin-top: 60px;
            touch-action: pan-y;
            transition: height 0.45s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .assistant-tabs-track {
            display: flex;
            align-items: flex-start;
            transition: transform 0.45s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }

        .assistant-tab-panel {
            flex: 0 0 100%;
            width: 100%;
        }

        /* Feature Cards Styling */
        .rankingcoach-feature-cards {
            max-width: 900px;
            margin: 0 auto;
            padding: 0;
        }

        .feature-card {
            display: flex;
            align-items: flex-start;
            margin-bottom: 80px;
            justify-content: center;
            gap: 60px;
        }

        .assistant-tab-panel .feature-card:last-child {
            margin-bottom: 0;
        }

        .feature-card.image-right {
            flex-direction: row-reverse;
        }

        .feature-content {
            width: 360px;
            margin: 0 40px;
            padding-top: 20px;
            text-align: left;
        }

        .feature-image {
            width: 360px;
            display: flex;
            justify-content: center;
        }

        .feature-image img {
            max-width: 100%;
            height: auto;
        }

        .feature-title {
            font-family: 'Overpass', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            font-size: 24px;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 24px;
            color: #0B2A63;
            line-height: 1.3;
            text-align: left;
        }

        .feature-description {
            text-align: left;
            font-size: 16px;
            line-height: 1.6;
            color: #506586;
            margin: 0;
        }

        /* FAQ Section Styling */
        .rankingcoach-faq-section {
            max-width: 900px;
            margin: 80px auto 40px;
            padding: 0 20px;
            text-align: center;
        }

        .faq-section-title {
            font-family: 'Overpass', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-top: 0;
            margin-bottom: 40px;
        }

        .faq-item {
            margin-bottom: 30px;
        }

        .faq-question {
            font-family: 'Overpass', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .faq-answer {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin: 0;
        }

        .rankingcoach-free-plan-message {
            display: inline-block;
            background-color: #F5F7F9;
            border: 1px solid #B6C1D3;
            color: #506586;
            padding: 12px 30px;
            border-radius: 5px;
            text-align: center;
            font-size: 18px;
            line-height: 1.4;
            margin: 20px auto 50px;
        }

        /*Mobile view*/
        @media (max-width: 768px) {
            .rankingcoach-upsell-header {
                margin-bottom: 60px;
            }

            .rankingcoach-upsell-title,
            .rankingcoach-upsell-subtitle {
                font-size: 26px;
                margin-bottom: 20px;
            }

            .rankingcoach-upsell-description {
                font-size: 15px;
                margin-bottom: 30px;
            }

            .rankingcoach-assistant-tabs {
                margin-top: 60px;
            }

            .assistant-tabs-nav {
                gap: 24px;
                justify-content: flex-start;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }

            .assistant-tabs-nav::-webkit-scrollbar {
                display: none;
            }

            .assistant-tab {
                font-size: 15px;
                padding-bottom: 12px;
            }

            .assistant-tabs-viewport {
                margin-top: 36px;
            }

            .faq-section-title {
                font-size: 28px;
                margin-bottom: 30px;
            }

            .faq-question {
                font-size: 18px;
            }

            .faq-answer {
                font-size: 15px;
            }

            .feature-card,
            .feature-card:nth-child(2) {
                flex-direction: column !important;
                text-align: center;
                gap: 15px;
            }

            .feature-content,
            .feature-image {
                flex: 1 1 auto;
                max-width: 100%;
                width: auto;
                margin: 0;
            }

            .feature-title {
                margin-bottom: 16px;
            }
        }
    </style>
</div>
