{*
* 2007-2019 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div>

    <div>
        {{l s='Please see our [a @href1@]video tutorial[/a] to find your merchant account id for each currency quickly.' mod='braintree'}|braintreereplace:['@href1@' => 'https://help.202-ecommerce.com/paypal-for-prestashop/set-merchant-account-id-braintree-currency/', '@target@' => {'target="blank"'}]}
    </div>

    <div>
        {{l s='Go to [a @href1@]Payment Preferences - Currency restrictions[/a] to see how PrestaShop manages the currency preferences for your payment module.' mod='braintree'}|braintreereplace:['@href1@' => {$link->getAdminLink('AdminPaymentPreferences', true)}, '@target@' => {'target="blank"'}]}
    </div>
</div>
