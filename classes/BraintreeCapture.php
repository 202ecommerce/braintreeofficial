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
namespace BraintreeAddons\classes;
/**
 * Class BraintreeCapture.
 */
class BraintreeCapture extends \ObjectModel
{
    /** @var string Capture ID */
    public $id_capture;

    /** @var integer BraintreeOrder ID */
    public $id_braintree_order;

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
        'table' => 'braintree_capture',
        'primary' => 'id_braintree_capture',
        'multilang' => false,
        'fields' => array(
            'id_capture' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_braintree_order' => array('type' => self::TYPE_INT),
            'capture_amount' => array('type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2),
            'result' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );
}
