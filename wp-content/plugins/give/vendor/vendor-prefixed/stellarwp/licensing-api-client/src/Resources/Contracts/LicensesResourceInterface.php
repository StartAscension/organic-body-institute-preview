<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Contracts;

use Generator;
use JsonException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\Activate;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\Alias\ImportAliases;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\Alias\RemoveAliases;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\Deactivate;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\DeleteActivation;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\LicenseReference;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\Listing\ListRequest;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\License\RegenerateKey;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\Activate as ActivateResponse;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\Alias\ImportAliases as ImportAliasesResponse;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\Alias\RemoveAliases as RemoveAliasesResponse;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\Deactivate as DeactivateResponse;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\DeleteActivation as DeleteActivationResponse;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\Listing\Listing;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\RegenerateKey as RegenerateKeyResponse;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\StatusChange;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\License\Validate;
use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the licenses resource surface.
 */
interface LicensesResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function list(ListRequest $request): Listing;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 *
	 * @return Generator<int, Listing, mixed, void>
	 */
	public function pages(ListRequest $request): Generator;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function activate(Activate $request): ActivateResponse;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function deactivate(Deactivate $request): DeactivateResponse;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function deleteActivation(DeleteActivation $request): DeleteActivationResponse;

	/**
	 * @param list<string> $productSlugs
	 *
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function validate(string $licenseKey, array $productSlugs, string $domain): Validate;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function suspend(LicenseReference $request): StatusChange;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function reinstate(LicenseReference $request): StatusChange;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function ban(LicenseReference $request): StatusChange;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function regenerateKey(RegenerateKey $request): RegenerateKeyResponse;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function importAliases(ImportAliases $request): ImportAliasesResponse;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function removeAliases(RemoveAliases $request): RemoveAliasesResponse;
}
