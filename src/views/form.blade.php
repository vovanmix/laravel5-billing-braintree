<script src="https://js.braintreegateway.com/v2/braintree.js"></script>
<script>
	braintree.setup("{{ Billing::getClientToken() }}", "custom", {id: "checkout"});
</script>

<form id="checkout" action="/your/server/endpoint" method="post">
	<input data-braintree-name="number" value="">
	<input data-braintree-name="cvv" value="">

	<input data-braintree-name="expiration_month" value="">
	<input data-braintree-name="expiration_year" value="">

	<input data-braintree-name="postal_code" value="">
	<input data-braintree-name="cardholder_name" value="">

	<input type="submit" id="submit" value="Pay">
</form>