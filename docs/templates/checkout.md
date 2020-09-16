**Template Checkout**

Checkout, Form, Shipping dan Pembayaran.\
`file : lsdcommerce/templates/checkout.php`

**#Functions**

Menampilkan Harga Produk berdasarkan ID Produk
```json
lsdc_product_price()
file : lsdcommerce/core/functions/core-functions.php on line 6
```
****
**Reference**

##### Form API -> ( Member Checkout ) -> Create Extra Form

##### Shipping API -> Create Extension

##### Payment API -> Create Extension

##### Order API -> Create Order via API

##### Extras Filter -> Create Extra Cost

****


**#Hook - Action**

`lsdcommerce_checkout`
`lsdcommerce_checkout_before_tab`
`lsdcommerce_checkout_form`
`lsdcommerce_checkout_shipping`
`lsdcommerce_checkout_summary_before`
`lsdcommerce_checkout_summary_after`
`lsdcommerce_checkout_payment_before`
`lsdcommerce_checkout_payment`
`lsdcommerce_checkout_payment_after`
`lsdcommerce_checkout_after_tab`