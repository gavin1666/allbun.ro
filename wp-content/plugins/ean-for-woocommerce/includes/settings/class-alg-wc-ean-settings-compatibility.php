<?php
/**
 * EAN for WooCommerce - Compatibility Section Settings
 *
 * @version 3.8.0
 * @since   2.2.9
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Compatibility' ) ) :

class Alg_WC_EAN_Settings_Compatibility extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.2.9
	 * @since   2.2.9
	 */
	function __construct() {
		$this->id   = 'compatibility';
		$this->desc = __( 'Compatibility', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.8.0
	 * @since   2.2.9
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Plugin Compatibility Options', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_plugin_compatibility_options',
			),
			array(
				'title'    => __( 'Point of Sale for WooCommerce', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will add EAN to the product search of the %s plugin.', 'ean-for-woocommerce' ),
						'<a target="_blank" href="' . 'https://woocommerce.com/products/point-of-sale-for-woocommerce/' . '">' .
							__( 'Point of Sale for WooCommerce', 'ean-for-woocommerce' ) . '</a>' ) . '<br>' .
					__( '<strong>Please note</strong> that "WooCommerce > Settings > EAN > General > Search" option must be enabled as well.', 'ean-for-woocommerce' ) . '<br>' .
					__( '<strong>Alternatively</strong> you can add "EAN" to the "Scanning Fields" and "Product SKU" to the "Search Includes" options in "Point of Sale > Settings > Register".', 'ean-for-woocommerce' ) . '<br>' .
					'* ' . __( 'To enable searching with a scanner, add "EAN" field to the "Scanning Fields" option in "Point of Sale > Settings > Register".', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wc_pos_search',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Print Invoice & Delivery Notes for WooCommerce', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will show EAN in PDF documents of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="' . 'https://wordpress.org/plugins/woocommerce-delivery-notes/' . '">' .
						__( 'Print Invoice & Delivery Notes for WooCommerce', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_ean_wcdn',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'WooCommerce PDF Invoices & Packing Slips', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will show EAN in PDF documents of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="' . 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/' . '">' .
						__( 'WooCommerce PDF Invoices & Packing Slips', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_ean_wpo_wcpdf',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Position', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wpo_wcpdf_position',
				'default'  => 'wpo_wcpdf_after_item_meta',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'wpo_wcpdf_before_item_meta' => __( 'Before item meta', 'ean-for-woocommerce' ),
					'wpo_wcpdf_after_item_meta'  => __( 'After item meta', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will show EAN in PDF documents of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="' . 'https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/' . '">' .
						__( 'WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_ean_wt_pklist',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Content', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wt_pklist_options[content]',
				'default'  => '<p>EAN: [alg_wc_ean]</p>',
				'type'     => 'textarea',
			),
			array(
				'desc'     => __( 'Position', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wt_pklist_options[position]',
				'default'  => 'after_product_meta',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'before_product_name' => __( 'Before item name', 'ean-for-woocommerce' ),
					'after_product_name'  => __( 'After item name', 'ean-for-woocommerce' ),
					'before_product_meta' => __( 'Before item meta', 'ean-for-woocommerce' ),
					'after_product_meta'  => __( 'After item meta', 'ean-for-woocommerce' ),
					'column'              => __( 'As a separate column', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Documents', 'ean-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Can be a comma-separated list, e.g.: %s.', 'ean-for-woocommerce' ),
						'<code>' . implode( ',', array( 'invoice', 'packinglist', 'deliverynote', 'dispatchlabel' ) ) . '</code>' ),
				'desc_tip' => __( 'Leave empty to include in all documents.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wt_pklist_options[documents]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Column title', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Used only if the "Position" option is set to the "As a separate column".', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wt_pklist_options[column_title]',
				'default'  => __( 'EAN', 'ean-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'desc'     => sprintf( __( 'Column HTML %s', 'ean-for-woocommerce' ), '<code>class</code>' ),
				'desc_tip' => __( 'Used only if the "Position" option is set to the "As a separate column".', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wt_pklist_options[column_class]',
				'default'  => 'wfte_product_table_head_ean wfte_text_center',
				'type'     => 'text',
			),
			array(
				'desc'     => sprintf( __( 'Column HTML %s', 'ean-for-woocommerce' ), '<code>style</code>' ),
				'desc_tip' => __( 'Used only if the "Position" option is set to the "As a separate column".', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wt_pklist_options[column_style]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'WooCommerce Customer / Order / Coupon Export', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will add %s column to the order items export of the %s plugin.', 'ean-for-woocommerce' ),
					'<code>item_ean</code>', '<a target="_blank" href="https://woocommerce.com/products/ordercustomer-csv-export/">' . __( 'WooCommerce Customer / Order / Coupon Export', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_ean_wc_customer_order_export',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Dokan', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will show EAN in vendor product form of the %s plugin.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="https://wordpress.org/plugins/dokan-lite/">' . __( 'Dokan', 'ean-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_ean_dokan',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Title', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_dokan_title',
				'default'  => __( 'EAN', 'ean-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Placeholder', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_dokan_placeholder',
				'default'  => __( 'Product EAN...', 'ean-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'WCFM', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'This will show EAN in product forms of the %s and %s plugins.', 'ean-for-woocommerce' ),
					'<a target="_blank" href="https://wordpress.org/plugins/wc-frontend-manager/">' . __( 'WCFM', 'ean-for-woocommerce' ) . '</a>',
					'<a target="_blank" href="https://wordpress.org/plugins/wc-multivendor-marketplace/">' . __( 'WCFM Marketplace', 'ean-for-woocommerce' ) . '</a>' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wcfm',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Title', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wcfm_title',
				'default'  => __( 'EAN', 'ean-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Placeholder', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wcfm_placeholder',
				'default'  => __( 'Product EAN...', 'ean-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Hints', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_wcfm_hints',
				'default'  => __( 'The International Article Number (also known as European Article Number or EAN) is a standard describing a barcode symbology and numbering system used in global trade to identify a specific retail product type, in a specific packaging configuration, from a specific manufacturer.', 'ean-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'height:110px;',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_plugin_compatibility_options',
			),
		);
	}

}

endif;

return new Alg_WC_EAN_Settings_Compatibility();
