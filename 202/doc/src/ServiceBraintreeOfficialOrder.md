---
name: Braintree Order Service
category: Services
---


## Definition

This class contains functions for work with BraintreeOfficialOrder objects


## Methods


- **doMigration()**
    
    - **Return Values**
    
      `void` 
      
- **deleteBtOrderFromPayPal()**
    
    - **Return Values**
    
      `void`

- **getBraintreeOrdersForValidation()**
    
    - **Return Values**
    
      `array` returns the array of BraintreeOfficialOrder objects
 
- **getCountOrders()**
    
    - **Return Values**
    
      `int`
      
- **loadByOrderId($id_order)**

    - **Parametres**
      
      id_order: `int` Prestashop Order ID
    
    - **Return Values**
    
      `object` BraintreeOfficialOrder object  
      
- **loadByTransactionId($id_transaction)**

    - **Parametres**
      
      id_transaction: `string` Transaction ID
    
    - **Return Values**
    
      `object` BraintreeOfficialOrder object      
      