<?php

namespace Vovanmix\Laravel5BillingBraintree\Interfaces;

use Exception;

interface BillingInterface {

	public function getEncryptionKey();

	public function getClientToken();

	/**
	 * @param array $customerData
	 * @return bool | int
	 * @throws Exception
	 */
	public function createCustomer($customerData);

	/**
	 * @param string $customer_id
	 * @param string $plan_id
	 * @param array $addOns
	 * @param array $discounts
	 * @param array $removeAddOns
	 * @param array $removeDiscounts
	 * @return bool | int
	 * @throws Exception
	 */
	public function createSubscription($customer_id, $plan_id, $addOns = [], $discounts = [], $removeAddOns = [], $removeDiscounts = []);

	/**
	 * @param string $subscription_id
	 * @param array $customerData
	 * @return bool
	 * @throws Exception
	 */
	public function updatePaymentMethod($subscription_id, $customerData);

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
	 * @return boolean
	 */
	public function checkIfSubscriptionIsPaid($subscription_id);

	/**
	 * @param string $subscription_id
	 * @param bool $get_payment_method_info
     * @param int $numberOfTransactions
	 * @return bool|\stdClass | {status, createdAt, updatedAt, cancelledAt, pastDue, daysPastDue, transactions}
	 */
	public function getSubscriptionInfo($subscription_id, $get_payment_method_info = true, $numberOfTransactions = 5);

	/**
	 * @param string $plan_id
	 * @param array $addOns
	 * @param array $discounts
	 * @param array $removeAddOns
	 * @param array $removeDiscounts
	 * @return mixed
	 */
	public function getPlanSummary($plan_id, $addOns = [], $discounts = [], $removeAddOns = [], $removeDiscounts = []);

}