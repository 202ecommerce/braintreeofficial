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

        $getway = new \Braintree_Gateway($params);

        return $getway;
    }
}
