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
require_once _PS_MODULE_DIR_.'braintreeofficial/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCustomer;
use BraintreeOfficialAddons\classes\BraintreeOfficialCustomer;


class ServiceBraintreeCustomerTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var ServiceBraintreeOfficialCustomer*/
    protected $service;

    protected function setUp()
    {
        $this->service = new ServiceBraintreeOfficialCustomer();
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
     * @dataProvider providerGetByOrderId
     */
    public function testLoadCustomerByMethod($id_customer, $sandbox)
    {
        $return = $this->service->loadCustomerByMethod($id_customer, $sandbox);
        $this->assertTrue($return instanceof BraintreeCustomer || $return === false);
    }



    public function providerGetByOrderId()
    {
        $data = array(
            array(null, null),
            array('string', false),
            array(0, 0),
            array(false, 1),
            array(99999999999, 'string'),
            array(1, true),
        );

        return $data;
    }

    public function providerLoadByOrderBraintreeId()
    {
        $data = $this->providerGetByOrderId();
        return $data;
    }
}
