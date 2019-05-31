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

<div>
    <ul class="list-unstyled">
        <li>
            <i class="icon-check" style="color: green"></i>
            {l s='PHP cURL extension must be enabled on your server' mod='braintree'}
        </li>

        <li>
            <i class="icon-check" style="color: green"></i>
            {l s='Your server must run PHP 5.3 or greater' mod='braintree'}
        </li>

        <li>
            <i class="icon-check" style="color: green"></i>
            {l s='OpenSSL 1.0.1 or later' mod='braintree'}
        </li>

        <li>
            <i class="icon-remove" style="color: red"></i>
            {l s='You must connect your Braintree account' mod='braintree'}
        </li>
    </ul>
</div>

<div>
    <button class="btn btn-default">{l s='Check' mod='braintree'}</button>
</div>


