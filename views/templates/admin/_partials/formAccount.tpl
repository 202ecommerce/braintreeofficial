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

<p>
	{l s='To find your API Keys, please follow those steps:' mod='braintreeofficial'}
</p>
<ul>
	<li>
		{{l s='Log into your [a @href1@]account[/a]' mod='braintreeofficial'}|braintreereplace:['@href1@' => {'https://www.braintreegateway.com/login'}, '@target@' => {'target="blank"'}]}
	</li>
	<li>
		{l s='Click on Parameters (the working wheel logo)' mod='braintreeofficial'}
	</li>
	<li>
		{l s='Click on API' mod='braintreeofficial'}
	</li>
	<li>
		{l s='Click the "Generate New API Key"' mod='braintreeofficial'}
	</li>
	<li>
		{l s='Click on "View" in the "Private key" column' mod='braintreeofficial'}
	</li>
	<li>
		{l s='Copy your "Private Key", "Public Key" and "Merchant ID" and paste them below:' mod='braintreeofficial'}
	</li>
</ul>

<p>
    {{l s='To retrieve sandbox API Keys please repeat the steps by connecting to [a @href1@]sandbox account[/a] or creating a new [a @href2@]one[/a]' mod='braintreeofficial'}|braintreereplace:['@href1@' => {'https://sandbox.braintreegateway.com/login'}, '@href2@' => {'https://www.braintreepayments.com/sandbox'},  '@target@' => {'target="blank"'}]}
</p>

<div>
	<button class="btn btn-default" type="button" data-role-collapse data-collapsed="#apiHelpMessage">
		{l s='Impossible to access to API via Braintree account?' mod='braintreeofficial'}
	</button>

	<div id="apiHelpMessage" class="alert alert-info bt__mt-2" style="display: none">
		{include './apiHelpMessage.tpl'}
	</div>
</div>

<div class="row form-account {if isset($sandboxEnvironment) && $sandboxEnvironment == false}current-account{/if} {if isset($braintreeofficial_merchant_id_live) && $braintreeofficial_merchant_id_live} visible{/if}">
	<div class="h3">{l s='Live' mod='braintreeofficial'}</div>
	<hr>
	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Public key' mod='braintreeofficial'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintreeofficial_public_key_live"
							{if isset($braintreeofficial_public_key_live)}value='{$braintreeofficial_public_key_live|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Private key' mod='braintreeofficial'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintreeofficial_private_key_live"
							{if isset($braintreeofficial_private_key_live)}value='{$braintreeofficial_private_key_live|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Merchant ID' mod='braintreeofficial'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintreeofficial_merchant_id_live"
							{if isset($braintreeofficial_merchant_id_live)}value='{$braintreeofficial_merchant_id_live|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>
</div>

<div class="row form-account {if isset($sandboxEnvironment) && $sandboxEnvironment}current-account{/if} {if isset($braintreeofficial_merchant_id_sandbox) && $braintreeofficial_merchant_id_sandbox} visible{/if}">
	<div class="h3">{l s='Sandbox' mod='braintreeofficial'}</div>
	<hr>

	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Public key' mod='braintreeofficial'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintreeofficial_public_key_sandbox"
							{if isset($braintreeofficial_public_key_sandbox)}value='{$braintreeofficial_public_key_sandbox|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
				{l s='Private key' mod='braintreeofficial'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintreeofficial_private_key_sandbox"
							{if isset($braintreeofficial_private_key_sandbox)}value='{$braintreeofficial_private_key_sandbox|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
				{l s='Merchant ID' mod='braintreeofficial'}
		</label>
		<div class="col-lg-9">
				<input type="text"
								name="braintreeofficial_merchant_id_sandbox"
								{if isset($braintreeofficial_merchant_id_sandbox)}value='{$braintreeofficial_merchant_id_sandbox|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>
</div>
