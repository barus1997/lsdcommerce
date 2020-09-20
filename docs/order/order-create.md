## Create Order

Creating **Order** will fired when user click **Pay** button in Checkout Page. or you can Call via Class **LSDC_Order**

***.lsdcommerce-checkout-fired*** will trigger and checking and validation every detail checkout

**Order Object**
```json
array(4) {
  ["customer"]=>
  array(3) {
    ["name"]=>
    string(11) "Customer Name"
    ["phone"]=>
    string(11) "081231231233"
    ["email"]=>
    string(19) "lsdplugins@gmail.com"
  }
  ["shipping"]=>
  array(1) {
    ["physical"]=>
    array(4) {
      ["service"]=>
      string(7) "jne-oke"
      ["state"]=>
      string(1) "3"
      ["city"]=>
      string(3) "331"
      ["address"]=>
      string(8) "alamat rumah"
    }
  }
  ["products"]=>
  array(2) {
    [0]=>
    array(3) {
      ["id"]=>
      string(4) "3335" //product_id
      ["qty"]=>
      string(1) "1"
      ["variation"]=>
      string(8) "original" //optional
    }
    [1]=>
    array(3) {
      ["id"]=>
      string(4) "3335"
      ["qty"]=>
      string(1) "1"
      ["variation"]=>
      string(5) "pedes" //optional
    }
  }
  ["payment"]=>
  string(7) "bankbca"
}
```