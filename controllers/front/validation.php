<?php
/**
 * 2007-2020 PayPal
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
 *  @author 2007-2020 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once _PS_MODULE_DIR_ . 'braintreeofficial/controllers/front/abstract.php';

/**
 * Validate BT payment
 */
class BraintreeOfficialValidationModuleFrontController extends BraintreeOfficialAbstarctModuleFrontController
{
    /* @var $method MethodBraintreeOfficial*/
    protected $method;

    public function init()
    {
        parent::init();
        $this->setMethod(AbstractMethodBraintreeOfficial::load('BraintreeOfficial'));
        $this->values['payment_method_nonce'] = isset($this->context->cookie->payment_method_nonce) ? $this->context->cookie->payment_method_nonce : Tools::getvalue('payment_method_nonce');
        $this->values['payment_method_bt'] = isset($this->context->cookie->payment_method_bt) ? $this->context->cookie->payment_method_bt : Tools::getvalue('payment_method_bt');
        $this->values['bt_vaulting_token'] = Tools::getvalue('bt_vaulting_token');
        $this->values['pbt_vaulting_token'] = Tools::getvalue('pbt_vaulting_token');
        $this->values['save_card_in_vault'] = Tools::getvalue('save_card_in_vault');
        $this->values['save_account_in_vault'] = Tools::getvalue('save_account_in_vault');
        $this->values['cvv_field'] = Tools::getValue('btCvvField');
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
        } catch (BraintreeOfficialAddons\classes\BraintreeOfficialException $e) {
            $this->errors['error_code'] = $e->getCode();
            $this->errors['logger_msg'] = $e->getMessage();
            $this->errors['error_msg'] = $this->l('Your payment has been declined : please try again or with another payment method.');
        } catch (Exception $e) {
            $this->errors['error_code'] = $e->getCode();
            $this->errors['error_msg'] = $e->getMessage();
        } finally {
            $this->transaction_detail = $this->method->getDetailsTransaction();
        }

        if (!empty($this->errors)) {
            $params = array(
                'error_msg' => $this->errors['error_msg']
            );
            $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $params);
        }
    }

    public function setMethod(AbstractMethodBraintreeOfficial $method)
    {
        $this->method = $method;
    }

    public function displayAjaxGetOrderInformation()
    {
        $customer = new Customer($this->context->cart->id_customer);
        $address = new Address($this->context->cart->id_address_delivery);
        $country = new Country($address->id_country);
        $use3dVerification = $this->module->use3dVerification();
        $iso = '';

        if ($address->id_state) {
            $state = new State((int) $address->id_state);
            $iso = $state->iso_code;
        }

        $responseContent = array(
            'success' => true,
            'use3dVerification' => $use3dVerification,
            'orderInformation' => array(
                'amount' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
                'email' => $customer->email,
                'billingAddress' => array(
                    'givenName' => iconv("utf-8", "ascii//TRANSLIT", $customer->firstname),
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
        $this->jsonValues = $responseContent;
    }
}
