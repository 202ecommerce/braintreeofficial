<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommence
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommence is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommence
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommence est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 */

namespace BraintreeTest;

$pathConfig = dirname(__FILE__) . '/../../../../config/config.inc.php';
$pathInit = dirname(__FILE__) . '/../../../../init.php';
if (file_exists($pathConfig)) {
    require_once $pathConfig;
}
if (file_exists($pathInit)) {
    require_once $pathInit;
}
require_once _PS_MODULE_DIR_.'braintree/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use BraintreeAddons\services\ServiceBraintreeVaulting;
use BraintreeAddons\classes\BraintreeVaulting;


class ServiceBraintreeVaultingTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var ServiceBraintreeVaulting*/
    protected $service;

    protected function setUp()
    {
        $this->service = new ServiceBraintreeVaulting();
        $this->moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager = $this->moduleManagerBuilder->build();
        $this->moduleNames = 'braintree';
    }

    public function testInstall()
    {
        $employees = \Employee::getEmployeesByProfile(_PS_ADMIN_PROFILE_);
        $contextTest = \Context::getContext();
        $contextTest->employee = new \Employee((int)$employees[0]['id_employee']);
        $contextTest->cookie->update();
        \Context::setInstanceForTesting($contextTest);
        $this->assertTrue((bool)$this->moduleManager->install($this->moduleNames), "Could not install $this->moduleNames");
    }

    /**
     * @dataProvider providerGetCustomerGroupedMethods
     */
    public function testGetCustomerGroupedMethods($customer)
    {
        $return = $this->service->getCustomerGroupedMethods($customer);
        $this->assertTrue(is_array($return));
    }

    /**
     * @dataProvider providerGetCustomerMethods
     */
    public function testGetCustomerMethods($customer, $method)
    {
        $this->assertTrue(is_array($this->service->getCustomerMethods($customer, $method)));

    }

    /**
     * @dataProvider providerVaultingExist
     */
    public function testVaultingExist($token, $customer)
    {
        $this->assertTrue(is_bool($this->service->vaultingExist($token, $customer)));
    }

    public function providerGetCustomerGroupedMethods()
    {
        $data = array(
            array(null),
            array('string'),
            array(0),
            array(false),
            array(99999999999),
            array(1),
        );

        return $data;
    }

    public function providerGetCustomerMethods()
    {
        $data = array(
            array(null, null),
            array('string', true),
            array(0, false),
            array(false, 'string'),
            array(99999999999, 0),
            array(1, 1),
        );

        return $data;
    }

    public function providerVaultingExist()
    {
        $data = $this->providerGetCustomerMethods();
        return $data;
    }
}
