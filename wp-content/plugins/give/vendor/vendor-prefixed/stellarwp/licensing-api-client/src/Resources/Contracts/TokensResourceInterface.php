<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Contracts;

use JsonException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Token\Create as CreateRequest;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Token\Revoke as RevokeRequest;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Token\Auth;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Token\TokenList;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Token\ValueObjects\TokenItem;
use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the tokens resource surface.
 */
interface TokensResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function list(string $licenseKey): TokenList;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function create(CreateRequest $request): TokenItem;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function revoke(RevokeRequest $request): TokenItem;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function auth(string $licenseKey, string $token, string $domain): Auth;
}
