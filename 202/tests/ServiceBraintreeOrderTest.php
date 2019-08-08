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
use BraintreeAddons\services\ServiceBraintreeOrder;
use BraintreeAddons\classes\BraintreeOrder;


class ServiceBraintreeOrderTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var ServiceBraintreeOrder*/
    protected $service;

    protected function setUp():void
    {
        $this->service = new ServiceBraintreeOrder();
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

    public function testGetBraintreeOrdersForValidation()
    {
        $return = $this->service->getBraintreeOrdersForValidation();
        $this->assertIsArray($return);
    }

    public function testGetCountOrders()
    {
        $this->assertIsInt($this->service->getCountOrders());
    }

    /**
     * @dataProvider providerLoadByOrderId
     */
    public function testLoadByOrderId($id_order)
    {
        $return = $this->service->loadByOrderId($id_order);
        $this->assertTrue($return instanceof BraintreeOrder || $return ===false);
    }

    /**
     * @dataProvider providerLoadByTransactionId
     */
    public function testLoadByTransactionId($id_transaction)
    {
        $return = $this->service->loadByTransactionId($id_transaction);
        $this->assertTrue($return instanceof BraintreeOrder || $return ===false);
    }

    public function providerLoadByOrderId()
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

    public function providerLoadByTransactionId()
    {
        $data = $this->providerLoadByOrderId();
        return $data;
    }
}
