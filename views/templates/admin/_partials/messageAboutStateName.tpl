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

<div class="alert alert-warning">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <div>
        {l s='Please note that some order status have been renamed for improving the user experience of your customers :' mod='braintreeofficial'}
    </div>

    <ul>
        <li>
            <div>
                {{l s='Old status name : "Awaiting for Braintree payment" -> New order status name : [b]"Payment pending (authorized)"[/b] - This status is used if your are using the "Authorize" payment action.' mod='braintreeofficial'}|braintreereplace}
            </div>
            <div>
                {{l s='[i]It means that the processor authorized the transaction. Your customer may see a pending charge on his or her account. However, before the customer is actually charged and before you receive the funds, you must submit the transaction for settlement (i.e.you have to change the order status for accepting or cancelling the payment).[/i]' mod='braintreeofficial'}|braintreereplace}
            </div>

        </li>

        <li>
            <div>
                {{l s='Old status name : "Awaiting for Braintree validation" -> New order status name : [b]"Payment processing (authorized)"[/b]' mod='braintreeofficial'}|braintreereplace}
            </div>
            <div>
                {{l s='[i]This order status means that the transaction is in the process of being settled. This is a transitory state.[/i]' mod='braintreeofficial'}|braintreereplace}
            </div>
        </li>
    </ul>
</div>
