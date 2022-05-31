<?php
/**
 * Plugin Name: Bulk WooCommerce Category Creator
 * Plugin URI: https://kartechify.com/product/bulk-woocommerce-category-creator/
 * Description: Plugin to create multiple WooCommerce categories in one go. You can create categories in Parent>>Child hierarchy. It also lets you assign the created categories to the selected products.
 * Version: 2.2
 * Author: Kartik Parmar
 * Author URI: https://twitter.com/kartikparmar19
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 6.0
 * License: GPL2
 *
 * @package  BWCC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'bwcc_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 */
	function bwcc_fs() {
		global $bwcc_fs;

		if ( ! isset( $bwcc_fs ) ) {
			// Include Freemius SDK.
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			$bwcc_fs = fs_dynamic_init(
				array(
					'id'             => '6018',
					'slug'           => 'bulk-woocommerce-category-creator',
					'type'           => 'plugin',
					'public_key'     => 'pk_b469d3947356eb090f7226290db7b',
					'is_premium'     => false,
					'has_addons'     => false,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'    => 'bulk_woocommerce_category_creator',
						'account' => false,
						'contact' => false,
						'parent'  => array(
							'slug' => 'edit.php?post_type=product',
						),
					),
				)
			);
		}

		return $bwcc_fs;
	}

	// Init Freemius.
	bwcc_fs();
	// Signal that SDK was initiated.
	do_action( 'bwcc_fs_loaded' );
}

if ( ! class_exists( 'BWCC_Bulk_WooCommerce_Category_Creator' ) ) {

	/**
	 * BWCC_Bulk_WooCommerce_Category_Creator class
	 */
	class BWCC_Bulk_WooCommerce_Category_Creator {

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'admin_init', array( &$this, 'bwcc_check_compatibility' ) );
			add_action( 'admin_menu', array( $this, 'bwcc_category_creator_menu' ) );
			add_action( 'init', array( &$this, 'bwcc_include_file' ), 5 );
			add_action( 'admin_init', array( &$this, 'bwcc_include_file' ) );

			// Language Translation.
			add_action( 'init', array( &$this, 'bwcc_update_po_file' ) );

			// Settings link on plugins page.
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'bwcc_plugin_add_category_link' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'bwcc_enqueue_scripts' ) );
		}

		/**
		 * Including files
		 *
		 * @since 1.4
		 */
		public static function bwcc_include_file() {
			include_once 'includes/class-bwcc-notice.php';
		}

		/**
		 * Adding Add Category link to plugin meta.
		 *
		 * @param string $links Link of the Plugin settings.
		 * @since 1.5
		 */
		public function bwcc_plugin_add_category_link( $links ) {
			$setting_link['add_categories'] = '<a href="' . esc_url( get_admin_url( null, 'edit.php?post_type=product&page=bulk_woocommerce_category_creator' ) ) . '">Add Categories</a>';
			$links                          = $setting_link + $links;
			return $links;
		}

		/**
		 * Including JS and CSS files
		 *
		 * @since 1.5
		 */
		public function bwcc_enqueue_scripts() {

			wp_enqueue_style(
				'bwcc-woocommerce_admin_styles',
				plugins_url() . '/woocommerce/assets/css/admin.css',
				'',
				'2.2',
				false
			);

			wp_register_script(
				'select2',
				plugins_url() . '/woocommerce/assets/js/select2/select2.min.js',
				array( 'jquery', 'jquery-ui-widget', 'jquery-ui-core' ),
				'2.2',
				false
			);

			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'bulk-wc-category-creator', plugins_url() . '/bulk-woocommerce-category-creator/js/bulk-wc-category-creator.js', array( 'jquery', 'select2' ) );
		}

		/**
		 * Ensure that the plugin is deactivated when WooCommerce is deactivated.
		 *
		 * @since 1.0
		 */
		public static function bwcc_check_compatibility() {

			if ( ! self::bwcc_check_woo_installed() ) {

				if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
					deactivate_plugins( plugin_basename( __FILE__ ) );

					add_action( 'admin_notices', array( 'BWCC_Bulk_WooCommerce_Category_Creator', 'bwcc_disabled_notice' ) );
					if ( isset( $_GET['activate'] ) ) {
						unset( $_GET['activate'] );
					}
				}
			}
		}

		/**
		 * Check if WooCommerce is active.
		 *
		 * @since 1.0
		 * @return boolean tru if WooCommerce is active else false
		 */
		public static function bwcc_check_woo_installed() {

			if ( class_exists( 'WooCommerce' ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Display a notice in the admin Plugins page if this plugin is activated while WooCommerce is deactivated.
		 *
		 * @since 1.0
		 */
		public static function bwcc_disabled_notice() {

			$class   = 'notice notice-error';
			$message = __( 'Bulk WooCommerce Category Creator requires WooCommerce installed and activate.', 'bwcc-bulk-woocommerce-categor-creator' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}

		/**
		 * Adds Bulk WooCommerce Category Creator menu under Product Menu
		 *
		 * @since 1.0
		 */
		public static function bwcc_category_creator_menu() {

			add_submenu_page(
				'edit.php?post_type=product',
				__( 'Bulk WooCommerce Category Creator Page', 'bwcc-bulk-woocommerce-categor-creator' ),
				__( 'Bulk Category Creator', 'bwcc-bulk-woocommerce-categor-creator' ),
				'manage_product_terms',
				'bulk_woocommerce_category_creator',
				array( 'BWCC_Bulk_WooCommerce_Category_Creator', 'bwcc_category_settings_page' )
			);

			add_action( 'admin_init', array( 'BWCC_Bulk_WooCommerce_Category_Creator', 'bwcc_register_plugin_settings' ) );
		}

		/**
		 * Language Translation
		 *
		 * @since 1.0
		 */
		public static function bwcc_update_po_file() {

			$domain = 'bwcc-bulk-woocommerce-categor-creator';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			if ( $loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' ) ) {
				return $loaded;
			} else {
				load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/i18n/languages/' );
			}
		}

		/**
		 * Registering the settings
		 *
		 * @since 1.0
		 */
		public static function bwcc_register_plugin_settings() {

			// register our settings.
			register_setting( 'bwcc-bulk-category-creator-group', 'options_textarea' );

			self::bwcc_create_categories();
		}

		/**
		 * Check for the added categoies and based on that create categories
		 *
		 * @since 1.0
		 */
		public static function bwcc_create_categories() {

			if ( isset( $_POST['bwcc_options_textarea'] ) ) {

				$all_bookable_products = isset( $_POST['custId'] ) ? sanitize_text_field( wp_unslash( $_POST['custId'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$selected_products     = isset( $_POST['bwcc_products'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bwcc_products'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

				if ( ! empty( $selected_products ) && in_array( 'all_products', $selected_products, true ) ) { // If all product is selected then get all product ids.
					$all_product_ids   = $all_bookable_products;
					$selected_products = explode( ',', $all_product_ids );
				}

				$returned_str = sanitize_textarea_field( $_POST['bwcc_options_textarea'] );
				$parent_id    = ( isset( $_POST['bwcc_parent'] ) && '' !== $_POST['bwcc_parent'] ) ? sanitize_text_field( $_POST['bwcc_parent'] ) : 0;
				$description  = ( isset( $_POST['bwcc_description_textarea'] ) && '' !== $_POST['bwcc_description_textarea'] ) ? sanitize_textarea_field( $_POST['bwcc_description_textarea'] ) : '';
				$display_type = ( isset( $_POST['bwcc_display_type'] ) && '' !== $_POST['bwcc_display_type'] ) ? sanitize_text_field( $_POST['bwcc_display_type'] ) : '';

				if ( '' !== $returned_str ) {

					$trimmed          = trim( $returned_str );
					$separator        = apply_filters( 'bwcc_separator', ',' );
					$categories_array = explode( $separator, $trimmed );
					$term_ids         = array();
					foreach ( $categories_array as $key => $value ) {

						$term = term_exists( $value, 'category' );

						if ( $term == 0 || is_null( $term ) ) {
							$term_id = BWCC_Bulk_WooCommerce_Category_Creator::bwcc_create_category( $value, $parent_id, $description, $display_type );
							if ( $term_id ) {
								$term_ids = array_merge( $term_ids, $term_id );
							}
						}
					}

					/**
					 * Setting category to product
					 */
					if ( count( $selected_products ) > 0 ) {
						foreach ( $selected_products as $key => $value ) {
							$product_term_ids = array();
							$terms            = wp_get_object_terms( $value, 'product_cat' );

							if ( count( $terms ) > 0 ) {
								foreach ( $terms as $item ) {
									$product_term_ids[] = $item->term_id;
								}
								$final_term_ids = array_merge( $product_term_ids, $term_ids );
							} else {
								$final_term_ids = $term_ids;
							}
							wp_set_object_terms( $value, $final_term_ids, 'product_cat' );
						}
					}
				} else {
					add_action( 'admin_notices', array( 'BWCC_Bulk_WooCommerce_Category_Creator', 'bwcc_admin_notice_error' ) );
				}
			}
		}

		/**
		 * Success notice
		 *
		 * @since 1.0
		 */
		public static function bwcc_admin_notice_success() {
			?>
			<div class="notice notice-success">
				<p><?php esc_html_e( 'Categories are created!', 'bwcc-bulk-woocommerce-categor-creator' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Error notice
		 *
		 * @since 1.0
		 */
		public static function bwcc_admin_notice_error() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'You have not entered anything into the category textbox.', 'bwcc-bulk-woocommerce-categor-creator' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Create WooCommerce category
		 *
		 * @param string $name string of the categories.
		 * @param int    $parent_id Parent ID of the Category.
		 * @param string $description Description.
		 * @param string $display_type Display Type.
		 * @since 1.0
		 */
		public static function bwcc_create_category( $name, $parent_id, $description, $display_type ) {

			$names          = explode( '>>', $name );
			$term_ids       = array();
			$required_msg   = false;
			$already_exists = false;
			$created        = false;

			if ( -1 == $parent_id ) {
				$parent_id = 0;
			}

			foreach ( $names as $key => $value ) {

				$get_term_by = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'name'       => $value,
						'parent'     => $parent_id,
						'hide_empty' => false,
					)
				);

				$parent_category_slug = '';
				$add                  = false;
				if ( empty( $get_term_by ) ) { // Category nathi present.
					$add = true;
				} else if ( ! empty( $get_term_by ) && $parent_id != $get_term_by[0]->parent ) {
					$add                  = true;
					$parent_category_slug = '-' . $get_term_by->slug;
				}

				if ( $add ) {

					$category_name = trim( $value );
					$description   = trim( $description );
					$category_slug = str_replace( '  ', '-', $category_name );

					$insert = wp_insert_term(
						$category_name,
						'product_cat',
						array(
							'description' => $description,
							'slug'        => $category_slug . $parent_category_slug,
							'parent'      => $parent_id,
							'display'     => $display_type,
						)
					);

					if ( ! is_wp_error( $insert ) ) {
						$id = $insert['term_id'];
						update_term_meta( $id, 'display_type', $display_type );
						$parent_id  = $id;
						$term_ids[] = $id;
						$created    = true;
					} else {
						$parent_id  = $insert->error_data['term_exists'];
						$term_ids[] = $parent_id;

						$msg = $insert->get_error_message();

						if ( ! $required_msg && 'A name is required for this term.' === $msg ) {
							$required_msg = true;
						}

						if ( ! $already_exists && 'A term with the name provided already exists with this parent.' === $msg ) {
							$already_exists = true;
						}
					}
				} else {
					$term_ids[]     = $get_term_by[0]->term_id;
					$parent_id      = $get_term_by[0]->term_id;
					$already_exists = true;
				}
			}

			if ( $required_msg ) {
				add_action( 'admin_notices', array( 'BWCC_Bulk_WooCommerce_Category_Creator', 'bwcc_admin_notice_name_required' ) );
			}

			if ( $already_exists ) {
				add_action( 'admin_notices', array( 'BWCC_Bulk_WooCommerce_Category_Creator', 'bwcc_admin_notice_already_exists' ) );
			}

			if ( $created ) {
				add_action( 'admin_notices', array( 'BWCC_Bulk_WooCommerce_Category_Creator', 'bwcc_admin_notice_success' ) );
			}

			return $term_ids;
		}

		/**
		 * Error notice when category string is empty
		 *
		 * @since 1.5
		 */
		public static function bwcc_admin_notice_name_required() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'Category name should not be blank.', 'bwcc-bulk-woocommerce-categor-creator' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Error notice when category already exists
		 *
		 * @since 1.5
		 */
		public static function bwcc_admin_notice_already_exists() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'A Category with the name provided already exists with this parent.', 'bwcc-bulk-woocommerce-categor-creator' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Bulk Category Creator Page
		 *
		 * @since 1.0
		 */
		public static function bwcc_category_settings_page() {

			$dropdown_args = array(
				'hide_empty'       => 0,
				'hide_if_empty'    => false,
				'taxonomy'         => 'product_cat',
				'name'             => 'bwcc_parent',
				'orderby'          => 'name',
				'hierarchical'     => true,
				'show_option_none' => __( 'None' ),
			);

			$dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, 'product_cat', 'new' );

			$args = array(
				'post_type'      => array( 'product' ),
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' ),
			);

			$products = get_posts( $args );

			?>
			<div class="wrap">

			<h1><?php esc_html_e( 'Bulk Category Creator', 'bwcc-bulk-woocommerce-categor-creator' ); ?> </h1>

			<form method='post'><input type='hidden' name='form-name' value='form 1' />

				<?php settings_fields( 'bwcc-bulk-category-creator-group' ); ?>

				<?php do_settings_sections( 'bwcc-bulk-category-creator-group' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Enter categories separated by commas', 'bwcc-bulk-woocommerce-categor-creator' ); ?>  </th>
						<td>
							<textarea cols="80" rows="5" name="bwcc_options_textarea"></textarea>
							<p><i><?php esc_html_e( 'The name is how it appears on your site. Add category names separated by commas. e.g Category A,Category B,Category C. To create categories in Parent-Child hierarchy then you can use >> to create them. e.g Parent A>>Child A', 'bwcc-bulk-woocommerce-categor-creator' ); ?></i></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Parent Category', 'bwcc-bulk-woocommerce-categor-creator' ); ?>  </th>
						<td>
							<?php wp_dropdown_categories( $dropdown_args ); ?>
							<p><i><?php esc_html_e( 'Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.', 'bwcc-bulk-woocommerce-categor-creator' ); ?></i></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Description', 'bwcc-bulk-woocommerce-categor-creator' ); ?>  </th>
						<td>
							<textarea name="bwcc_description_textarea" id="bwcc_description_textarea" rows="5" cols="80" spellcheck="false"></textarea>
							<p><i><?php esc_html_e( 'The description is not prominent by default; however, some themes may show it.', 'bwcc-bulk-woocommerce-categor-creator' ); ?></i></p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top"><label><?php esc_html_e( 'Display type', 'woocommerce' ); ?></label></th>
						<td>
							<select id="bwcc_display_type" name="bwcc_display_type" class="postform">
								<option value=""><?php esc_html_e( 'Default', 'bwcc-bulk-woocommerce-categor-creator' ); ?></option>
								<option value="products"><?php esc_html_e( 'Products', 'bwcc-bulk-woocommerce-categor-creator' ); ?></option>
								<option value="subcategories"><?php esc_html_e( 'Subcategories', 'bwcc-bulk-woocommerce-categor-creator' ); ?></option>
								<option value="both"><?php esc_html_e( 'Both', 'bwcc-bulk-woocommerce-categor-creator' ); ?></option>
							</select>
						</td>	
					</tr>

					<tr>
						<th scope="row" valign="top"><label><?php esc_html_e( 'Select Products', 'bwcc-bulk-woocommerce-categor-creator' ); ?></label></th>
						<td>
							<select id="bwcc_products"
									name="bwcc_products[]"
									placehoder="Select Products"
									class="bwcc_products"
									style="width: 300px"
									multiple="multiple">
								<option value="all_products"><?php esc_html_e( 'All Products', 'bwcc-bulk-woocommerce-categor-creator' ); ?></option>
								<?php
								$productss = '';
								foreach ( $products as $bkey => $bval ) {

									$productss .= $bval->ID . ',';
									?>
									<option value="<?php echo esc_attr( $bval->ID ); ?>"><?php echo esc_html( $bval->post_title ); ?></option>
									<?php

								}
								if ( '' !== $productss ) {
									$productss = substr( $productss, 0, -1 );
								}
								?>
							</select>
							<p><i><?php esc_html_e( 'Automatically assign the created categories to the selected products.', 'bwcc-bulk-woocommerce-categor-creator' ); ?></i></p>
							<input type="hidden" id="bwcc_all_products" name="custId" value="<?php echo esc_attr( $productss ); ?>">
						</td>	
					</tr>
				</table>

				<?php submit_button( __( 'Bulk Create Categories', 'bwcc-bulk-woocommerce-categor-creator' ) ); ?>

			</form>
			<div style="font-size: 15px;">
				<b><?php esc_html_e( 'Your donation will help us to make this plugin better. Thank you!' , 'bwcc-bulk-woocommerce-categor-creator' ); ?> </b>
				<a class="button" target="_blank" style="background:#ffc437;font-size: 15px;color: black;font-weight: 600;border: none;border-radius: 20px;padding: 0 30px;" href="https://www.paypal.com/paypalme/kartikparmar"><?php esc_html_e( 'Donate', 'bwcc-bulk-woocommerce-categor-creator' ); ?></a>
			</div>
			<?php
		}
	}
	$bwcc = new BWCC_Bulk_WooCommerce_Category_Creator();
}
