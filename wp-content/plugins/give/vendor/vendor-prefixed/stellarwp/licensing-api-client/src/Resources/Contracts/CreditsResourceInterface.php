<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Contracts;

use JsonException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\RecordUsage as RecordUsageRequest;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\Refund as RefundRequest;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\BalanceCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\RecordUsage;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\Refund;
use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the root credits resource surface.
 */
interface CreditsResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function balance(string $licenseKey, string $domain, ?string $creditType = null, ?string $sort = null): BalanceCollection;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function recordUsage(RecordUsageRequest $request): RecordUsage;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function refund(RefundRequest $request): Refund;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function pools(): CreditsPoolsResourceInterface;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function quotas(): CreditsQuotasResourceInterface;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function ledger(): CreditsLedgerResourceInterface;
}
