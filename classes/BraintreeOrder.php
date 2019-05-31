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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class BraintreeOrder.
 */
class BraintreeOrder extends ObjectModel
{
    /** @var integer Prestashop Order generated ID */
    public $id_order;

    /** @var integer Prestashop Cart generated ID */
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
    public static $definition = array(
        'table' => 'braintree_order',
        'primary' => 'id_braintree_order',
        'multilang' => false,
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_transaction' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_payment' => array('type' => self::TYPE_STRING),
            'payment_method' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'currency' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'total_paid' => array('type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2),
            'payment_status' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'total_prestashop' => array('type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2),
            'payment_tool' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'sandbox' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );

}
