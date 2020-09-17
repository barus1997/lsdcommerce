# Checkout

Checkout template terdiri dari pengambilan data pemesan, data pengiriman dan juga detail produk serta pembayaran, cek detail filenya
`file : lsdcommerce/templates/checkout.php`

**#Functions**

Menampilkan Harga Produk berdasarkan ID Produk
```json
lsdc_product_price()
file : lsdcommerce/core/functions/core-functions.php on line 6
```
****

##### Form API 
Form API berguna untuk menambah form pada halaman checkout, dengan begini kamu bisa mengambil data yang diperlukan


`lsdcommerce_checkout_form`

****

##### Shipping API
Payment API berguna untuk menambah piliha metode pengiriman, baik itu untuk produk digital ataupun untuk produk fisik

`lsdcommerce_checkout_shipping`

```json
lsdc_shipping_method()
file : lsdcommerce/core/public/public-ajax.php on line 55
```
****
##### Payment API
Payment API berguna untuk menambah fitur pada pembayaran, ini berarti kamu bisa membuat metode pembayaran seperti midtrans di LSDCommerce 

Hook : `lsdcommerce_checkout_payment`

****

##### Order API
Order API berguna untuk menambah proses CRUD pada Order dan juga mengganti status order yang ada.

> Baca lebih detail di folder : **order**/
****

##### Extras API
Extras API berguna untuk menambah tambahan biaya pada Checkout, kamu bisa menambah biaya tambahan seperti donasi, atau biaya lainnya

> Baca lebih detail di folder : **payment**/
****


**#Hook - Action**

`lsdcommerce_checkout`
`lsdcommerce_checkout_before_tab`
`lsdcommerce_checkout_summary_before`
`lsdcommerce_checkout_summary_after`
`lsdcommerce_checkout_payment_before`
`lsdcommerce_checkout_payment_after`
`lsdcommerce_checkout_after_tab`