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

namespace BraintreeOfficialAddons\classes;

/**
 * Class BraintreeOfficialCapture.
 */
class BraintreeOfficialCapture extends \ObjectModel
{
    /** @var string Capture ID */
    public $id_capture;

    /** @var integer BraintreeOfficialOrder ID */
    public $id_braintreeofficial_order;

    /** @var float Captured amount */
    public $capture_amount;

    /** @var string Transaction status */
    public $result;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'braintreeofficial_capture',
        'primary' => 'id_braintreeofficial_capture',
        'multilang' => false,
        'fields' => array(
            'id_capture' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_braintreeofficial_order' => array('type' => self::TYPE_INT),
            'capture_amount' => array('type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2),
            'result' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );
}
