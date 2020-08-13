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

{if isset($show_paypal_benefits) && $show_paypal_benefits}
	{include file='module:braintreeofficial/views/templates/front/paypal_info.tpl'}
{/if}

<div class="row">
	<div class="col-xs-12 col-md-10">
		<div class="braintree-braintree-row-payment bt__pb-3">
			<div class="payment_module paypal-braintree">
				<form action="{$braintreeSubmitUrl}" data-braintree-paypal-form method="post">
					{if !isset($init_error)}
						{if isset($active_vaulting) && isset($payment_methods) && !empty($payment_methods)}
							<div id="bt-vault-form" class="bt__mt-2 bt__mb-3">
								<p><b>{l s='Choose your PayPal account' mod='braintreeofficial'}:</b></p>
								<select name="pbt_vaulting_token" data-bt-vaulting-token="pbt" class="form-control bt__form-control">
									<option value="">{l s='Use a new paypal account' mod='braintreeofficial'}</option>
									{foreach from=$payment_methods key=method_key  item=method}
										<option value="{$method.token|escape:'htmlall':'UTF-8'}">
											{if $method.name}{$method.name|escape:'htmlall':'UTF-8'} - {/if}
											{$method.info|escape:'htmlall':'UTF-8'}
										</option>
									{/foreach}
								</select>
							</div>
						{/if}
						<div data-form-new-account>
							<input type="hidden" name="payment_method_nonce" data-payment-method-nonce="pbt"/>
							<input type="hidden" name="payment_method_bt" value="{$bt_method|escape:'htmlall':'UTF-8'}"/>
							{if isset($active_vaulting) && $active_vaulting}
								<div class="bt__mb-4" data-bt-save-account>
									<input type="checkbox" name="save_account_in_vault" id="save_account_in_vault"/> 
									<label for="save_account_in_vault" class="form-check-label bt__form-check-label"> {l s='Memorize my PayPal account' mod='braintreeofficial'}</label>
								</div>						
							{/if}
							<div data-bt-vault-info class="bt__hidden bt__mb-4">
								<p>{l s='Please complete your payment by confirming below' mod='braintreeofficial'}</p>
							</div>
							<div data-braintree-button id="braintree-button" class="braintree-button bt__mb-4"></div>
						</div>
					{else}
						<div class="alert alert-danger">{$init_error}</div>
					{/if}
				</form>
				<div data-bt-pp-error-msg class="bt__hidden alert alert-danger"></div>
			</div>
		</div>
	</div>
</div>