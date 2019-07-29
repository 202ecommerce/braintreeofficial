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
    <div class="h4">
        {l s='Fill your merchant account id for every currency' mod='braintree'}
    </div>

    <div>
        {{l s='You are currently having multiple [a @href1@]currencies[/a] enabled in your PrestaShop. The currency selected by
        the customer in the Front Office is using for payment
        (see your [a @href2@]Payment Preferences - Currency restrictions[/a]).' mod='braintree'}|totlreplace:['@href1@' => {$link->getAdminLink('AdminCurrencies', true)}, '@href2@' => {$link->getAdminLink('AdminPaymentPreferences', true)}, '@target@' => {'target="blank"'}]}
    </div>

    <div>
        {l s='To offer the payment via Braintree to all your customers in every currency, you must add your merchant
        account id(s). Otherwise, the payment via Braintree will not be offered in currencies with empty merchant account id.
        If you prefer to change your payment preferences for enabling currency conversion please click on the button
        "Change my payment preferences".' mod='braintree'}
    </div>

    <div>
        <a class="btn btn-default" href="#configuration_form_2">
            {l s='Add my merchant account ID(s)' mod='braintree'}
        </a>
        <a class="btn btn-default"
           href="{$link->getAdminLink('AdminPaymentPreferences', true)}"
           target="_blank">
            {l s='Change my payment preferences' mod='braintree'}
        </a>
    </div>
</div>