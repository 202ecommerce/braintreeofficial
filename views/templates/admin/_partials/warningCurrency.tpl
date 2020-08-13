{*
* 2007-2020 PayPal
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
*  @author 2007-2020 PayPal
*  @author 202 ecommerce <tech@202-ecommerce.com>

*  @copyright PayPal
*  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div>
    <div class="h4">
        {l s='Fill your merchant account id for every currency' mod='braintreeofficial'}
    </div>

    <div>
        {{l s='You are currently having multiple [a @href1@]currencies[/a] enabled in your PrestaShop. The currency selected by
        the customer in the Front Office is using for payment
        (see your [a @href2@]Payment Preferences - Currency restrictions[/a]).' mod='braintreeofficial'}|braintreereplace:['@href1@' => {$link->getAdminLink('AdminCurrencies', true)}, '@href2@' => {$link->getAdminLink('AdminPaymentPreferences', true)}, '@target@' => {'target="blank"'}]}
    </div>

    <div>
        {l s='To offer the payment via Braintree to all your customers in every currency, you must add your merchant
        account id(s). Otherwise, the payment via Braintree will not be offered in currencies with empty merchant account id.
        If you prefer to change your payment preferences for enabling currency conversion please click on the button
        "Change my payment preferences".' mod='braintreeofficial'}
    </div>

    <div>
        <a class="btn btn-default" href="#configuration_form_2">
            {l s='Add my merchant account ID(s)' mod='braintreeofficial'}
        </a>
        <a class="btn btn-default"
           href="{$link->getAdminLink('AdminPaymentPreferences', true)}"
           target="_blank">
            {l s='Change my payment preferences' mod='braintreeofficial'}
        </a>
    </div>
</div>
