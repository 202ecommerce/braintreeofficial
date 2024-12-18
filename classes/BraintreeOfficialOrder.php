<?php
/**
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
 */

namespace BraintreeOfficialAddons\classes;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class BraintreeOfficialOrder.
 */
class BraintreeOfficialOrder extends \ObjectModel
{
    /** @var int Prestashop Order generated ID */
    public $id_order;

    /** @var int Prestashop Cart generated ID */
    public $id_cart;

    /** @var string Transaction ID */
    public $id_transaction;

    /** @var string Payment ID */
    public $id_payment;

    /** @var string Transaction type returned by API */
    public $payment_method;

    /** @var string Currency iso code */
    public $currency;

    /** @var float Total paid amount by customer */
    public $total_paid;

    /** @var string Transaction status */
    public $payment_status;

    /** @var float Prestashop order total */
    public $total_prestashop;

    /** @var string BT tool (cards or paypal) */
    public $payment_tool;

    /** @var bool mode of payment (sandbox or live) */
    public $sandbox;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'braintreeofficial_order',
        'primary' => 'id_braintreeofficial_order',
        'multilang' => false,
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_transaction' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'id_payment' => ['type' => self::TYPE_STRING],
            'payment_method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'currency' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'total_paid' => ['type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2],
            'payment_status' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'total_prestashop' => ['type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2],
            'payment_tool' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'sandbox' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
    ];
}
