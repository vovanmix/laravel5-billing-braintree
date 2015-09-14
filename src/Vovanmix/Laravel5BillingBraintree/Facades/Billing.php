<?php

namespace Vovanmix\Laravel5BillingBraintree\Facades;

use Illuminate\Support\Facades\Facade;

class Billing extends Facade{

	protected static function getFacadeAccessor() { return 'BillingBraintree'; }

}