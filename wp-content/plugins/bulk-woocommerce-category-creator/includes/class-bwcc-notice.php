<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bwcc_Notice' ) ) {

	/**
	 * Bwcc Notice Class
	 *
	 * @class BWwcc Notice Class
	 */
	class Bwcc_Notice {

		/**
		 * Default constructor
		 *
		 * @since 1.4
		 */
		public function __construct() {
			register_activation_hook( __FILE__, array( $this, 'bwcc_set_activation_date' ) );
			add_action( 'admin_init', 			array( $this, 'bwcc_check_installation_date' ) );
			add_action( 'admin_init', 			array( $this, 'bwcc_notice_dismissed' ), 5 );
		}

		/**
		 * Get the current time and set it as an option when the plugin is activated.
		 *
		 * @link   https://www.winwar.co.uk/2014/10/ask-wordpress-plugin-reviews-week/?utm_source=codesnippet
		 *
		 * @return null
		 */
		public static function bwcc_set_activation_date() {
			$now = strtotime( "now" );
			add_option( 'bwcc_activation_date', $now );
		}

		/**
		 * Check date on admin initiation and add to admin notice if it was over 10 days ago.
		 *
		 * @link   https://www.winwar.co.uk/2014/10/ask-wordpress-plugin-reviews-week/?utm_source=codesnippet
		 *
		 *
		 */
		public static function bwcc_check_installation_date() {

			$install_date = get_option( 'bwcc_activation_date' );
			$past_date    = strtotime( '-7 days' );
			$closed       = get_option( 'bwcc_notice_dismissed' );
			if ( ! $closed && $install_date && $past_date >= $install_date ) {
				add_action( 'admin_notices', array( 'Bwcc_Notice', 'bwcc_display_admin_notice' ) );
			}
		}

		/**
		 * Display Admin Notice, asking for a review
		 */
		public static function bwcc_display_admin_notice() {

			$reviewurl    = 'https://wordpress.org/plugins/bulk-woocommerce-category-creator/#reviews';
			$bwcc_dismiss = get_admin_url() . '?bwcc_notice_dismissed=1';
			echo '<div class="updated">';
			printf( __( '<p>If you enjoy using <b>Bulk WooCommerce Category Creator</b>, would you mind taking a moment to rate it? It won\'t take more than a minute. Thanks for your support! <br><br> <a href="%s" class="button-primary button-large" target="_blank">Leave A Review</a> &nbsp;<a href="%s" class="">No Thanks</a></p>' ), $reviewurl, $bwcc_dismiss );
			echo '</div>';
		}

		/**
		 * Setting flag is user clicks on No Thanks button
		 */
		public static function bwcc_notice_dismissed() {

			$nobug = "";
			if ( isset( $_GET['bwcc_notice_dismissed'] ) ) {
				$nobug = esc_attr( $_GET['bwcc_notice_dismissed'] );
			}
			if ( 1 == $nobug ) {
				add_option( 'bwcc_notice_dismissed', TRUE );
			}
		}
	}
	$bwcc_notice = new Bwcc_Notice();
}
