---
name: Method Braintree
category: Classes
---

The MethodBraintree extends the abstract class AbstractMethodBrainree.

## Definition

This class contains functions for creating transactions for paying for an order, 
refunding money for already created transactions and other functions 
related to the payment method "Express checkout"


## Methods


- **createCustomer()**
    
    - **Return Values**
    
      `object` returns BraintreeCustomer object
      
- **createForCurrency(\[$currency = null\])**

    - **Parametres**
      
      currency: `string` ISO of the currency
    
    - **Return Values**
    
      `array` Create new BT account for currency added on PS and returns array 
      like \[curr_iso_code => account_id\]  

- **createMethodNonce($token)**

    - **Parametres**
      
      token: `string` 
    
    - **Return Values**
    
      `string` returns **nonce** of the payment method    
      
- **createVaulting($result, $braintree_customer)**

    - **Parametres**
    
      result: `object` Braintree\Result\Successful object
      
      braintree_customer: `object` BraintreeCustomer 
    
    - **Return Values**
    
      `void`   
            
- **deleteVaultedMethod($payment_method)**

    - **Parametres**
    
      payment_method: `object` BraintreeVaulting 
    
    - **Return Values**
    
      `void`       
      
- **formatPrice($price)**

    - **Parametres**
          
      price: `float` price
    
    - **Return Values**
    
      `string` returns the converted price      
      
- **getAllCurrency(\[$mode = null\])**

    - **Parametres**
          
      mode: `boolean` the mode of the environment. True is Sandbox, False is Live
    
    - **Return Values**
    
      `array` take the all merchant accounts and returns array like \[iso_currency => account_id\]
      
- **getLinkToTransaction($id_transaction, $sandbox)**

    - **Parametres**
          
      id_transaction: `string` id of the payment transaction
          
      sandbox: `boolean` the mode of the environment. True is Sandbox, False is Live
    
    - **Return Values**
    
      `string` returns the the link to the page of the transaction on the site of Braintree       
      
      
- **getOrderId($cart)**

    - **Parametres**
          
      cart: `object` Cart object
    
    - **Return Values**
    
      `string` returns the order key for the transaction     
      
- **getTransactionStatus($orderBraintree)**

    - **Parametres**
          
      orderBraintree: `object` BraintreeOrder object
    
    - **Return Values**
    
      `string|boolean` returns status of the transaction or False      
      
- **init()**
    
    - **Return Values**
    
      `mixed` returns client token (`string`) or `array` with error code and error message      
      
- **isConfigured()**
    
    - **Return Values**
    
      `boolean` the connection with Braintree is configured or not      
      
      
- **isValidStatus($status)**

    - **Parametres**
          
      status: `string` the status of the transaction
    
    - **Return Values**
    
      `boolean` the status is valid or not     
      
- **sale($cart, $token_payment)**

    - **Parametres**
          
      cart: `object` Cart object
      
      token_payment: `string`
    
    - **Return Values**
    
      `mixed` execute and return the transaction       
            
- **searchTransaction($braintreeOrder)**

    - **Parametres**
          
      braintreeOrder: `object` BraintreeOrder object
    
    - **Return Values**
    
      `object` Braintree\Transaction object      
      
      
- **searchTransactions($braintreeOrders)**

    - **Parametres**
          
      braintreeOrdera: `array` array of the braintree order ids
    
    - **Return Values**
    
      `array` array of the Braintree\Transaction objects      
      
- **updateCustomer($braintree_customer)**

    - **Parametres**
          
      braintree_customer: `object` BraintreeCustomer object
    
    - **Return Values**
    
      `void`
      
- **validateMerchantAccounts($merchantAccounts)**

    - **Parametres**
          
      merchantAccounts: `array` the data for the validation [iso => mechantAccountId]
    
    - **Return Values**
    
      `array` returns wrong merchant accounts [iso => mechantAccountId]      
   
- **initConfig(\[$order_mode = null\])**

    - **Parametres**
          
      order_mode: `bool` mode of sandbox / live (true / false)
    
    - **Return Values**
    
      `void`         
      
      
      
      
      

 
