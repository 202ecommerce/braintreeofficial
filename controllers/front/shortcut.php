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
 *  @author 202-ecommerce <tech@202-ecommerce.com>
 *  @copyright Copyright (c) 202-ecommerce
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once _PS_MODULE_DIR_ . 'braintreeofficial/controllers/front/abstract.php';

/**
 * Validate BT payment
 */
class BraintreeOfficialShortcutModuleFrontController extends BraintreeOfficialAbstarctModuleFrontController
{
    protected $paymentData;

    protected $checkoutInfo;

    public function init()
    {
        parent::init();
        $this->setPaymentData(Tools::jsonDecode(Tools::getValue('paymentData')));
        $this->setCheckoutInfo(Tools::getAllValues());
    }

    public function postProcess()
    {
        try {
            $this->redirectUrl = $this->context->link->getPageLink('order', null, null, array('step'=>2));

            if ($this->getCheckoutInfo()['page'] == BRAINTREE_PRODUCT_PAGE) {
                $this->updateCart();
            }

            $this->prepareOrder();
        } catch (Exception $e) {
            $this->errors['error_code'] = $e->getCode();
            $this->errors['error_msg'] = $e->getMessage();
        }

        if (!empty($this->errors)) {
            $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $this->errors);
        }

    }

    public function prepareOrder()
    {
        $customer = $this->getCustomer();

        $id_cart = $this->context->cart->id; // save id cart
        $this->context->updateCustomer($customer);
        $this->context->cart = new Cart($id_cart); // Reload cart
        $this->context->cart->id_customer = $customer->id;
        $this->context->cart->update();

        Hook::exec('actionAuthentication', array('customer' => $this->context->customer));
        // Login information have changed, so we check if the cart rules still apply
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        $this->context->cookie->__set('payment_method_nonce', $this->getPaymentData()->nonce);
        $this->context->cookie->__set('brainteeofficial_payer_email', $this->getPaymentData()->details->email);
        $this->context->cookie->__set('payment_method_bt', BRAINTREE_PAYPAL_PAYMENT);

        $address = $this->getAddress($customer);

        if (Validate::isLoadedObject($address) == false) {
            return false;
        }

        $this->context->cart->id_address_delivery = $address->id;
        $this->context->cart->id_address_invoice = $address->id;;

        $addressValidator = new AddressValidator();
        $invalidAddressIds = $addressValidator->validateCartAddresses($this->context->cart);

        if (empty($invalidAddressIds) == false) {
            $vars = array(
                'id_address' => $address->id,
                'editAddress' => 'delivery'
            );
            session_start();
            $_SESSION['notifications'] = Tools::jsonEncode(array('error' => $this->l('Your address is incomplete, please update it.')));
            $url = Context::getContext()->link->getPageLink('order', null, null, $vars);
            $this->redirectUrl = $url;
            return false;
        }

        return true;
    }

    protected function getCustomer()
    {
        if ($this->context->customer->isLogged()) {
            return $this->context->customer;
        }

        if ($idCustomer = Customer::customerExists($this->getPaymentData()->details->email)) {
            return new Customer($idCustomer);
        }

        $customer = new Customer();
        $customer->firstname = $this->getPaymentData()->details->firstName;
        $customer->lastname = $this->getPaymentData()->details->lastName;
        $customer->passwd = Tools::encrypt(Tools::passwdGen());
        $customer->save();

        return $customer;
    }

    /**
     * @param $customer Customer object
     * @return Address
     * */
    protected function getAddress(Customer $customer)
    {
        $addresses = $this->context->customer->getAddresses($this->context->language->id);
        $paypalAddress = $this->getPaymentData()->details->shippingAddress;
        $count = 1;
        $id_state = $this->module->getIdStateByPaypalCode($paypalAddress->state, $paypalAddress->countryCode);

        foreach ($addresses as $address) {
            if ($address['firstname'].' '.$address['lastname'] == $paypalAddress->recipientName
                && $address['address1'] == $paypalAddress->line1
                && (empty($paypalAddress->line2) || $address['address2'] == $paypalAddress->line2)
                && $address['id_country'] == Country::getByIso($paypalAddress->countryCode)
                && $address['city'] == $paypalAddress->city
                && (empty($paypalAddress->state) || $address['id_state'] == $id_state)
                && $address['postcode'] == $paypalAddress->postalCode
            ) {
                return new Address((int)$address['id_address']);
            } else {
                if ((strrpos($address['alias'], 'Paypal_Address')) !== false) {
                    $count = (int)(Tools::substr($address['alias'], -1)) + 1;
                }
            }
        }

        $orderAddress = new Address();
        $nameArray = explode(" ", $paypalAddress->recipientName);
        $orderAddress->firstname = $nameArray[0];
        $orderAddress->lastname = isset($nameArray[1]) ? $nameArray[1] : '';
        $orderAddress->address1 = $paypalAddress->line1;

        if (isset($paypalAddress->line2)) {
            $orderAddress->address2 = $paypalAddress->line2;
        }

        $orderAddress->id_country = Country::getByIso($paypalAddress->countryCode);
        $orderAddress->city = $paypalAddress->city;

        if ($id_state) {
            $orderAddress->id_state = $id_state;
        }

        $orderAddress->postcode = $paypalAddress->postalCode;
        $orderAddress->id_customer = $customer->id;
        $orderAddress->alias = 'Paypal_Address '.($count);
        $validationMessage = $orderAddress->validateFields(false, true);

        if (Country::containsStates($orderAddress->id_country) && $orderAddress->id_state == false) {
            $validationMessage = $this->l('State is required in order to process payment. Please fill in state field.');
        }

        $country = new Country($orderAddress->id_country);

        if ($country->active == false) {
            $validationMessage = $this->l('Country is not active');
        }

        if (is_string($validationMessage)) {
            $vars = array(
                'newAddress' => 'delivery',
                'address1' => $orderAddress->address1,
                'firstname' => $orderAddress->firstname,
                'lastname' => $orderAddress->lastname,
                'postcode' => $orderAddress->postcode,
                'id_country' => $orderAddress->id_country,
                'city' => $orderAddress->city,
                'phone' => $orderAddress->phone,
                'address2' => $orderAddress->address2,
                'id_state' => $orderAddress->id_state
            );
            session_start();
            $_SESSION['notifications'] = Tools::jsonEncode(array('error' => $validationMessage));
            $url = Context::getContext()->link->getPageLink('order', null, null, $vars);
            $this->redirectUrl = $url;
            return false;
        }

        $orderAddress->save();
        return $orderAddress;
    }

    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;
    }

    public function getPaymentData()
    {
        return $this->paymentData;
    }

    public function displayAjaxGetCartAmount()
    {
        $response = array(
            'success' => true,
            'amount' => $this->context->cart->getOrderTotal(true, Cart::BOTH)
        );
        $this->jsonValues = $response;
    }

    public function displayAjaxGetProductAmount()
    {
        $product = new Product((int)Tools::getValue('idProduct'));

        if (Validate::isLoadedObject($product) == false) {
            $response = array(
                'success' => false
            );
            return $this->jsonValues = $response;
        }

        $idProductAttribute = (int)Tools::getValue('idProductAttribute');
        $quantity = (int)Tools::getValue('quantity');

        // we don't pass quantity because in version Prestashop 1.7.6 this method does not take in account quantity
        $amount = $product->getPrice(
            true,
            $idProductAttribute ? $idProductAttribute : 0,
            6,
            null,
            false,
            true
        );

        $available = $this->isProductAvailable($product, $idProductAttribute, $quantity);

        $response = array(
            'success' => true,
            'amount' => Tools::ps_round($amount, _PS_PRICE_DISPLAY_PRECISION_) * $quantity,
            'available' => $available
        );

        $this->jsonValues = $response;
    }

    public function displayAjaxCheckProductAvailability()
    {
        $product = new Product((int)Tools::getValue('idProduct'));
        $idProductAttribute = (int)Tools::getValue('idProductAttribute');
        $quantity = (int)Tools::getValue('quantity');

        if (Validate::isLoadedObject($product) == false) {
            $response = array(
                'success' => false
            );
            return $this->jsonValues = $response;
        }

        $available = $this->isProductAvailable($product, $idProductAttribute, $quantity);

        $response = array(
            'success' => true,
            'available' => $available
        );

        $this->jsonValues = $response;
    }

    public function isProductAvailable($product, $idProductAttribute, $quantity)
    {
        $product->id_product_attribute = $idProductAttribute;
        if ($product->checkQty($quantity)) {
            return true;
        } else {
            return false;
        }
    }

    public function setCheckoutInfo($data)
    {
        $vars = array(
            'idProduct' => (int)$data['idProduct'],
            'idProductAttribute' => (int)$data['idProductAttribute'],
            'page' => (int)$data['page'],
            'quantity' => (int)$data['quantity']
        );

        $this->checkoutInfo = $vars;
    }

    public function getCheckoutInfo()
    {
        return $this->checkoutInfo;
    }

    protected function updateCart()
    {
        if (empty($this->context->cart->id)) {
            $this->context->cart->add();
            $this->context->cookie->id_cart = $this->context->cart->id;
            $this->context->cookie->write();
        } else {
            // delete all product in cart
            $products = $this->context->cart->getProducts();
            foreach ($products as $product) {
                $this->context->cart->deleteProduct($product['id_product'], $product['id_product_attribute'], $product['id_customization'], $product['id_address_delivery']);
            }
        }

        $this->context->cart->updateQty($this->getCheckoutInfo()['quantity'], $this->getCheckoutInfo()['idProduct'], $this->getCheckoutInfo()['idProductAttribute']);
    }
}
