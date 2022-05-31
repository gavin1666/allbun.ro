<?php
/**
 * EAN for WooCommerce - Order Tools Class
 *
 * @version 3.9.0
 * @since   3.9.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Order_Tools' ) ) :

class Alg_WC_EAN_Order_Tools {

	/**
	 * Constructor.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	function __construct() {
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'orders_add' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'orders_delete' ) );
	}

	/**
	 * get_orders.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
	 */
	function get_orders() {
		return wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) );
	}

	/**
	 * orders_delete.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 */
	function orders_delete() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_orders_delete', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_orders_delete', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_orders() as $order_id ) {
					$order = wc_get_order( $order_id );
					if ( ! $order ) {
						continue;
					}
					foreach ( $order->get_items() as $item_id => $item ) {
						if ( wc_delete_order_item_meta( $item_id, alg_wc_ean()->core->ean_key ) ) {
							$count++;
						}
					}
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN deleted for %s order items.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

	/**
	 * orders_add.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 */
	function orders_add() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_orders_add', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_orders_add', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_orders() as $order_id ) {
					$count += alg_wc_ean()->core->orders->add_ean_to_order_items_meta( $order_id );
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN added for %s order items.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

}

endif;

return new Alg_WC_EAN_Order_Tools();
