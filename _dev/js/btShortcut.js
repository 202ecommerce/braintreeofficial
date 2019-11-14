/**
 * 2007-2019 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author 202-ecommerce <tech@202-ecommerce.com>
 * @copyright 202-ecommerce
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

const BtCheckout = {

    button: null,

    data: {
        amount: null,
        currency: null,
        mode: null,
        authorizationToken: null,
        controller: null
    },

    init() {
        let btnSc = $('[data-braintree-button]');

        if (btnSc == 0) {
            return;
        }

        this.button = btnSc;
        this.initData();
        this.addListeners();
        this.initPaymentBtn('checkout');
    },

    addListeners() {
        prestashop.on('updateCart', (data) => {
            BtCheckout.checkAvaibility();
            BtCheckout.updateAmount(data);
        });
    },

    updateAmount(data) {

    },

    initData() {
        if (typeof(paypal_braintree_authorization) != 'undefined') {
            this.data.authorizationToken = paypal_braintree_authorization;
        }

        if (typeof(paypal_braintree_currency) != 'undefined') {
            this.data.currency = paypal_braintree_currency;
        }

        if (typeof(paypal_braintree_mode) != 'undefined') {
            this.data.mode = paypal_braintree_mode;
        }

        if (typeof(paypal_braintree_amount) != 'undefined') {
            this.data.amount = paypal_braintree_amount;
        }

        if (typeof(paypal_braintree_contoller) != 'undefined') {
            this.data.controller = paypal_braintree_contoller;
        }
    },

    showError(msgError) {
        alert(msgError);
    },

    sendData(data) {
        let form = document.createElement('form');
        let input = document.createElement('input');

        input.name = "paymentData";
        input.value = JSON.stringify(data);

        form.method = "POST";
        form.action = BtCheckout.data.controller;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    },

    initPaymentBtn(flow) {
        if (BtCheckout.button == null) {
            return;
        }

        braintree.client.create({
            authorization: this.data.authorizationToken,
        }, (clientErr, clientInstance) => {
            // Stop if there was a problem creating the client.
            // This could happen if there is a network error or if the authorization
            // is invalid.
            if (clientErr) {
                console.error('Error creating client:', clientErr);
                return;
            }

            // Create a PayPal Checkout component.
            braintree.paypalCheckout.create({
                client: clientInstance,
            }, (paypalCheckoutErr, paypalCheckoutInstance) => {
                // Stop if there was a problem creating PayPal Checkout.
                // This could happen if there was a network error or if it's incorrectly
                // configured.
                if (paypalCheckoutErr) {
                    this.showError(paypalCheckoutErr);
                    return;
                }

                $('[data-braintree-button]').html('');
                paypal.Button.render({
                    env: BtCheckout.data.mode, // 'production' or 'sandbox'
                    style: {
                        tagline: false
                    },

                    payment() {
                        return paypalCheckoutInstance.createPayment({
                            flow,
                            amount: BtCheckout.data.amount,
                            currency: BtCheckout.data.currency,
                            billingAgreementDescription: '',
                            enableShippingAddress: true,
                            shippingAddressEditable: false,
                        });
                    },

                    onAuthorize(data, actions) {
                        return paypalCheckoutInstance.tokenizePayment(data)
                            .then((payload) => {
                                // Submit `payload.nonce` to your server.
                                console.log(payload);
                                BtCheckout.sendData(payload);
                                /*$('[data-payment-method-nonce="pbt"]').val(payload.nonce);
                                $('[data-braintree-button]').hide();
                                $('[data-bt-pp-error-msg]').hide();
                                $('[data-bt-save-account]').hide();
                                $('[data-bt-vault-info]').show().append(`${payload.details.firstName} ${payload.details.lastName} ${payload.details.email}`);*/
                            });
                    },
                    onError(err) {
                        BtCheckout.showError(err);
                    },
                }, '[data-braintree-button]').then((e) => {

                });
            });
        });
    },

    checkAvaibility() {

    }
};

$(document).ready(() => {
    BtCheckout.init();
});
