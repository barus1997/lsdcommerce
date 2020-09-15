**Template Single**

Menampilkan Detail Produk dan Cart Manager.\
`file : lsdcommerce/templates/single.php`

**#Functions**

Menampilkan Harga Produk berdasarkan ID Produk
```json
lsdc_product_price()
file : lsdcommerce/core/functions/core-functions.php on line 6
```

Menampilkan Berat Produk berdasarkan ID Produk
```json
lsdc_product_weight()
file : lsdcommerce/core/functions/core-functions.php on line 24
```

Menampilkan Stok Produk berdasarkan ID Produk
```json
lsdc_product_stock()
file : lsdcommerce/core/functions/core-functions.php on line 31
```

**#Hook - Action**

`lsdcommerce_single`
`lsdcommerce_single_before`
`lsdcommerce_single_price`
`lsdcommerce_single_tab_before`
`lsdcommerce_single_tabs`
`lsdcommerce_single_tab_after`
`lsdcommerce_single_after`

**#Image Size**

Ukuran untuk Mini Produk ( 90 x 90px ) :: `lsdcommerce-thumbnail-mini`
Ukuran untuk Detail Produk ( 500 x Auto ) :: `lsdcommerce-thumbnail-single`
