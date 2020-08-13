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
 * Class BraintreeOfficialVaulting.
 */
class BraintreeOfficialVaulting extends \ObjectModel
{
    /** @var string Token received from BT */
    public $token;

    /** @var int BT Customer ID */
    public $id_braintreeofficial_customer;

    /** @var string client can set card name in prestashop account */
    public $name;

    /** @var string Card or paypal account short info like last 4 numbers/exp. date */
    public $info;

    /** @var string card ou paypal, etc... */
    public $payment_tool;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'braintreeofficial_vaulting',
        'primary' => 'id_braintreeofficial_vaulting',
        'multilang' => false,
        'fields' => array(
            'token' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_braintreeofficial_customer' => array('type' => self::TYPE_INT),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'info' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'payment_tool' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );
}
