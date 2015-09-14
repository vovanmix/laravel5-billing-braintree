<?php

namespace Vovanmix\Laravel5BillingBraintree;

use Illuminate\Support\ServiceProvider;

use Braintree_Configuration;
use Braintree_ClientToken;

use Blade;

class BraintreeServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		
		$this->publishes([
		    __DIR__.'/../../config/braintree.php' => config_path('billing_braintree.php'),
		]);

		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Billing', 'Vovanmix\Laravel5BillingBraintree\Facades\Billing');
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
