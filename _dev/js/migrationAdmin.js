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


var MigrationAdmin = {
	init() {
		$(document).on('click', '#start-migration', () => MigrationAdmin.startMigration());
		$(document).on('click', '#skip-migration', () => MigrationAdmin.skipMigration());
		$(document).on('click', '#save-account', () => MigrationAdmin.saveAccount());
	},

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
