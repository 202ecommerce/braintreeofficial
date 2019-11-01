<?php
/**
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
 *  @author 202-ecommerce <tech@202-ecommerce.com>
 *  @copyright Copyright (c) 202-ecommerce
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use BraintreeofficialPPBTlib\Install\ModuleInstaller;
use BraintreeOfficialAddons\classes\BraintreeOfficialCustomer;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCustomer;

/**
 * @param $module BraintreeOfficial
 * @return bool
 */
function upgrade_module_1_2_0($module)
{
    $result = true;
    $result &= $module->renameOrderState();

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

    return $result;
}
