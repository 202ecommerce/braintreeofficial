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

<div class="panel migration-page">
   <div id="section-one">
       <div class="h3 text-center">{l s='Hello!' mod='braintreeofficial'}</div>
       <div>{l s='Welcome to the new Braintree module. ' mod='braintreeofficial'}</div>
       <div class="mb-20">
           {l s='Starting July 2019, Braintree payment solution is separated from the PayPal module. You are currently using Braintree via the PayPal module. You can quickly migrate your Braintree configurations by clicking on the "Import Settings" button below. We can migrate all your current configurations but you will need to add your Public Key and Private Key for finishing your account setup.' mod='braintreeofficial'}
       </div>
       <div>{l s='Please check our FAQ before migration :' mod='braintreeofficial'}</div>
       <ul>
           <li>
               <div class="font-italic">{l s='What should I do before migration?' mod='braintreeofficial'}</div>
               <div>
                   {l s='We highly recommend enabling the maintenance mode before starting the migration process (the import of the Braintree settings can not take more than 10 minutes).' mod='braintreeofficial'}
               </div>
           </li>

           <li>
               <div class="font-italic">{l s='What is happening with previously passed orders?' mod='braintreeofficial'}</div>
               <div>
                   {l s='We will migrate all data, so every order passed via Braintree payment solution in the PayPal module will be connected to the new Braintree module. There are no additional actions to do from your side after migration. You can simply manage your orders as usual.' mod='braintreeofficial'}
               </div>
           </li>

           <li>
               <div class="font-italic">{l s='What should I do with the PayPal module?' mod='braintreeofficial'}</div>
               <div>
                   {l s='PayPal module will be automatically disabled after migration. Please uninstall the Paypal module once you finished the verifications and you get some new orders passed via new Braintree module.' mod='braintreeofficial'}
               </div>
           </li>
       </ul>

       <div class="mb-20">
           {l s='If you do not want to migrate your Braintree settings automatically please click on the button "No, thank you". Pay attention that PayPal module will be disabled.' mod='braintreeofficial'}
       </div>

       <div class="flex space-between">
           <div class="btn btn-success flex direction-column space-around" id="start-migration">
               <span class="h4">{l s='Start now' mod='braintreeofficial'}</span>
               <span>{l s='Import Braintree Settings' mod='braintreeofficial'}</span>
           </div>

           <div class="btn btn-warning flex direction-column space-around" id="skip-migration">
               <span class="h4">{l s='No, thank you' mod='braintreeofficial'}</span>
               <span>{l s='Start Braintree' mod='braintreeofficial'}</span>
               <span>{l s='Configurations' mod='braintreeofficial'}</span>
           </div>
       </div>
   </div>

    <div id="section-two" style="display: none">
        <div>
            <div class="h2 text-center">{l s='Please wait... We are migrating your Braintree settings' mod='braintreeofficial'}</div>
            <div class="h2 text-center">{l s='Please keep this page open.' mod='braintreeofficial'}</div>
            <div class="text-center">
                <img src="/modules/braintreeofficial/views/img/loading.gif" style="width: 100px">
            </div>
            <div class="text-center">
							{{l s='The migration will take about 5 minutes. If it is not finished after 10 minutes please contact [a @href1@]our support team.[/a]' mod='braintreeofficial'}|braintreereplace:['@href1@' => {'https://addons.prestashop.com/fr/contactez-nous?id_product=1748'}, '@target@' => {'target="blank"'}]}
            </div>
        </div>
    </div>
</div>



