Braintree for Laravel 5
==============

### Installation

In your Laravel project's composer.json file, add `oureastudios/laravel5-braintree` as a dependency in the require object:

```js
"oureastudios/laravel5-braintree": "dev-master"
```
    
You do *not* need to add any other dependencies, as `oureastudios/laravel5-braintree` loads in the other dependencies automatically.

Finally, do a `composer update`.

Once installed, add the ServiceProvider to your provider array within `config/app.php`:

```php
'providers' => [
	....
	/*
	 * Braintree Service Provider
	 */
    'Oureastudios\Laravel\BraintreeServiceProvider',
]
```

### Configuration

To publish a the package configuration file, run:

```shell
php artisan vendor:publish
```

Then open `config/oureastudios.braintree.php` to setup your environment and keys:

### Usage

Once setup, you can use the Braintree PHP classes as spelled out in the [documentation](https://www.braintreepayments.com/docs/php/transactions/overview).

#### braintree.js (v2)

If you are using [braintree.js (v2)](https://www.braintreepayments.com/docs/javascript), then you can easily output a generated client token using '@braintreeClientToken'.

Below is an example:

~~~html
<script src="https://js.braintreegateway.com/v2/braintree.js"></script>
<script>
	braintree.setup("@braintreeClientToken", "<integration>", options);
</script>
~~~

#### braintree.js (v1)

If you are using the lagacy version [braintree.js (v1)](https://www.braintreepayments.com/braintrust/braintree-js), you can output your client side encryption key into your blade views.

The service provider extends the blade view to allow you to use the '@braintreeClientSideEncryptionKey' to output the CSE Key from your config file.

Below is an example.

~~~html
<script type="text/javascript" src="https://js.braintreegateway.com/v1/braintree.js"></script>
<script type="text/javascript">
    var braintree = Braintree.create("@braintreeClientSideEncryptionKey");
    ...
</script>
~~~

### Credits

Thanks to the [bradleyboy/laravel-braintree](https://github.com/bradleyboy/laravel-braintree) package. I used it as a base and converted it into Laravel 5.
