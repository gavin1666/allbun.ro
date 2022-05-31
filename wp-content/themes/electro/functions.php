<?php
/**
 * electro engine room
 *
 * @package electro
 */

/**
 * Initialize all the things.
 */
require get_template_directory() . '/inc/init.php';

/**
 * Note: Do not add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * http://codex.wordpress.org/Child_Themes
 */

/**
 * @snippet       Display Variation SKUs @ WooCommerce Product Admin
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @testedwith    WooCommerce 6
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
  
add_filter( 'woocommerce_product_get_sku', 'bbloomer_variable_product_skus_admin', 9999, 2 );
 
function bbloomer_variable_product_skus_admin( $sku, $product ) {
   if ( ! is_admin() ) return $sku;
   global $post_type, $pagenow;
   if ( 'edit.php' === $pagenow && 'product' === $post_type ) {
      if ( $product->is_type('variable') ) {
         $sku = '';
         foreach ( $product->get_children() as $child_id ) {
            $variation = wc_get_product( $child_id ); 
            if ( $variation && $variation->exists() ) $sku .= '(' . $variation->get_sku() . ') ';
         }
      }
   }
   return $sku;
}