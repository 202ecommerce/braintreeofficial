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


{*Displaying a button or the iframe*}
<div class="row">
	<div class="col-xs-12 col-md-10">
		<div class="bt braintree-row-payment bt__pb-3">
			<div class="bt__mb-2">
				<i class="material-icons mi-lock">lock</i>
				<b>{l s='Pay securely using your credit card.' mod='braintreeofficial'}</b>
			<img style="width: 120px" class="bt__ml-2" src="{$baseDir|addslashes}modules/braintreeofficial/views/img/braintree-paypal.png">
			</div>	
			<div class="payment_module braintree-card">
				{if !isset($init_error)}
					<form action="{$braintreeSubmitUrl}" data-braintree-card-form method="post">
						{if isset($active_vaulting) && isset($payment_methods) && !empty($payment_methods)}
							<div id="bt-vault-form" class="bt__mt-2 bt__mb-3">
								<p><b>{l s='Choose your card' mod='braintreeofficial'}:</b></p>
								<select name="bt_vaulting_token" data-bt-vaulting-token="bt" class="form-control bt__form-control">
									<option value="">{l s='Use a new card' mod='braintreeofficial'}</option>
									{foreach from=$payment_methods key=method_key  item=method}
										<option value="{$method.token|escape:'htmlall':'UTF-8'}" data-nonce="{$method.nonce}">
											{if $method.name}{$method.name|escape:'htmlall':'UTF-8'} - {/if}
											{$method.info|escape:'htmlall':'UTF-8'}
										</option>
									{/foreach}
								</select>
							</div>
						{/if}
						
						<div data-form-new-card>							
							<div id="block-card-number" class="form-group">
								<label for="card-number" class="bt__form-label">{l s='Card number' mod='braintreeofficial'}</label>
								<div id="card-number" class="form-control bt__form-control bt__position-relative" data-bt-field="number">
									<div id="card-image"></div>
								</div>
								<div data-bt-error-msg class="bt__text-danger bt__mt-1"></div>
							</div>
							<div class="bt__form-row">
								<div id="block-expiration-date" class="form-group col-md-6 bt__flex bt__flex-column">
									<label for="expiration-date" class="bt__form-label bt__flex bt__align-items-center bt__flex-grow-1">{l s='Expiration Date' mod='braintreeofficial'}
										<span class="text-muted">{l s='(MM/YY)' mod='braintreeofficial'}</span>
									</label>
									<div id="expiration-date" class="form-control bt__form-control bt__position-relative" data-bt-field="expirationDate"></div>
									<div data-bt-error-msg class="bt__text-danger bt__mt-1"></div>
								</div>

								<div id="block-cvv" class="form-group col-md-6 bt__flex bt__flex-column" data-bt-card-cvv>
									<label for="cvv" class="bt__form-label bt__flex bt__align-items-center bt__flex-grow-1">
										<div class="bt__flex bt__align-items-center">
											<div>
												{l s='CVV' mod='braintreeofficial'}
											</div>
											<div class="bt__ml-2 bt__flex-grow-1">
												{include file='module:braintreeofficial/views/templates/front/_partials/svg/cvv.tpl'}
											</div>
										</div>
									</label>
									<div id="cvv" class="form-control bt__form-control bt__position-relative" data-bt-field="cvv"></div>
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
							{if isset($active_vaulting) && $active_vaulting}
								<div class="bt__my-2">
									<input type="checkbox" name="save_card_in_vault" id="save_card_in_vault"/> 
									<label for="save_card_in_vault" class="form-check-label bt__form-check-label"> {l s='Memorize my card' mod='braintreeofficial'}</label>
								</div>
							{/if}
						</div>

						<div data-form-cvv-field class="bt__hidden">
							<div id="block-cvv-field" class="form-group col-md-6 bt__pl-0">
								<label for="btCvvField" class="bt__form-label">{l s='CVV' mod='braintreeofficial'}</label>
								<input type="number" name="btCvvField" id="btCvvField" class="form-control bt__form-control bt__number-field" placeholder="123">
							</div>
							<div data-bt-cvv-error-msg class="bt__text-danger bt__mt-1 col-lg-12"></div>
						</div>
					</form>
					<div data-bt-card-error-msg class="alert alert-danger bt__hidden"></div>
				{else}
					<div class="alert alert-danger">{$init_error|escape:'htmlall':'UTF-8'}</div>
				{/if}
			</div>
		</div>
	</div>
</div>