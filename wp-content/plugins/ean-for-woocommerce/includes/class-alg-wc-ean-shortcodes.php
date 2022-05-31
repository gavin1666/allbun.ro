<?php
/**
 * EAN for WooCommerce - Shortcodes Class
 *
 * @version 3.6.0
 * @since   3.5.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Shortcodes' ) ) :

class Alg_WC_EAN_Shortcodes {

	/**
	 * Constructor.
	 *
	 * @version 3.6.0
	 * @since   3.5.0
	 *
	 * @todo    [maybe] (feature) add `[alg_wc_ean_type]` shortcode?
	 */
	function __construct() {
		$this->data = array();
		// Shortcodes
		add_shortcode( 'alg_wc_ean',                  array( $this, 'ean_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_attr',     array( $this, 'product_attr_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_image',    array( $this, 'product_image_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_name',     array( $this, 'product_name_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_sku',      array( $this, 'product_sku_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_price',    array( $this, 'product_price_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_id',       array( $this, 'product_id_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_meta',     array( $this, 'product_meta_shortcode' ) );
		add_shortcode( 'alg_wc_ean_product_function', array( $this, 'product_function_shortcode' ) );
	}

	/**
	 * do_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.1.2
	 *
	 * @todo    [maybe] (dev) add `set_data()`, `reset_data()` functions?
	 */
	function do_shortcode( $content, $data = array() ) {
		if ( ! empty( $data ) ) {
			$this->data = $data;
		}
		$result = do_shortcode( $content );
		if ( ! empty( $data ) ) {
			$this->data = array();
		}
		return $result;
	}

	/**
	 * output.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function output( $result, $atts ) {
		return ( '' === $result ? $atts['on_empty'] : ( $atts['before'] . $result . $atts['after'] ) );
	}

	/**
	 * get_shortcode_att.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function get_shortcode_att( $att, $atts, $default = '' ) {
		return ( ! empty( $atts[ $att ] ) ? $atts[ $att ] : ( ! empty( $this->data[ $att ] ) ? $this->data[ $att ] : $default ) );
	}

	/**
	 * get_products.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function get_products( $atts, $default = false ) {
		$product_id  = $this->get_shortcode_att( 'product_id', $atts, $default );
		$product_ids = array( $product_id );
		if ( filter_var( $atts['children'], FILTER_VALIDATE_BOOLEAN ) && ( $product = wc_get_product( $product_id ) ) ) {
			$product_ids = array_merge( $product_ids, $product->get_children() );
		}
		return $product_ids;
	}

	/**
	 * product_function_shortcode.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function product_function_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'name'       => '',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_function' );

		// Check the required atts
		if ( '' === $atts['name'] || ! ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			return '';
		}

		// Product ID
		if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
			$product_id = $product_parent_id;
		}

		// Check if function exists
		if ( ! ( $product = wc_get_product( $product_id ) ) || ! is_callable( array( $product, $atts['name'] ) ) ) {
			return '';
		}

		// Result
		$result = $product->{$atts['name']}();

		return $this->output( $result, $atts );
	}

	/**
	 * product_meta_shortcode.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function product_meta_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'key'        => '',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_meta' );

		// Check the required atts
		if ( '' === $atts['key'] || ! ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			return '';
		}

		// Product ID
		if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
			$product_id = $product_parent_id;
		}

		// Result
		$result = get_post_meta( $product_id, $atts['key'], true );

		return $this->output( $result, $atts );
	}

	/**
	 * product_image_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function product_image_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'output'     => 'img',
			'size'       => 'woocommerce_thumbnail',
			'width'      => 30, // px // for `output = img`
			'height'     => 30, // px // for `output = img`
			'is_in_pdf'  => false,    // for `output = img`
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_image' );

		// Result
		$result = '';
		if (
			( 'url' === $atts['output'] || ! $this->get_shortcode_att( 'is_in_pdf', $atts, false ) || function_exists( 'curl_init' ) ) &&
			( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) )
		) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( $product = wc_get_product( $product_id ) ) {
				if ( ( $img_id = $product->get_image_id() ) && ( $img_url = wp_get_attachment_image_src( $img_id, $atts['size'] ) ) ) {
					$img_url = $img_url[0];
				}
				if ( ! $img_url ) {
					$img_url = wc_placeholder_img_src( $atts['size'] );
				}
				$img_url = esc_url( $img_url );
				$result = ( 'url' === $atts['output'] ? $img_url : '<img src="' . $img_url . '" width="' . $atts['width'] . '" height="' . $atts['height'] . '">' );
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_name_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function product_name_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'type'       => 'name',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_name' );

		// Result
		$result = '';
		if ( ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( 'name' === $atts['type'] ) {
				if ( ( $product = wc_get_product( $product_id ) ) ) {
					$result = $product->get_formatted_name();
				}
			} else { // 'title'
				$result = get_the_title( $product_id );
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_sku_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function product_sku_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_sku' );

		// Result
		$result = '';
		if ( ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( ( $product = wc_get_product( $product_id ) ) ) {
				$result = $product->get_sku();
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_price_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 *
	 * @todo    [now] [!] (dev) `sale`: do we really need to use `get_regular_price()` as a fallback?
	 * @todo    [now] [!] (desc) `sale` and `regular` prices won't work for a) variable products, b) parent of a variation
	 */
	function product_price_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'raw'        => 'no',
			'type'       => 'final',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_price' );

		// Result
		$result = '';
		if ( ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( ( $product = wc_get_product( $product_id ) ) ) {
				switch ( $atts['type'] ) {
					case 'final':
						$result = ( 'yes' === $atts['raw'] ? $product->get_price() : $product->get_price_html() );
						break;
					case 'regular':
						$result = ( 'yes' === $atts['raw'] ? $product->get_regular_price() : wc_price( $product->get_regular_price() ) );
						break;
					case 'sale':
						$price  = ( '' === ( $sale = $product->get_sale_price() ) ? $product->get_regular_price() : $sale );
						$result = ( 'yes' === $atts['raw'] ? $price : wc_price( $price ) );
						break;
				}
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * product_id_shortcode.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function product_id_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_id' );

		// Result
		$product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() );
		$result     = ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ? $product_parent_id : $product_id );

		return $this->output( $result, $atts );
	}

	/**
	 * product_attr_shortcode.
	 *
	 * @version 3.5.0
	 * @since   2.7.0
	 *
	 * @todo    [now] [!!] (feature) multiple attributes (comma-separated)
	 * @todo    [now] [!!] (feature) all attributes at once (maybe use `WC_Product::get_attributes()`?
	 * @todo    [now] [!!] (feature) all attributes starting with X
	 */
	function product_attr_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'parent'     => 'no',
			'attr'       => '',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean_product_attr' );

		// Result
		$result = '';
		if ( '' !== $atts['attr'] && ( $product_id = $this->get_shortcode_att( 'product_id', $atts, get_the_ID() ) ) ) {
			if ( 'yes' === $atts['parent'] && 0 != ( $product_parent_id = wp_get_post_parent_id ( $product_id ) ) ) {
				$product_id = $product_parent_id;
			}
			if ( ( $product = wc_get_product( $product_id ) ) && is_callable( array( $product, 'get_attribute' ) ) ) {
				$result = $product->get_attribute( $atts['attr'] );
			}
		}

		return $this->output( $result, $atts );
	}

	/**
	 * ean_shortcode.
	 *
	 * @version 3.6.0
	 * @since   1.5.1
	 *
	 * @todo    [now] [!!] (feature) `parent`? (same for barcodes)
	 * @todo    [maybe] (dev) check if valid?
	 * @todo    [maybe] (feature) add `children` attribute to all shortcodes, e.g. `product_attr_shortcode()`
	 */
	function ean_shortcode( $atts, $content = '' ) {

		// Atts
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
			'on_empty'   => '',
			'children'   => 'no',
			'glue'       => ', ', // for `children = yes`
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean' );

		// Products
		$product_ids = $this->get_products( $atts );

		// Result
		$result = array();
		foreach ( $product_ids as $product_id ) {
			$result[] = alg_wc_ean()->core->get_ean( $product_id );
		}
		$result = implode( $atts['glue'], $result );

		return $this->output( $result, $atts );
	}

}

endif;

return new Alg_WC_EAN_Shortcodes();
