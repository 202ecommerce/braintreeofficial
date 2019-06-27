export const hoverConfig = (el) => {

	$('.defaultForm').removeClass('bt-settings-link-on');
	$('#subtab-AdminBraintreeCustomizeCheckout').removeClass('bt-settings-link-on bt__border-b-primary');
	$('#subtab-AdminBraintreeSetup').removeClass('bt-settings-link-on bt__border-b-primary');
	el.addClass('bt-settings-link-on');
	$('html, body').animate({
		scrollTop: el.offset().top - 200 + "px"
	}, 900);
}

export const hoverTabConfig = () => {
	if ($('#subtab-AdminBraintreeCustomizeCheckout').hasClass('current')) {
		$('#subtab-AdminBraintreeSetup').addClass('bt-settings-link-on bt__border-b-primary');
	} else if ($('#subtab-AdminBraintreeSetup').hasClass('current')) {
		$('#subtab-AdminBraintreeCustomizeCheckout').addClass('bt-settings-link-on bt__border-b-primary');
	}
	$('html, body').animate({
		scrollTop: $('#head_tabs').offset().top - 200 + "px"
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