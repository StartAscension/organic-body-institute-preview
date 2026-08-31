<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClientWordPress\Exceptions;

use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

/**
 * Represents a transport failure reported by WordPress HTTP APIs.
 */
final class WordPressHttpClientException extends RuntimeException implements ClientExceptionInterface
{
}
