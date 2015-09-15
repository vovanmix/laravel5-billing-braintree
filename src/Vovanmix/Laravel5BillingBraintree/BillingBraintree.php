<?php

namespace Vovanmix\Laravel5BillingBraintree;

use Braintree_Configuration;
use Braintree_ClientToken;
use Braintree_Customer;
use Braintree_Subscription;
use Braintree_Plan;
use Braintree_AddOn;
use Braintree_Discount;

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
			'paymentMethodNonce' => $customerData['nonce'],
			'creditCard' => [
				'billingAddress' => [
					'firstName' => $customerData['first_name'],
					'lastName' => $customerData['last_name'],
					'streetAddress' => $customerData['address'],
					'locality' => $customerData['city'],
					'region' => $customerData['state'],
					'postalCode' => $customerData['zip']
				]
			]
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
	 * @param array $addOns
	 * @param array $discounts
	 * @return bool
	 * @throws Exception
	 */
	public function createSubscription($customer_id, $plan_id, $addOns = [], $discounts = []){
		$customer = Braintree_Customer::find($customer_id);
		$the_token = null;
		if (!empty($customer)) {
			$the_token = $customer->paymentMethods[0]->token;
		} else {
			throw new Exception("Customer not found \n");
		}

		$formattedAddOns = [];
		foreach($addOns as $addOn){
			$formattedAddOns[] = [
				'inheritedFromId' => $addOn
			];
		}

		$formattedDiscounts = [];
		foreach($discounts as $discount){
			$formattedDiscounts[] = [
				'inheritedFromId' => $discount
			];
		}

		$result = Braintree_Subscription::create([
			'paymentMethodToken' => $the_token,
			'planId' => $plan_id,
//			'firstBillingDate' => ''
			'addOns' => [
				'add' => $formattedAddOns
			],
			'discounts' => [
				'add' => $formattedDiscounts
			]
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

		$summary = [];

		$plans = Braintree_Plan::all();

		foreach($plans as $plan){
			if($plan->id == $plan_id){
				$summary = [];
				$summary['price'] = $plan->price;
				$summary['summary'] = $plan->price;

				//add all default add-ons
				if(!empty($plan->addOns)){
					foreach($plan->addOns as $planAddOn){
						$summary['addOns'][] = [
							'name' => $planAddOn->name,
							'description' => $planAddOn->description,
							'amount' => $planAddOn->amount
						];
						$summary['summary'] += $planAddOn->amount;
					}
				}

				//add all default discounts
				if(!empty($plan->discounts)) {
					foreach ($plan->discounts as $planDiscount) {
						$summary['discounts'][] = [
							'name' => $planDiscount->name,
							'description' => $planDiscount->description,
							'amount' => $planDiscount->amount
						];
						$summary['summary'] -= $planDiscount->amount;
					}
				}

				break;
			}
		}

		//add all manually added add-ons
		$SystemAddOns = Braintree_AddOn::all();
		foreach($addOns as $addOn) {
			foreach($SystemAddOns as $SystemAddOn){
				if ($SystemAddOn->id == $addOn) {
					$summary['addOns'][] = [
						'name' => $SystemAddOn->name,
						'description' => $SystemAddOn->description,
						'amount' => $SystemAddOn->amount
					];
					$summary['summary'] += $SystemAddOn->amount;
				}
			}
		}

		//add all manually added discounts
		$SystemDiscounts = Braintree_Discount::all();
		foreach($discounts as $discount){
			foreach($SystemDiscounts as $SystemDiscount){
				if($SystemDiscount->id == $discount){
					$summary['discounts'][] = [
						'name' => $SystemDiscount->name,
						'description' => $SystemDiscount->description,
						'amount' => $SystemDiscount->amount
					];
					$summary['summary'] -= $SystemDiscount->amount;
				}
			}
		}

		return $summary;
	}

}