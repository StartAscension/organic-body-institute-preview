<?php declare(strict_types=1);

namespace Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Concerns;

use Give\Vendors\LiquidWeb\LicensingApiClient\Http\RequestHeaderCollection;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsLedgerResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsPoolsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsQuotasResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\EntitlementsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\LicensesResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\ProductsResource;
use Give\Vendors\LiquidWeb\LicensingApiClient\Resources\TokensResource;

/**
 * Provides immutable request-header rebinding for resource views.
 *
 * @mixin CreditsLedgerResource
 * @mixin CreditsPoolsResource
 * @mixin CreditsQuotasResource
 * @mixin CreditsResource
 * @mixin EntitlementsResource
 * @mixin LicensesResource
 * @mixin ProductsResource
 * @mixin TokensResource
 */
trait RebindsRequestHeaderCollection
{
	public function withRequestHeaderCollection(RequestHeaderCollection $requestHeaderCollection): self {
		if ($this->requestHeaderCollection === $requestHeaderCollection) {
			return $this;
		}

		return $this->rebindWithRequestHeaderCollection($requestHeaderCollection);
	}

	abstract protected function rebindWithRequestHeaderCollection(RequestHeaderCollection $requestHeaderCollection): self;
}
