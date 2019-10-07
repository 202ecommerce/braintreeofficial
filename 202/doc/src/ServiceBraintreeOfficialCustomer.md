---
name: Braintree Customer Service
category: Services
---


## Definition

This class contains functions for work with BraintreeOfficialCustomer objects


## Methods


- **doMigration()**
    
    - **Return Values**
    
      `void` 
      
- **loadCustomerByMethod($id_customer, $sandbox)**

    - **Parametres**
      
      id_customer: `int` PrestaShop Customer ID
      
      sandbox: `bool` mode of customer
    
    - **Return Values**
    
      `object` BraintreeOfficialCustomer object

- **setProfileKeyForCustomers($sandbox)**

    - **Parametres**
      
      sandbox: `bool` mode of customer
    
    - **Return Values**
    
      `bool`
      
- **setProfileKeyForCustomer($braintreeCustomer, \[$method = null\])**

    - **Parametres**
      
      braintreeCustomer: `object` BraintreeOfficialCustomer object
      
      method: `object` MethodBraintreeOfficial object
    
    - **Return Values**
    
      `bool`
 
