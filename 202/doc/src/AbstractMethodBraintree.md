---
name: Abstract method
category: Classes
---

## Definition
The AbstractMethodBraintree extends the abstract class AbstractMethod from PPBTlib.

#### load()

Static function.
Required parameter  : $method like BT, EC, PPP.
Return method Class.

#### getPaymentMethod()

Protected.
Get payment method name depending on sandbox or live env.

#### init()

Abstract function. Must be defined in each children class. 
Initialize payment method.

#### validation()

Abstract function. Must be defined in each children class. 
Validate the transaction. Create PrestaShop order.

#### confirmCapture , refund, void

Abstract functions. 
Required parameter  : $orderBraintree - BraintreeOrder object.
 
#### partialRefund()

Abstract functions. 
Required parameter  : $params - hookActionOrderSlipAdd parameters.

#### getConfig()

Generate Helper Forms for specific method.

#### setConfig()

Save method configurations.

 
