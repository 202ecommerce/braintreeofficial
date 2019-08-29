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
require_once _PS_MODULE_DIR_.'braintree/braintree.php';

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use BraintreeTest\MethodBraintreeMockTest;

class BraintreeTest extends TestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var \Braintree*/
    protected $braintree;

    protected function setUp()
    {
        $this->braintree = new \Braintree();
        $this->moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager = $this->moduleManagerBuilder->build();
        $this->moduleNames = 'braintree';
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
        $methodBraintreeMock = new MethodBraintreeMockTest();
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
