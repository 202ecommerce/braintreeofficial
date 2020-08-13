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
    <div class="text-center">
        <i class="material-icons" style="font-size: 60px">
            check_circle
        </i>
    </div>
   <h2 class="text-center mb-20">
       {l s='Perfect! Your Braintree settings were migrated successfully!' mod='braintreeofficial'}
   </h2>
    <p class="text-center mb-20">
        {l s='Please check again all settings and disable the maintenance mode in your shop.' mod='braintreeofficial'}
    </p>
    <p class="text-center text-danger mb-20">
        {l s='Please uninstall the PayPal module once you finished all verifications.' mod='braintreeofficial'}
    </p>
    <p class="text-center">
        <a href="{$link->getAdminLink('AdminBraintreeOfficialSetup', true)}" class="btn btn-default">
            {l s='Go to Braintree Settings' mod='braintreeofficial'}
        </a>
    </p>
</div>



