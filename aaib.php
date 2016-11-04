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

*  @version  Release: $Revision: 7095 $

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)

*  International Registered Trademark & Property of PrestaShop SA

*/

if (!defined('_PS_VERSION_'))
	exit;

class aaib extends PaymentModule
{
	private $_html = '';

	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'aaib';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->author = 'Tiefanovic';

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('AAIB_MERCHANT_ID', 'AAIB_ACCESS_CODE', 'AAIB_HASH_SECRET'));

		if (isset($config['AAIB_MERCHANT_ID']))
			$this->aaib_merchant_id = $config['AAIB_MERCHANT_ID'];
			
		if (isset($config['AAIB_ACCESS_CODE']))
			$this->aaib_access_code = $config['AAIB_ACCESS_CODE'];
			
		if (isset($config['AAIB_HASH_SECRET']))
			$this->aaib_hash_secret = $config['AAIB_HASH_SECRET'];

		parent::__construct();


		$this->displayName = $this->l('aaib Payment Gateway');
		$this->description = $this->l('Accept payments by Arab African International Bank.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your merchant info?');

	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('AAIB_MERCHANT_ID') || !Configuration::deleteByName('AAIB_HASH_SECRET') || !Configuration::deleteByName('AAIB_ACCESS_CODE')
				|| !parent::uninstall())

			return false;
		return true;
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('aaib_merchant_id'))
				$this->_postErrors[] = $this->l('Merchant ID is required.');
			if (!Tools::getValue('aaib_access_code'))
				$this->_postErrors[] = $this->l('Access Code is required.');
            if (!Tools::getValue('aaib_hash_secret'))
				$this->_postErrors[] = $this->l('Hash Secret is required.');
		}

	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('AAIB_MERCHANT_ID', Tools::getValue('aaib_merchant_id'));
			Configuration::updateValue('AAIB_ACCESS_CODE', Tools::getValue('aaib_access_code'));
			Configuration::updateValue('AAIB_HASH_SECRET', Tools::getValue('aaib_hash_secret'));
		}

		$this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';

	}

	private function _displayCheckoutPayment()
	{
		$this->_html .= '<img src="../modules/aaib/logo.png" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments through Arab African International Bank.').'</b><br /><br />';

	}



	private function _displayForm()
	{
		$this->_html .=

		'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">

			<fieldset>

			<legend>'.$this->l('Merchant settings').'</legend>

				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">

					<tr><td colspan="2">'.$this->l('Please specify your merchant info').'.<br /><br /></td></tr>

					<tr><td width="130" style="height: 35px;">'.$this->l('Merchant ID').'</td><td><input type="text" name="aaib_merchant_id" value="'.htmlentities(Tools::getValue('aaib_merchant_id', $this->aaib_merchant_id), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">'.$this->l('Access Code').'</td><td><input type="text" name="aaib_access_code" value="'.htmlentities(Tools::getValue('aaib_access_code', $this->aaib_access_code), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">'.$this->l('Hash Secret').'</td><td><input type="text" name="aaib_hash_secret" value="'.htmlentities(Tools::getValue('aaib_hash_secret', $this->aaib_hash_secret), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
					

					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>

				</table>

			</fieldset>

		</form>';

	}


	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)

					$this->_html .= '<div class="alert error">'.$err.'</div>';

		}

		else
			$this->_html .= '<br />';

		$this->_displayCheckoutPayment();

		$this->_displayForm();

		return $this->_html;

	}

	public function hookPayment($params)

	{
		global $cookie;
		if (!$this->active)
			return;
			
		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');

	}


	public function hookPaymentReturn($params)
	{
		
		if (!$this->active)
			return;
		
		$state = $params['objOrder']->getCurrentState();
		
	
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'details' => $val_data['amount'],
				'Address' => Tools::nl2br($this->address),
				
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		return $this->display(__FILE__, 'payment_return.tpl');

	}
	     
	function sendRequest($gateway_url, $request_string){
		$ch = @curl_init();
		@curl_setopt($ch, CURLOPT_URL, $gateway_url);
		@curl_setopt($ch, CURLOPT_POST, true);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $request_string);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_HEADER, false);
		@curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		@curl_setopt($ch, CURLOPT_VERBOSE, true);
		$result = @curl_exec($ch);
		if (!$result)
			die(curl_error($ch));

		@curl_close($ch);
		
		return $result;
	}
	
	function getErrorMessage($error){
		$msg = '';
		switch($error){
			case '0001':
				$msg = 'Merchant ID and password does not match';
				break;
				
			case '0002':
				$msg = 'API Key not valid';
				break;
				
			case '0003':
				$msg = 'Transaction ID not found';
				break;
				
			case '0004':
				$msg = 'Unknown transaction error occurred';
				break;
				
			case '0005':
				$msg = 'The currency code is not available for this merchant';
				break;
		}
		
		return $msg;
	}
	
	function getCountryIsoCode($code){
		$countries = array(
			"AF" => array("AFGHANISTAN", "AF", "AFG", "004"),
			"AL" => array("ALBANIA", "AL", "ALB", "008"),
			"DZ" => array("ALGERIA", "DZ", "DZA", "012"),
			"AS" => array("AMERICAN SAMOA", "AS", "ASM", "016"),
			"AD" => array("ANDORRA", "AD", "AND", "020"),
			"AO" => array("ANGOLA", "AO", "AGO", "024"),
			"AI" => array("ANGUILLA", "AI", "AIA", "660"),
			"AQ" => array("ANTARCTICA", "AQ", "ATA", "010"),
			"AG" => array("ANTIGUA AND BARBUDA", "AG", "ATG", "028"),
			"AR" => array("ARGENTINA", "AR", "ARG", "032"),
			"AM" => array("ARMENIA", "AM", "ARM", "051"),
			"AW" => array("ARUBA", "AW", "ABW", "533"),
			"AU" => array("AUSTRALIA", "AU", "AUS", "036"),
			"AT" => array("AUSTRIA", "AT", "AUT", "040"),
			"AZ" => array("AZERBAIJAN", "AZ", "AZE", "031"),
			"BS" => array("BAHAMAS", "BS", "BHS", "044"),
			"BH" => array("BAHRAIN", "BH", "BHR", "048"),
			"BD" => array("BANGLADESH", "BD", "BGD", "050"),
			"BB" => array("BARBADOS", "BB", "BRB", "052"),
			"BY" => array("BELARUS", "BY", "BLR", "112"),
			"BE" => array("BELGIUM", "BE", "BEL", "056"),
			"BZ" => array("BELIZE", "BZ", "BLZ", "084"),
			"BJ" => array("BENIN", "BJ", "BEN", "204"),
			"BM" => array("BERMUDA", "BM", "BMU", "060"),
			"BT" => array("BHUTAN", "BT", "BTN", "064"),
			"BO" => array("BOLIVIA", "BO", "BOL", "068"),
			"BA" => array("BOSNIA AND HERZEGOVINA", "BA", "BIH", "070"),
			"BW" => array("BOTSWANA", "BW", "BWA", "072"),
			"BV" => array("BOUVET ISLAND", "BV", "BVT", "074"),
			"BR" => array("BRAZIL", "BR", "BRA", "076"),
			"IO" => array("BRITISH INDIAN OCEAN TERRITORY", "IO", "IOT", "086"),
			"BN" => array("BRUNEI DARUSSALAM", "BN", "BRN", "096"),
			"BG" => array("BULGARIA", "BG", "BGR", "100"),
			"BF" => array("BURKINA FASO", "BF", "BFA", "854"),
			"BI" => array("BURUNDI", "BI", "BDI", "108"),
			"KH" => array("CAMBODIA", "KH", "KHM", "116"),
			"CM" => array("CAMEROON", "CM", "CMR", "120"),
			"CA" => array("CANADA", "CA", "CAN", "124"),
			"CV" => array("CAPE VERDE", "CV", "CPV", "132"),
			"KY" => array("CAYMAN ISLANDS", "KY", "CYM", "136"),
			"CF" => array("CENTRAL AFRICAN REPUBLIC", "CF", "CAF", "140"),
			"TD" => array("CHAD", "TD", "TCD", "148"),
			"CL" => array("CHILE", "CL", "CHL", "152"),
			"CN" => array("CHINA", "CN", "CHN", "156"),
			"CX" => array("CHRISTMAS ISLAND", "CX", "CXR", "162"),
			"CC" => array("COCOS (KEELING) ISLANDS", "CC", "CCK", "166"),
			"CO" => array("COLOMBIA", "CO", "COL", "170"),
			"KM" => array("COMOROS", "KM", "COM", "174"),
			"CG" => array("CONGO", "CG", "COG", "178"),
			"CK" => array("COOK ISLANDS", "CK", "COK", "184"),
			"CR" => array("COSTA RICA", "CR", "CRI", "188"),
			"CI" => array("COTE D'IVOIRE", "CI", "CIV", "384"),
			"HR" => array("CROATIA (local name: Hrvatska)", "HR", "HRV", "191"),
			"CU" => array("CUBA", "CU", "CUB", "192"),
			"CY" => array("CYPRUS", "CY", "CYP", "196"),
			"CZ" => array("CZECH REPUBLIC", "CZ", "CZE", "203"),
			"DK" => array("DENMARK", "DK", "DNK", "208"),
			"DJ" => array("DJIBOUTI", "DJ", "DJI", "262"),
			"DM" => array("DOMINICA", "DM", "DMA", "212"),
			"DO" => array("DOMINICAN REPUBLIC", "DO", "DOM", "214"),
			"TL" => array("EAST TIMOR", "TL", "TLS", "626"),
			"EC" => array("ECUADOR", "EC", "ECU", "218"),
			"EG" => array("EGYPT", "EG", "EGY", "818"),
			"SV" => array("EL SALVADOR", "SV", "SLV", "222"),
			"GQ" => array("EQUATORIAL GUINEA", "GQ", "GNQ", "226"),
			"ER" => array("ERITREA", "ER", "ERI", "232"),
			"EE" => array("ESTONIA", "EE", "EST", "233"),
			"ET" => array("ETHIOPIA", "ET", "ETH", "210"),
			"FK" => array("FALKLAND ISLANDS (MALVINAS)", "FK", "FLK", "238"),
			"FO" => array("FAROE ISLANDS", "FO", "FRO", "234"),
			"FJ" => array("FIJI", "FJ", "FJI", "242"),
			"FI" => array("FINLAND", "FI", "FIN", "246"),
			"FR" => array("FRANCE", "FR", "FRA", "250"),
			"FX" => array("FRANCE, METROPOLITAN", "FX", "FXX", "249"),
			"GF" => array("FRENCH GUIANA", "GF", "GUF", "254"),
			"PF" => array("FRENCH POLYNESIA", "PF", "PYF", "258"),
			"TF" => array("FRENCH SOUTHERN TERRITORIES", "TF", "ATF", "260"),
			"GA" => array("GABON", "GA", "GAB", "266"),
			"GM" => array("GAMBIA", "GM", "GMB", "270"),
			"GE" => array("GEORGIA", "GE", "GEO", "268"),
			"DE" => array("GERMANY", "DE", "DEU", "276"),
			"GH" => array("GHANA", "GH", "GHA", "288"),
			"GI" => array("GIBRALTAR", "GI", "GIB", "292"),
			"GR" => array("GREECE", "GR", "GRC", "300"),
			"GL" => array("GREENLAND", "GL", "GRL", "304"),
			"GD" => array("GRENADA", "GD", "GRD", "308"),
			"GP" => array("GUADELOUPE", "GP", "GLP", "312"),
			"GU" => array("GUAM", "GU", "GUM", "316"),
			"GT" => array("GUATEMALA", "GT", "GTM", "320"),
			"GN" => array("GUINEA", "GN", "GIN", "324"),
			"GW" => array("GUINEA-BISSAU", "GW", "GNB", "624"),
			"GY" => array("GUYANA", "GY", "GUY", "328"),
			"HT" => array("HAITI", "HT", "HTI", "332"),
			"HM" => array("HEARD ISLAND & MCDONALD ISLANDS", "HM", "HMD", "334"),
			"HN" => array("HONDURAS", "HN", "HND", "340"),
			"HK" => array("HONG KONG", "HK", "HKG", "344"),
			"HU" => array("HUNGARY", "HU", "HUN", "348"),
			"IS" => array("ICELAND", "IS", "ISL", "352"),
			"IN" => array("INDIA", "IN", "IND", "356"),
			"ID" => array("INDONESIA", "ID", "IDN", "360"),
			"IR" => array("IRAN, ISLAMIC REPUBLIC OF", "IR", "IRN", "364"),
			"IQ" => array("IRAQ", "IQ", "IRQ", "368"),
			"IE" => array("IRELAND", "IE", "IRL", "372"),
			"IL" => array("ISRAEL", "IL", "ISR", "376"),
			"IT" => array("ITALY", "IT", "ITA", "380"),
			"JM" => array("JAMAICA", "JM", "JAM", "388"),
			"JP" => array("JAPAN", "JP", "JPN", "392"),
			"JO" => array("JORDAN", "JO", "JOR", "400"),
			"KZ" => array("KAZAKHSTAN", "KZ", "KAZ", "398"),
			"KE" => array("KENYA", "KE", "KEN", "404"),
			"KI" => array("KIRIBATI", "KI", "KIR", "296"),
			"KP" => array("KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", "KP", "PRK", "408"),
			"KR" => array("KOREA, REPUBLIC OF", "KR", "KOR", "410"),
			"KW" => array("KUWAIT", "KW", "KWT", "414"),
			"KG" => array("KYRGYZSTAN", "KG", "KGZ", "417"),
			"LA" => array("LAO PEOPLE'S DEMOCRATIC REPUBLIC", "LA", "LAO", "418"),
			"LV" => array("LATVIA", "LV", "LVA", "428"),
			"LB" => array("LEBANON", "LB", "LBN", "422"),
			"LS" => array("LESOTHO", "LS", "LSO", "426"),
			"LR" => array("LIBERIA", "LR", "LBR", "430"),
			"LY" => array("LIBYAN ARAB JAMAHIRIYA", "LY", "LBY", "434"),
			"LI" => array("LIECHTENSTEIN", "LI", "LIE", "438"),
			"LT" => array("LITHUANIA", "LT", "LTU", "440"),
			"LU" => array("LUXEMBOURG", "LU", "LUX", "442"),
			"MO" => array("MACAU", "MO", "MAC", "446"),
			"MK" => array("MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", "MK", "MKD", "807"),
			"MG" => array("MADAGASCAR", "MG", "MDG", "450"),
			"MW" => array("MALAWI", "MW", "MWI", "454"),
			"MY" => array("MALAYSIA", "MY", "MYS", "458"),
			"MV" => array("MALDIVES", "MV", "MDV", "462"),
			"ML" => array("MALI", "ML", "MLI", "466"),
			"MT" => array("MALTA", "MT", "MLT", "470"),
			"MH" => array("MARSHALL ISLANDS", "MH", "MHL", "584"),
			"MQ" => array("MARTINIQUE", "MQ", "MTQ", "474"),
			"MR" => array("MAURITANIA", "MR", "MRT", "478"),
			"MU" => array("MAURITIUS", "MU", "MUS", "480"),
			"YT" => array("MAYOTTE", "YT", "MYT", "175"),
			"MX" => array("MEXICO", "MX", "MEX", "484"),
			"FM" => array("MICRONESIA, FEDERATED STATES OF", "FM", "FSM", "583"),
			"MD" => array("MOLDOVA, REPUBLIC OF", "MD", "MDA", "498"),
			"MC" => array("MONACO", "MC", "MCO", "492"),
			"MN" => array("MONGOLIA", "MN", "MNG", "496"),
			"MS" => array("MONTSERRAT", "MS", "MSR", "500"),
			"MA" => array("MOROCCO", "MA", "MAR", "504"),
			"MZ" => array("MOZAMBIQUE", "MZ", "MOZ", "508"),
			"MM" => array("MYANMAR", "MM", "MMR", "104"),
			"NA" => array("NAMIBIA", "NA", "NAM", "516"),
			"NR" => array("NAURU", "NR", "NRU", "520"),
			"NP" => array("NEPAL", "NP", "NPL", "524"),
			"NL" => array("NETHERLANDS", "NL", "NLD", "528"),
			"AN" => array("NETHERLANDS ANTILLES", "AN", "ANT", "530"),
			"NC" => array("NEW CALEDONIA", "NC", "NCL", "540"),
			"NZ" => array("NEW ZEALAND", "NZ", "NZL", "554"),
			"NI" => array("NICARAGUA", "NI", "NIC", "558"),
			"NE" => array("NIGER", "NE", "NER", "562"),
			"NG" => array("NIGERIA", "NG", "NGA", "566"),
			"NU" => array("NIUE", "NU", "NIU", "570"),
			"NF" => array("NORFOLK ISLAND", "NF", "NFK", "574"),
			"MP" => array("NORTHERN MARIANA ISLANDS", "MP", "MNP", "580"),
			"NO" => array("NORWAY", "NO", "NOR", "578"),
			"OM" => array("OMAN", "OM", "OMN", "512"),
			"PK" => array("PAKISTAN", "PK", "PAK", "586"),
			"PW" => array("PALAU", "PW", "PLW", "585"),
			"PA" => array("PANAMA", "PA", "PAN", "591"),
			"PG" => array("PAPUA NEW GUINEA", "PG", "PNG", "598"),
			"PY" => array("PARAGUAY", "PY", "PRY", "600"),
			"PE" => array("PERU", "PE", "PER", "604"),
			"PH" => array("PHILIPPINES", "PH", "PHL", "608"),
			"PN" => array("PITCAIRN", "PN", "PCN", "612"),
			"PL" => array("POLAND", "PL", "POL", "616"),
			"PT" => array("PORTUGAL", "PT", "PRT", "620"),
			"PR" => array("PUERTO RICO", "PR", "PRI", "630"),
			"QA" => array("QATAR", "QA", "QAT", "634"),
			"RE" => array("REUNION", "RE", "REU", "638"),
			"RO" => array("ROMANIA", "RO", "ROU", "642"),
			"RU" => array("RUSSIAN FEDERATION", "RU", "RUS", "643"),
			"RW" => array("RWANDA", "RW", "RWA", "646"),
			"KN" => array("SAINT KITTS AND NEVIS", "KN", "KNA", "659"),
			"LC" => array("SAINT LUCIA", "LC", "LCA", "662"),
			"VC" => array("SAINT VINCENT AND THE GRENADINES", "VC", "VCT", "670"),
			"WS" => array("SAMOA", "WS", "WSM", "882"),
			"SM" => array("SAN MARINO", "SM", "SMR", "674"),
			"ST" => array("SAO TOME AND PRINCIPE", "ST", "STP", "678"),
			"SA" => array("SAUDI ARABIA", "SA", "SAU", "682"),
			"SN" => array("SENEGAL", "SN", "SEN", "686"),
			"RS" => array("SERBIA", "RS", "SRB", "688"),
			"SC" => array("SEYCHELLES", "SC", "SYC", "690"),
			"SL" => array("SIERRA LEONE", "SL", "SLE", "694"),
			"SG" => array("SINGAPORE", "SG", "SGP", "702"),
			"SK" => array("SLOVAKIA (Slovak Republic)", "SK", "SVK", "703"),
			"SI" => array("SLOVENIA", "SI", "SVN", "705"),
			"SB" => array("SOLOMON ISLANDS", "SB", "SLB", "90"),
			"SO" => array("SOMALIA", "SO", "SOM", "706"),
			"ZA" => array("SOUTH AFRICA", "ZA", "ZAF", "710"),
			"ES" => array("SPAIN", "ES", "ESP", "724"),
			"LK" => array("SRI LANKA", "LK", "LKA", "144"),
			"SH" => array("SAINT HELENA", "SH", "SHN", "654"),
			"PM" => array("SAINT PIERRE AND MIQUELON", "PM", "SPM", "666"),
			"SD" => array("SUDAN", "SD", "SDN", "736"),
			"SR" => array("SURINAME", "SR", "SUR", "740"),
			"SJ" => array("SVALBARD AND JAN MAYEN ISLANDS", "SJ", "SJM", "744"),
			"SZ" => array("SWAZILAND", "SZ", "SWZ", "748"),
			"SE" => array("SWEDEN", "SE", "SWE", "752"),
			"CH" => array("SWITZERLAND", "CH", "CHE", "756"),
			"SY" => array("SYRIAN ARAB REPUBLIC", "SY", "SYR", "760"),
			"TW" => array("TAIWAN, PROVINCE OF CHINA", "TW", "TWN", "158"),
			"TJ" => array("TAJIKISTAN", "TJ", "TJK", "762"),
			"TZ" => array("TANZANIA, UNITED REPUBLIC OF", "TZ", "TZA", "834"),
			"TH" => array("THAILAND", "TH", "THA", "764"),
			"TG" => array("TOGO", "TG", "TGO", "768"),
			"TK" => array("TOKELAU", "TK", "TKL", "772"),
			"TO" => array("TONGA", "TO", "TON", "776"),
			"TT" => array("TRINIDAD AND TOBAGO", "TT", "TTO", "780"),
			"TN" => array("TUNISIA", "TN", "TUN", "788"),
			"TR" => array("TURKEY", "TR", "TUR", "792"),
			"TM" => array("TURKMENISTAN", "TM", "TKM", "795"),
			"TC" => array("TURKS AND CAICOS ISLANDS", "TC", "TCA", "796"),
			"TV" => array("TUVALU", "TV", "TUV", "798"),
			"UG" => array("UGANDA", "UG", "UGA", "800"),
			"UA" => array("UKRAINE", "UA", "UKR", "804"),
			"AE" => array("UNITED ARAB EMIRATES", "AE", "ARE", "784"),
			"GB" => array("UNITED KINGDOM", "GB", "GBR", "826"),
			"US" => array("UNITED STATES", "US", "USA", "840"),
			"UM" => array("UNITED STATES MINOR OUTLYING ISLANDS", "UM", "UMI", "581"),
			"UY" => array("URUGUAY", "UY", "URY", "858"),
			"UZ" => array("UZBEKISTAN", "UZ", "UZB", "860"),
			"VU" => array("VANUATU", "VU", "VUT", "548"),
			"VA" => array("VATICAN CITY STATE (HOLY SEE)", "VA", "VAT", "336"),
			"VE" => array("VENEZUELA", "VE", "VEN", "862"),
			"VN" => array("VIET NAM", "VN", "VNM", "704"),
			"VG" => array("VIRGIN ISLANDS (BRITISH)", "VG", "VGB", "92"),
			"VI" => array("VIRGIN ISLANDS (U.S.)", "VI", "VIR", "850"),
			"WF" => array("WALLIS AND FUTUNA ISLANDS", "WF", "WLF", "876"),
			"EH" => array("WESTERN SAHARA", "EH", "ESH", "732"),
			"YE" => array("YEMEN", "YE", "YEM", "887"),
			"YU" => array("YUGOSLAVIA", "YU", "YUG", "891"),
			"ZR" => array("ZAIRE", "ZR", "ZAR", "180"),
			"ZM" => array("ZAMBIA", "ZM", "ZMB", "894"),
			"ZW" => array("ZIMBABWE", "ZW", "ZWE", "716"),
		);
		
		return $countries[$code][2];
	}
}