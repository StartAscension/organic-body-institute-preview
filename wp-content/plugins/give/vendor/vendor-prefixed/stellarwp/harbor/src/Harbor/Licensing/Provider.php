<?php declare( strict_types=1 );

namespace Give\Vendors\LiquidWeb\Harbor\Licensing;

use Give\Vendors\LiquidWeb\Harbor\Config;
use Give\Vendors\LiquidWeb\Harbor\Contracts\Abstract_Provider;
use Give\Vendors\LiquidWeb\Harbor\Harbor;
use Give\Vendors\LiquidWeb\Harbor\Licensing\Registry\Product_Registry;
use Give\Vendors\LiquidWeb\Harbor\Licensing\Repositories\License_Repository;
use Give\Vendors\LiquidWeb\LicensingApiClient\Config as LicensingConfig;
use Give\Vendors\LiquidWeb\LicensingApiClient\Contracts\LicensingClientInterface;
use Give\Vendors\LiquidWeb\LicensingApiClientWordPress\Http\WordPressHttpClient;
use Give\Vendors\LiquidWeb\LicensingApiClientWordPress\WordPressApiFactory;
use Give\Vendors\Nyholm\Psr7\Factory\Psr17Factory;

/**
 * Registers the Licensing subsystem in the DI container.
 *
 * @since 1.0.0
 */
final class Provider extends Abstract_Provider {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->container->singleton(
			LicensingClientInterface::class,
			function () {
				$psr17   = $this->container->get( Psr17Factory::class );
				$factory = new WordPressApiFactory(
					$this->container->get( WordPressHttpClient::class ),
					$psr17,
					$psr17
				);
				return $factory->make(
					new LicensingConfig(
						Config::get_licensing_base_url(),
						null,
						'lw-harbor/' . Harbor::VERSION
					)
				);
			}
		);

		$this->container->singleton( License_Repository::class );
		$this->container->singleton( Product_Registry::class );
		$this->container->singleton( License_Manager::class );

		add_action(
			'lw-harbor/unified_license_key_changed',
			function () {
				/** @var License_Repository $license_repository */
				$license_repository = $this->container->get( License_Repository::class );
				$license_repository->delete_products();
				$license_repository->delete_validation_state();
			}
		);

		add_action(
			'activated_plugin',
			function () {
				/** @var License_Manager $license_manager */
				$license_manager = $this->container->get( License_Manager::class );
				$license_manager->store_embedded_key_if_present();
			}
		);
	}
}
