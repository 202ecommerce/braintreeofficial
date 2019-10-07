---
name: Validation
category: Front Controllers
---

## Definition

BraintreeOfficialValidationModuleFrontController

This controller is used like handler. Braintree does the redirection to this controller when
the customer confirms the payment.

####  init()
This method call the `parent::init()` and set the variables that are necessary for correct 
work of the controller

#### postProcess()
This method call the `MethodEC::validation` method and handle the errors

#### displayAjaxGetOrderInformation()
This method generate data for 3DS verification



