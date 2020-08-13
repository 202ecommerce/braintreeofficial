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

<div>
	<p>
		{l s='Braintree Account' mod='braintreeofficial'}.
	</p>
	<p>
		{l s='In order to activate the module, you must connect your existing Braintree account or create a new one.' mod='braintreeofficial'}
	</p>
	<p>
		{if isset($accountConfigured) && $accountConfigured}
			<span class="mr-20">
				{l s='Connected Merchand ID' mod='braintreeofficial'}
			</span>

			<strong>
				{if isset($sandboxEnvironment) && $sandboxEnvironment && isset($braintreeofficial_merchant_id_sandbox)}
					{$braintreeofficial_merchant_id_sandbox|escape:'htmlall':'utf-8'}
				{elseif isset($braintreeofficial_merchant_id_live)}
					{$braintreeofficial_merchant_id_live|escape:'htmlall':'utf-8'}
				{/if}
			</strong>

			<span class="btn btn-default ml-20" data-toggle="modal" data-target="#credentialBlock">
				<i class="icon-edit"></i>
				{l s='Edit' mod='braintreeofficial'}
			</span>

			<span class="btn btn-default ml-20" data-bt-logout>
				<i class="icon-signout"></i>
				{l s='Logout' mod='braintreeofficial'}
			</span>
		{else}
			<span class="btn btn-default" data-toggle="modal" data-target="#credentialBlock">
				<i class="icon-signin"></i>
				{l s='Connect or create Braintree account' mod='braintreeofficial'}
			</span>
		{/if}

		{if isset($showMigrationBtn) && $showMigrationBtn}
			<div>
				<a class="btn btn-default" href="{$link->getAdminLink('AdminBraintreeOfficialMigration', true)}">
					{l s='Migrate your settings from PayPal module' mod='braintreeofficial'}
				</a>
			</div>
		{/if}
	</p>
</div>
<div class="modal fade" id="credentialBlock" role="dialog" aria-labelledby="credentialBlock" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div>
					<div>
						<p class="h3">{l s='API Credentials' mod='braintreeofficial'}</p>
						<p>
							{l s='In order to accept Braintree payments, please fill your API Keys.' mod='braintreeofficial'}
						</p>
						<p>
							{{l s='If you do not have a Braintree account, you can create by following this [a @href1@]link[/a]' mod='braintreeofficial'}|braintreereplace:['@href1@' => {'https://signups.braintreepayments.com/'}, '@target@' => {'target="blank"'}]}
						</p>

						{include './formAccount.tpl'}
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{l s='Cancel' mod='braintreeofficial'}</button>
				<button type="button" data-confirm-credentials class="btn btn-primary">{l s='Confirm API Credentials' mod='braintreeofficial'}</button>
			</div>
		</div>
	</div>
</div>