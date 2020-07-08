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

<div class="alert alert-info">
	<p>
        {l s='Note : As part of European Regulation PSD2 and related SCA (Strong Customer Authentication) planned on September 14th 2019, all transactions will have to go through SCA (3DS 2.0) with the aim to reduce friction (fewer “client challenges”) while raise conversion and protection (more liability shifts from merchant to bank).' mod='braintreeofficial'}
	</p>

	<p>
        {l s='It is thus recommended to enable 3D Secure in order to avoid bank declines and impact to your business. (Go to "Customize Experience Checkout")' mod='braintreeofficial'}
	</p>

	<p>
        {{l s='More info in our blog post [b]to get the last updates:[/b]' mod='braintreeofficial'}|braintreereplace}
		<a href="https://www.braintreepayments.com/ie/features/3d-secure">
			https://www.braintreepayments.com/ie/features/3d-secure
		</a>
	</p>
</div>

<div class="panel active-panel bt__flex bt__align-items-center">
	<div class="bt__pr-4">
		<img style="width: 135px" src="/modules/braintreeofficial/views/img/braintree-paypal.png">
	</div>
	<div class="bt__pl-5">
		<p>
			{l s='Activate the Braintree module to start converting better on mobile and all platforms in 45+ countries' mod='braintreeofficial'}.
		</p>
		{if $page_header_toolbar_title !== 'Help' && $page_header_toolbar_title !== 'Logs'}
			<p>{l s='Activate in 4 easy steps' mod='braintreeofficial'}: </p>
			<p>
				<ul class="list-unstyled">
					<li>
						<a href="#bt_config_account" data-bt-link-settings>1) {l s='Connect below your existing Braintree account or create a new one' mod='braintreeofficial'}.</a>
					</li>
					<li>
						<a href="#bt_config_behavior" data-bt-link-settings>2) {l s='Enable Credit Card and/or PayPal wallet as payment methods' mod='braintreeofficial'}.</a>
					</li>
					<li>
						<a href="#bt_config_payment" data-bt-link-settings>3) {l s='Adjust your Payment setting to either capture payments instantly (Sale), or after you confirm the order (Authorize)' mod='braintreeofficial'}.</a>
					</li>
					<li>
						<a href="#bt_config_environment" data-bt-link-settings>4) {l s='Make sure the module is set to Production mode' mod='braintreeofficial'}.</a>
					</li>
				</ul>
			</p>
			<p><a target="_blank" href="https://addons.prestashop.com/fr/modules-paiement-prestashop/46833-braintree-officiel.html">{l s='More Information' mod='braintreeofficial'}</a></p>
		{/if}
	</div>
</div>