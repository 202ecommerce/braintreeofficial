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
* @author PrestaShop SA <contact@prestashop.com>
	* @copyright 2007-2019 PrestaShop SA
	* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
	* International Registered Trademark & Property of PrestaShop SA
	*}

{include './_partials/headerLogo.tpl'}

<div class="container-fluid bt__p-0">
	<div class="row flex">
		<div class="col-lg-8 stretchHeightForm">
			{if isset($formAccountSettings)}
				{$formAccountSettings nofilter}
			{/if}
		</div>
		<div class="col-lg-4">
			{if isset($formEnvironmentSettings)}
				{$formEnvironmentSettings nofilter}
			{/if}
		</div>
	</div>

	<div class="row flex">
		<div class="col-lg-8">
			{if isset($formPaymentSettings)}
				{$formPaymentSettings nofilter}
			{/if}
			{if isset($formMerchantAccounts)}
				{$formMerchantAccounts nofilter}
			{/if}
		</div>
		<div class="col-lg-4 stretchHeightForm" id="status-block">
			{if isset($formStatus)}
				{$formStatus nofilter}
			{/if}
		</div>
	</div>
</div>