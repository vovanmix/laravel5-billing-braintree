<?php

namespace Vovanmix\Laravel5BillingBraintree;

use Braintree_Configuration;
use Braintree_ClientToken;
use Braintree_Customer;
use Braintree_Subscription;
use Config;
use League\Flysystem\Exception;

class BillingBraintree {

	public function __construct(){
		Braintree_Configuration::environment(
			Config::get('billing_braintree.environment')
		);

		Braintree_Configuration::merchantId(
			Config::get('billing_braintree.merchantId')
		);

		Braintree_Configuration::publicKey(
			Config::get('billing_braintree.publicKey')
		);

		Braintree_Configuration::privateKey(
			Config::get('billing_braintree.privateKey')
		);


	}

	public function getEncryptionKey(){
		$encryptionKey = Config::get('billing_braintree.clientSideEncryptionKey');
		return $encryptionKey;
	}

	public function getClientToken(){
		$clientToken = Braintree_ClientToken::generate();
		return $clientToken;
	}

	public function createClient($clientData){
		$result = Braintree_Customer::create([
			'firstName' => $clientData['name'],
			'paymentMethodNonce' => $clientData['nonce']
		]);
		if ($result->success) {
			return $result->customer->id;
		} else {
			foreach($result->errors->deepAll() AS $error) {
				throw new Exception($error->code . ": " . $error->message . "\n");
			}
		}
		return false;
	}

	public function createSubscription($customer_id, $plan_id){
		$result = Braintree_Customer::find($customer_id);
		$the_token = null;
		if ($result->success) {
			$the_token = $result->customer->paymentMethods[0]->token;
		} else {
			foreach($result->errors->deepAll() AS $error) {
				throw new Exception($error->code . ": " . $error->message . "\n");
			}
		}

		$result = Braintree_Subscription::create([
			'paymentMethodToken' => $the_token,
			'planId' => $plan_id,
//			'firstBillingDate' => ''
		]);
		if ($result->success) {
			return $result->subscription->id;
		} else {
			foreach($result->errors->deepAll() AS $error) {
				throw new Exception($error->code . ": " . $error->message . "\n");
			}
		}
		return false;
	}

	public function checkSubscription(){

	}

}