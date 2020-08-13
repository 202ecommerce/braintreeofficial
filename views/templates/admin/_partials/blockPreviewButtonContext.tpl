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
  * @copyright 202-ecommerce
  * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
  * International Registered Trademark & Property of PrestaShop SA
  *}

<div class="block-preview-button-context bt__flex">
	<div class="bt__py-2 bt__px-1">
		<div class="bt__mb-2">
			<input type="checkbox" {if isset($braintreeofficial_express_checkout_shortcut_cart) &&
				$braintreeofficial_express_checkout_shortcut_cart}checked{/if} name="braintreeofficial_express_checkout_shortcut_cart" value="1"
				   id="braintreeofficial_express_checkout_shortcut_cart">
			<label for="braintreeofficial_express_checkout_shortcut_cart" class="control-label">
				<span
					class="label-tooltip"
					data-toggle="tooltip"
					data-html="true"
					title=""
					data-original-title="<p style='text-align:left'>{l s='Activating Express Checkout Shortcut on Cart Page is recommended in specific cases only:' mod='braintreeofficial'}</p>
						<p style='text-align:left'>- {l s='Multi Products web sites' mod='braintreeofficial'}</p>"
					>
					{l s='Cart Page' mod='braintreeofficial'}
				</span>
			</label>
		</div>
		<img src="{$moduleDir|addslashes}braintreeofficial/views/img/cart_page_button.png" class="img-responsive" alt="cart_page">
	</div>

	<div class="bt__py-2 bt__px-1">
		<div class="bt__mb-2">
			<input type="checkbox" {if isset($braintreeofficial_express_checkout_shortcut) &&
				$braintreeofficial_express_checkout_shortcut}checked{/if} name="braintreeofficial_express_checkout_shortcut"
				id="braintreeofficial_express_checkout_shortcut" value="1">
			<label for="braintreeofficial_express_checkout_shortcut"  class="control-label">
				<span
					class="label-tooltip"
					data-toggle="tooltip"
					data-html="true"
					title=""
					data-original-title="<p style='text-align:left'>{l s='Activating Express Checkout Shortcut on Product Pages is recommended in specific cases only:' mod='braintreeofficial'}</p>
						<p style='text-align:left'>- {l s='Mono Product' mod='braintreeofficial'}</p>
						<p style='text-align:left'>- {l s='Digital Goods' mod='braintreeofficial'}</p>
						<p style='text-align:left'>{l s='Example:' mod='braintreeofficial'}</p>
						<p style='text-align:left'>- {l s='Parking lot rental' mod='braintreeofficial'}</p>
						<p style='text-align:left'>- {l s='Escape game reservation' mod='braintreeofficial'}</p>
						<p style='text-align:left'>- {l s='Booking services' mod='braintreeofficial'}</p>
						<p style='text-align:left'>- {l s='Single product sale' mod='braintreeofficial'}</p>"
					>
					{l s='Product Pages' mod='braintreeofficial'}
				</span>
			</label>
		</div>
		<img src="{$moduleDir|addslashes}braintreeofficial/views/img/product_page_button.png" alt="product_page_button">
	</div>
</div>

<div class="alert alert-info">
	<button type="button" class="close" data-dismiss="alert">×</button>
    {l s='Shortcut converts best when activated on both product pages & cart page' mod='braintreeofficial'}
</div>

