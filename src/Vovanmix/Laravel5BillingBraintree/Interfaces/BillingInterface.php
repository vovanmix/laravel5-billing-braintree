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
	 * @param string $customer_id
	 * @return mixed
	 */
	public function checkActiveSubscription($customer_id);

	/**
	 * @param string $plan_id
	 * @param array $addOns
	 * @param array $discounts
	 * @return mixed
	 */
	public function getPlanSummary($plan_id, $addOns = [], $discounts = []);

}