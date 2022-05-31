<?php
/**
 * EAN for WooCommerce - Compatibility Class
 *
 * @version 3.8.0
 * @since   2.2.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Compatibility' ) ) :

class Alg_WC_EAN_Compatibility {

	/**
	 * Constructor.
	 *
	 * @version 3.8.0
	 * @since   2.2.0
	 *
	 * @todo    [now] (dev) "Point of Sale for WooCommerce": add `( 'yes' === get_option( 'alg_wc_ean_wc_pos', 'yes' ) )` / "This will add EAN field to the "Register > Scanning Fields" option of the %s plugin." / Point of Sale for WooCommerce / https://woocommerce.com/products/point-of-sale-for-woocommerce/
	 * @todo    [next] (feature) WCFM: customizable position, i.e. instead of right below the "SKU" field in "Inventory" tab
	 * @todo    [next] (feature) Dokan: customizable position, i.e. instead of `dokan_new_product_after_product_tags` and `dokan_product_edit_after_product_tags`
	 * @todo    [maybe] (feature) https://wordpress.org/plugins/woocommerce-xml-csv-product-import/ (WooCommerce add-on for "WP All Import")
	 */
	function __construct() {

		// "Point of Sale for WooCommerce" plugin
		add_filter( 'wc_pos_scanning_fields', array( $this, 'wc_pos_scanning_fields' ), PHP_INT_MAX );
		if ( 'yes' === get_option( 'alg_wc_ean_wc_pos_search', 'no' ) ) {
			add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'wc_pos_add_ean_to_product_name' ), PHP_INT_MAX, 3 );
		}

		// "Dokan – Best WooCommerce Multivendor Marketplace Solution – Build Your Own Amazon, eBay, Etsy" plugin
		if ( 'yes' === get_option( 'alg_wc_ean_dokan', 'no' ) ) {
			add_action( 'dokan_new_product_after_product_tags',  array( $this, 'dokan_add_ean_field' ) );
			add_action( 'dokan_product_edit_after_product_tags', array( $this, 'dokan_add_ean_field' ), 10, 2 );
			add_action( 'dokan_new_product_added',               array( $this, 'dokan_save_ean_field' ), 10, 2 );
			add_action( 'dokan_product_updated',                 array( $this, 'dokan_save_ean_field' ), 10, 2 );
			add_action( 'dokan_product_after_variation_pricing', array( $this, 'dokan_add_ean_field_variation' ), 10, 3 );
		}

		// WCFM
		if ( 'yes' === get_option( 'alg_wc_ean_wcfm', 'no' ) ) {
			add_filter( 'wcfm_product_fields_stock',            array( $this, 'wcfm_add_ean_field' ), 10, 3 );
			add_action( 'after_wcfm_products_manage_meta_save', array( $this, 'wcfm_save_ean_field' ), 10, 2 );
		}

		// "Print Invoice & Delivery Notes for WooCommerce" plugin
		if ( 'yes' === get_option( 'alg_wc_ean_wcdn', 'no' ) ) {
			add_action( 'wcdn_order_item_after', array( $this, 'add_to_wcdn_ean' ), 10, 3 );
		}

		// "WooCommerce PDF Invoices & Packing Slips" plugin
		if ( 'yes' === get_option( 'alg_wc_ean_wpo_wcpdf', 'no' ) ) {
			add_action( get_option( 'alg_wc_ean_wpo_wcpdf_position', 'wpo_wcpdf_after_item_meta' ), array( $this, 'add_to_wpo_wcpdf_ean' ), 10, 3 );
		}

		// "WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels" plugin
		if ( 'yes' === get_option( 'alg_wc_ean_wt_pklist', 'no' ) ) {
			// Options
			$this->wt_pklist_options = array_replace(
				array(
					'content'      => '<p>EAN: [alg_wc_ean]</p>',
					'position'     => 'after_product_meta',
					'documents'    => '',
					'column_title' => __( 'EAN', 'ean-for-woocommerce' ),
					'column_class' => 'wfte_product_table_head_ean wfte_text_center',
					'column_style' => '',
				),
				get_option( 'alg_wc_ean_wt_pklist_options', array() )
			);
			// Hooks
			switch ( $this->wt_pklist_options['position'] ) {
				case 'column':
					add_filter( 'wf_pklist_package_product_table_additional_column_val', array( $this, 'add_to_wt_pklist_column_ean' ), 10, 6 );
					add_filter( 'wf_pklist_product_table_additional_column_val',         array( $this, 'add_to_wt_pklist_column_ean' ), 10, 6 );
					add_filter( 'wf_pklist_alter_product_table_head',                    array( $this, 'add_to_wt_pklist_column_head_ean' ), 10, 3 );
					break;
				case 'before_product_meta':
				case 'after_product_meta':
					add_filter( 'wf_pklist_add_product_meta',                            array( $this, 'add_to_wt_pklist_ean' ), 10, 5 );
					add_filter( 'wf_pklist_add_package_product_meta',                    array( $this, 'add_to_wt_pklist_ean' ), 10, 5 );
					break;
				default: // 'after_product_name', 'before_product_name'
					add_filter( 'wf_pklist_alter_product_name',                          array( $this, 'add_to_wt_pklist_ean' ), 10, 5 );
					add_filter( 'wf_pklist_alter_package_product_name',                  array( $this, 'add_to_wt_pklist_ean' ), 10, 5 );
			}
		}

		// "WooCommerce Google Product Feed"
		add_filter( 'woocommerce_gpf_custom_field_list', array( $this, 'add_to_woocommerce_gpf_custom_field_list' ), PHP_INT_MAX );

		// "WooCommerce Customer / Order / Coupon Export"
		if ( 'yes' === get_option( 'alg_wc_ean_wc_customer_order_export', 'no' ) ) {
			add_filter( 'wc_customer_order_export_format_data_sources',            array( $this, 'wc_customer_order_export_add_column' ), 10, 3 );
			add_filter( 'wc_customer_order_export_csv_order_row_one_row_per_item', array( $this, 'wc_customer_order_export_render_column' ), 10, 4 );
		}

	}

	/**
	 * wc_customer_order_export_add_column.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 *
	 * @todo    [now] [!!] (dev) `item_ean` to `item_alg_wc_ean`?
	 * @todo    [next] (dev) XML (at least in `OrderLineItems`)?
	 */
	function wc_customer_order_export_add_column( $data_sources, $export_type, $output_type ) {
		if ( 'orders' === $export_type && 'csv' === $output_type ) {
			$data_sources[] = 'item_ean';
		}
		return $data_sources;
	}

	/**
	 * wc_customer_order_export_render_column.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 */
	function wc_customer_order_export_render_column( $data, $item, $order, $generator ) {
		$data['item_ean'] = ( ! empty( $item['id'] ) && ( $_item = new WC_Order_Item_Product( $item['id'] ) ) ? alg_wc_ean()->core->get_ean_from_order_item( $_item ) : '' );
		return $data;
	}

	/**
	 * wt_pklist_check_template_type.
	 *
	 * @version 3.5.1
	 * @since   3.5.1
	 */
	function wt_pklist_check_template_type( $template_type ) {
		if ( ! empty( $this->wt_pklist_options['documents'] ) ) {
			if ( ! is_array( $this->wt_pklist_options['documents'] ) ) {
				$this->wt_pklist_options['documents'] = array_map( 'trim', explode( ',', $this->wt_pklist_options['documents'] ) );
			}
			return ( in_array( $template_type, $this->wt_pklist_options['documents'] ) );
		}
		return true;
	}

	/**
	 * add_to_wt_pklist_column_head_ean.
	 *
	 * @version 3.5.1
	 * @since   3.5.1
	 */
	function add_to_wt_pklist_column_head_ean( $columns, $template_type, $order ) {
		if ( $this->wt_pklist_check_template_type( $template_type ) ) {
			$columns['ean'] = '<th' .
					' class="' . $this->wt_pklist_options['column_class'] . '"' .
					' style="' . $this->wt_pklist_options['column_style'] . '"' .
					' col-type="' . sanitize_key( $this->wt_pklist_options['column_title'] ) . '"' .
				'>' . $this->wt_pklist_options['column_title'] . '</th>';
		}
		return $columns;
	}

	/**
	 * add_to_wt_pklist_column_ean.
	 *
	 * @version 3.5.1
	 * @since   3.5.1
	 */
	function add_to_wt_pklist_column_ean( $value, $template_type, $column_key, $product, $order_item, $order ) {
		if ( 'ean' === $column_key ) {
			$value = $this->add_to_wt_pklist_ean( $value, $template_type, $product, $order_item, $order );
		}
		return $value;
	}

	/**
	 * add_to_wt_pklist_ean.
	 *
	 * @version 3.5.1
	 * @since   3.5.0
	 *
	 * @see     https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/
	 */
	function add_to_wt_pklist_ean( $value, $template_type, $product, $order_item, $order ) {
		if ( $this->wt_pklist_check_template_type( $template_type ) ) {
			$result = alg_wc_ean()->core->shortcodes->do_shortcode( $this->wt_pklist_options['content'], array( 'product_id' => $product->get_id() ) );
			$value  = ( in_array( $this->wt_pklist_options['position'], array( 'after_product_meta', 'after_product_name' ) ) ? $value . $result : $result . $value );
		}
		return $value;
	}

	/**
	 * add_to_woocommerce_gpf_custom_field_list.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [now] (dev) make this optional?
	 */
	function add_to_woocommerce_gpf_custom_field_list( $fields ) {
		$fields[ 'meta:' . alg_wc_ean()->core->ean_key ] = __( 'EAN', 'ean-for-woocommerce' );
		return $fields;
	}

	/**
	 * add_to_wcdn_ean.
	 *
	 * @version 2.6.0
	 * @since   1.4.0
	 *
	 * @todo    [next] (feature) customizable wrapper
	 * @todo    [next] (dev) check if valid?
	 */
	function add_to_wcdn_ean( $product, $order, $item ) {
		if ( false !== ( $ean = alg_wc_ean()->core->get_ean_from_order_item( $item ) ) ) {
			echo '<small class="ean_wrapper">' . esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ) . ' ' .
					'<span class="ean">' . $ean . '</span>' .
				'</small>';
		}
	}

	/**
	 * add_to_wpo_wcpdf_ean.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 *
	 * @todo    [next] (feature) customizable template?
	 * @todo    [next] (dev) check if valid?
	 */
	function add_to_wpo_wcpdf_ean( $type, $item, $order ) {
		if ( ! empty( $item['item_id'] ) && ( $item = new WC_Order_Item_Product( $item['item_id'] ) ) && false !== ( $ean = alg_wc_ean()->core->get_ean_from_order_item( $item ) ) ) {
			echo '<dl class="meta">' .
					'<dt class="ean">' . esc_html( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) ) . ':' . '</dt>' .
					'<dd class="ean">' . $ean . '</dd>' .
				'</dl>';
		}
	}

	/**
	 * wcfm_save_ean_field.
	 *
	 * @version 2.2.2
	 * @since   2.2.2
	 *
	 * @see     https://plugins.svn.wordpress.org/wc-frontend-manager/tags/6.5.10/controllers/products-manager/wcfm-controller-products-manage.php
	 */
	function wcfm_save_ean_field( $new_product_id, $wcfm_products_manage_form_data ) {
		$id = 'wcfm_' . alg_wc_ean()->core->ean_key;
		if ( isset( $wcfm_products_manage_form_data[ $id ] ) ) {
			update_post_meta( $new_product_id, alg_wc_ean()->core->ean_key, wc_clean( $wcfm_products_manage_form_data[ $id ] ) );
		}
	}

	/**
	 * wcfm_add_ean_field.
	 *
	 * @version 2.2.2
	 * @since   2.2.2
	 *
	 * @see     https://plugins.svn.wordpress.org/wc-frontend-manager/tags/6.5.10/views/products-manager/wcfm-view-products-manage-tabs.php
	 *
	 * @todo    [now] (dev) do we need `esc_html` everywhere, e.g. in `hints`? (same for `dokan_add_ean_field()`)
	 * @todo    [next] (dev) variable products?
	 * @todo    [next] (feature) optional EAN validation
	 */
	function wcfm_add_ean_field( $fields, $product_id, $product_type ) {
		$_fields  = array();
		$is_added = false;
		$_key     = 'wcfm_' . alg_wc_ean()->core->ean_key;
		$_field   = array(
			'label'       => esc_html( get_option( 'alg_wc_ean_wcfm_title', __( 'EAN', 'ean-for-woocommerce' ) ) ),
			'type'        => 'text',
			'class'       => 'wcfm-text',
			'label_class' => 'wcfm_title',
			'value'       => alg_wc_ean()->core->get_ean( $product_id ),
			'hints'       => esc_html( get_option( 'alg_wc_ean_wcfm_hints', __( 'The International Article Number (also known as European Article Number or EAN) is a standard describing a barcode symbology and numbering system used in global trade to identify a specific retail product type, in a specific packaging configuration, from a specific manufacturer.', 'ean-for-woocommerce' ) ) ),
			'placeholder' => esc_html( get_option( 'alg_wc_ean_wcfm_placeholder', __( 'Product EAN...', 'ean-for-woocommerce' ) ) ),
		);
		foreach ( $fields as $key => $field ) {
			$_fields[ $key ] = $field;
			if ( 'sku' === $key ) {
				$_fields[ $_key ] = $_field;
				$is_added         = true;
			}
		}
		if ( ! $is_added ) {
			$_fields[ $_key ] = $_field; // fallback
		}
		return $_fields;
	}

	/**
	 * dokan_add_ean_field_variation.
	 *
	 * @version 3.1.2
	 * @since   3.1.2
	 */
	function dokan_add_ean_field_variation( $loop, $variation_data, $variation ) {
		$key         = alg_wc_ean()->core->ean_key;
		$id          = "variable{$key}_{$loop}";
		$name        = "variable{$key}[{$loop}]";
		$value       = alg_wc_ean()->core->get_ean( $variation->ID );
		$title       = esc_html( get_option( 'alg_wc_ean_dokan_title', __( 'EAN', 'ean-for-woocommerce' ) ) );
		$placeholder = alg_wc_ean()->core->get_ean( $variation->post_parent );
		echo '<div class="dokan-form-group">' .
			'<label for="' . $id . '" class="form-label">' . $title . '</label>' .
			'<input type="text" name="' . $name . '" id="' . $id . '" class="dokan-form-control alg-wc-ean" placeholder="' . $placeholder . '" value="' . $value . '">' .
		'</div>';
	}

	/**
	 * dokan_save_ean_field.
	 *
	 * @version 2.2.2
	 * @since   2.2.2
	 *
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Dashboard/Templates/Products.php#L353
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Dashboard/Templates/Products.php#L482
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Product/functions.php#L127
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/Product/functions.php#L129
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/includes/REST/Manager.php#L172
	 */
	function dokan_save_ean_field( $product_id, $data ) {
		$id = 'dokan_' . alg_wc_ean()->core->ean_key;
		if ( isset( $data[ $id ] ) ) {
			update_post_meta( $product_id, alg_wc_ean()->core->ean_key, wc_clean( $data[ $id ] ) );
		}
	}

	/**
	 * dokan_add_ean_field.
	 *
	 * @version 2.2.2
	 * @since   2.2.2
	 *
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/templates/products/new-product.php#L257
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/templates/products/tmpl-add-product-popup.php#L148
	 * @see     https://github.com/weDevsOfficial/dokan/blob/v3.2.8/templates/products/new-product-single.php#L338
	 *
	 * @todo    [next] (feature) optional EAN validation
	 */
	function dokan_add_ean_field( $post = false, $post_id = false ) {
		$id          = 'dokan_' . alg_wc_ean()->core->ean_key;
		$value       = ( ! empty( $post_id ) ? alg_wc_ean()->core->get_ean( $post_id ) : ( isset( $_REQUEST[ $id ] ) ? esc_html( wc_clean( $_REQUEST[ $id ] ) ) : '' ) ); // Edit product vs Add product
		$title       = esc_html( get_option( 'alg_wc_ean_dokan_title', __( 'EAN', 'ean-for-woocommerce' ) ) );
		$placeholder = esc_html( get_option( 'alg_wc_ean_dokan_placeholder', __( 'Product EAN...', 'ean-for-woocommerce' ) ) );
		echo '<div class="dokan-form-group">' .
			'<label for="' . $id . '" class="form-label">' . $title . '</label>' .
			'<input type="text" name="' . $id . '" id="' . $id . '" class="dokan-form-control alg-wc-ean" placeholder="' . $placeholder . '" value="' . $value . '">' .
		'</div>';
	}

	/**
	 * wc_pos_scanning_fields.
	 *
	 * Adds "EAN" field to the "Scanning Fields" option in "Point of Sale > Settings > Register".
	 *
	 * @version 2.2.0
	 * @since   2.2.0
	 *
	 * @see     https://woocommerce.com/products/point-of-sale-for-woocommerce/
	 */
	function wc_pos_scanning_fields( $fields ) {
		$fields[ alg_wc_ean()->core->ean_key ] = __( 'EAN', 'ean-for-woocommerce' );
		return $fields;
	}

	/**
	 * wc_pos_add_ean_to_product_name.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 *
	 * @see     https://woocommerce.com/products/point-of-sale-for-woocommerce/
	 *
	 * @todo    [now] [!!] (dev) `get_route()`: better solution, e.g. exact match with `/wc-pos/products`?
	 * @todo    [now] [!!] (dev) find better solution, e.g. add elsewhere, not to the name?
	 */
	function wc_pos_add_ean_to_product_name( $response, $product, $request ) {
		if ( ( false !== strpos( $request->get_route(), '/wc-pos/' ) ) && '' !== ( $ean = alg_wc_ean()->core->get_ean( $product->get_id() ) ) ) {
			$response->data['name'] .= ' (' . sprintf( __( 'EAN: %s', 'ean-for-woocommerce' ), $ean ) . ')';
		}
		return $response;
	}

}

endif;

return new Alg_WC_EAN_Compatibility();
