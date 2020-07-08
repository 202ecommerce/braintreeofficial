/*
 * 2007-2020 PayPal
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
 *  @author 2007-2020 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */


var MigrationAdmin = {
	// Init migration
	init() {
		$(document).on('click', '#start-migration', () => MigrationAdmin.startMigration());
		$(document).on('click', '#skip-migration', () => MigrationAdmin.skipMigration());
		$(document).on('click', '#save-account', () => MigrationAdmin.saveAccount());
	},

	// Do migration and show step 2
	startMigration() {
		$.ajax({
			url: controllerUrl,
			type: 'POST',
			dataType: 'JSON',
			data: {
				ajax: true,
				action: 'StartMigration',
			},
			beforeSend: () => {
				$('#section-one').hide();
				$('#section-two').show();
			},
			success(response) {
				if (response.status) {
					$('.migration-page').parent().html(response.content);
				}
			},
		});
	},

	// Skip migration, disable paypal module
	skipMigration() {
		$.ajax({
			url: controllerUrl,
			type: 'POST',
			dataType: 'JSON',
			data: {
				ajax: true,
				action: 'SkipMigration',
			},
			success(response) {
				if (response.status) {
					document.location = response.urlRedirect;
				}
			},
		});
	},

	// Save aacount info, disable paypal module
	saveAccount() {
		$.ajax({
			url: controllerUrl + "&" + $('#form-account').serialize(),
			type: 'POST',
			dataType: 'JSON',
			data: {
				ajax: true,
				action: 'SaveAccount',
			},
			beforeSend: () => {
				$("#save-account").button('loading');
			},
			success(response) {
				if (response.status == true) {
					$('.migration-page').parent().html(response.content);
				} else {
					const statusMigration = $('.status-migration'),
								icon = $(".status-migration-icon");

					icon.html("report_problem");
					icon.addClass('text-danger');
					statusMigration.html(response.content);
					statusMigration.addClass('text-danger');
					$("#save-account").button('reset');
				}
			},
		});
	}
};

$(document).ready(() => MigrationAdmin.init());
