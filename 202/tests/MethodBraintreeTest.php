<?php
/**
 * 2007-2021 PayPal
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
 *  @author 2007-2021 PayPal
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
require_once dirname(__FILE__) . '/MethodBraintreeMock.php';

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use BraintreeOfficialAddons\classes\BraintreeOfficialLog;

class MethodBraintreeTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var \MethodBraintreeMock*/
    protected $methodBraintree;

    protected function setUp()
    {
        $this->methodBraintree = new \MethodBraintreeMock();
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
     * @dataProvider providerFormatPrice
     */
    public function testFormatPrice($price)
    {
        $methodBraintree = $this->methodBraintree->getInstance();
        $return = $methodBraintree->formatPrice($price);
        $this->assertTrue(is_float($return));
    }

    public function testGetAllCurrency()
    {
        $methodConfiguredModes = array(true, false);
        foreach ($methodConfiguredModes as $methodConfiguredMode) {
            $method = $this->methodBraintree->getInstance($methodConfiguredMode);
            $this->assertTrue(is_array($method->getAllCurrency()));
        }
    }

    /**
     * @dataProvider providerGetLinkToTransaction
     */
    public function testGetLinkToTransaction($log)
    {
        $method = $this->methodBraintree->getInstance();
        $this->assertTrue(is_string($method->getLinkToTransaction($log)));
    }

    public function testGetOrderId()
    {
        $method = $this->methodBraintree->getInstance();
        $cart = $this->getMockBuilder(\Cart::class)
            ->getMock();

        $cart->secure_key = 'string';
        $cart->id = 1;

        $this->assertTrue(is_string($method->getOrderId($cart)));
    }

    public function testInitSuccess()
    {
        $method = $this->methodBraintree->getInstance();
        $this->assertTrue(is_string($method->init()));
    }

    public function testInitFailed()
    {
        $method = $this->methodBraintree->getInstance(false);
        $return = $method->init();

        $this->assertArrayHasKey('error_code', $return);
        $this->assertArrayHasKey('error_msg', $return);
    }

    /**
     * @dataProvider providerIsConfigured
     */
    public function testIsConfigured($method, $result)
    {
        $this->assertEquals($result, $method->isConfigured());
    }

    public function providerFormatPrice()
    {
        $data = array(
            array(95.0),
            array(0),
            array(3),
            array(null),
        );

        return $data;
    }

    public function providerGetLinkToTransaction()
    {
        $logSandbox = new BraintreeOfficialLog();
        $logSandbox->sandbox = true;
        $logSandbox->id_transaction = 'transactionRef';

        $logLive = new BraintreeOfficialLog();
        $logLive->sandbox = false;
        $logLive->id_transaction = 'transactionRef';

        $data = array(
            array($logSandbox),
            array($logLive)
        );

        return $data;
    }

    public function providerIsConfigured()
    {
        $this->setUp();
        $data = array(
            array($this->methodBraintree->getInstance(), true),
            array($this->methodBraintree->getInstance(false), false)
        );

        return $data;
    }
}
