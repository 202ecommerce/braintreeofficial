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
<div class="panel">
	<div>
		<p>
      {{l s='If you encounter rounding issues with your orders, please change PrestaShop round mode in: [a @href1@]Preferences > General[/a] then change for:' mod='braintreeofficial'}|braintreereplace:['@href1@' => {{$link->getAdminLink('AdminPreferences', true)}}, '@target@' => {'target="blank"'}]}
		</p>
		<ul>
			<li class="h4">{l s='Round mode: "Round up away from zero, when it is half way there (recommended) "' mod='braintreeofficial'}</li>
			<li class="h4">{l s='Round type: "Round on each item"' mod='braintreeofficial'}</li>
		</ul>
	</div>
</div>
