<?php
declare(strict_types=1);

namespace App\Domain\Integrations\WordPress\Seo\Entities\Optimiser\Base\Adapters;

use App\Domain\Integrations\WordPress\Seo\Entities\Optimiser\Base\Interfaces\ContentProviderInterface;

/**
 * Class RankingcoachProvider
 *
 * Provides methods to manage content for SEO in plugin.
 */
class RankingcoachProvider implements ContentProviderInterface
{

    public function getPostUrl(int $postId): string
    {
        // TODO: Implement getPostUrl() method.
    }

    public function analyzePostRobotsDirectives(string $postUrl): array
    {
        // TODO: Implement analyzePostRobotsDirectives() method.
    }
}
