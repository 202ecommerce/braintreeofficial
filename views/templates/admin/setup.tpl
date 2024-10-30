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

{extends file='./admin.tpl'}

{block name='content'}
	{if isset($showMessageAboutStateName) && $showMessageAboutStateName}
		{include './_partials/messageAboutStateName.tpl'}
	{/if}

	{if isset($need_rounding) && $need_rounding}
		{include './_partials/roundingSettingsMessage.tpl'}
	{/if}

	{include './_partials/headerLogo.tpl'}

	<div class="container-fluid bt__p-0">
		<div class="row flex">
			<div class="col-lg-8 col-sm-12 stretchHeightForm">
				{if isset($formAccountSettings)}
					{$formAccountSettings nofilter} {*can not escape a variable. Variable contains html code*}
				{/if}
			</div>
			<div class="col-lg-4 col-sm-12" id="status-block">
				{if isset($formStatus)}
					{$formStatus nofilter} {*can not escape a variable. Variable contains html code*}
				{/if}
			</div>
		</div>

		<div class="row flex">
			<div class="col-lg-8 col-sm-12">
				{if isset($formMerchantAccounts)}
					{$formMerchantAccounts nofilter} {*can not escape a variable. Variable contains html code*}
				{/if}
			</div>
			<div class="col-lg-4 col-sm-12 stretchHeightForm">
				{if isset($formPaymentSettings)}
					{$formPaymentSettings nofilter} {*can not escape a variable. Variable contains html code*}
				{/if}
			</div>
		</div>
	</div>
{/block}
