=== EAN for WooCommerce ===
Contributors: wpcodefactory, algoritmika, anbinder
Tags: woocommerce, ean, gtin, barcode, woo commerce
Requires at least: 4.4
Tested up to: 6.0
Stable tag: 3.9.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Manage product GTIN (EAN, UPC, ISBN, etc.) in WooCommerce. Beautifully.

== Description ==

**EAN for WooCommerce** plugin lets you manage product GTIN (EAN, UPC, ISBN, etc.) in WooCommerce.

Currently supported standards: EAN-13, UPC-A, EAN-8, ISBN-13, JAN, Custom.

### &#9989; Main Features ###

* **Save product's EAN** in backend.
* For **variable products** set EAN for each variation individually or set single EAN for all variations at once.
* **Search by EAN** in backend (including AJAX search) and in frontend.
* Add sortable EAN column to **admin products list**.
* Optionally **show EAN** on **single product page**, **shop pages** and/or in **cart** on frontend.
* Add EAN to **product structured data**, e.g. for Google Search Console.
* Add EAN to **WooCommerce REST API** order and product responses; search orders and products by EAN.
* Show EAN in **order items table**, including emails, "thank you" page, etc.
* **Export** and **import** EAN.
* Use product **quick** and **bulk edit** to manage EAN.
* Output EAN with a **shortcode**.
* And more...

### &#129520; Tools ###

Plugin has tools that will help you generate, copy, assign and delete EANs in bulk, automatically or periodically.

* **Generate** EANs automatically with customizable EAN type, country prefix(es) and seed.
* **Copy** EANs **from** product **SKU**, product **ID** or product **meta**.
* **Assign** EANs from the predefined **list**.
* **Delete** EANs for all products at once.
* **Copy** EANs **to** product **SKU** or product **attribute**.
* **Add** or **delete** EANs for all **orders** at once.
* And more...

### &#129309; Compatibility ###

* [Point of Sale for WooCommerce](https://woocommerce.com/products/point-of-sale-for-woocommerce/) plugin.
* [Dokan marketplace](https://wordpress.org/plugins/dokan-lite/) plugin.
* [WCFM](https://wordpress.org/plugins/wc-frontend-manager/) and [WCFM Marketplace](https://wordpress.org/plugins/wc-multivendor-marketplace/) plugins.
* [Print Invoice & Delivery Notes for WooCommerce](https://wordpress.org/plugins/woocommerce-delivery-notes/) plugin.
* [WooCommerce PDF Invoices & Packing Slips](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/) plugin.
* [WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels](https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/) plugin.
* [WooCommerce Google Product Feed](https://woocommerce.com/products/google-product-feed/) plugin.
* [Rank Math SEO](https://wordpress.org/plugins/seo-by-rank-math/) plugin.
* [WooCommerce Customer / Order / Coupon Export](https://woocommerce.com/products/ordercustomer-csv-export/) plugin.
* And more...

### &#127942; Premium Version ###

With [premium plugin version](https://wpfactory.com/item/ean-for-woocommerce/) you can:

* Generate and display **barcode image** for your product EAN (frontend, backend, order items table (including emails), REST API, etc.).
* Barcodes can be **one-dimensional** (1D barcodes) or **two-dimensional** (2D barcodes, QR codes).
* Additionally you can **print** multiple EANs and barcodes to **PDF** file.

### &#128472; Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/ean-for-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > EAN".

== Screenshots ==

1. EAN for WooCommerce - Backend - Simple product
2. EAN for WooCommerce - Backend - Variable product
3. EAN for WooCommerce - Frontend - Variable product
4. EAN for WooCommerce - Admin settings
5. EAN for WooCommerce - Tools

== Changelog ==

= 3.9.1 - 24/05/2022 =
* Dev - Print - Admin settings rearranged.
* Tested up to: 6.0.

= 3.9.0 - 16/05/2022 =
* Dev - Tools - Product Tools:
    * "Copy to product SKU" tool added.
    * "Copy to product attribute" tool added.
    * "Variable products" option added.
    * Generate - "Seed" options added (defaults to "Product ID").
    * Admin settings descriptions updated.
    * Code refactoring.
* Dev - Developers - `alg_wc_ean_display` filter added.
* WC tested up to: 6.5.

= 3.8.0 - 07/04/2022 =
* Dev - Compatibility:
    * Point of Sale for WooCommerce - "Add EAN to search" option added.
    * "WooCommerce Customer / Order / Coupon Export" plugin compatibility added. Includes barcodes.
* Dev - Barcodes:
    * "[2D] Barcode (image link)" and "[2D] Barcode (base64)" columns added to "Products > All Products > Export".
    * Barcode generator - Always checking if EAN is valid for 1D barcodes.
* Dev - Advanced - Export/Import Plugin Settings - Import:
    * Validating JSON data now.
    * Resetting all options before the import now.
* Dev - Shortcodes - Barcodes - Template:
    * `%barcode_base64%` placeholder added.
    * `%barcode_link%` placeholder added.
* Dev - Developers - Product structured data - `alg_wc_ean_product_structured_data_markup_key`:
    * `$product` is passed to the filter as well now.
    * Filter is applied for the "Custom key" option as well now.
* Dev - Admin settings:
    * General - "Admin search" renamed to "Admin product search".
    * Barcodes - "Advanced Options" renamed to "Advanced Barcodes Options".
    * Barcodes - "Compatibility" moved to separate sections.
    * Print - "Advanced Options" renamed to "Advanced Print Options".
* Dev - Code refactoring:
    * EAN edit.
    * Export Plugin Settings.
    * Barcode generator.

= 3.7.2 - 22/03/2022 =
* Dev - Tools - Product Tools - Generate - "Product attribute (optional)" option added.

= 3.7.1 - 21/03/2022 =
* Dev - Tools - Product Tools - Copy product meta - "Meta sub key (optional)" option added.

= 3.7.0 - 21/03/2022 =
* Dev - Single product page - Positions - "Product additional information tab" position added.
* Dev - Product structured data - "Rank Math SEO" option added (defaults to `no`).
* Dev - Barcodes - Cart:
    * Using shortcodes now.
    * "Cart template" option added.
* Dev - REST API:
    * Products - "Search by EAN" option added.
    * Orders - "Search by EAN" option added.
    * Admin settings descriptions updated.
* Dev - Tools:
    * Product Tools - "Get stats" tool added.
    * Product Tools - "... total products" message added.
    * Product Tools - Assign from the list - "Reuse deleted" option added (defaults to `no`).
* Dev - Shortcodes - Barcodes - `[alg_wc_ean_barcode_2d_base64]` - Default `w` and `h` are set to `2` now (was `3`).
* Dev - "TCPDF" library updated to v6.4.4.
* Dev - Code refactoring:
    * `Alg_WC_EAN_REST_API` class added.
    * `Alg_WC_EAN_Order_Items_Table` class added.
    * `Alg_WC_EAN_Order_Items_Table_Barcodes` class added.
    * `Alg_WC_EAN_Barcodes` class added.
* WC tested up to: 6.3.

= 3.6.0 - 03/03/2022 =
* Dev - General - Admin product edit page - "Check if valid" option added (defaults to `yes`).
* Dev - General - Admin product edit page - "Check if unique" option added (defaults to `no`).
* Dev - General - "Admin product duplicate" option added (defaults to `yes`).
* Dev - Shortcodes - `[alg_wc_ean_product_meta]` shortcode added.
* Dev - Shortcodes - `[alg_wc_ean_product_function]` shortcode added.
* Dev - Shortcodes - `[alg_wc_ean]` - `children` attribute (defaults to `no`) and `glue` attribute (defaults to `, `) added.
* Dev - Developers - `alg_wc_ean_edit` filter added.
* Dev - Print - "Style" option added.
* Dev - Barcodes - Shortcodes - `on_empty` attribute added to `[alg_wc_ean_barcode]`, `[alg_wc_ean_barcode_2d]`, `[alg_wc_ean_barcode_base64]` and `[alg_wc_ean_barcode_2d_base64]` shortcodes.
* Dev - Admin settings rearranged ("Orders & Emails" subsections added (includes barcodes)).
* Dev - Code refactoring.

= 3.5.1 - 25/02/2022 =
* Dev - Compatibility - "WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels" (includes barcodes):
    * "Position" option added (defaults to "After item meta" (was "After item name")). Available positions: "Before item name", "After item name", "After item meta", "Before item meta" and "As a separate column".
    * "Documents" option added (defaults to all documents (was "Invoice" and "Dispatch label" only)).
    * "Column title", "Column HTML class" and "Column HTML style" options added.

= 3.5.0 - 24/02/2022 =
* Dev - Compatibility - "WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels" plugin compatibility added. Includes barcodes.
* Dev - Shortcodes - `[alg_wc_ean]` - `on_empty` attribute added.
* Dev - Shortcodes - `[alg_wc_ean_product_attr]` - `product_id`, `before`, `after`, `parent` attributes added. Shortcode moved to the free plugin version.
* Dev - Shortcodes - `[alg_wc_ean_product_image]` shortcode added.
* Dev - Shortcodes - `[alg_wc_ean_product_name]` shortcode added.
* Dev - Shortcodes - `[alg_wc_ean_product_sku]` shortcode added.
* Dev - Shortcodes - `[alg_wc_ean_product_price]` shortcode added.
* Dev - Shortcodes - `[alg_wc_ean_product_id]` shortcode added.
* Dev - Barcodes - Shortcodes - `color` attribute added to `[alg_wc_ean_barcode]`, `[alg_wc_ean_barcode_2d]`, `[alg_wc_ean_barcode_base64]` and `[alg_wc_ean_barcode_2d_base64]` shortcodes (defaults to `#000000`).
* Dev - Print - "Cell border" option added (defaults to "No").
* Dev - Print - Placeholders are deprecated now (shortcodes should be used instead). Default value and admin settings description updated for the "Template" option. Now "Barcode Options", "2D Barcode Options" and "Product Image Options" admin settings subsections are visible only if there is corresponding placeholder in the "Template".
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.

= 3.4.0 - 23/02/2022 =
* Dev - General - Product structured data - "Automatic key" options added.
* Dev - General - Type - Type details - Admin settings restyled.
* Dev - Tools - Product Tools - Generate - Type - Admin settings description added.
* Dev - Barcodes - Shortcodes - Optional `ean` attribute added.
* Dev - Barcodes - Shortcodes - Shortcodes are always available now (even if the corresponding barcodes section is disabled), e.g. for the "Print" section.
* Dev - Print - Developers - `alg_wc_ean_print_render_meta_box_shop_order_force_refunded` filter added.
* Dev - Print - Developers - `alg_wc_ean_print_barcode_shop_order_refunded_item_qty` filter added.
* Dev - Code refactoring.

= 3.3.0 - 21/02/2022 =
* Dev - General - Type - "ISBN-13" type added.
* Dev - General - Type - "JAN" type added.
* Dev - General - Type - "Custom" type added; "CODE 128" type removed.
* Dev - General - Type - "Type details" description added.
* Dev - Tools - Product Tools - Generate - "County prefix length" option added (for EAN-8 type only).
* Dev - 1D Barcodes - Advanced Options - "Barcode type" option added (defaults to `Automatic`).
* Dev - Code refactoring.

= 3.2.0 - 15/02/2022 =
* Dev - Orders - REST API - Now using current product EAN as a fallback (i.e. if there is no EAN in order item meta).
* Dev - Barcodes - "Orders" options added ("Show barcode image on admin order edit page").
* Dev - Barcodes - "REST API" (product and order) options added.
* Dev - Barcodes - `[alg_wc_ean_barcode_base64]` and `[alg_wc_ean_barcode_2d_base64]` shortcodes added.
* Dev - Admin settings rearranged: "REST API" subsections added.
* Dev - Code refactoring.

= 3.1.2 - 11/02/2022 =
* Dev - Compatibility - Dokan - Field added to variations. Includes barcodes.
* Dev - Compatibility - WooCommerce PDF Invoices & Packing Slips - "Position" option added (defaults to "After item meta"). Includes barcodes.
* Dev - Deploy script added.
* WC tested up to: 6.2.

= 3.1.1 - 04/02/2022 =
* Dev - Print - Template - `%product_price_regular%`, `%product_price_sale%`, `%product_price_regular_raw%`, `%product_price_sale_raw%` placeholders added.

= 3.1.0 - 04/02/2022 =
* Dev - Advanced - "Export/Import Plugin Settings" options added.
* Dev - Advanced - Meta key - Option mark as "required" now.
* Dev - Order items table - Pages - Outputting barcode directly now (i.e. will work on `localhost` environment now). "Advanced > Force remote image" option added.
* Dev - Order items table - "Emails" options added. Includes barcodes.
* Dev - Order items table - "Template" options ("HTML" and "Plain text (emails only)") added.
* Dev - Print - Template - `%product_price%` and `%product_price_raw%` placeholders added.
* Tested up to: 5.9.

= 3.0.0 - 20/01/2022 =
* Dev - Single product page - Variable products - Safe-checks added in `variations_add_params()` function. Fixes the compatibility issue with the "WooCommerce Bulk Variations" plugin.
* Dev - Admin products list column - `width: 10%` style added.
* Dev - Compatibility - "WooCommerce Google Product Feed" plugin compatibility added.
* Dev - Tools - Assign from the list - "Product categories" option added.
* Dev - Barcodes - Compatibility - "Dokan" options added.
* Dev - Print - Print buttons - Single order - Using order item quantities now (and ignoring "Use stock quantity" option).
* Dev - Print - Print buttons - Single order - "Refunded items" buttons added.
* Dev - Print - Print buttons - "Variations print buttons" option added (defaults to `Variations tab`).
* Dev - Print - Page format - Dimensions added to the format descriptions.
* Dev - Print - Page format - Custom - Now using `LETTER` as a fallback, in case if custom width or height is set to `0` (zero).
* Dev - Code refactoring.
* WC tested up to: 6.1.

= 2.9.0 - 24/12/2021 =
* Fix - Text domain (translation) fixed.
* Fix - Tools - Product Tools - Automatic actions - Variations update fixed.
* Dev - General - "REST API" (product) option added.
* Dev - Tools - Product Tools - "Periodic action" options added.
* Dev - Tools - Product Tools - "Assign EAN from the list for all products" tool added.
* Dev - Tools - Product Tools - "Automatic actions" options added ("Automatically generate EAN for new products / on product update" options removed).
* Dev - Tools - Product Tools - Automatic actions - "Copy product SKU", "Copy product ID", "Copy product meta" actions added.
* Dev - Tools - Product Tools - Automatic actions - Hook priority increased (from `10` to `PHP_INT_MAX`).
* Dev - Tools - Product Tools - Products are sorted by ID (ascending) now.
* Dev - Tools - Product Tools - Settings restyled.
* Dev - Print - Print buttons - "Print buttons style" option added.
* Dev - Code refactoring.
* Plugin description improved.

= 2.8.0 - 16/12/2021 =
* Dev - General - Shop pages - Now using "Title" option in the template.
* Dev - General - Orders - "REST API" option added.
* Dev - Advanced - "Meta key" option added.
* WC tested up to: 6.0.

= 2.7.0 - 12/11/2021 =
* Dev - Tools - Product Tools - Generate - "Seed prefix" option added (optional). "Prefix" options renamed to "Country prefix".
* Dev - Tools - Product Tools - "Products > Bulk actions" option added (defaults to "Generate EAN" and "Delete EAN" actions).
* Dev - Print - "Print barcode" (i.e. vs "Get barcode PDF") buttons added.
* Dev - Print - Advanced Options - "Use Print.js" option added.
* Dev - Print - Advanced Options - "Skip products without EAN" option added.
* Dev - Print - Print buttons - Single product - Separate variation buttons added.
* Dev - Print - Shortcodes - `[alg_wc_ean_product_attr]` shortcode added.
* Dev - Print - Placeholders - `%product_parent_title%` placeholder added.
* Dev - Print - Placeholders - `%product_parent_sku%` placeholder added.
* Dev - Print - Placeholders - `%product_parent_id%` placeholder added.
* Dev - Print - Admin settings restyled.
* Dev - Barcodes - Shortcodes - `content` - `sku` value added.
* WC tested up to: 5.9.

= 2.6.0 - 03/11/2021 =
* Dev - Compatibility - "WooCommerce PDF Invoices & Packing Slips" plugin compatibility options added.
* Dev - Compatibility - Print Invoice & Delivery Notes for WooCommerce - Using our "General > Title" option value in PDFs now.
* Dev - Print - Print buttons - "Single order" option added.
* Dev - Print - Print buttons - "Single product" option added.
* Dev - Print - "Print buttons" option added (defaults to `Products > Bulk actions`).
* Dev - Print - Template - `%product_sku%` placeholder added.
* Dev - Print - Template - `%product_image%` - Now checking if `curl_init()` function exists. This prevents critical PHP error.
* Dev - Admin settings description updated.
* Dev - Code refactoring.

= 2.5.0 - 28/10/2021 =
* Dev - Print - "Font" and "Font size" options added. "DejaVu Sans (Unicode)" font added (normal only; italic and bold were not added to reduce the size of the plugin). All other available fonts (i.e. "Times New Roman", "Helvetica" and "Courier") have italic and bold included.
* WC tested up to: 5.8.

= 2.4.2 - 30/09/2021 =
* Dev - Search - "Flatsome" theme - Allowing partial EAN matches now.

= 2.4.1 - 29/09/2021 =
* Fix - Possible PHP parse error fixed.

= 2.4.0 - 27/09/2021 =
* Dev - Developers - `alg_wc_ean_get_type` filter added.
* Dev - Admin settings description updated.
* Dev - 1D Barcodes - Checking if EAN is valid before generating the barcode now.
* Dev - Print - Template - `%type%` placeholder added (mostly for debugging).
* Dev - Code refactoring.

= 2.3.0 - 23/09/2021 =
* Dev - Search - Safe checks added (checking for the valid `$post` variable now).

= 2.2.9 - 22/09/2021 =
* Dev - General/Barcodes - Single product page - "Variable products: Position in variation" option added.
* Dev - Compatibility - Admin settings rearranged: moved to a separate settings section.
* Dev - Advanced - "JS selector in variation" option added.
* WC tested up to: 5.7.

= 2.2.8 - 20/09/2021 =
* Dev - Tools - Product Tools - Generate - "Automatically generate EAN for new products" option added.
* Dev - Tools - Product Tools - Generate - "Automatically generate EAN on product update" option added.
* Dev - Tools - Product Tools - "Copy EAN from product meta for all products" tool added.
* Dev - Tools - Product Tools - Not overwriting EANs for products with existing EAN now.
* Dev - Developers - `alg_wc_ean_settings_page_label` filter added.

= 2.2.7 - 16/09/2021 =
* Dev - General - "Title" option added.
* Dev - Tools - Product Tools - Generate - "Prefix to" option added (optional). "Prefix" option renamed to "Prefix from".
* Dev - Tools - Product Tools - Generate - Code refactoring.

= 2.2.6 - 15/09/2021 =
* Dev - Tools - Product Tools - Generate - "Type" option added.
* Dev - Tools - Product Tools - Generate - "Prefix" option added.
* Dev - Tools - Product Tools - Generate - Code refactoring.
* Dev - Tools - Admin settings restyled.

= 2.2.5 - 14/09/2021 =
* Fix - General - Admin products list column - Validate - Fixed.
* Dev - Tools - "Generate EAN for all products" tool added.
* Dev - Tools - "Copy EAN from product SKU for all products" tool added.
* Dev - Tools - Copy EAN from product ID for all products - Showing the tool for all EAN types now (not only for `CODE 128`).
* Dev - Tools - Admin settings rearranged: moved to a separate settings section. Settings descriptions updated.
* Dev - Barcodes - Outputting barcodes even for non-valid EANs now.

= 2.2.4 - 07/09/2021 =
* Fix - Print - Page format - Custom Width/Height - Admin settings description fixed.
* Dev - Print - Advanced - "Suppress errors" option added (defaults to `yes`).
* Dev - Print - General - "Page break margin" option added.
* Dev - Print - General - All margins (top/left/right) can be zero now.
* Dev - Print - Admin settings rearranged: "Unit" option moved higher.
* Dev - Print - Admin settings descriptions updated.
* Dev - Barcodes - Advanced - "Suppress errors" options added (defaults to `yes`).

= 2.2.3 - 31/08/2021 =
* Dev - Barcodes - Shortcodes - `content` - `add_to_cart` value added.
* Dev - Barcodes - Shortcodes - `content` - `add_to_cart_url` value added.
* WC tested up to: 5.6.

= 2.2.2 - 04/08/2021 =
* Dev - Plugin Compatibility Options - "Dokan" options added.
* Dev - Plugin Compatibility Options - "WCFM" options added.
* Dev - Admin settings restyled.

= 2.2.1 - 01/08/2021 =
* Fix - Search - Our frontend search option caused issues on WooCommerce Analytics page, e.g. when searching for a coupon code in filter. This is fixed now.
* Fix - Admin settings - "Undefined property" PHP notice fixed. Was occurring in "General" settings section, when "Enable plugin" option was disabled.
* WC tested up to: 5.5.
* Tested up to: 5.8.

= 2.2.0 - 28/06/2021 =
* Dev - Print - General Options - "Use quantity" option added.
* Dev - Print - General Options - Template - `%product_name%` and `%product_title%` placeholders added.
* Dev - Compatibility - Point of Sale for WooCommerce - EAN field added to the "Register > Scanning Fields" option.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.
* Dev - "PHP Barcode Generator" library removed.
* Dev - "TCPDF" library updated to v6.4.1 (from v6.3.5).
* WC tested up to: 5.4.

= 2.1.1 - 23/03/2021 =
* Dev - 2D Barcodes - Advanced Options - "Barcode type" option added (defaults to `QR code: Low error correction`).

= 2.1.0 - 19/03/2021 =
* Fix - Print - `%barcode_2d%` - Barcode dimension fixed (was `1d`).
* Dev - General - "Orders" options ("Add EAN to new order items meta" and "Admin order") added.
* Dev - General - Tools - "Delete EANs from all order items" tool added.
* Dev - General - Tools - "Add EANs to all order items" tool added.
* Dev - General - Tools - "Delete all EANs for all products" tool added.
* Dev - General - Tools - "Generate EANs automatically for all products from product IDs" tool added (for `CODE 128` type only).
* Dev - General - Single product page - "Template" option added.
* Dev - General - Single product page - "Position", "Position priority" options added.
* Dev - General - Search - "Flatsome theme" option added.
* Dev - Barcodes - Admin products list column - "Column title" option added.
* Dev - Barcodes - Admin products list column - "Column template" options added. Defaults to barcodes **including product children**.
* Dev - Barcodes - Shortcodes - Checking if EAN is valid now (when `content` is set to `ean`).
* Dev - Barcodes - Shortcodes - `children` (defaults to `no`) and `glue` (defaults to empty string) attributes added. This will implode all variation barcodes for variable product.
* Dev - Barcodes - Shortcodes - `template` attribute added (defaults to `%barcode_img%`). Additional placeholders: `%product_id%`, `%product_title%`, `%value%`.
* Dev - Barcodes - Shortcodes - `content` attribute added. Defaults to `ean`. Other possible values: `url`, `admin_url`, `admin_search`, `increase_stock` and `decrease_stock`.
* Dev - Barcodes - Shortcodes - `w` and `h` attributes added.
* Dev - Barcodes - Shortcodes - `product_id` defaults to `get_the_ID()` now.
* Dev - Barcodes - Shortcodes - Now accessible in "Print barcodes (PDF)" section (i.e. in "Template" option).
* Dev - Print - General Options - "Variations" option added.
* Dev - Code refactoring.
* WC tested up to: 5.1.
* Tested up to: 5.7.

= 2.0.0 - 10/01/2021 =
* Dev - "Shop pages" options added.
* Dev - "Cart" options added.
* Dev - Shortcodes - `[alg_wc_ean_barcode]` - Shortcode is now available even if "Barcodes > Single product page" option is disabled.
* Dev - Shortcodes - `[alg_wc_ean_barcode_2d]` shortcode added.
* Dev - "2D Barcodes" section added.
* Dev - "Print" section added.
* Dev - Barcodes - "Admin products list column" options added.
* Dev - Barcodes - "Enable section" option added (defaults to `no`).
* Dev - Localization - `load_plugin_textdomain` moved to the `init` action.
* Dev - Settings - All barcode options moved to new "Barcodes" section, subsections merged, etc.
* Dev - Settings - Print Invoice & Delivery Notes for WooCommerce - Link updated.
* Dev - Code refactoring.
* WC tested up to: 4.8.
* Tested up to: 5.6.

= 1.5.1 - 29/11/2020 =
* Dev - `[alg_wc_ean]` shortcode added.
* Dev - `[alg_wc_ean_barcode]` shortcode added.

= 1.5.0 - 24/11/2020 =
* Dev - Type - "Automatic (EAN-13, UPC-A, EAN-8)" option added.
* Dev - EAN field added to the WooCommerce Export and Import tools.
* Dev - EAN field added to the WooCommerce Quick and Bulk edit.
* Dev - Backend Options - Product list column - Column is sortable now.

= 1.4.0 - 24/11/2020 =
* Dev - "Type" option added. Now (in addition to the default `EAN-13`) these types are available: `CODE 128`, `EAN-8`, `UPC-A`.
* Dev - "Print Invoice & Delivery Notes for WooCommerce" plugin options added.
* WC tested up to: 4.7.

= 1.3.0 - 28/10/2020 =
* Fix - Frontend - Show barcode - Variations - It only worked if "Show EAN" option was also enabled. This is fixed now.
* Dev - Free plugin version released.
* WC tested up to: 4.6.

= 1.2.0 - 13/10/2020 =
* Dev - "Order Items Table" options added.
* Dev - Frontend - Translation domain fixed.

= 1.1.1 - 09/09/2020 =
* Dev - Backend - "Position" option added.
* WC tested up to: 4.5.

= 1.1.0 - 27/08/2020 =
* Fix - Displaying variations codes for variable products with no *main* EAN set - Fixed.
* Dev - JS files minified.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.
* Tested up to: 5.5.
* WC tested up to: 4.4.

= 1.0.3 - 14/01/2020 =
* Fix - Backend - Search - `meta_query` fixed.

= 1.0.2 - 08/01/2020 =
* Dev - Backend - Search - "AJAX search" option added.
* Dev - Code refactoring.

= 1.0.1 - 05/01/2020 =
* Dev - EAN-13 validation added.
* Dev - Backend - EAN input pattern now set to accept numbers only; max length set to 13.

= 1.0.0 - 30/12/2019 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
