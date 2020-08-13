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

<div class="panel migration-page flex justify-content-center">
   <div>
       <div class="text-center">
           <i class="material-icons status-migration-icon" style="font-size: 60px">
               check_circle
           </i>
       </div>
       <div class="h2 status-migration">
           {l s='Perfect! Your Braintree settings were migrated successfully!' mod='braintreeofficial'}
       </div>

       {if isset($isMultishop) == false || $isMultishop == false}

           <p>
               {l s='Please add your Public and Private Key for completing your account setup.' mod='braintreeofficial'}
           </p>
           <form class="defaultForm form-horizontal" id="form-account">
               {include './formAccount.tpl'}
           </form>
           <p class="text-center text-danger">
               {l s='PayPal module will be disabled once the process is finished.' mod='braintreeofficial'}
           </p>
           <div class="text-center">
               <button class="btn btn-default"
                       id="save-account"
                       data-loading-text="{l s='Saving' mod='braintreeofficial'}">
                   {l s='Save Account Settings' mod='braintreeofficial'}
               </button>
           </div>

           <div class="bt__mt-4 bt__mb-1">
                <div class="text-center">{l s='If needed you can add your API keys later (not recommended).' mod='braintreeofficial'}</div>
                <div class="text-center">{l s='In this case, the payment by Braintree will not be available for your customers until you add your account information.' mod='braintreeofficial'}</div>
           </div>

       {else}

           <p class="text-center text-danger">
               {l s='You are using the multishop configuration. Please go to the Braintree module configuration page to add your credentials.' mod='braintreeofficial'}
           </p>

           <p class="text-center text-danger">
               {l s='You have to add your credentials for each shop if you are using different Braintree accounts.' mod='braintreeofficial'}
           </p>

           <p class="text-center text-danger">
               {l s='PayPal module will be disabled once the process is finished.' mod='braintreeofficial'}
           </p>

       {/if}

       <div class="text-center">
           <a class="btn btn-default"
              href="{$link->getAdminLink('AdminBraintreeOfficialSetup', true)|addslashes}"
           >
               {l s='Add API keys later' mod='braintreeofficial'}
           </a>
       </div>
   </div>
</div>



