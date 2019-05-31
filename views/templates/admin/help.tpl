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

<div class="panel help">
    <ul class="tick">
        <li class="paypal-bold li-padding">{l s='Discover module ducumentation before configuration' mod='paypal'}</li>
        <div class="btn-padding form-group"">
        <a target="_blank" href="https://addons.prestashop.com/documentation/e582dd0854d8994e815d6c0e8886e703bfdf7713" class="btn btn-default">
            {l s='Access user documentation for module configuration.' mod='paypal'}
        </a>
</div>
<li class="paypal-bold li-padding">{l s='Check requirements before installation' mod='paypal'}</li>
{l s='Are you using the required TLS version? Did you select a default country? Click on the button below and check if all requirements are completed!' mod='paypal'}
<div class="btn-padding form-group"">
<button  name="submit-ckeck_requirements"  class="btn btn-default" id="ckeck_requirements">{l s='Check requirements' mod='paypal'}</button>
<br><br>
<div class="action_response"></div>
</div>

<li class="paypal-bold li-padding">{l s='Check your transactions history log and potential errors.' mod='paypal'}</li>
<div class="btn-padding form-group"">
<a href="{$AdminPaypalProcessLogger_link|addslashes}"
   class="btn btn-default"
   target="_blank">{l s='Transaction log' mod='paypal'}</a>
</div>

<li class="paypal-bold li-padding">{l s='Do you still have any questions?' mod='paypal'}</li>
{l s='Contact us! We will be happy to help!' mod='paypal'}
<div class="btn-padding form-group"">
<a target="_blank" href="https://www.paypal.com/fr/webapps/mpp/contact-us" class="btn btn-default">
    {l s='Contact our product team for any functional questions' mod='paypal'}
</a>
</div>
<div class="btn-padding form-group">
    <a target="_blank" href="https://addons.prestashop.com/fr/contactez-nous?id_product=1748" class="btn btn-default">
        {l s='Contact our technical support' mod='paypal'}
    </a>
</div>
</ul>
</div>
