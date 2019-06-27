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
		<div class="bt braintree-row-payment bt__p-1 bt__pl-2 bt__mb-2">
			<div class="bt__mb-2">
				<i class="material-icons mi-lock">lock</i>
				<b>{l s='Pay securely using your credit card.' mod='braintree'}</b>
			<img style="width: 120px" class="bt__ml-2" src="/modules/braintree/views/img/braintree-paypal.png">
			</div>	
			<div class="payment_module braintree-card">
				<form action="{$braintreeSubmitUrl}" data-braintree-card-form method="post">
					{if !isset($init_error)}
						{if isset($active_vaulting) && isset($payment_methods) && !empty($payment_methods)}
							<div id="bt-vault-form" class="bt__mt-2 bt__mb-3">
								<p><b>{l s='Choose your card' mod='braintree'}:</b></p>
								<select name="bt_vaulting_token" data-bt-vaulting-token="bt" class="form-control bt__form-control">
									<option value="">{l s='Use a new card' mod='braintree'}</option>
									{foreach from=$payment_methods key=method_key  item=method}
										<option value="{$method.token|escape:'htmlall':'UTF-8'}" {if $check3Dsecure} data-nonce="{$method.nonce}"{/if}>
											{if $method.name}{$method.name|escape:'htmlall':'UTF-8'} - {/if}
											{$method.info|escape:'htmlall':'UTF-8'}
										</option>
									{/foreach}
								</select>
							</div>
						{/if}
						
						<div data-form-new-card>							
							<div id="block-card-number" class="form-group">
								<label for="card-number" class="bt__form-label">{l s='Card number' mod='braintree'}</label>
								<div id="card-number" class="form-control bt__form-control"><div id="card-image"></div></div>
								<div data-bt-error-msg class="bt__text-danger bt__mt-1"></div>
							</div>
							<div class="bt__form-row">
								<div id="block-expiration-date" class="form-group col-md-6">
									<label for="expiration-date" class="bt__form-label">{l s='Expiration Date' mod='braintree'}
										<span class="text-muted">{l s='(MM/YY)' mod='braintree'}</span>
									</label>
									<div id="expiration-date" class="form-control bt__form-control"></div>
									<div data-bt-error-msg class="bt__text-danger bt__mt-1"></div>
								</div>

								<div id="block-cvv" class="form-group col-md-6" data-bt-card-cvv>
									<label for="cvv" class="bt__form-label">{l s='CVV' mod='braintree'}</label>
									<div id="cvv" class="form-control bt__form-control"></div>
									<div data-bt-error-msg class="bt__text-danger bt__mt-1"></div>
								</div>
							</div>

							<input type="hidden" name="deviceData" id="deviceData"/>
							<input type="hidden" name="client_token" value="{$braintreeToken|escape:'htmlall':'UTF-8'}">
							<input type="hidden" name="liabilityShifted" id="liabilityShifted"/>
							<input type="hidden" name="liabilityShiftPossible" id="liabilityShiftPossible"/>
							<input type="hidden" name="payment_method_nonce" data-payment-method-nonce="bt" />
							<input type="hidden" name="card_type" data-bt-card-type />
							<input type="hidden" name="payment_method_bt" value="{$method_bt|escape:'htmlall':'UTF-8'}"/>
							<div class="clearfix"></div>
							<div data-bt-card-error-msg class="alert alert-danger bt__hidden"></div>
							{if isset($active_vaulting) && $active_vaulting}
								<div class="bt__my-2">
									<input type="checkbox" name="save_card_in_vault" id="save_card_in_vault"/> 
									<label for="save_card_in_vault" class="form-check-label bt__form-check-label"> {l s='Memorize my card' mod='braintree'}</label>
								</div>
							{/if}
						</div>
					{else}
						<div class="alert alert-danger">{$init_error|escape:'htmlall':'UTF-8'}</div>
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
        date:"{l s='MM/YY' mod='braintree'}",
        hosted:"{l s='Error create Hosted fields' mod='braintree'}",
        invalid:"{l s='is invalid.' mod='braintree'}",
        token:"{l s='Tokenization failed server side. Is the card valid?' mod='braintree'}",
        network:"{l s='Network error occurred when tokenizing.' mod='braintree'}",
        tkn_failed:"{l s='Tokenize failed' mod='braintree'}",
        https:"{l s='3D Secure requires HTTPS.' mod='braintree'}",
        load_3d:"{l s='Load 3D Secure Failed' mod='braintree'}",
        request_problem:"{l s='There was a problem with your request.' mod='braintree'}",
        failed_3d:"{l s='3D Secure Failed' mod='braintree'}",
        empty_field:"{l s='is empty.' mod='braintree'}",
        expirationDate:"{l s='This expiration date ' mod='braintree'}",
        number:"{l s='This card number ' mod='braintree'}",
        cvv:"{l s='Please fill out a CVV.' mod='braintree'}",
    };
</script>


