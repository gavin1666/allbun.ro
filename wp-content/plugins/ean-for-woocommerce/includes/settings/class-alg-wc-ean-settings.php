<?php
/**
 * EAN for WooCommerce - Settings
 *
 * @version 3.8.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings' ) ) :

class Alg_WC_EAN_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 3.8.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id    = 'alg_wc_ean';
		$this->label = apply_filters( 'alg_wc_ean_settings_page_label', __( 'EAN', 'ean-for-woocommerce' ) );
		parent::__construct();
		// Sections
		require_once( 'class-alg-wc-ean-settings-section.php' );
		require_once( 'class-alg-wc-ean-settings-general.php' );
		require_once( 'class-alg-wc-ean-settings-tools.php' );
		require_once( 'class-alg-wc-ean-settings-compatibility.php' );
		require_once( 'class-alg-wc-ean-settings-barcodes.php' );
		require_once( 'class-alg-wc-ean-settings-barcodes-compatibility.php' );
		new Alg_WC_EAN_Settings_Barcodes( '1d' );
		new Alg_WC_EAN_Settings_Barcodes_Compatibility( '1d' );
		new Alg_WC_EAN_Settings_Barcodes( '2d' );
		new Alg_WC_EAN_Settings_Barcodes_Compatibility( '2d' );
		require_once( 'class-alg-wc-ean-settings-print.php' );
		require_once( 'class-alg-wc-ean-settings-advanced.php' );
		// Custom fields
		add_action( 'woocommerce_admin_field_' . 'alg_wc_ean_file', array( $this, 'alg_wc_ean_file' ) );
	}

	/**
	 * alg_wc_ean_file.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.1.1/plugins/woocommerce/includes/admin/class-wc-admin-settings.php#L720
	 */
	function alg_wc_ean_file( $value ) {

		// Custom attribute handling.
		$custom_attributes = array();
		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		// Description handling.
		$field_description = WC_Admin_Settings::get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];

		?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp forminp-file">
				<input
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="file"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value=""
					class="<?php echo esc_attr( $value['class'] ); ?>"
					placeholder=""
					<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
					/><?php echo esc_html( $value['suffix'] ); ?> <?php echo $description; // WPCS: XSS ok. ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * get_settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;
		return array_merge( apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ), array(
			array(
				'title'     => __( 'Reset Settings', 'ean-for-woocommerce' ),
				'type'      => 'title',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
			array(
				'title'     => __( 'Reset section settings', 'ean-for-woocommerce' ),
				'desc'      => '<strong>' . __( 'Reset', 'ean-for-woocommerce' ) . '</strong>',
				'desc_tip'  => __( 'Check the box and save changes to reset.', 'ean-for-woocommerce' ),
				'id'        => $this->id . '_' . $current_section . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
		) );
	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function maybe_reset_settings() {
		global $current_section;
		if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
			foreach ( $this->get_settings() as $value ) {
				if ( isset( $value['id'] ) ) {
					$id = explode( '[', $value['id'] );
					delete_option( $id[0] );
				}
			}
			add_action( 'admin_notices', array( $this, 'admin_notices_settings_reset_success' ), PHP_INT_MAX );
		}
	}

	/**
	 * admin_notices_settings_reset_success.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function admin_notices_settings_reset_success() {
		echo '<div class="notice notice-success is-dismissible"><p><strong>' .
			__( 'Your settings have been reset.', 'ean-for-woocommerce' ) . '</strong></p></div>';
	}

	/**
	 * save.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	function save() {
		parent::save();
		$this->maybe_reset_settings();
		global $current_section;
		do_action( 'alg_wc_ean_settings_saved', $current_section );
	}

}

endif;

return new Alg_WC_EAN_Settings();
