<?php

namespace Vovanmix\Laravel5BillingBraintree;

use Braintree_Configuration;
use Braintree_ClientToken;
use Braintree_Customer;
use Braintree_Subscription;
use Braintree_Plan;
use Braintree_AddOn;
use Braintree_Discount;
use Braintree_PaymentMethod;

use Config;
use Exception;

use Vovanmix\Laravel5BillingBraintree\Interfaces\BillingInterface;

class BillingBraintree implements BillingInterface {

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
			'creditCard' => [
				'paymentMethodNonce' => $customerData['nonce'],
				'options' => [
					'verifyCard' => true
				],
				//todo: put cardholder name here
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
			$errors = $result->errors->deepAll();
			if(!empty($errors)) {
				foreach ($errors AS $error) {
					throw new Exception($error->code . ": " . $error->message . "\n");
				}
			}
			elseif(!empty($result->verification['processorResponseCode']) && ($result->verification['processorResponseCode'] >= 2000 ) && !empty($result->verification['processorResponseText'])){
				throw new Exception("Card could not be verified: ".$result->verification['processorResponseText']." \n");
			}
			else{
				throw new Exception("Card could not be processed: ".$result->message." \n");
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


	/**
	 * @param string $subscription_id
	 * @return boolean
	 */
	public function checkIfSubscriptionIsActive($subscription_id){
		$subscription = Braintree_Subscription::find($subscription_id);
		if(!empty($subscription)){
			if($subscription->status != Braintree_Subscription::CANCELED && $subscription->status != Braintree_Subscription::EXPIRED){
				return true;
			}
		}
		return false;
	}


	/**
	 * @param string $subscription_id
	 * @return boolean
	 */
	public function checkIfSubscriptionIsEnabled($subscription_id){
		$subscription = Braintree_Subscription::find($subscription_id);
		if(!empty($subscription)){
			if($subscription->status === Braintree_Subscription::ACTIVE || $subscription->status === Braintree_Subscription::PENDING){
				return true;
			}
		}
		return false;
	}


	/**
	 * @param string $subscription_id
	 * @return boolean
	 */
	public function checkIfSubscriptionIsPastDue($subscription_id){
		$subscription = Braintree_Subscription::find($subscription_id);
		if(!empty($subscription)){
			if($subscription->status === Braintree_Subscription::PAST_DUE){
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $subscription_id
	 * @param bool $get_payment_method_info
	 * @return bool|\stdClass
	 */
	public function getSubscriptionInfo($subscription_id, $get_payment_method_info = true){
		$subscription = Braintree_Subscription::find($subscription_id);
		if(!empty($subscription)){
			$data = new \stdClass();
			$statuses = [
				Braintree_Subscription::ACTIVE => 'ACTIVE',
				Braintree_Subscription::CANCELED => 'CANCELED',
				Braintree_Subscription::EXPIRED => 'EXPIRED',
				Braintree_Subscription::PAST_DUE => 'PAST_DUE',
				Braintree_Subscription::PENDING => 'PENDING'
			];
			$data->status = $statuses[$subscription->status];
			$data->createdAt = $subscription->createdAt;


			if($get_payment_method_info) {
				$paymentMethod = Braintree_PaymentMethod::find($subscription->paymentMethodToken);

				$data->payment_method = new \stdClass();
				$data->payment_method->credit_card = [
					'type' => $paymentMethod->cardType,
					'last4' => $paymentMethod->last4,
					'expiration_month' => $paymentMethod->expirationMonth,
					'expiration_year' => $paymentMethod->expirationYear
				];
				$data->payment_method->billing_address = [
					'first_name' => $paymentMethod->billingAddress->firstName,
					'last_name' => $paymentMethod->billingAddress->lastName,
					'address' => $paymentMethod->billingAddress->streetAddress,
					'city' => $paymentMethod->billingAddress->locality,
					'state' => $paymentMethod->billingAddress->region,
					'zip' => $paymentMethod->billingAddress->postalCode
				];
			}

			return $data;
		}
		return false;
	}

	/**
	 * @param string $plan_id
	 * @param array $addOns
	 * @param array $discounts
	 * @return mixed
	 */
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