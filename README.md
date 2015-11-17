Braintree for Laravel 5
==============

# Installation
Add to composer.json this line:

    "vovanmix/laravel5-billing-braintree": "1.*",
    
Add to config/app.php "providers" section:

    "Vovanmix\Laravel5BillingBraintree\BraintreeServiceProvider",

That's it!    


# Configuration

To publish a the package configuration file, run in console:

```
php artisan vendor:publish
```

Then open `config/billing_braintree.php` to setup your environment and keys

# Usage

## Obtain a client side token:

    Billing::getClientToken()
    
To use inside Blade template:

    {{ Billing::getClientToken() }}
    
## Get summary for the plan
    $planExternalId = 'test';
    $planAddOns = [1, 2]; // IDs of  add ons, optional
    $planDiscounts = [1, 2]; // IDs of discounts, optional
    $summary = \Billing::getPlanSummary($planExternalId, $planAddOns, $planDiscounts);
    
## Create customer
    $messageBag = new MessageBag();    // catching errors is optional but is a good practice
    try {    
        $customerData = [        
            'first_name' => Input::get('first_name'),            
            'last_name' => Input::get('last_name'),           
            'nonce' => Input::get('nonce'),    // payment method nonce, obtained at the front end using Braintree Javascript library. See more in Braintree Docs        
            'address' => Input::get('address'),            
            'city' => Input::get('city'),            
            'state' => Input::get('state'),            
            'zip' => Input::get('zip')            
        ];        
        $customerId = \Billing::createCustomer($customerData);        
    } catch (\Exception $e){    
        $messageBag->add('error', $e->getMessage());        
    }
    
## Create subscription
    $messageBag = new MessageBag();    
    try {   
        $planExternalId = 'test';
        $planAddOns = [1, 2]; // IDs of  add ons, optional
        $planDiscounts = [1, 2]; // IDs of discounts, optional
        $subscriptionId = \Billing::createSubscription($customerId, $planExternalId, $planAddOns, $planDiscounts);
    } catch (\Exception $e){    
        $messageBag->add('error', $e->getMessage());        
    }
    
## Update subscription payment method
    $customerData = [        
        'first_name' => Input::get('first_name'),            
        'last_name' => Input::get('last_name'),           
        'nonce' => Input::get('nonce'),    // payment method nonce, obtained at the front end using Braintree Javascript library. See more in Braintree Docs      
        'address' => Input::get('address'),            
        'city' => Input::get('city'),            
        'state' => Input::get('state'),            
        'zip' => Input::get('zip')            
    ];  
    $success = \Billing::updatePaymentMethod($subscriptionId, $customerData);
    
## Get subscription details
    $subscriptionInfo = \Billing::getSubscriptionInfo($subscriptionId);
    
## Checks:
Checks can be used to easily get information about subscription state. This methods implement some additional logic, like Past Due handling and Grape Period

### Enabled
    \Billing::checkIfSubscriptionIsEnabled($subscriptionId);

Most important check. Enabled means that user can still use subscription

Returns True for the following states:

+ ACTIVE
+ CANCELED (until grace period ends)
+ PAST_DUE (if allowed so by config)

### Active
    \Billing::checkIfSubscriptionIsActive($subscriptionId);
Active means that subscription can be used in the future. Canceled and Expired subscriptions cannot be updated.

Returns True for the following states:

+ ACTIVE
+ PAST_DUE
+ PENDING

### Paid
    \Billing::checkIfSubscriptionIsPaid($subscriptionId);

Paid means that this user doesn't owe money for now

Returns True for the following states:

+ ACTIVE
+ PENDING

### PastDue
    \Billing::checkIfSubscriptionIsPastDue($subscriptionId);
    
Returns true if the subscription is past due
    
## Grace period
Grace period can be enabled or disabled in this plugin's config, parameter `allowGracePeriod`. It's enabled by default

If it is enabled, after cancelling subscription user can use the subscription until the end of the billing cycle. For example, if the payment date is 10th od each month, and user paid his bill on November 10, and cancelled his subscription on November 11, he still can use the subscription till December 10.

The ability to use the subscription means that `checkIfSubscriptionIsEnabled()` method will return `TRUE`.

##Past Due handling
By default subscriptions with Past Due will be considered Enabled. You have to cancel subscription to them in order to block access

It can be changed in config, parameter `allowAccessForPastDue`.
    
## Cancel subscription
    \Billing::cancelSubscription($subscriptionId);
    