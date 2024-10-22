---
name: Abstract
category: Front Controllers
---


## Definition
BraintreeOfficialAbstarctModuleFrontController

Abstarct class that must be extended by other module front controllers.

##### jsonValues
Contain ajax response. Must be an array.

##### redirectUrl
Contain redirect URL.

##### values
An array of POST and GET values. Can be manually defined during unit test
instead of environment variables.

##### errors
An array of error information : error_msg, error_code, msg_long.

### run
Overrider ModuleFrontController run function for make controllers more testable.
Redirect or send ajax response only in run function.
