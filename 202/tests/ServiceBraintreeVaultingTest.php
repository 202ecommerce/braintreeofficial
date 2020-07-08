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

namespace BraintreeTest;

$pathConfig = dirname(__FILE__) . '/../../../../config/config.inc.php';
$pathInit = dirname(__FILE__) . '/../../../../init.php';
if (file_exists($pathConfig)) {
    require_once $pathConfig;
}
if (file_exists($pathInit)) {
    require_once $pathInit;
}
require_once _PS_MODULE_DIR_.'braintreeofficial/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialVaulting;
use BraintreeOfficialAddons\classes\BraintreeOfficialVaulting;


class ServiceBraintreeVaultingTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var ServiceBraintreeOfficialVaulting*/
    protected $service;

    protected function setUp()
    {
        $this->service = new ServiceBraintreeOfficialVaulting();
        $this->moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager = $this->moduleManagerBuilder->build();
        $this->moduleNames = 'braintreeofficial';
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
