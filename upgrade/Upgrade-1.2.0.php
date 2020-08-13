<?php
/**
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

if (!defined('_PS_VERSION_')) {
    exit;
}

use BraintreeofficialPPBTlib\Install\ModuleInstaller;

/**
 * @param $module BraintreeOfficial
 * @return bool
 */
function upgrade_module_1_2_0($module)
{
    $result = true;
    $shops = Shop::getShops();
    $installer = new ModuleInstaller($module);
    $result &= $installer->registerHooks();
    $result &= $module->renameOrderState();
    $configs = array(
        'BRAINTREEOFFICIAL_CUSTOMIZE_ORDER_STATUS' => 0,
        'BRAINTREEOFFICIAL_OS_REFUNDED' => (int)Configuration::get('PS_OS_REFUND'),
        'BRAINTREEOFFICIAL_OS_CANCELED' => (int)Configuration::get('PS_OS_CANCELED'),
        'BRAINTREEOFFICIAL_OS_ACCEPTED' => (int)Configuration::get('PS_OS_PAYMENT'),
        'BRAINTREEOFFICIAL_OS_CAPTURE_CANCELED' => (int)Configuration::get('PS_OS_CANCELED'),
        'BRAINTREEOFFICIAL_OS_ACCEPTED_TWO' => (int)Configuration::get('PS_OS_PAYMENT'),
        'BRAINTREEOFFICIAL_OS_PENDING' => (int)Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING'),
        'BRAINTREEOFFICIAL_OS_PROCESSING' => (int)Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION'),
    );

    if ($result) {
        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                $result &= Configuration::updateValue('BRAINTREEOFFICIAL_SHOW_MESSAGE_ABOUT_STATE_NAME', 1, false, null, (int)$shop['id_shop']);
            }
        } else {
            $result &= Configuration::updateValue('BRAINTREEOFFICIAL_SHOW_MESSAGE_ABOUT_STATE_NAME', 1);
        }
    }

    foreach ($configs as $config => $value) {
        if (Shop::isFeatureActive()) {
            foreach ($shops as $shop) {
                $result &= Configuration::updateValue($config, $value, false, null, (int)$shop['id_shop']);
            }
        } else {
            $result &= Configuration::updateValue($config, $value);
        }
    }

    return $result;
}
