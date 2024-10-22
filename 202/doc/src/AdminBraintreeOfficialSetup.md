---
name: Setup
category: Admin Controllers
---

## Definition

This controller has five sections. For displaying all sections the HelperForm is used.

##### Account setting

This section allows to configure the connection with your paypal account.
Method `initAccountSettingsBlock()` is responsible for displaying this section.

##### Payment settings

This section allows to configure the type of the payment action (Salle / Authorize).
Method `initPaymentSettingsBlock()` is responsible for displaying this section.

##### Braintree merchant accounts

This section allows to configure merchant account ID
Method `initMerchantAccountForm()` is responsible for displaying this section.

##### Environment settings

This section allows switching between module operating modes (Sandbox / Live).
Method `initEnvironmentSettings()` is responsible for displaying this section.

##### Status

Serves for displaying of the condition of the configuration of the module
and signals if something is wrong.
Method `initStatusBlock()` is responsible for displaying this section.