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
use BraintreeTest\MethodBraintreeMock;

class MethodBraintreeTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var MethodBraintreeMock*/
    protected $methodBraintree;

    protected function setUp()
    {
        $this->methodBraintree = new MethodBraintreeMock();
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
    public function testGetLinkToTransaction($id_transaction, $sandbox)
    {
        $method = $this->methodBraintree->getInstance();
        $this->assertTrue(is_string($method->getLinkToTransaction($id_transaction, $sandbox)));
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
        $data = array(
            array('string', 'string'),
            array('string', 1),
            array('string', null),
            array('string', false),
            array(1, false),
            array(null, false),
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
