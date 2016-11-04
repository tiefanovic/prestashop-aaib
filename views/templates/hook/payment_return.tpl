{*
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
*  @version  Release: $Revision: 7471 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


<p>{l s='Your order on %s is complete.' sprintf=$this->context->cutomer->first_name mod='aaib'}
		<br /><br />
		{l s='You have chosen the Securely Master Card Payment Method.' mod='aaib'}
		<br /><br /><span class="bold">{l s='Your order will be sent very soon.' mod='aaib'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='aaib'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='aaib'}</a>.
</p>