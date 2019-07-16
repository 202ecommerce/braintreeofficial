/**
 * 2007-2019 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

export const hoverConfig = (el) => {
	$('.defaultForm').removeClass('bt-settings-link-on');
	$('.page-head-tabs a').removeClass('bt-settings-link-on bt__border-b-primary');
	el.addClass('bt-settings-link-on');
	$('html, body').animate({
		scrollTop: el.offset().top - 200 + "px"
	}, 900);
}

export const hoverTabConfig = () => {
	let tabs = document.querySelectorAll('.page-head-tabs a'),
		currentTab = $('.page-head-tabs a.current');
	tabs.forEach( el => {
		let checkoutTab = $(el).attr('href').includes('AdminBraintreeCustomizeCheckout'),
		 	setupTab = $(el).attr('href').includes('AdminBraintreeSetup');
		if ((currentTab.attr('href').includes('AdminBraintreeCustomizeCheckout') && setupTab)
			|| (currentTab.attr('href').includes('AdminBraintreeSetup') && checkoutTab)) {
			$(el).addClass('bt-settings-link-on bt__border-b-primary');
		} 
	})

	$('html, body').animate({
		scrollTop: $('.page-head-tabs').offset().top - 200 + "px"
	}, 900);
}

export const selectOption = (select, el) => {
	if (select) {
		select.on('change', (e) => {
			let index = e.target.selectedIndex;
			if (index == 0) {
				el.show();
			} else {
				el.hide();
			}   
		})
	}
}