<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Enviroment
    |--------------------------------------------------------------------------
    |
    | Please provide the enviroment you would like to use for braintree.
    | This can be either 'sandbox' or 'production'.
    |
    */
	'environment' => 'sandbox',

	/*
    |--------------------------------------------------------------------------
    | Merchant ID
    |--------------------------------------------------------------------------
    |
    | Please provide your Merchant ID.
    |
    */
	'merchantId' => 'my_merchant_id',

	/*
    |--------------------------------------------------------------------------
    | Public Key
    |--------------------------------------------------------------------------
    |
    | Please provide your Public Key.
    |
    */
	'publicKey' => 'my_public_key',

	/*
    |--------------------------------------------------------------------------
    | Private Key
    |--------------------------------------------------------------------------
    |
    | Please provide your Private Key.
    |
    */
	'privateKey' => 'my_private_key',

	/*
    |--------------------------------------------------------------------------
    | Client Side Encryption Key
    |--------------------------------------------------------------------------
    |
    | Please provide your CSE Key.
    |
    */
	'clientSideEncryptionKey' => 'my_client_side_encryption_key',

	/**
	 * If set to True, Subscriptions with Past Due will be considered Enabled. You have to cancel subscription to them in order to block access
	 */
	'allowAccessForPastDue' => true,

	/**
	 * Grace period is the time before the paid billing cycle will end. If set to True, Cancelled Subscription will be considered Enabled until end of the last billing cycle.
	 */
	'allowGracePeriod' => true
	
];