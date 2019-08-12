<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use BraintreeAddons\classes\AbstractMethodBraintree;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once _PS_MODULE_DIR_ . 'braintree/controllers/front/abstract.php';

/**
 * Validate BT payment
 */
class BraintreeValidationModuleFrontController extends BraintreeAbstarctModuleFrontController
{
    /* @var $method MethodBraintree*/
    protected $method;

    public function init()
    {
        parent::init();
        $this->setMethod(AbstractMethodBraintree::load('Braintree'));
        $this->values['payment_method_nonce'] = Tools::getvalue('payment_method_nonce');
        $this->values['payment_method_bt'] = Tools::getvalue('payment_method_bt');
        $this->values['bt_vaulting_token'] = Tools::getvalue('bt_vaulting_token');
        $this->values['pbt_vaulting_token'] = Tools::getvalue('pbt_vaulting_token');
        $this->values['save_card_in_vault'] = Tools::getvalue('save_card_in_vault');
        $this->values['save_account_in_vault'] = Tools::getvalue('save_account_in_vault');
    }

    public function postProcess()
    {
        try {
            $this->method->setParameters($this->values);
            $this->method->validation();
            $cart = Context::getContext()->cart;
            $customer = new Customer($cart->id_customer);
            $module = Module::getInstanceByName($this->name);
            $this->redirectUrl = 'index.php?controller=order-confirmation&id_cart=' . $cart->id .'&id_module=' . $module->id .'&key='.$customer->secure_key;
        } catch (BraintreeAddons\classes\BraintreeException $e) {
            $this->errors['error_code'] = $e->getCode();
            $this->errors['error_msg'] = $e->getMessage();
            $this->errors['msg_long'] = $e->getMessageLong();
        } catch (Exception $e) {
            $this->errors['error_code'] = $e->getCode();
            $this->errors['error_msg'] = $e->getMessage();
        } finally {
            $this->transaction_detail = $this->method->getDetailsTransaction();
        }

        if (!empty($this->errors)) {
            $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $this->errors);
        }
    }

    public function setMethod(AbstractMethodBraintree $method)
    {
        $this->method = $method;
    }

    public function displayAjaxGetOrderInformation()
    {
        $customer = new Customer($this->context->cart->id_customer);
        $address = new Address($this->context->cart->id_address_delivery);
        $country = new Country($address->id_country);
        $iso = '';

        if ($address->id_state) {
            $state = new State((int) $address->id_state);
            $iso = $state->iso_code;
        }

        $responseContent = array(
            'success' => true,
            'orderInformation' => array(
                'amount' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
                'email' => $customer->email,
                'billingAddress' => array(
                    'givenName' => $customer->firstname,
                    'surneme' => $customer->lastname,
                    'phoneNumber' => $address->phone,
                    'streetAddress' => $address->address1,
                    'locality' => $address->city,
                    'countryCodeAlpha2' => $country->iso_code,
                    'region' => $iso,
                    'postalCode' => $address->postcode
                ),
                'additionalInformation' => array(
                    'shippingGivenName' => $address->firstname,
                    'shippingSurname' => $address->lastname,
                    'shippingPhone' => $address->phone,
                    'shippingAddress' => array(
                        'streetAddress' => $address->address1,
                        'locality' => $address->city,
                        'countryCodeAlpha2' => $country->iso_code,
                        'region' => $iso,
                        'postalCode' => $address->postcode
                    )
                )
            )
        );
        $response = new JsonResponse($responseContent);
        $response->send();
    }
}
