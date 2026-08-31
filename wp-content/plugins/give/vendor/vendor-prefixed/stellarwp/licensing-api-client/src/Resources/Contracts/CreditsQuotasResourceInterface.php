<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Contracts;

use JsonException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\SetQuota;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\DeleteQuota;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\QuotaCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\ValueObjects\SiteQuota;
use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the credits quotas resource surface.
 */
interface CreditsQuotasResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function list(string $licenseKey): QuotaCollection;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function set(SetQuota $request): SiteQuota;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function delete(string $licenseKey, string $domain, string $creditType): DeleteQuota;
}
