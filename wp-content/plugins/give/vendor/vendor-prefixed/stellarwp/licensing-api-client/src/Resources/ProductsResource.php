<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources;

use JsonException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\AuthState;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\Factories\ApiUriFactory;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestExecutor;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestHeaderCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Concerns\RebindsAuthState;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Concerns\RebindsRequestHeaderCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Contracts\ProductsResourceInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Product\Catalog;
use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;

/**
 * Provides operations for the products API resource.
 *
 * @phpstan-type ActivationDomainPayload array{
 *     activated_at: string,
 *     deactivated_at: string|null,
 *     is_active: bool,
 *     is_production: bool
 * }
 * @phpstan-type ActivationDomainsPayload array<string, ActivationDomainPayload>
 * @phpstan-type CatalogPayload array{
 *     products: list<array{
 *         product_slug: string,
 *         tier: string,
 *         status: string,
 *         expires: string,
 *         capabilities: list<string>,
 *         activations: array{
 *             site_limit: int,
 *             active_count: int,
 *             over_limit: bool,
 *             excess_activations: int,
 *             domains: ActivationDomainsPayload
 *         },
 *         activated_here?: bool,
 *         validation_status?: string,
 *         is_valid?: bool
 *     }>
 * }
 */
final class ProductsResource implements ProductsResourceInterface
{
	use RebindsAuthState;
	use RebindsRequestHeaderCollection;

	private RequestExecutor $requestExecutor;

	private ApiUriFactory $apiUriFactory;

	private AuthState $authState;

	private RequestHeaderCollection $requestHeaderCollection;

	public function __construct(
		RequestExecutor $requestExecutor,
		ApiUriFactory $apiUriFactory,
		AuthState $authState,
		RequestHeaderCollection $requestHeaderCollection
	) {
		$this->requestExecutor         = $requestExecutor;
		$this->apiUriFactory           = $apiUriFactory;
		$this->authState               = $authState;
		$this->requestHeaderCollection = $requestHeaderCollection;
	}

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function catalog(string $licenseKey, ?string $domain = null): Catalog {
		$result = $this->requestExecutor->executeJson(
			'GET',
			$this->apiUriFactory->make('/products'),
			array_filter([
				'license_key' => $licenseKey,
				'domain'      => $domain,
			], static fn($value): bool => $value !== null),
			null,
			$this->authState->optionalToken(),
			$this->requestHeaderCollection->all()
		);

		/** @var CatalogPayload $result */
		return Catalog::from($result);
	}

	protected function rebindWithAuthState(AuthState $authState): self {
		return new self($this->requestExecutor, $this->apiUriFactory, $authState, $this->requestHeaderCollection);
	}

	protected function rebindWithRequestHeaderCollection(RequestHeaderCollection $requestHeaderCollection): self {
		return new self($this->requestExecutor, $this->apiUriFactory, $this->authState, $requestHeaderCollection);
	}
}
