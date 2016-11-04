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

*  @version  Release: $Revision: 15094 $

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)

*  International Registered Trademark & Property of PrestaShop SA

*/



/**

 * @since 1.5.0

 */

class aaibValidationModuleFrontController extends ModuleFrontController
{
	/**

	 * @see FrontController::postProcess()

	 */
	private $val_data;
	public function null2unknown($data) {
		if ($data == "") {
			return "No Value Returned";
		} else {
			return $data;
		}
	} 
	public function getResponseDescription($responseCode) {

    switch ($responseCode) {
        case "0" : $result = "Transaction Successful"; break;
        case "?" : $result = "Transaction status is unknown"; break;
        case "1" : $result = "Unknown Error"; break;
        case "2" : $result = "Bank Declined Transaction"; break;
        case "3" : $result = "No Reply from Bank"; break;
        case "4" : $result = "Expired Card"; break;
        case "5" : $result = "Insufficient funds"; break;
        case "6" : $result = "Error Communicating with Bank"; break;
        case "7" : $result = "Payment Server System Error"; break;
        case "8" : $result = "Transaction Type Not Supported"; break;
        case "9" : $result = "Bank declined transaction (Do not contact Bank)"; break;
        case "A" : $result = "Transaction Aborted"; break;
        case "C" : $result = "Transaction Cancelled"; break;
        case "D" : $result = "Deferred transaction has been received and is awaiting processing"; break;
        case "F" : $result = "3D Secure Authentication failed"; break;
        case "I" : $result = "Card Security Code verification failed"; break;
        case "L" : $result = "Shopping Transaction Locked (Please try the transaction again later)"; break;
        case "N" : $result = "Cardholder is not enrolled in Authentication scheme"; break;
        case "P" : $result = "Transaction has been received by the Payment Adaptor and is being processed"; break;
        case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
        case "S" : $result = "Duplicate SessionID (OrderInfo)"; break;
        case "T" : $result = "Address Verification Failed"; break;
        case "U" : $result = "Card Security Code Failed"; break;
        case "V" : $result = "Address Verification and Card Security Code Failed"; break;
        default  : $result = "Unable to be determined"; 
    }
    return $result;
}
	
	public function postProcess()
	{
		$cart = $this->context->cart;

		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');


		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'aaib')
			{
				$authorized = true;
				break;
			}

		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$currency = $this->context->currency;
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');
		
		
		//check payment status
		
			
				
			$val_data = array(
			'amount'          => $this->null2unknown(Tools::getValue('amount')),
			'locale'          => $this->null2unknown(Tools::getValue('locale')),
			'batchNo'         => $this->null2unknown(Tools::getValue('BatchNo')),
			'command'         => $this->null2unknown(Tools::getValue('Command')),
			'message'         => $this->null2unknown(Tools::getValue('message')),
			'version'         => $this->null2unknown(Tools::getValue('version')),
			'cardType'        => $this->null2unknown(Tools::getValue('cardType')),
			'orderInfo'       => $this->null2unknown(Tools::getValue('orderInfo')),
			'receiptNo'       => $this->null2unknown(Tools::getValue('receiptNo')),
			'merchantID'      => $this->null2unknown(Tools::getValue('merchant')),
			'authorizeID'     => $this->null2unknown(Tools::getValue('authorizeId')),
			'merchTxnRef'     => $this->null2unknown(Tools::getValue('merchTxnRef')),
			'transactionNo'   => $this->null2unknown(Tools::getValue('transactionNo')),
			'acqResponseCode' => $this->null2unknown(Tools::getValue('acqResponseCode')),
			'txnResponseCode' => $this->null2unknown(Tools::getValue('txnResponseCode'))
			);
			$this->val_data = $val_data;
			
		
		if ($val_data['txnResponseCode'] == 0 ) { 
			//success
			$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
			$this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, 'Transaction Reference: ' . $_POST['payment_reference'], array(), (int)$currency->id, false, $customer->secure_key);
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
			return;
		} else {
			//fail
			$this->error = $this->getResponseDescription( $val_data['txnResponseCode']);//$this->module->getErrorMessage($object->error_code);
			$total = (float)$cart->getOrderTotal(true, Cart::BOTH);	
			$this->module->validateOrder($cart->id, Configuration::get('PS_OS_ERROR'), $total, $this->module->displayName, $this->error, array(), (int)$currency->id, false, $customer->secure_key);
		}
	}
	
	public function initContent()
	{   
		global $cookie;

		parent::initContent();

		$this->context->smarty->assign(array(
				'error' => $this->error,
				'order_url' => 'index.php?controller=order&step=1'
		));
	
		$this->setTemplate('payment_error.tpl');
	}
	public function getData()
	{
		return $this->val_data;
	}
}