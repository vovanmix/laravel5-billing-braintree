<?php

namespace Vovanmix\Laravel5BillingBraintree;

use Braintree_Configuration;
use Braintree_ClientToken;

class BillingBraintree {

	public function __construct(){
		Braintree_Configuration::environment(
			$this->app['config']->get('billing_braintree.environment')
		);

		Braintree_Configuration::merchantId(
			$this->app['config']->get('billing_braintree.merchantId')
		);

		Braintree_Configuration::publicKey(
			$this->app['config']->get('billing_braintree.publicKey')
		);

		Braintree_Configuration::privateKey(
			$this->app['config']->get('billing_braintree.privateKey')
		);


	}

	public function getEncryptionKey(){
		$encryptionKey = $this->app['config']->get('billing_braintree.clientSideEncryptionKey');
		return $encryptionKey;
	}

	public function getClientToken(){
		$clientToken = Braintree_ClientToken::generate();
		return $clientToken;
	}

	public function makeForm(){

	}

	public function subscribe(){

	}

	public function checkSubscription(){

	}

}