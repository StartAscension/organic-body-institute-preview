<?php declare( strict_types=1 );

namespace Give\Vendors\LiquidWeb\Harbor\Http;

use Give\Vendors\LiquidWeb\LicensingApiClientWordPress\Http\WordPressHttpClient;
use Give\Vendors\Nyholm\Psr7\Factory\Psr17Factory;
use Give\Vendors\Psr\Http\Client\ClientInterface;
use Give\Vendors\Psr\Http\Message\RequestFactoryInterface;
use Give\Vendors\Psr\Http\Message\StreamFactoryInterface;
use Give\Vendors\LiquidWeb\Harbor\Contracts\Abstract_Provider;

/**
 * Registers shared PSR-17 HTTP message factories in the DI container.
 *
 * @since 1.0.0
 */
final class Provider extends Abstract_Provider {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->container->singleton( WordPressHttpClient::class );
		$this->container->singleton( ClientInterface::class, WordPressHttpClient::class );
		$this->container->singleton( Psr17Factory::class );
		$this->container->singleton( RequestFactoryInterface::class, Psr17Factory::class );
		$this->container->singleton( StreamFactoryInterface::class, Psr17Factory::class );
	}
}
