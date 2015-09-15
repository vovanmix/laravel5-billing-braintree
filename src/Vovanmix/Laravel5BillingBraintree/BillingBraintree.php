<?php

namespace Vovanmix\Laravel5BillingBraintree;

use Braintree_Configuration;
use Braintree_ClientToken;
use Braintree_Customer;
use Braintree_Subscription;
use Braintree_Plan;

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

	/**
	 * @param array $customerData
	 * @return bool
	 * @throws Exception
	 */
	public function createCustomer($customerData){
		$result = Braintree_Customer::create([
			'firstName' => $customerData['first_name'],
			'lastName' => $customerData['last_name'],
			'paymentMethodNonce' => $customerData['nonce']
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

	/**
	 * @param string $customer_id
	 * @param string $plan_id
	 * @return bool
	 * @throws Exception
	 */
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

	public function getPlanSummary($plan_id, $addOns = [], $discounts = []){

		$plans = Braintree_Plan::all();
		foreach($plans as $plan){
			if($plan->id == $plan_id){
				$summary = [];
				$summary['price'] = $plan->price;
				$summary['summary'] = $plan->price;

				if(!empty($addOns)){
					foreach($addOns as $addOn){
						foreach($plan->addOns as $planAddOn){
							if($planAddOn->id == $addOn){
								$summary['addOns'][] = [
									'name' => $planAddOn->name,
									'description' => $planAddOn->description,
									'amount' => $planAddOn->amount
								];
								$summary['summary'] += $planAddOn->amount;
							}
						}
					}
				}

				if(!empty($discounts)){
					foreach($discounts as $discount){
						foreach($plan->discount as $planDiscount){
							if($planDiscount->id == $discount){
								$summary['addOns'][] = [
									'name' => $planDiscount->name,
									'description' => $planDiscount->description,
									'amount' => $planDiscount->amount
								];
								$summary['summary'] -= $planDiscount->amount;
							}
						}
					}
				}

				return $summary;
			}
		}

		return null;
	}

}