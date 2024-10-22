---
name: Braintree customer
category: Entities
---

## Definition

* Entity name: BraintreeOfficialCustomer
* Table: braintreeofficial_customer
* Fields:

|Name|Type|Description|Validateur|
|------|------|------|------|
|id_braintreeofficial_customer|integer|ID of the record|isUnsignedId|
|id_customer|integer|PS customer ID|isUnsignedId|
|reference|string|Unique customer reference in Braintree|
|method|string|Method alias|isBool|
|profile_key|string|Profile key|isString|
|sandbox|boolean|Sandbox or live|Added in v. 4.5|
|date_add|datetime|Date of the creation||
|date_upd|datetime|Date of the update||

 
