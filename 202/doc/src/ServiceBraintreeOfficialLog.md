---
name: Braintree Log Service
category: Services
---


## Definition

This class contains functions for work with BraintreeOfficialLog objects


## Methods


- **doMigration()**
    
    - **Return Values**
    
      `void` 
      
- **getCartBtId()**
    
    - **Return Values**
    
      `array` returns the array of the cart ids that are correspond 
      to paypal order entities created by braintree method (method is used while migrate data)

- **getLinkToTransaction($log)**

    - **Parametres**
      
      log: `object` BraintreeOfficialLog
    
    - **Return Values**
    
      `string` the link to braintree transaction
 
