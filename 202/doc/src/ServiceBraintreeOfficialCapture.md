---
name: Braintree Capture Service
category: Services
---


## Definition

This class contains functions for work with BraintreeOfficialCapture objects


## Methods


- **doMigration()**
    
    - **Return Values**
    
      `void` 
      
- **getByOrderId($id_order)**

    - **Parametres**
      
      id_order: `int` PrestaShop order ID
    
    - **Return Values**
    
      `array` returns the array of the BraitnreeOfficialCapture

- **getPayPalOrderBtId()**
    
    - **Return Values**
    
      `array` returns the array of the braintree orders that were created by paypal module (method is used while migrate data)        
      
- **loadByOrderBraintreeId($id_braintree_order)**

    - **Parametres**
      
      id_braintree_order: `int` BraintreeOfficialOrder ID
    
    - **Return Values**
    
      `object` BraitnreeOfficialCapture object
      
- **updateCapture($transaction_id, $amount, $status, $id_braintree_order)**

    - **Parametres**
      
      transaction_id: `string` New transaction ID that correspond to capture
      
      amount: `float` Captured amount
      
      status: `string` new payment status
      
      id_braintree_order: `int` BraintreeOfficialOrder ID
    
    - **Return Values**
    
      `bool`
      

 
