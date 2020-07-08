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
* @author 202-ecommerce <tech@202-ecommerce.com>
	* @copyright PayPal
	* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
	* International Registered Trademark & Property of PrestaShop SA
	*}
{include './_partials/headerLogo.tpl'}

{if isset($need_rounding) && $need_rounding}
	{include file="./_partials/block_info.tpl"}
{/if}

<div class="panel help">
	<ul class="tick">
		<li>
			<p class="h4">
				{l s='Discover module ducumentation before configuration' mod='braintreeofficial'}
			</p>
			<p>
				<a target="_blank" href="https://help.202-ecommerce.com/wp-content/uploads/2019/12/User-guide-Braintree-PrestaShop-module.pdf"
					class="btn btn-default">
					{l s='Access user documentation for module configuration.' mod='braintreeofficial'}
				</a>
			</p>
		</li>
		<li>
			<p class="h4">
				{l s='Check requirements before installation' mod='braintreeofficial'}
			</p>
			<p>
				{l s='Are you using the required TLS version? Did you select a default country? Click on the button below and check if all requirements are completed!' mod='braintreeofficial'}
			</p>
			<p>
				<button name="submit-ckeck_requirements" class="btn btn-default" id="ckeck_requirements">
					{l s='Check requirements' mod='braintreeofficial'}
				</button>
				<p class="action_response"></p>
			</p>
		</li>

		<li>
			<p class="h4">
				{l s='Impossible to access to API via Braintree account?' mod='braintreeofficial'}
			</p>

			<div>
				<button class="btn btn-default" type="button" data-role-collapse data-collapsed="#apiHelpMessage">
					{l s='Discover more details' mod='braintreeofficial'}
				</button>

				<div id="apiHelpMessage" class="alert alert-info bt__mt-2" style="display: none">
					{include './_partials/apiHelpMessage.tpl'}
				</div>
			</div>

		</li>

		<li>
			<p class="h4">
				{l s='Do you still have any questions?' mod='braintreeofficial'}
			</p>
			<p>
				{l s='Contact us! We will be happy to help!' mod='braintreeofficial'}
			</p>
			<p>
				<a target="_blank" href="https://www.paypal.com/fr/webapps/mpp/contact-us" class="btn btn-default">
					{l s='Contact our product team for any functional questions' mod='braintreeofficial'}
				</a>
			</p>
			<p>
				<a target="_blank" href="https://addons.prestashop.com/en/contact-us?id_product=46833"
					class="btn btn-default">
					{l s='Contact our technical support' mod='braintreeofficial'}
				</a>
			</p>
		</li>
	</ul>
</div>