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

<p>
	{l s='To find your API Keys, please follow those steps:' mod='braintree'}
</p>
<ul>
	<li>
		{{l s='Log into your [a @href1@]account[/a]' mod='braintree'}|braintreereplace:['@href1@' => {'https://www.braintreegateway.com/login'}, '@target@' => {'target="blank"'}]}
	</li>
	<li>
		{l s='Click the working wheel (logo)' mod='braintree'}
	</li>
	<li>
		{l s='Click on API' mod='braintree'}
	</li>
	<li>
		{l s='Click the "Generate New API Key"' mod='braintree'}
	</li>
	<li>
		{l s='Click on "View" in the "Private key" column' mod='braintree'}
	</li>
	<li>
		{l s='Cpoy your "Private Key", "Public Key" and "Mechand ID" and paste them below:' mod='braintree'}
	</li>
</ul>

<div class="row form-account {if isset($sandboxEnvironment) && $sandboxEnvironment == false}current-account{/if} {if isset($braintree_merchant_id_live) && $braintree_merchant_id_live} visible{/if}">
	<div class="h3">{l s='Live' mod='braintree'}</div>
	<hr>
	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Public key' mod='braintree'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintree_public_key_live"
							{if isset($braintree_public_key_live)}value='{$braintree_public_key_live|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Private key' mod='braintree'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintree_private_key_live"
							{if isset($braintree_private_key_live)}value='{$braintree_private_key_live|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Merchant ID' mod='braintree'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintree_merchant_id_live"
							{if isset($braintree_merchant_id_live)}value='{$braintree_merchant_id_live|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>
</div>

<div class="row form-account {if isset($sandboxEnvironment) && $sandboxEnvironment}current-account{/if} {if isset($braintree_merchant_id_sandbox) && $braintree_merchant_id_sandbox} visible{/if}">
	<div class="h3">{l s='Sandbox' mod='braintree'}</div>
	<hr>
	<p>
		{{l s='To retrieve sandbox API Keys please repeat the steps by connecting to [a @href1@]sandbox account[/a] or creating a new [a @href2@]one[/a]' mod='braintree'}|braintreereplace:['@href1@' => {'https://www.braintreepayments.com/fr/sandbox'}, '@href2@' => {'https://www.braintreepayments.com/sandbox'},  '@target@' => {'target="blank"'}]}
	</p>
	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Public key' mod='braintree'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintree_public_key_sandbox"
							{if isset($braintree_public_key_sandbox)}value='{$braintree_public_key_sandbox|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
				{l s='Private key' mod='braintree'}
		</label>
		<div class="col-lg-9">
			<input type="text"
							name="braintree_private_key_sandbox"
							{if isset($braintree_private_key_sandbox)}value='{$braintree_private_key_sandbox|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
				{l s='Merchant ID' mod='braintree'}
		</label>
		<div class="col-lg-9">
				<input type="text"
								name="braintree_merchant_id_sandbox"
								{if isset($braintree_merchant_id_sandbox)}value='{$braintree_merchant_id_sandbox|escape:'htmlall':'utf-8'}'{/if}>
		</div>
	</div>
</div>
