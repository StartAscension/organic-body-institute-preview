<?php
declare(strict_types=1);

namespace RankingCoach\Inc\Core\ChannelFlow\Traits;

if (!defined('ABSPATH')) {
    exit;
}

use RankingCoach\Inc\Core\ChannelFlow\ChannelResolver;
use RankingCoach\Inc\Core\ChannelFlow\FlowManager;
use RankingCoach\Inc\Core\ChannelFlow\OptionStore;
use RankingCoach\Inc\Core\Helpers\WordpressHelpers;
use RankingCoach\Inc\Core\Admin\AdminManager;
use Throwable;

/**
 * FlowGuardTrait
 *
 * Shared Flow evaluation helper for admin pages to avoid duplication.
 * New canonical location: RankingCoach\Inc\Core\ChannelFlow\Traits\FlowGuardTrait
 */
trait FlowGuardTrait
{
    /**
     * Evaluate the current channel flow and return a normalized result.
     *
     * @return array{channel?:string,next_step?:string,description?:string,meta?:mixed}
     */
    private function evaluateFlow(): array
    {
        $store    = new OptionStore();
        $resolver = new ChannelResolver($store);
        $flow     = new FlowManager($store, $resolver);

        return $flow->evaluate();
    }

    /**
     * Flow guard for the page.
     *
     * @param callable|null $failCallback
     * @param bool $force Whether to force flow evaluation even if flow guard is disabled
     * @return void
     */
    private function applyFlowGuard(?callable $failCallback = null, bool $force = false): void
    {
        // $this->flowGuardEnabled is expected to be defined in the class using this trait
        $isEnabled = property_exists($this, 'flowGuardEnabled') ? $this->flowGuardEnabled : false;

        if (!$isEnabled && !$force && WordpressHelpers::isActivationCompleted()) {
            return;
        }

        try {
            $result = $this->evaluateFlow();
            $step   = $result['next_step'] ?? '';

            // Allowed steps
            if ($step === 'done' || ($step === 'main' && WordpressHelpers::isActivationCompleted())) {
                return; // proceed rendering
            }

            // Redirect mapping
            $destination = match ($step) {
                'activate', 'email_validation', 'register', 'finalizing' => 'activation',
                'onboarding'                                  => 'onboarding',
                default                                       => 'main',
            };

            // Get AdminManager instance dynamically
            $manager = method_exists(AdminManager::class, 'getInstance') ? AdminManager::getInstance() : null;

            if ($manager instanceof AdminManager) {
                $manager->redirectPage($destination);
            }
            if (is_callable($failCallback)) {
                $failCallback();
            }
            exit;
        } catch (Throwable $e) {
            // Fail-open: if evaluation fails, let the page render
            return;
        }
    }
}
