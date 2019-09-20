---
name: Braintree vaulting
category: Entities
---

## Definition
Stock information about vaulted credit cards 
or paypal accounts for each BT customer.  

* Entity name: BraintreeVaulting
* Table: braintree_vaulting
* Fields:

|Name|Type|Description|Validateur|
|------|------|------|------|
|id_braintree_vaulting|integer|ID of the record|isUnsignedId|
|token|string|unique token for each vaulted method (creditCard/paypal))||
|id_braintree_customer|integer|key from braintree_customer|isUnsignedId|
|name|string|Custom defined name of payment source||
|info|string|Card or account info||
|payment_tool|string|Method alias||
|date_add|datetime|Date of the creation||
|date_upd|datetime|Date of the update||


