<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient;

use Give\Vendors\LiquidWeb\LicensingApiClient\Http\ApiVersion;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\AuthContext;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\AuthState;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\Factories\ApiUriFactory;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\Factories\ResponseExceptionFactory;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\JsonDecoder;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestBuilder;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestExecutor;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestHeaderCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsLedgerResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsPoolsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsQuotasResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\EntitlementsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\LicensesResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\ProductsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\TokensResource;
use Give\Vendors\Psr\Http\Client\ClientInterface as HttpClient;
use Give\Vendors\Psr\Http\Message\RequestFactoryInterface;
use Give\Vendors\Psr\Http\Message\StreamFactoryInterface;

/**
 * Builds a fully-wired API client from the transport dependencies.
 *
 * Use this if your application is not using a container to build dependencies.
 */
final class ApiBuilder
{
	private HttpClient $httpClient;

	private RequestFactoryInterface $requestFactory;

	private StreamFactoryInterface $streamFactory;

	private Config $config;

	public function __construct(
		HttpClient $httpClient,
		RequestFactoryInterface $requestFactory,
		StreamFactoryInterface $streamFactory,
		Config $config
	) {
		$this->httpClient     = $httpClient;
		$this->requestFactory = $requestFactory;
		$this->streamFactory  = $streamFactory;
		$this->config         = $config;
	}

	public function build(): Api {
		$authState               = new AuthState(new AuthContext(), $this->config->configuredToken);
		$requestHeaderCollection = new RequestHeaderCollection();
		$apiUriFactory           = new ApiUriFactory($this->config, ApiVersion::default());
		$requestExecutor         = $this->buildRequestExecutor();
		$creditsPools            = new CreditsPoolsResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection);
		$creditsQuotas           = new CreditsQuotasResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection);
		$creditsLedger           = new CreditsLedgerResource(
			$requestExecutor,
			$apiUriFactory,
			$authState,
			$requestHeaderCollection
		);

		return new Api(
			$authState,
			$requestHeaderCollection,
			new LicensesResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection),
			new ProductsResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection),
			new CreditsResource(
				$requestExecutor,
				$apiUriFactory,
				$authState,
				$requestHeaderCollection,
				$creditsPools,
				$creditsQuotas,
				$creditsLedger
			),
			new EntitlementsResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection),
			new TokensResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection)
		);
	}

	private function buildRequestExecutor(): RequestExecutor {
		$jsonDecoder = new JsonDecoder();

		return new RequestExecutor(
			$this->httpClient,
			new RequestBuilder(
				$this->requestFactory,
				$this->streamFactory
			),
			$jsonDecoder,
			new ResponseExceptionFactory($jsonDecoder)
		);
	}
}
