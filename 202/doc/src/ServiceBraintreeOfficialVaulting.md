---
name: Braintree Vaulting Service
category: Services
---


## Definition

This class contains functions for work with BraintreeOfficialVaulting objects


## Methods


- **doMigration()**
    
    - **Return Values**
    
      `void` 
      
- **getCustomerGroupedMethods($customer)**

    - **Parametres**
      
      customer: `int` PrestaShop Customer ID
    
    - **Return Values**
    
      `array`  

- **getCustomerMethods($customer, $method)**

    - **Parametres**
      
      customer: `int` PrestaShop Customer ID
      
      method: `string` payment tool (card or paypal account)
    
    - **Return Values**
    
      `array`  BraintreeOfficialVaulting
      
- **vaultingExist($token, $customer)**

    - **Parametres**
      
      token: `string` 
      
      customer: `int` PrestaShop Customer ID
    
    - **Return Values**
    
      `bool`







      