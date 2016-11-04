	<?php

/*

* 2007-2012 PrestaShop

*

* NOTICE OF LICENSE

*

* This source file is subject to the Academic Free License (AFL 3.0)

* that is bundled with this package in the file LICENSE.txt.

* It is also available through the world-wide-web at this URL:

* http://opensource.org/licenses/afl-3.0.php

* If you did not receive a copy of the license and are unable to

* obtain it through the world-wide-web, please send an email

* to license@prestashop.com so we can send you a copy immediately.

*

* DISCLAIMER

*

* Do not edit or add to this file if you wish to upgrade PrestaShop to newer

* versions in the future. If you wish to customize PrestaShop for your

* needs please refer to http://www.prestashop.com for more information.

*

*  @author PrestaShop SA <contact@prestashop.com>

*  @copyright  2007-2012 PrestaShop SA

*  @version  Release: $Revision: 17805 $

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)

*  International Registered Trademark & Property of PrestaShop SA

*/



/**

 * @since 1.5.0

 */

class aaibPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**

	 * @see FrontController::initContent()

	 */

	public function initContent()
	{   
		global $cookie;

		parent::initContent();

		$cart = $this->context->cart;
               

		
			$gateway_url = 'http://localhost/vpc/return.php?';

		$currency = new Currency((int)($cart->id_currency));
		$customer = new Customer(intval($cart->id_customer));

		$address = new Address(intval($cart->id_address_invoice));
		$shipping = new Address(intval($cart->id_address_delivery));
              
		$invoice_country = new Country($address->id_country);
		$invoice_country1 = new Country($shipping->id_country);
		
		$shippingState = NULL;
                $shippingMethod = new CarrierCore($cart->id_carrier);
                
		if ($address->id_state)
			$invoice_state = new State((int)($address->id_state));

		$amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');


		$products = $cart->getProducts();
		
           //  print_r($shipping);die;
		
		$order_description = '';
        $categories = "";
        $product_title = "";
        $quantity = "";
        $per_price = "";
        $per_title = "";
        $total = "";
        $i = 0;

		//die;
		$total_product_ammout = 0;
		foreach ($products AS $product){


		

			$dd=$amount-$product['total'];
			$total_product_ammout += $product['total'];

			if ($i >= 1 ){
				$order_description .= ', '. $product['name'];
                $categories .= ', ' . $product['category'] ;
                $product_title .= ', '. $product['name'] ;
                $quantity .= ' || '. $product['cart_quantity'];
                $per_price .= ' || '. number_format($product['price_wt'],3) ;
                $per_title .= ' || '. $product['name'] ;
			}
			else {
				$total .= $product['total'];
				$order_description .= $product['name'];
                $categories .= $product['category'] ;
                $product_title .= $product['name'] ;
                $quantity  .= $product['cart_quantity'];
                $per_price .= number_format($product['price_wt'],3) ;
                $per_title .= $product['name'] ;

			}
			$i++;
                    
		}
		$discount = $total_product_ammout + $cart->getOrderTotal(true, Cart::ONLY_SHIPPING) - $amount;


		if ($_SERVER['SERVER_PORT']==443) {
			$protocol='https://';
		}else{
			$protocol='http://';
		}
	
	$lang_ = "English";
       	if ($this->context->language->iso_code == "ar"){
       		$lang_  = "Arabic";
       	}

	$request_param = array(
                    'vpc_Version' => 1,
                    'vpc_Command' => 'pay',
                    'vpc_AccessCode' => $this->module->aaib_access_code,
		    'vpc_Merchant' => $this->module->aaib_merchant_id,
                    'Title'=> 'OurBabies processing Payment',
                    'vpc_MerchTxnRef' =>  $cart->id,
                     
	                'vpc_OrderInfo'  => $customer->email,
	                'vpc_Amount' =>100 * ($total_product_ammout + $cart->getOrderTotal(true, Cart::ONLY_SHIPPING)),
	                
	                
	                "vpc_Locale" => 'en',
	                'vpc_ReturnURL' => Context::getContext()->link->getModuleLink('aaib', 'validation')
	                
			        
	            );
$secure_hash = $this->module->aaib_hash_secret;	
$md5HashData = $secure_hash;         
ksort($request_param);

$appendAmp = 0;

foreach($request_param as $key => $value) {

    // create the md5 input and URL leaving out any fields that have no value
    if (strlen($value) > 0) {
        
        // this ensures the first paramter of the URL is preceded by the '?' char
        if ($appendAmp == 0) {
            $gateway_url .= urlencode($key) . '=' . urlencode($value);
            $appendAmp = 1;
        } else {
            $gateway_url .= '&' . urlencode($key) . "=" . urlencode($value);
        }
        $md5HashData .= $value;
    }
}
if (strlen($secure_hash) > 0) {
    $gateway_url .= "&vpc_SecureHash=" . strtoupper(md5($md5HashData));
}

		$request_string = http_build_query($request_param);
		
		//$response_data = $this->module->sendRequest($gateway_url, $request_string);
		//$object = json_decode($response_data);

		//PrestaShopLogger::addLog($object->result, 3, $object->response_code, 'Cart', (int)$id_cart, false);

		
        
			$payment_url = $gateway_url ;
			echo $payment_url;
			$this->context->smarty->assign(array(
				'payment_url' => $payment_url
			));
	
			$this->setTemplate('payment_execution.tpl');
		
	}
}