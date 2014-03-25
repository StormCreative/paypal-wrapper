<?php

class Paypal_helper
{	
	/**
	 * Path of the company logo for PayPal
	 * 
	 * @var string
	 */
	public static $image_url = '_admin/assets/images/login-company-logo.png';

	/**
	 * Handles the process to send all data to PayPal
	 * and automatically fires off to PayPal payment page
	 * 
	 * @param array $order_details - (customer_email, total, basket_id)
	 * @param optional string $desc - the description of the order
	 * @return void
	 */
	public static function paypal_checkout($order_details=array(), $desc="")
	{
		$paypal = new SetExpressCheckout($order_details['total']);
        
        $paypal->setNVP("RETURNURL", DOMAIN.'order/success/paypal?orderreference='.$order_details['basket_id']);
        $paypal->setNVP("CANCELURL", DOMAIN.'basket/view');
        $paypal->setNVP("HDRIMG", DOMAIN.self::$image_url);

        $paypal->setNVP("EMAIL", $order_details['customer_email']);
        $paypal->setNVP("AMT", $order_details['total']);
        $paypal->setNVP("INVNUM", $order_details['basket_id']);

        $paypal->setNVP("CURRENCYCODE", "GBP");
        $paypal->setNVP("NOSHIPPING", "1");
        $paypal->setNVP('PAYMENTREQUEST_0_PAYMENTACTION', 'Sale');
        $paypal->setNVP('USERACTION', 'commit');

        $paypal->setNVP("DESC", $desc );

        // An example of a custom field to pass to PayPal
        //$paypal->setNVP("CUSTOM", "Anything you want");

        $paypal->getResponse();

        exit();
	}

	/**
	 * This is get the incoming order
	 * Can be used on it's own for showing a screen to the customer
	 * to confirm their order
	 * 
	 * @return string
	 */
	public static function paypal_get_checkout()
	{
		$paypal = new GetExpressCheckoutDetails;
		$details = $paypal->getResponse();
		
		// Retrieves the order number for searching database key
		$inv = $details['INVNUM'];

		return $inv;
	}

	/**
	 * Confirms the order to PayPal
	 * 
	 * @param string $total
	 * @return bool
	 */
	public static function paypal_process_order($total)
	{
		// First get the order
		self::paypal_get_checkout();

		$total = $total;
		
		// Confirm the payment
		$payment = new DoExpressCheckoutPayment($total);
		$payment->setNVP("CURRENCYCODE", "GBP");
		$payment->setNVP('PAYMENTREQUEST_0_PAYMENTACTION', 'Sale');
		$response = $payment->getResponse();
		
		if ($response['PAYMENTSTATUS'] == 'Completed' || $response['PAYMENTSTATUS'] == 'Pending') {
			$result = true;
		} else {
			$result = false;
		}

		return $result;
	}
}

?>