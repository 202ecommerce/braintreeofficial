{**
 * since 2007 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *}

<div>
	<p>
		{l s='Merchant Country:' mod='braintreeofficial'} <b>{$merchantCountry|escape:'htmlall':'UTF-8'}</b>
	</p>
	<p>
    {{l s='To  modify country: [a @href1@]International > Localization[/a]' mod='braintreeofficial'}|braintreereplace:['@href1@' => {$link->getAdminLink('AdminLocalization', true)}, '@target@' => {'target="blank"'}]}
	</p>
	<p>
		<span class="btn btn-default" id="btn-check-requirements">{l s='Check requirements' mod='braintreeofficial'}</span>
	</p>
	<ul class="list-unstyled">
		<li>
            {if isset($sslActivated) && $sslActivated}
				<i class="icon-check text-success"></i>
                {l s='SSL enabled.' mod='braintreeofficial'}
            {else}
				<i class="icon-remove text-danger"></i>
                {l s='SSL should be enabled on your web site.' mod='braintreeofficial'}
            {/if}
		</li>

		<li>
			{if isset($tlsVersion) && $tlsVersion['status']}
				<i class="icon-check text-success"></i>
				{l s='PHP cURL extension must be enabled on your server' mod='braintreeofficial'}
			{elseif isset($tlsVersion) && $tlsVersion['status'] == false}
				<i class="icon-remove text-danger"></i>
				{l s='PHP cURL extension must be enabled on your server' mod='braintreeofficial'}. {$tlsVersion['error_message']}
			{/if}
		</li>
		<li>
			{if isset($accountConfigured) && $accountConfigured}
				<i class="icon-check text-success"></i>
			{else}
				<i class="icon-remove text-danger"></i>
			{/if}
			{l s='You must connect your Braintree account' mod='braintreeofficial'}
		</li>

		{if $paymentCustomerCurrency}
			<li>
				{if isset($merchantAccountIdConfigured) && $merchantAccountIdConfigured}
					<i class="icon-check text-success"></i>
				{else}
					<i class="icon-remove text-danger"></i>
				{/if}
				{l s='You must add your merchant account ID for each currency' mod='braintreeofficial'}
			</li>
		{/if}

	</ul>
</div>