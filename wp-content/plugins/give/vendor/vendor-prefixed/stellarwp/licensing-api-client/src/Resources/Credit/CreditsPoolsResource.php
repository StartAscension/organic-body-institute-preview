<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit;

use JsonException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\ApiResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\AuthState;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\Factories\ApiUriFactory;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestExecutor;
use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestHeaderCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\CreatePool;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\DeletePool as DeletePoolRequest;
use Give\Vendors\LiquidWeb\LicensingApiClient\Requests\Credit\UpdatePool;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Concerns\RebindsAuthState;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Concerns\RebindsRequestHeaderCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Contracts\CreditsPoolsResourceInterface;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\DeletePool;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\PoolCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Responses\Credit\ValueObjects\CreditPool;
use Give\Vendors\Psr\Http\Client\ClientExceptionInterface;

/**
 * Provides operations for the credits pools API resource.
 *
 * @phpstan-import-type CreatePoolPayload from CreatePool
 * @phpstan-import-type UpdatePoolPayload from UpdatePool
 * @phpstan-import-type DeletePoolPayload from DeletePoolRequest
 * @phpstan-type PoolPayload array{
 *     pool_id: int,
 *     credit_type: string,
 *     credits_total: int,
 *     credits_used: int,
 *     overage_limit: ?int,
 *     priority: int,
 *     period: string,
 *     first_period_start: ?string,
 *     expires_at: ?string,
 *     is_expired: bool
 * }
 * @phpstan-type PoolCollectionPayload array{
 *     pools: array<int|string, PoolPayload>
 * }
 * @phpstan-type DeletePoolResponsePayload array{
 *     deleted: bool,
 *     pool_id: int
 * }
 */
final class CreditsPoolsResource implements CreditsPoolsResourceInterface
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
	 * @throws ApiResponseException
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function list(string $licenseKey, bool $active = false): PoolCollection {
		$result = $this->requestExecutor->executeJson(
			'GET',
			$this->apiUriFactory->make('/credits/pools'),
			[
				'license_key' => $licenseKey,
				'active'      => $active,
			],
			null,
			$this->authState->requiredToken(),
			$this->requestHeaderCollection->all()
		);

		/** @var PoolCollectionPayload $result */
		return PoolCollection::from($result);
	}

	/**
	 * @throws ApiResponseException
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function create(CreatePool $request): CreditPool {
		/** @var CreatePoolPayload $body */
		$body = $request->toArray();

		$result = $this->requestExecutor->executeJson(
			'POST',
			$this->apiUriFactory->make('/credits/pools'),
			[],
			$body,
			$this->authState->requiredToken(),
			$this->requestHeaderCollection->all()
		);

		/** @var PoolPayload $result */
		return CreditPool::from($result);
	}

	/**
	 * @throws ApiResponseException
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function update(UpdatePool $request): CreditPool {
		/** @var UpdatePoolPayload $body */
		$body = $request->toArray();

		$result = $this->requestExecutor->executeJson(
			'PATCH',
			$this->apiUriFactory->make('/credits/pools'),
			[],
			$body,
			$this->authState->requiredToken(),
			$this->requestHeaderCollection->all()
		);

		/** @var PoolPayload $result */
		return CreditPool::from($result);
	}

	/**
	 * @throws ApiResponseException
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function delete(DeletePoolRequest $request): DeletePool {
		/** @var DeletePoolPayload $body */
		$body = $request->toArray();

		$result = $this->requestExecutor->executeJson(
			'DELETE',
			$this->apiUriFactory->make('/credits/pools'),
			[],
			$body,
			$this->authState->requiredToken(),
			$this->requestHeaderCollection->all()
		);

		/** @var DeletePoolResponsePayload $result */
		return DeletePool::from($result);
	}

	protected function rebindWithAuthState(AuthState $authState): self {
		return new self($this->requestExecutor, $this->apiUriFactory, $authState, $this->requestHeaderCollection);
	}

	protected function rebindWithRequestHeaderCollection(RequestHeaderCollection $requestHeaderCollection): self {
		return new self($this->requestExecutor, $this->apiUriFactory, $this->authState, $requestHeaderCollection);
	}
}
