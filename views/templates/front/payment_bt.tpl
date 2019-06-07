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


{*Displaying a button or the iframe*}
<div class="row">
    <div class="col-xs-12 col-md-10">
        <div class="braintree-row-payment">
            <div class="payment_module braintree-card">
                <form action="{$braintreeSubmitUrl}" id="braintree-card-form" method="post">
                {if isset($init_error)}
                    <div class="alert alert-danger">{$init_error|escape:'htmlall':'UTF-8'}</div>
                    <div id="logo_braintree_by_paypal"><img src="https://s3-us-west-1.amazonaws.com/bt-partner-assets/paypal-braintree.png" height="20px"></div>
                {else}

                        {if isset($active_vaulting) && isset($payment_methods) && !empty($payment_methods)}
                            <div id="bt-vault-form">
                                <p><b>{l s='Choose your card' mod='braintree'}:</b></p>
                                <select name="bt_vaulting_token" class="form-control">
                                    <option value="">{l s='Choose your card' mod='braintree'}</option>
                                    {foreach from=$payment_methods key=method_key  item=method}
                                        <option value="{$method.token|escape:'htmlall':'UTF-8'}" {if $check3Dsecure} data-nonce="{$method.nonce}"{/if}>
                                            {if $method.name}{$method.name|escape:'htmlall':'UTF-8'} - {/if}
                                            {$method.info|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        {/if}

                        <div id="block-card-number" class="block_field">
                            <div id="card-number" class="hosted_field"><div id="card-image"></div></div>

                        </div>

                        <div id="block-expiration-date" class="block_field half_block_field">
                            <div id="expiration-date" class="hosted_field"></div>
                        </div>

                        <div id="block-cvv" class="block_field half_block_field">
                            <div id="cvv" class="hosted_field"></div>
                        </div>

                        <input type="hidden" name="deviceData" id="deviceData"/>
                        <input type="hidden" name="client_token" value="{$braintreeToken|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" name="liabilityShifted" id="liabilityShifted"/>
                        <input type="hidden" name="liabilityShiftPossible" id="liabilityShiftPossible"/>
                        <input type="hidden" name="payment_method_nonce" id="payment_method_nonce"/>
                        <input type="hidden" name="card_type" id="braintree_card_type"/>
                        <input type="hidden" name="payment_method_bt" value="{$method_bt|escape:'htmlall':'UTF-8'}"/>
                        <div class="braintree_clear"></div>
                        <div id="bt-card-error-msg" class="alert alert-danger"></div>
                        {if isset($active_vaulting) && $active_vaulting}
                            <div class="save-in-vault">
                                <input type="checkbox" name="save_card_in_vault" id="save_card_in_vault"/> <label for="save_card_in_vault"> {l s='Memorize my card' mod='braintree'}</label>
                            </div>
                        {/if}
                        <div id="logo_braintree_by_paypal"><img src="https://s3-us-west-1.amazonaws.com/bt-partner-assets/paypal-braintree.png" height="20px"></div>

                {/if}
                </form>
            </div>
        </div>
    </div>
</div>


<script>

    var authorization = '{$braintreeToken|escape:'htmlall':'UTF-8'}';
    var bt_amount = {$braintreeAmount|escape:'htmlall':'UTF-8'};
    var check3DS = {$check3Dsecure|escape:'htmlall':'UTF-8'};
    var bt_translations = {
        client:"{l s='Error create Client' mod='braintree'}",
        card_nmb:"{l s='Card number' mod='braintree'}",
        cvc:"{l s='CVC' mod='braintree'}",
        date:"{l s='MM/YY' mod='braintree'}",
        hosted:"{l s='Error create Hosted fields' mod='braintree'}",
        empty:"{l s='All fields are empty! Please fill out the form.' mod='braintree'}",
        invalid:"{l s='Some fields are invalid :' mod='braintree'}",
        token:"{l s='Tokenization failed server side. Is the card valid?' mod='braintree'}",
        network:"{l s='Network error occurred when tokenizing.' mod='braintree'}",
        tkn_failed:"{l s='Tokenize failed' mod='braintree'}",
        https:"{l s='3D Secure requires HTTPS.' mod='braintree'}",
        load_3d:"{l s='Load 3D Secure Failed' mod='braintree'}",
        request_problem:"{l s='There was a problem with your request.' mod='braintree'}",
        failed_3d:"{l s='3D Secure Failed' mod='braintree'}",
        empty_field:"{l s='is empty.' mod='braintree'}",
        expirationDate:"{l s='Expiration Date' mod='braintree'}",
        number:"{l s='card number' mod='braintree'}",
        cvv:"{l s='CVV' mod='braintree'}",
    };

</script>


