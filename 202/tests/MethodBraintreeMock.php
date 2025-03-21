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

$pathConfig = dirname(__FILE__) . '/../../../../config/config.inc.php';
$pathInit = dirname(__FILE__) . '/../../../../init.php';
if (file_exists($pathConfig)) {
    require_once $pathConfig;
}
if (file_exists($pathInit)) {
    require_once $pathInit;
}
require_once _PS_MODULE_DIR_.'braintreeofficial/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'braintreeofficial/classes/MethodBraintreeOfficial.php';

use Braintree\Gateway as Braintree_Gateway;
use PHPUnit\Framework\TestCase;

class MethodBraintreeMock extends TestCase
{
    public function getInstance($configured = true)
    {
        $methodMock = $this->getMockBuilder(\MethodBraintreeOfficial::class)
            ->setMethods(array('initConfig'))
            ->getMock();

        $methodMock->gateway = $this->getGetway($configured);

        return $methodMock;
    }

    protected function getGetway($configured)
    {
        if ($configured) {
            $params = array(
                'environment' => 'sandbox',
                'publicKey' => '4m2shvm347ws2mjq',
                'privateKey' => 'acf3866dcf658bbfea256ce689e2bbde',
                'merchantId' => 'w8rmq9dh54rk2gmw'
            );
        } else {
            $params = array(
                'environment' => 'sandbox',
                'publicKey' => '',
                'privateKey' => '',
                'merchantId' => ''
            );
        }

        $getway = new Braintree_Gateway($params);

        return $getway;
    }
}
