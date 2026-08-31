<?php

namespace Give\Vendors\LiquidWeb\Harbor\Contracts;

use Give\Vendors\StellarWP\ContainerContract\ContainerInterface;
use Give\Vendors\LiquidWeb\Harbor\Config;

abstract class Abstract_Provider implements Provider_Interface {

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor for the class.
	 *
	 * @param ContainerInterface $container The DI container instance.
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: Config::get_container();
	}
}
