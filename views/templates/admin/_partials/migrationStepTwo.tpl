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

<div class="panel migration-page flex justify-content-center">
   <div>
       <div class="h2 status-migration">
           {l s='Perfect! Your Braintree settings were migrated successfully!' mod='braintree'}
       </div>
       <p>
           {l s='Please add your Public and Private Key for completing your account setup.' mod='braintree'}
       </p>
       <form class="defaultForm form-horizontal" id="form-account">
           {include './formAccount.tpl'}
       </form>
       <p class="text-center text-danger">
           {l s='PayPal module will be disabled once the process is finished.' mod='braintree'}
       </p>
       <div class="text-center">
           <button class="btn btn-default" id="save-account">
               {l s='Save Account Settings' mod='braintree'}
           </button>
       </div>
   </div>
</div>



