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

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCapture;
use BraintreeOfficialAddons\classes\BraintreeOfficialCapture;


class ServiceBraintreeCaptureTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var ServiceBraintreeOfficialCapture*/
    protected $service;

    protected function setUp()
    {
        $this->service = new ServiceBraintreeOfficialCapture();
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
    public function testGetByOrderId($id_order)
    {
        $return = $this->service->getByOrderId($id_order);
        $this->assertTrue($return instanceof BraintreeCapture || $return === false);
    }

    public function testGetPayPalOrderBtId()
    {
        $this->assertTrue(is_array($this->service->getPayPalOrderBtId()));
    }

    /**
     * @dataProvider providerLoadByOrderBraintreeId
     */
    public function testLoadByOrderBraintreeId($id_braintree_order)
    {
        $return = $this->service->loadByOrderBraintreeId($id_braintree_order);
        $this->assertTrue($return instanceof BraintreeCapture || $return === false);
    }

    public function providerGetByOrderId()
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

    public function providerLoadByOrderBraintreeId()
    {
        $data = $this->providerGetByOrderId();
        return $data;
    }

}
