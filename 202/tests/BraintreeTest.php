<?php
/**
 * since 2007 PayPal
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
 *  @author since 2007 PayPal
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
require_once _PS_MODULE_DIR_.'braintreeofficial/braintreeofficial.php';
require_once dirname(__FILE__) . '/MethodBraintreeMock.php';

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class BraintreeTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var \Braintreeofficial*/
    protected $braintree;

    protected function setUp()
    {
        $this->braintree = new \Braintreeofficial();
        $this->moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager = $this->moduleManagerBuilder->build();
        $this->moduleNames = 'braintreeofficial';
        $contextTest = \Context::getContext();
        $contextTest->customer = new \Customer(1);
        $contextTest->currency = new \Currency(1);
        \Context::setInstanceForTesting($contextTest);
    }

    public function testInstall()
    {
        $employees = \Employee::getEmployeesByProfile(_PS_ADMIN_PROFILE_);
        $id_employee = (int)$employees[0]['id_employee'] ? (int)$employees[0]['id_employee'] : 1;
        $contextTest = \Context::getContext();
        $contextTest->employee = new \Employee($id_employee);
        $contextTest->cookie->update();
        \Context::setInstanceForTesting($contextTest);
        $this->assertTrue((bool)$this->moduleManager->install($this->moduleNames), "Could not install $this->moduleNames");
    }

    /**
     * @dataProvider providerDisplayInformation
     */
    public function testDisplayInformation($message)
    {
        $return = $this->braintree->displayInformation($message);
        $this->assertTrue(is_string($return));
    }

    /**
     * @dataProvider providerGenerateFormBT
     */
    public function testGenerateFormBT($method)
    {
        $this->braintree->setMethodBraitree($method);
        $return = $this->braintree->generateFormBT();
        $this->assertTrue(is_string($return));
    }

    /**
     * @dataProvider providerGenerateFormBT
     */
    public function testGenerateFormPB($method)
    {
        $this->braintree->setMethodBraitree($method);
        $return = $this->braintree->generateFormPB();
        $this->assertTrue(is_string($return));
    }


    public function testGetCurrentModePaymentCurrency()
    {
        $return = $this->braintree->getCurrentModePaymentCurrency();
        $this->assertTrue(is_int($return) || is_null($return));
    }

    /**
     * @dataProvider providerGetNameMerchantAccountForCurrency
     */
    public function testGetNameMerchantAccountForCurrency($currency, $mode)
    {
        $return = $this->braintree->getNameMerchantAccountForCurrency($currency, $mode);
        $this->assertTrue(is_string($return));
    }

    public function testGetPaymentCurrencyIso()
    {
        $iso = $this->braintree->getPaymentCurrencyIso();
        $this->assertTrue(\Currency::getIdByIsoCode($iso) > 0);
    }

    /**
     * @dataProvider providerHookPaymentOptions
     */
    public function testHookPaymentOptions($method)
    {
        $this->braintree->setMethodBraitree($method);
        $return = $this->braintree->hookPaymentOptions(array());
        $this->assertTrue(is_array($return));

    }

    public function testIsSslActive()
    {
        $this->assertTrue(is_bool($this->braintree->isSslActive()));
    }

    public function testMerchantAccountForCurrencyConfigured()
    {
        $this->assertTrue(is_bool($this->braintree->merchantAccountForCurrencyConfigured()));
    }

    public function testNeedConvert()
    {
        $return = $this->braintree->needConvert();
        $this->assertTrue($return === false || is_int($return));
    }

    public function providerDisplayInformation()
    {
        $data = array(
            array(''),
            array('test message'),
            array(1),
            array(null),
            array(false)
        );

        return $data;
    }

    public function providerGenerateFormBT()
    {
        $methodBraintreeMock = new \MethodBraintreeMock();
        $data = array(
            array($methodBraintreeMock->getInstance(true)),
            array($methodBraintreeMock->getInstance(false))
        );

        return $data;
    }

    public function providerGetNameMerchantAccountForCurrency()
    {
        $data = array(
            array('', 1),
            array('iso', null),
            array('eur', 0),
            array(null, ''),
            array(false, false)
        );

        return $data;
    }

    public function providerHookPaymentOptions()
    {
        $data = $this->providerGenerateFormBT();
        return $data;
    }
}
