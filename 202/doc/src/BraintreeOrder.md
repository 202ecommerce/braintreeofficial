---
name: Braintree order
category: Entities
---

## Definition
Entity for orders payment related information.

* Entity name: BraintreeOrder
* Table: braintree_order
* Fields:

|Name|Type|Description|Validateur|
|------|------|------|------|
|id_braintree_order|integer|ID of the record|isUnsignedId|
|id_order|integer|ID of PS order|isUnsignedId|
|id_cart|integer|ID of PS cart|isUnsignedId|
|id_transaction|string|The payment-related transaction id|isString|
|id_payment|string|ID of payment| |
|payment_method|string|Transaction type returned by API||
|currency|string|Currency iso code|
|total_paid|float|Amount really paid by customer||
|payment_status|string|Status of payment||
|total_prestashop|float|Total amount calculating by PS||
|method|string|Method alias||
|payment_tool|string|BT tool (cards or paypal)||
|sandbox|boolean|Sandbox or live||
|date_add|datetime|Date of the creation||
|date_upd|datetime|Date of the update||






