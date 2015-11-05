<?php

namespace Vovanmix\Laravel5BillingBraintree\Interfaces;

use Exception;

interface BillingInterface {

	public function getEncryptionKey();

	public function getClientToken();

	/**
	 * @param array $customerData
	 * @return bool
	 * @throws Exception
	 */
	public function createCustomer($customerData);

	/**
	 * @param string $customer_id
	 * @param string $plan_id
	 * @param array $addOns
	 * @param array $discounts
	 * @return bool
	 * @throws Exception
	 */
	public function createSubscription($customer_id, $plan_id, $addOns = [], $discounts = []);

	/**
	 * @param string $subscription_id
	 * @return boolean
	 */
	public function checkIfSubscriptionIsActive($subscription_id);

	/**
	 * @param string $subscription_id
	 * @return boolean
	 */
	public function checkIfSubscriptionIsEnabled($subscription_id);

	/**
	 * @param string $subscription_id
	 * @return boolean
	 */
	public function checkIfSubscriptionIsPastDue($subscription_id);

	/**
	 * @param string $subscription_id
	 * @param bool $get_payment_method_info
	 * @return bool|\stdClass
	 */
	public function getSubscriptionInfo($subscription_id, $get_payment_method_info = true);

	/**
	 * @param string $plan_id
	 * @param array $addOns
	 * @param array $discounts
	 * @return mixed
	 */
	public function getPlanSummary($plan_id, $addOns = [], $discounts = []);

}