<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Contracts;

use JsonException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\CreatePool;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\DeletePool as DeletePoolRequest;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\UpdatePool;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\DeletePool;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\PoolCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\ValueObjects\CreditPool;
use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the credits pools resource surface.
 */
interface CreditsPoolsResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function list(string $licenseKey, bool $active = false): PoolCollection;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function create(CreatePool $request): CreditPool;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function update(UpdatePool $request): CreditPool;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function delete(DeletePoolRequest $request): DeletePool;
}
