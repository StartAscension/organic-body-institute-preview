<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions;

use RuntimeException;

/**
 * Thrown when a request requires authentication but no token is available.
 */
final class MissingAuthenticationException extends RuntimeException
{
}
