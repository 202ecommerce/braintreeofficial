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

namespace BraintreeAddons\services;

use BraintreeAddons\classes\BraintreeVaulting;

class ServiceBraintreeVaulting
{
    /**
     * Checking if vault was created already for this card/pp account
     * @return boolean
     */
    public function vaultingExist($token, $customer)
    {
        $collection = new \PrestaShopCollection(BraintreeVaulting::class);
        $collection->where('token', '=', pSQL($token));
        $collection->where('id_braintree_customer', '=', (int)$customer);
        $braintreeVaulting = $collection->getFirst();
        return \Validate::isLoadedObject($braintreeVaulting) ? true : false;
    }
    /**
     * Get all vaulted methods (cards, accounts) for this customer
     * @param integer $customer PrestaShop Customer ID
     * @param string $method payment tool (card or paypal account)
     * @return array BraintreeVaulting
     */
    public function getCustomerMethods($customer, $method)
    {
        $db = \Db::getInstance();
        $query = new \DbQuery();
        $query->select('*');
        $query->from('braintree_vaulting', 'bv');
        $query->leftJoin('braintree_customer', 'bc', 'bv.id_braintree_customer = bc.id_braintree_customer');
        $query->where('bc.id_customer = '.(int)$customer);
        $query->where('bv.payment_tool = "'.pSQL($method).'"');
        $query->where('bc.sandbox = ' . (int)\Configuration::get('BRAINTREE_SANDBOX'));
        $result = $db->executeS($query);
        return $result;
    }

    /**
     * Get vaulted methods grouped by tools (card or paypal account)
     * @param integer $customer PrestaShop Customer ID
     * @return array BraintreeVaulting
     */
    public function getCustomerGroupedMethods($customer)
    {
        $db = \Db::getInstance();
        $methods = array();
        $query = new \DbQuery();
        $query->select('*');
        $query->from('braintree_vaulting', 'bv');
        $query->leftJoin('braintree_customer', 'bc', 'bv.id_braintree_customer = bc.id_braintree_customer');
        $query->where('bc.id_customer = '.(int)$customer);
        $results = $db->query($query);
        while ($result = $db->nextRow($results)) {
            $methods[$result['payment_tool']][] = $result;
        }
        return $methods;
    }

}
