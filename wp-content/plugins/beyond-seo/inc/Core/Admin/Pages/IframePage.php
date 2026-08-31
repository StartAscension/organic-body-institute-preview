<?php
declare( strict_types=1 );

namespace RankingCoach\Inc\Core\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Exception;
use RankingCoach\Inc\Core\Admin\AdminManager;
use RankingCoach\Inc\Core\Admin\AdminPage;
use RankingCoach\Inc\Core\Api\HttpApiClient;
use RankingCoach\Inc\Core\Base\BaseConstants;
use RankingCoach\Inc\Core\Base\Traits\RcLoggerTrait;
use RankingCoach\Inc\Core\ChannelFlow\OptionStore;
use RankingCoach\Inc\Core\ChannelFlow\Traits\FlowGuardTrait;
use RankingCoach\Inc\Core\Helpers\CoreHelper;
use RankingCoach\Inc\Core\Helpers\WordpressHelpers;
use RankingCoach\Inc\Core\Jobs\AccountSyncJob;
use RankingCoach\Inc\Core\ChannelFlow\ChannelResolver;
use RankingCoach\Inc\Core\Plugin\RankingCoachPlugin;
use RankingCoach\Inc\Core\TokensManager;
use RankingCoach\Inc\Exceptions\HttpApiException;
use RankingCoach\Inc\Exceptions\InvalidTokenException;
use RankingCoach\Inc\Traits\SingletonManager;
use ReflectionException;
use RankingCoach\Inc\Core\Settings\SettingsManager;

use Throwable;
use function rceh;

/**
 * Class IframePage
 *
 * Singleton AdminIframePage Class
 * @method IframePage getInstance(): static
 */
class IframePage extends AdminPage
{
    use SingletonManager;
    use RcLoggerTrait;
    use FlowGuardTrait;

    public string $name = 'main';

    public static ?AdminManager $managerInstance = null;

    /** Feature flag: when true, IframePage will use FlowManager to guard access (step must be 'main' or 'done'). */
    private bool $flowGuardEnabled = false;

    /**
     * IframePage constructor.
     * Initializes the IframePage instance.
     */
    public function __construct() {
        parent::__construct();
        $this->flowGuardEnabled = OptionStore::isFlowGuardActive();
    }

    /**
     * @return string
     */
    public function page_name(): string
    {
        return $this->name;
    }

    /**
     * Registers the reset and reactivate form submission handler.
     */
    public function handleResetAndReactivate(): void {
        add_action('admin_post_rc_reset_and_reactivate', [$this, 'processResetAndReactivate']);
    }

    /**
     * Processes the reset and reactivate form submission.
     */
    public function processResetAndReactivate(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'beyond-seo'));
        }

        $nonce = WordpressHelpers::sanitize_input('POST', '_wpnonce');
        if (empty($nonce) || !wp_verify_nonce($nonce, 'rc_reset_and_reactivate')) {
            wp_die(esc_html__('Nonce verification failed.', 'beyond-seo'));
        }

        $this->log('Reset and Reactivate triggered.', 'INFO');
        TokensManager::instance()->resetActivationData();

        // Determine where to redirect based on channel
        $store = new OptionStore();
        $resolver = new ChannelResolver($store);
        $channel = $resolver->resolve();
        $nextPage = ($channel === 'ionos' || $channel === 'extendify') ? 'activation' : 'registration';

        if (self::$managerInstance instanceof AdminManager) {
            self::$managerInstance->redirectPage($nextPage);
        }
        exit;
    }

    /**
     * Main page content renderer (dashboard iframe).
     * - Decoupled cookie detection UI into template: views/cookie/third-party-cookie-warning.php
     * - Decoupled iframe UI into template: views/iframe-page.php
     * - Optional Flow guard (feature-flagged)
     *
     * @param callable|null $failCallback
     * @return void
     * @throws HttpApiException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function page_content(?callable $failCallback = null): void
    {
        // Optional flow guard (disabled by default; enable by flipping $this->flowGuardEnabled)
        $this->applyFlowGuard($failCallback);

        /** @var TokensManager $tokensManager */
        $tokensManager = TokensManager::instance();
        $locationId   = (int) get_option(BaseConstants::OPTION_RANKINGCOACH_LOCATION_ID, 0);

        try {
            // Ensure we have a valid access token (refresh if needed)
            $accessToken = $tokensManager->getAccessToken(static::class);
        } catch (Throwable $e) {
            $this->log('Token validation failed in IframePage: ' . $e->getMessage(), 'ERROR');
            
            // Re-trigger flow guard to force redirect if not activated
            $this->applyFlowGuard($failCallback, true);
            
            // Fallback redirect if flow guard didn't catch it
            $store = new OptionStore();
            $resolver = new ChannelResolver($store);
            $channel = $resolver->resolve();
            $nextPage = ($channel === 'ionos' || $channel === 'extendify') ? 'activation' : 'registration';
            
            if (self::$managerInstance instanceof AdminManager) {
                self::$managerInstance->redirectPage($nextPage);
            }
            exit;
        }

        if (!$accessToken || !$tokensManager::validateToken($accessToken)) {
            rceh()->error(new InvalidTokenException('The access token is invalid or expired'));
        }

        // Handle 'ref' query parameter for account sync
        // ===============================================
        // If 'ref=account_sync' means that a successful upsell occurred and we need to trigger an account sync
        // ===============================================
        $ref = WordpressHelpers::sanitize_input('GET', 'ref');
        if ($ref === 'account_sync') {
            try {
                $accountSyncJob = AccountSyncJob::instance();
                $syncSuccess = $accountSyncJob->forceSync();
                if ($syncSuccess) {
                    $this->log('Account sync triggered by ref parameter', 'INFO');
                } else {
                    $this->log('Account sync failed', 'ERROR');
                }
            } catch (Exception $e) {
                $this->log('Error during account sync: ' . $e->getMessage(), 'ERROR');
            }
        }

        // Build iframe URL from config
        $config    = require RANKINGCOACH_PLUGIN_APP_DIR . 'config/app/externalIntegrations.php';
        $language  = WordpressHelpers::current_language_code_helper(WordpressHelpers::get_wp_locale()) ?? 'en';
        $locale    = WordpressHelpers::get_wp_locale();
        $baseEnv   = RankingCoachPlugin::isProductionMode() ? 'liveEnv' : 'devEnv';
        $installationId = (string)get_option(BaseConstants::OPTION_INSTALLATION_ID, '');
        $parentOrigin = urlencode(site_url());
        $iframeUrl = sprintf($config['iframeUrl'], $config[$baseEnv], $language, $locationId, $installationId, $parentOrigin, $accessToken);
        if(get_option(BaseConstants::OPTION_RANKINGCOACH_COUPON_CODE)) {
            $couponCode = (string)get_option(BaseConstants::OPTION_RANKINGCOACH_COUPON_CODE);
            $iframeUrl = sprintf($config['codeUrl'], $config[$baseEnv], $locale, $couponCode, urlencode($iframeUrl));
        }

        // Check if iframe destination requires authentication
        $status = $tokensManager->checkIframeUrlStatus($iframeUrl);
        if ($status === 401) {
            $this->log('Iframe returned 401. Attempting reactivation.', 'WARNING');
            $activationCode = get_option(BaseConstants::OPTION_ACTIVATION_CODE);
            $revalidated = false;
            if (!empty($activationCode)) {
                try {
                    $apiClient = new HttpApiClient();
                    $revalidated = $apiClient->revalidateWithActivationCode($activationCode);
                } catch (Throwable $e) {
                    $this->log('Reactivation attempt failed: ' . $e->getMessage(), 'ERROR');
                }
            }

            if ($revalidated) {
                // Refresh tokens and rebuild URL
                $accessToken = $tokensManager->getAccessToken(static::class);
                $iframeUrl = sprintf($config['iframeUrl'], $config[$baseEnv], $language, $locationId, $installationId, $parentOrigin, $accessToken);
                if(get_option(BaseConstants::OPTION_RANKINGCOACH_COUPON_CODE)) {
                    $couponCode = (string)get_option(BaseConstants::OPTION_RANKINGCOACH_COUPON_CODE);
                    $iframeUrl = sprintf($config['codeUrl'], $config[$baseEnv], $locale, $couponCode, urlencode($iframeUrl));
                }
            } else {
                // Show reactivation page instead
                wp_enqueue_style(
                    'rankingcoach-activation',
                    plugin_dir_url(dirname(__FILE__)) . 'assets/css/activation.css',
                    [],
                    RANKINGCOACH_VERSION
                );
                wp_enqueue_script(
                    'rankingcoach-activation',
                    plugin_dir_url(dirname(__FILE__)) . 'assets/js/activation.js',
                    [],
                    RANKINGCOACH_VERSION,
                    true
                );
                wp_localize_script('rankingcoach-activation', 'rcActivation', [
                    'errorEmptyCode' => __('Activation code is required.', 'beyond-seo'),
                ]);
                include __DIR__ . '/views/reactivate-page.php';
                return;
            }
        }

        $settingsManager = SettingsManager::instance();
        $openInNewTab = (bool)$settingsManager->get_option('open_rc_dashboard_in_new_tab', false);
        $highestPlan = CoreHelper::isHighestPaid();

        // 1) Cookie detection UI (JS + warning overlay)
        include __DIR__ . '/views/cookie/third-party-cookie-warning.php';
        // 2) Iframe UI (skeleton + main iframe)
        include __DIR__ . '/views/iframe-page.php';

        // Include FlowGuard components if enabled
        if (defined('BSEO_FLOW_GUARD_ENABLED') && BSEO_FLOW_GUARD_ENABLED) {
            include __DIR__ . '/views/flowguard-button.php';
            include __DIR__ . '/views/flowguard-panel.php';
        }
    }
}
