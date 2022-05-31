<?php
/*
Plugin Name: WPC Composite Products for WooCommerce
Plugin URI: https://wpclever.net/
Description: WPC Composite Products provide a powerful kit-building solution for WooCommerce store.
Version: 5.2.0
Author: WPClever
Author URI: https://wpclever.net
Text Domain: wpc-composite-products
Domain Path: /languages/
Requires at least: 4.0
Tested up to: 6.0
WC requires at least: 3.0
WC tested up to: 6.5
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WOOCO_VERSION' ) && define( 'WOOCO_VERSION', '5.2.0' );
! defined( 'WOOCO_URI' ) && define( 'WOOCO_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOCO_DOCS' ) && define( 'WOOCO_DOCS', 'https://doc.wpclever.net/wooco/' );
! defined( 'WOOCO_REVIEWS' ) && define( 'WOOCO_REVIEWS', 'https://wordpress.org/support/plugin/wpc-composite-products/reviews/?filter=5' );
! defined( 'WOOCO_CHANGELOG' ) && define( 'WOOCO_CHANGELOG', 'https://wordpress.org/plugins/wpc-composite-products/#developers' );
! defined( 'WOOCO_DISCUSSION' ) && define( 'WOOCO_DISCUSSION', 'https://wordpress.org/support/plugin/wpc-composite-products' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOCO_URI );

include 'includes/wpc-dashboard.php';
include 'includes/wpc-menu.php';
include 'includes/wpc-kit.php';
include 'includes/wpc-notice.php';

if ( ! function_exists( 'wooco_init' ) ) {
	add_action( 'plugins_loaded', 'wooco_init', 11 );

	function wooco_init() {
		// load text-domain
		load_plugin_textdomain( 'wpc-composite-products', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0', '>=' ) ) {
			add_action( 'admin_notices', 'wooco_notice_wc' );

			return;
		}

		if ( ! class_exists( 'WC_Product_Composite' ) && class_exists( 'WC_Product' ) ) {
			class WC_Product_Composite extends WC_Product {
				public function __construct( $product = 0 ) {
					parent::__construct( $product );
				}

				public function get_type() {
					return 'composite';
				}

				public function add_to_cart_url() {
					$product_id = $this->id;

					return apply_filters( 'woocommerce_product_add_to_cart_url', get_permalink( $product_id ), $this );
				}

				public function add_to_cart_text() {
					if ( $this->is_purchasable() && $this->is_in_stock() ) {
						$text = WPCleverWooco::localization( 'button_select', esc_html__( 'Select options', 'wpc-composite-products' ) );
					} else {
						$text = WPCleverWooco::localization( 'button_read', esc_html__( 'Read more', 'wpc-composite-products' ) );
					}

					return apply_filters( 'wooco_product_add_to_cart_text', $text, $this );
				}

				public function single_add_to_cart_text() {
					$text = WPCleverWooco::localization( 'button_single', esc_html__( 'Add to cart', 'wpc-composite-products' ) );

					return apply_filters( 'wooco_product_single_add_to_cart_text', $text, $this );
				}

				public function get_price( $context = 'view' ) {
					if ( ( $context === 'view' ) && ( (float) $this->get_regular_price() == 0 ) ) {
						return '0';
					}

					if ( ( $context === 'view' ) && ( (float) parent::get_price( $context ) == 0 ) ) {
						return '0';
					}

					return parent::get_price( $context );
				}

				// extra functions

				public function get_pricing() {
					$product_id = $this->id;

					return get_post_meta( $product_id, 'wooco_pricing', true );
				}

				public function get_discount() {
					$product_id = $this->id;
					$discount   = 0;

					if ( ( $this->get_pricing() !== 'only' ) && ( $_discount = get_post_meta( $product_id, 'wooco_discount_percent', true ) ) && is_numeric( $_discount ) && ( (float) $_discount < 100 ) && ( (float) $_discount > 0 ) ) {
						$discount = (float) $_discount;
					}

					return $discount;
				}

				public function get_components() {
					$product_id = $this->id;

					if ( ( $components = get_post_meta( $product_id, 'wooco_components', true ) ) && is_array( $components ) && count( $components ) > 0 ) {
						return $components;
					}

					return false;
				}

				public function get_composite_price() {
					// FB for WC
					return $this->get_price();
				}

				public function get_composite_price_including_tax() {
					// FB for WC
					return $this->get_price();
				}
			}
		}

		if ( ! class_exists( 'WPCleverWooco' ) && class_exists( 'WC_Product' ) ) {
			class WPCleverWooco {
				public static $localization = array();

				function __construct() {
					// Init
					add_action( 'init', [ $this, 'init' ] );

					// Menu
					add_action( 'admin_menu', [ $this, 'admin_menu' ] );

					// Enqueue frontend scripts
					add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

					// Enqueue backend scripts
					add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

					// AJAX
					add_action( 'wp_ajax_wooco_add_component', [ $this, 'add_component' ] );
					add_action( 'wp_ajax_wooco_save_components', [ $this, 'save_components' ] );

					// Add to selector
					add_filter( 'product_type_selector', [ $this, 'product_type_selector' ] );

					// Product data tabs
					add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data_tabs' ], 10, 1 );

					// Product data panels
					add_action( 'woocommerce_product_data_panels', [ $this, 'product_data_panels' ] );
					add_action( 'woocommerce_process_product_meta', [ $this, 'process_meta' ] );
					add_action( 'woocommerce_process_product_meta_composite', [ $this, 'process_meta_composite' ] );

					// Add to cart form & button
					add_action( 'woocommerce_composite_add_to_cart', [ $this, 'add_to_cart_form' ] );
					add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_to_cart_button' ] );

					// Add to cart
					add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', [
						$this,
						'found_in_cart'
					], 10, 2 );
					add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], 10, 2 );
					add_action( 'woocommerce_add_to_cart', [ $this, 'add_to_cart' ], 10, 6 );
					add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 10, 2 );
					add_filter( 'woocommerce_get_cart_item_from_session', [
						$this,
						'get_cart_item_from_session'
					], 10, 2 );

					// Undo remove
					add_action( 'woocommerce_restore_cart_item', [ $this, 'restore_cart_item' ], 10, 1 );

					// Admin
					add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );

					// Cart item
					add_filter( 'woocommerce_cart_item_name', [ $this, 'cart_item_name' ], 10, 2 );
					add_filter( 'woocommerce_cart_item_quantity', [ $this, 'cart_item_quantity' ], 10, 3 );
					add_filter( 'woocommerce_cart_item_remove_link', [ $this, 'cart_item_remove_link' ], 10, 2 );
					add_filter( 'woocommerce_cart_contents_count', [ $this, 'cart_contents_count' ] );
					add_action( 'woocommerce_after_cart_item_quantity_update', [
						$this,
						'update_cart_item_quantity'
					], 1, 2 );
					add_action( 'woocommerce_before_cart_item_quantity_zero', [
						$this,
						'update_cart_item_quantity'
					], 1 );
					add_action( 'woocommerce_cart_item_removed', [ $this, 'cart_item_removed' ], 10, 2 );
					add_filter( 'woocommerce_cart_item_price', [ $this, 'cart_item_price' ], 10, 2 );
					add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'cart_item_subtotal' ], 10, 2 );

					// Hide on cart & checkout page
					if ( get_option( '_wooco_hide_component', 'no' ) !== 'no' ) {
						add_filter( 'woocommerce_cart_item_visible', [ $this, 'cart_item_visible' ], 10, 2 );
						add_filter( 'woocommerce_order_item_visible', [ $this, 'cart_item_visible' ], 10, 2 );
						add_filter( 'woocommerce_checkout_cart_item_visible', [ $this, 'cart_item_visible' ], 10, 2 );
					}

					// Hide on mini-cart
					if ( get_option( '_wooco_hide_component_mini_cart', 'no' ) === 'yes' ) {
						add_filter( 'woocommerce_widget_cart_item_visible', [ $this, 'cart_item_visible' ], 10, 2 );
					}

					// Item class
					if ( get_option( '_wooco_hide_component', 'no' ) !== 'yes' ) {
						add_filter( 'woocommerce_cart_item_class', [ $this, 'cart_item_class' ], 10, 2 );
						add_filter( 'woocommerce_mini_cart_item_class', [ $this, 'cart_item_class' ], 10, 2 );
						add_filter( 'woocommerce_order_item_class', [ $this, 'cart_item_class' ], 10, 2 );
					}

					// Get item data
					if ( get_option( '_wooco_hide_component', 'no' ) === 'yes_text' ) {
						add_filter( 'woocommerce_get_item_data', [ $this, 'get_item_data' ], 10, 2 );
						add_action( 'woocommerce_checkout_create_order_line_item', [
							$this,
							'checkout_create_order_line_item'
						], 10, 4 );
					}

					// Hide item meta
					add_filter( 'woocommerce_order_item_get_formatted_meta_data', [
						$this,
						'order_item_get_formatted_meta_data'
					], 10, 1 );

					// Order item
					add_action( 'woocommerce_checkout_create_order_line_item', [
						$this,
						'add_order_item_meta'
					], 10, 3 );
					add_filter( 'woocommerce_order_item_name', [ $this, 'cart_item_name' ], 10, 2 );
					add_filter( 'woocommerce_order_formatted_line_subtotal', [
						$this,
						'order_formatted_line_subtotal'
					], 10, 2 );

					// Admin order
					add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hidden_order_itemmeta' ], 10, 1 );
					add_action( 'woocommerce_before_order_itemmeta', [ $this, 'before_order_itemmeta' ], 10, 2 );

					// Add settings link
					add_filter( 'plugin_action_links', [ $this, 'action_links' ], 10, 2 );
					add_filter( 'plugin_row_meta', [ $this, 'row_meta' ], 10, 2 );

					// Loop add-to-cart
					add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'loop_add_to_cart_link' ], 10, 2 );

					// Calculate price
					add_action( 'woocommerce_before_mini_cart_contents', [ $this, 'before_mini_cart_contents' ], 10 );
					add_action( 'woocommerce_before_calculate_totals', [ $this, 'before_calculate_totals' ], 9999 );

					// Shipping
					add_filter( 'woocommerce_cart_shipping_packages', [ $this, 'cart_shipping_packages' ] );

					// Price html
					add_filter( 'woocommerce_get_price_html', [ $this, 'get_price_html' ], 99, 2 );

					// Price class
					add_filter( 'woocommerce_product_price_class', [ $this, 'product_price_class' ] );

					// Order again
					add_filter( 'woocommerce_order_again_cart_item_data', [
						$this,
						'order_again_cart_item_data'
					], 10, 2 );
					add_action( 'woocommerce_cart_loaded_from_session', [ $this, 'cart_loaded_from_session' ] );

					// Coupons
					add_filter( 'woocommerce_coupon_is_valid_for_product', [
						$this,
						'coupon_is_valid_for_product'
					], 10, 4 );

					// Export
					add_filter( 'woocommerce_product_export_column_names', [ $this, 'export_columns' ] );
					add_filter( 'woocommerce_product_export_product_default_columns', [ $this, 'export_columns' ] );
					add_filter( 'woocommerce_product_export_product_column_wooco_components', [
						$this,
						'export_data'
					], 10, 2 );

					// Import
					add_filter( 'woocommerce_csv_product_import_mapping_options', [ $this, 'import_options' ] );
					add_filter( 'woocommerce_csv_product_import_mapping_default_columns', [ $this, 'import_columns' ] );
					add_filter( 'woocommerce_product_import_pre_insert_product_object', [
						$this,
						'import_process'
					], 10, 2 );
				}

				function init() {
					// localization
					self::$localization = (array) get_option( 'wooco_localization' );
				}

				function add_component() {
					self::component( true, $_POST['component'] );
					die();
				}

				function component( $active = false, $component = array() ) {
					$component_default = array(
						'name'       => 'Name',
						'desc'       => 'Description',
						'type'       => '',
						'categories' => '',
						'products'   => '',
						'tags'       => '',
						'other'      => '',
						'orderby'    => 'default',
						'order'      => 'default',
						'exclude'    => '',
						'default'    => '',
						'optional'   => 'yes',
						'qty'        => 1,
						'custom_qty' => 'no',
						'price'      => '',
						'min'        => 0,
						'max'        => 1000
					);

					if ( ! empty( $component ) ) {
						$component = array_merge( $component_default, $component );
					} else {
						$component = $component_default;
					}

					$search_products_id   = uniqid( 'wooco_search_products-' );
					$search_categories_id = uniqid( 'wooco_search_categories-' );
					$search_default_id    = uniqid( 'wooco_search_default-' );
					$search_exclude_id    = uniqid( 'wooco_search_exclude-' );

					if ( class_exists( 'WPCleverWoopq' ) && ( get_option( '_woopq_decimal', 'no' ) === 'yes' ) ) {
						$step = '0.000001';
					} else {
						$step             = '1';
						$component['qty'] = (int) $component['qty'];
						$component['min'] = (int) $component['min'];
						$component['max'] = (int) $component['max'];
					}
					?>
                    <tr class="wooco_component">
                        <td>
                            <div class="wooco_component_inner <?php echo esc_attr( $active ? 'active' : '' ); ?>">
                                <div class="wooco_component_heading">
                                    <span class="wooco_move_component"></span>
                                    <span class="wooco_component_name"><?php echo $component['name']; ?></span>
                                    <a class="wooco_duplicate_component"
                                       href="#"><?php esc_html_e( 'duplicate', 'wpc-composite-products' ); ?></a>
                                    <a class="wooco_remove_component"
                                       href="#"><?php esc_html_e( 'remove', 'wpc-composite-products' ); ?></a>
                                </div>
                                <div class="wooco_component_content">
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Name', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input name="wooco_components[name][]" type="text"
                                                   class="wooco_component_name_val"
                                                   value="<?php echo $component['name']; ?>"/>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Description', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <textarea class="wooco_component_desc_val"
                                                      name="wooco_components[desc][]"><?php echo $component['desc']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Source', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <select name="wooco_components[type][]"
                                                    class="wooco_component_type wooco_component_type_val">
                                                <option value=""><?php esc_html_e( 'Select source', 'wpc-composite-products' ); ?></option>
                                                <option value="products" <?php echo esc_attr( $component['type'] === 'products' ? 'selected' : '' ); ?>>
													<?php esc_html_e( 'Products', 'wpc-composite-products' ); ?>
                                                </option>
                                                <option value="categories" <?php echo esc_attr( $component['type'] === 'categories' ? 'selected' : 'disabled' ); ?>>
													<?php esc_html_e( 'Categories', 'wpc-composite-products' ); ?>
                                                </option>
                                                <option value="tags" <?php echo esc_attr( $component['type'] === 'tags' ? 'selected' : 'disabled' ); ?>>
													<?php esc_html_e( 'Tags', 'wpc-composite-products' ); ?>
                                                </option>
                                                <option value="types" <?php echo esc_attr( $component['type'] === 'types' ? 'selected' : 'disabled' ); ?>>
													<?php esc_html_e( 'Product Type', 'wpc-composite-products' ); ?>
                                                </option>
												<?php
												$taxonomies = get_taxonomies( [ 'object_type' => [ 'product' ] ], 'objects' );

												foreach ( $taxonomies as $taxonomy ) {
													if ( in_array( $taxonomy->name, array(
														'product_cat',
														'product_tag',
														'product_type'
													) ) ) {
														continue;
													}

													echo '<option value="' . esc_attr( $taxonomy->name ) . '" ' . ( $component['type'] === $taxonomy->name ? 'selected' : 'disabled' ) . '>' . esc_html( $taxonomy->label ) . '</option>';
												}
												?>
                                            </select>
                                            <span><?php esc_html_e( 'Order by', 'wpc-composite-products' ); ?> <select
                                                        name="wooco_components[orderby][]"
                                                        class="wooco_component_orderby_val">
                                                        <option value="default" <?php echo esc_attr( $component['orderby'] === 'default' ? 'selected' : '' ); ?>><?php esc_html_e( 'Default', 'wpc-composite-products' ); ?></option>
                                                        <option value="none" <?php echo esc_attr( $component['orderby'] === 'none' ? 'selected' : '' ); ?>><?php esc_html_e( 'None', 'wpc-composite-products' ); ?></option>
                                                        <option value="ID" <?php echo esc_attr( $component['orderby'] === 'ID' ? 'selected' : '' ); ?>><?php esc_html_e( 'ID', 'wpc-composite-products' ); ?></option>
                                                        <option value="name" <?php echo esc_attr( $component['orderby'] === 'name' ? 'selected' : '' ); ?>><?php esc_html_e( 'Name', 'wpc-composite-products' ); ?></option>
                                                        <option value="type" <?php echo esc_attr( $component['orderby'] === 'type' ? 'selected' : '' ); ?>><?php esc_html_e( 'Type', 'wpc-composite-products' ); ?></option>
                                                        <option value="rand" <?php echo esc_attr( $component['orderby'] === 'rand' ? 'selected' : '' ); ?>><?php esc_html_e( 'Rand', 'wpc-composite-products' ); ?></option>
                                                        <option value="date" <?php echo esc_attr( $component['orderby'] === 'date' ? 'selected' : '' ); ?>><?php esc_html_e( 'Date', 'wpc-composite-products' ); ?></option>
                                                        <option value="price" <?php echo esc_attr( $component['orderby'] === 'price' ? 'selected' : '' ); ?>><?php esc_html_e( 'Price', 'wpc-composite-products' ); ?></option>
                                                        <option value="modified" <?php echo esc_attr( $component['orderby'] === 'modified' ? 'selected' : '' ); ?>><?php esc_html_e( 'Modified', 'wpc-composite-products' ); ?></option>
                                                    </select></span> &nbsp;
                                            <span><?php esc_html_e( 'Order', 'wpc-composite-products' ); ?> <select
                                                        name="wooco_components[order][]"
                                                        class="wooco_component_order_val">
                                                        <option value="default" <?php echo esc_attr( $component['order'] === 'default' ? 'selected' : '' ); ?>><?php esc_html_e( 'Default', 'wpc-composite-products' ); ?></option>
                                                        <option value="DESC" <?php echo esc_attr( $component['order'] === 'DESC' ? 'selected' : '' ); ?>><?php esc_html_e( 'DESC', 'wpc-composite-products' ); ?></option>
                                                        <option value="ASC" <?php echo esc_attr( $component['order'] === 'ASC' ? 'selected' : '' ); ?>><?php esc_html_e( 'ASC', 'wpc-composite-products' ); ?></option>
                                                        </select></span>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line wooco_hide wooco_show_if_other">
                                        <div class="wooco_component_content_line_label wooco_component_type_label">
											<?php esc_html_e( 'Terms', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <span class="wooco-dialog-btn wooco-product-types-btn"><?php esc_html_e( 'Available product types on your site.', 'wpc-composite-products' ); ?></span>
                                            <input class="wooco_component_other_val" style="margin-top: 10px"
                                                   name="wooco_components[other][]" type="text"
                                                   placeholder="<?php esc_attr_e( 'Add some terms, split by a comma...', 'wpc-composite-products' ); ?>"
                                                   value="<?php echo esc_attr( isset( $component['other'] ) ? $component['other'] : '' ); ?>"/>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line wooco_hide wooco_show_if_tags">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Tags', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input class="wooco_component_tags_val" style="margin-top: 10px"
                                                   name="wooco_components[tags][]" type="text"
                                                   placeholder="<?php esc_attr_e( 'Add some tags, split by a comma...', 'wpc-composite-products' ); ?>"
                                                   value="<?php echo esc_attr( isset( $component['tags'] ) ? $component['tags'] : '' ); ?>"/>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line wooco_hide wooco_show_if_categories">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Categories', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input id="<?php echo $search_categories_id; ?>"
                                                   class="wooco-category-search-input wooco_component_categories_val"
                                                   name="wooco_components[categories][]" type="hidden"
                                                   value="<?php echo esc_attr( isset( $component['categories'] ) ? $component['categories'] : '' ); ?>"/>
                                            <select class="wc-category-search wooco-category-search"
                                                    multiple="multiple"
                                                    style="width: 100%;"
                                                    data-placeholder="<?php esc_attr_e( 'Search for a category&hellip;', 'wpc-composite-products' ); ?>">
												<?php
												$category_ids = explode( ',', $component['categories'] );

												foreach ( $category_ids as $category_id ) {
													if ( is_numeric( $category_id ) ) {
														$category = get_term_by( 'id', absint( $category_id ), 'product_cat' );
													} else {
														$category = get_term_by( 'slug', $category_id, 'product_cat' );
													}

													if ( $category ) {
														echo '<option value="' . esc_attr( $category_id ) . '" selected="selected">' . wp_kses_post( $category->name ) . '</option>';
													}
												}
												?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line wooco_hide wooco_show_if_products">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Products', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input id="<?php echo $search_products_id; ?>"
                                                   class="wooco-product-search-input wooco_component_products_val"
                                                   name="wooco_components[products][]" type="hidden"
                                                   value="<?php echo esc_attr( isset( $component['products'] ) ? $component['products'] : '' ); ?>"/>
                                            <select class="wc-product-search wooco-product-search"
                                                    multiple="multiple"
                                                    style="width: 100%;" data-sortable="1"
                                                    data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'wpc-composite-products' ); ?>"
                                                    data-action="woocommerce_json_search_products_and_variations">
												<?php
												$_product_ids = explode( ',', $component['products'] );

												foreach ( $_product_ids as $_product_id ) {
													$_product = wc_get_product( $_product_id );

													if ( $_product ) {
														echo '<option value="' . esc_attr( $_product_id ) . '" selected="selected">' . wp_kses_post( $_product->get_formatted_name() ) . '</option>';
													}
												}
												?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line wooco_show wooco_hide_if_products">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Exclude', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input id="<?php echo $search_exclude_id; ?>"
                                                   class="wooco-product-search-input wooco_component_exclude_val"
                                                   name="wooco_components[exclude][]" type="hidden"
                                                   value="<?php echo $component['exclude']; ?>"/>
                                            <select class="wc-product-search wooco-product-search"
                                                    multiple="multiple"
                                                    style="width: 100%;" data-sortable="1"
                                                    data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'wpc-composite-products' ); ?>"
                                                    data-action="woocommerce_json_search_products_and_variations">
												<?php
												$_product_ids = explode( ',', $component['exclude'] );

												foreach ( $_product_ids as $_product_id ) {
													$_product = wc_get_product( $_product_id );

													if ( $_product ) {
														echo '<option value="' . esc_attr( $_product_id ) . '" selected="selected">' . wp_kses_post( $_product->get_formatted_name() ) . '</option>';
													}
												}
												?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Default option', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input id="<?php echo $search_default_id; ?>"
                                                   class="wooco-product-search-input wooco_component_default_val"
                                                   name="wooco_components[default][]" type="hidden"
                                                   value="<?php echo $component['default']; ?>"/>
                                            <select class="wc-product-search wooco-product-search"
                                                    style="width: 100%;" data-allow_clear="true"
                                                    data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'wpc-composite-products' ); ?>"
                                                    data-action="woocommerce_json_search_products_and_variations">
												<?php
												if ( ! empty( $component['default'] ) ) {
													$product_default = wc_get_product( $component['default'] );

													if ( $product_default ) {
														echo '<option value="' . esc_attr( $component['default'] ) . '" selected="selected">' . wp_kses_post( $product_default->get_formatted_name() ) . '</option>';
													}
												}
												?>
                                            </select>
                                        </div>
                                    </div>
									<?php echo '<script>jQuery(document.body).trigger( \'wc-enhanced-select-init\' );</script>'; ?>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Required', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <select name="wooco_components[optional][]"
                                                    class="wooco_component_optional_val">
                                                <option value="no" <?php echo esc_attr( $component['optional'] === 'no' ? 'selected' : '' ); ?>>
													<?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?>
                                                </option>
                                                <option value="yes" <?php echo esc_attr( $component['optional'] === 'yes' ? 'selected' : '' ); ?>>
													<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'New price', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input name="wooco_components[price][]" type="text"
                                                   style="width: 60px; display: inline-block"
                                                   class="wooco_component_price_val"
                                                   value="<?php echo self::format_price( $component['price'] ); ?>"/>
                                            <span class="woocommerce-help-tip"
                                                  data-tip="<?php esc_html_e( 'Set a new price using a number (eg. "49" for $49) or a percentage (eg. "90%" of the original price).', 'wpc-composite-products' ); ?>"></span>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Quantity', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input name="wooco_components[qty][]" type="number" min="0"
                                                   class="wooco_component_qty_val"
                                                   step="<?php echo esc_attr( $step ); ?>"
                                                   value="<?php echo esc_attr( $component['qty'] ); ?>"/>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Custom quantity', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <select name="wooco_components[custom_qty][]"
                                                    class="wooco_component_custom_qty_val">
                                                <option value="no" <?php echo esc_attr( $component['custom_qty'] === 'no' ? 'selected' : '' ); ?>>
													<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                </option>
                                                <option value="yes" <?php echo esc_attr( $component['custom_qty'] === 'yes' ? 'selected' : '' ); ?>>
													<?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Min', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input name="wooco_components[min][]" type="number" min="0"
                                                   class="wooco_component_min_val"
                                                   step="<?php echo esc_attr( $step ); ?>"
                                                   value="<?php echo esc_attr( $component['min'] ); ?>"/>
                                        </div>
                                    </div>
                                    <div class="wooco_component_content_line">
                                        <div class="wooco_component_content_line_label">
											<?php esc_html_e( 'Max', 'wpc-composite-products' ); ?>
                                        </div>
                                        <div class="wooco_component_content_line_value">
                                            <input name="wooco_components[max][]" type="number" min="0"
                                                   class="wooco_component_max_val"
                                                   step="<?php echo esc_attr( $step ); ?>"
                                                   value="<?php echo esc_attr( $component['max'] ); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
				<?php }

				function save_components() {
					$pid       = $_POST['pid'];
					$form_data = $_POST['form_data'];

					if ( $pid && $form_data ) {
						$components = array();
						parse_str( $form_data, $components );

						if ( isset( $components['wooco_components'] ) ) {
							update_post_meta( $pid, 'wooco_components', self::format_array( $components['wooco_components'] ) );
						}
					}

					die();
				}

				function admin_menu() {
					add_submenu_page( 'wpclever', esc_html__( 'WPC Composite Products', 'wpc-composite-products' ), esc_html__( 'Composite Products', 'wpc-composite-products' ), 'manage_options', 'wpclever-wooco', array(
						&$this,
						'admin_menu_content'
					) );
				}

				function admin_menu_content() {
					add_thickbox();
					$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
					?>
                    <div class="wpclever_settings_page wrap">
                        <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Composite Products', 'wpc-composite-products' ) . ' ' . WOOCO_VERSION; ?></h1>
                        <div class="wpclever_settings_page_desc about-text">
                            <p>
								<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'wpc-composite-products' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                                <br/>
                                <a href="<?php echo esc_url( WOOCO_REVIEWS ); ?>"
                                   target="_blank"><?php esc_html_e( 'Reviews', 'wpc-composite-products' ); ?></a> | <a
                                        href="<?php echo esc_url( WOOCO_CHANGELOG ); ?>"
                                        target="_blank"><?php esc_html_e( 'Changelog', 'wpc-composite-products' ); ?></a>
                                | <a href="<?php echo esc_url( WOOCO_DISCUSSION ); ?>"
                                     target="_blank"><?php esc_html_e( 'Discussion', 'wpc-composite-products' ); ?></a>
                            </p>
                        </div>
                        <div class="wpclever_settings_page_nav">
                            <h2 class="nav-tab-wrapper">
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-wooco&tab=how' ); ?>"
                                   class="<?php echo $active_tab === 'how' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'How to use?', 'wpc-composite-products' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-wooco&tab=settings' ); ?>"
                                   class="<?php echo $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Settings', 'wpc-composite-products' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-wooco&tab=localization' ); ?>"
                                   class="<?php echo $active_tab === 'localization' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Localization', 'wpc-composite-products' ); ?>
                                </a>
                                <a href="<?php echo esc_url( WOOCO_DOCS ); ?>" class="nav-tab" target="_blank">
									<?php esc_html_e( 'Docs', 'wpc-composite-products' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-wooco&tab=premium' ); ?>"
                                   class="<?php echo $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>"
                                   style="color: #c9356e">
									<?php esc_html_e( 'Premium Version', 'wpc-composite-products' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-kit' ); ?>" class="nav-tab">
									<?php esc_html_e( 'Essential Kit', 'wpc-composite-products' ); ?>
                                </a>
                            </h2>
                        </div>
                        <div class="wpclever_settings_page_content">
							<?php if ( $active_tab === 'how' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>
										<?php esc_html_e( 'When creating the product, please choose product data is "Smart composite" then you can see the search field to start search and add component products.', 'wpc-composite-products' ); ?>
                                    </p>
                                    <p>
                                        <img src="<?php echo WOOCO_URI; ?>assets/images/how-01.jpg"/>
                                    </p>
                                </div>
								<?php
							} elseif ( $active_tab === 'settings' ) {
								$price_format             = get_option( '_wooco_price_format', 'from_regular' );
								$selector                 = get_option( '_wooco_selector', 'ddslick' );
								$exclude_hidden           = get_option( '_wooco_exclude_hidden', 'no' );
								$exclude_unpurchasable    = get_option( '_wooco_exclude_unpurchasable', 'yes' );
								$show_alert               = get_option( '_wooco_show_alert', 'load' );
								$show_qty                 = get_option( '_wooco_show_qty', 'yes' );
								$show_image               = get_option( '_wooco_show_image', 'yes' );
								$show_price               = get_option( '_wooco_show_price', 'yes' );
								$show_availability        = get_option( '_wooco_show_availability', 'yes' );
								$option_none_image        = get_option( '_wooco_option_none_image', 'placeholder' );
								$option_none_image_id     = get_option( '_wooco_option_none_image_id', '' );
								$option_none_required     = get_option( '_wooco_option_none_required', 'no' );
								$checkbox                 = get_option( '_wooco_checkbox', 'no' );
								$change_price             = get_option( '_wooco_change_price', 'yes' );
								$change_price_custom      = get_option( '_wooco_change_price_custom', '.summary > .price' );
								$product_link             = get_option( '_wooco_product_link', 'no' );
								$coupon_restrictions      = get_option( '_wooco_coupon_restrictions', 'no' );
								$cart_contents_count      = get_option( '_wooco_cart_contents_count', 'composite' );
								$hide_composite_name      = get_option( '_wooco_hide_composite_name', 'no' );
								$hide_component_name      = get_option( '_wooco_hide_component_name', 'yes' );
								$hide_component           = get_option( '_wooco_hide_component', 'no' );
								$hide_component_mini_cart = get_option( '_wooco_hide_component_mini_cart', 'no' );
								?>
                                <form method="post" action="options.php">
									<?php wp_nonce_field( 'update-options' ); ?>
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'General', 'wpc-composite-products' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Price format', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_price_format">
                                                    <option value="from_regular" <?php echo esc_attr( $price_format === 'from_regular' ? 'selected' : '' ); ?>><?php esc_html_e( 'From regular price', 'wpc-composite-products' ); ?></option>
                                                    <option value="from_sale" <?php echo esc_attr( $price_format === 'from_sale' ? 'selected' : '' ); ?>><?php esc_html_e( 'From sale price', 'wpc-composite-products' ); ?></option>
                                                    <option value="normal" <?php echo esc_attr( $price_format === 'normal' ? 'selected' : '' ); ?>><?php esc_html_e( 'Regular and sale price', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Choose a price format for composites on the archive page.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Selector interface', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_selector">
                                                    <option value="ddslick" <?php echo esc_attr( $selector === 'ddslick' ? 'selected' : '' ); ?>><?php esc_html_e( 'ddSlick', 'wpc-composite-products' ); ?></option>
                                                    <option value="select2" <?php echo esc_attr( $selector === 'select2' ? 'selected' : '' ); ?>><?php esc_html_e( 'Select2', 'wpc-composite-products' ); ?></option>
                                                    <option value="select" <?php echo esc_attr( $selector === 'select' ? 'selected' : '' ); ?>><?php esc_html_e( 'HTML select tag', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description">Read more about <a
                                                            href="https://designwithpc.com/Plugins/ddSlick"
                                                            target="_blank">ddSlick</a>, <a
                                                            href="https://select2.org/" target="_blank">Select2</a> and <a
                                                            href="https://www.w3schools.com/tags/tag_select.asp"
                                                            target="_blank">HTML select tag</a></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Exclude hidden', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_exclude_hidden">
                                                    <option value="yes" <?php echo esc_attr( $exclude_hidden === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $exclude_hidden === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Exclude hidden products from the list.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Exclude unpurchasable', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_exclude_unpurchasable">
                                                    <option value="yes" <?php echo esc_attr( $exclude_unpurchasable === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $exclude_unpurchasable === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Exclude unpurchasable products from the list.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show alert', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_show_alert">
                                                    <option value="load" <?php echo esc_attr( $show_alert === 'load' ? 'selected' : '' ); ?>><?php esc_html_e( 'On composite loaded', 'wpc-composite-products' ); ?></option>
                                                    <option value="change" <?php echo esc_attr( $show_alert === 'change' ? 'selected' : '' ); ?>><?php esc_html_e( 'On composite changing', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $show_alert === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No, always hide the alert', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Show the inline alert under the components.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show quantity', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_show_qty">
                                                    <option value="yes" <?php echo esc_attr( $show_qty === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $show_qty === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Show the quantity before component product name.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show image', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_show_image">
                                                    <option value="yes" <?php echo esc_attr( $show_image === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $show_image === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'wpc-composite-products' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show price', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_show_price">
                                                    <option value="yes" <?php echo esc_attr( $show_price === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $show_price === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'wpc-composite-products' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show availability', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_show_availability">
                                                    <option value="yes" <?php echo esc_attr( $show_availability === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $show_availability === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'wpc-composite-products' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Component selector', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_checkbox">
                                                    <option value="yes" <?php echo esc_attr( $checkbox === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Checkbox', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $checkbox === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'Option none', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Use checkbox or Option none.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Option none image', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_option_none_image">
                                                    <option value="placeholder" <?php echo esc_attr( $option_none_image === 'placeholder' ? 'selected' : '' ); ?>><?php esc_html_e( 'Placeholder image', 'wpc-composite-products' ); ?></option>
                                                    <option value="product" <?php echo esc_attr( $option_none_image === 'product' ? 'selected' : '' ); ?>><?php esc_html_e( 'Main product\'s image', 'wpc-composite-products' ); ?></option>
                                                    <option value="custom" <?php echo esc_attr( $option_none_image === 'custom' ? 'selected' : '' ); ?>><?php esc_html_e( 'Custom image', 'wpc-composite-products' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'If you choose "Placeholder image", you can change it in WooCommerce > Settings > Products > Placeholder image.', 'wpc-composite-products' ); ?></span>
                                                <div class="wooco_option_none_image_custom" style="display: none">
													<?php wp_enqueue_media(); ?>
                                                    <span class="wooco_option_none_image_preview"
                                                          id="wooco_option_none_image_preview">
                                                        <?php if ( $option_none_image_id ) {
	                                                        echo '<img src="' . wp_get_attachment_url( $option_none_image_id ) . '"/>';
                                                        } ?>
                                                    </span>
                                                    <input id="wooco_option_none_image_upload" type="button"
                                                           class="button"
                                                           value="<?php esc_attr_e( 'Upload image', 'wpc-composite-products' ); ?>"/>
                                                    <input type="hidden" name="_wooco_option_none_image_id"
                                                           id="wooco_option_none_image_id"
                                                           value="<?php echo $option_none_image_id; ?>"/>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show "Option none" for required component', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_option_none_required">
                                                    <option value="yes" <?php echo esc_attr( $option_none_required === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( $option_none_required === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'wpc-composite-products' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change price', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_change_price">
                                                    <option value="yes" <?php echo esc_attr( $change_price === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="yes_custom" <?php echo esc_attr( $change_price === 'yes_custom' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, custom selector', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( $change_price === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                                <input type="text" name="_wooco_change_price_custom"
                                                       value="<?php echo esc_attr( $change_price_custom ); ?>"
                                                       placeholder=".summary > .price"/>
                                                <span class="description"><?php esc_html_e( 'Change the main product’s price based on the changes in prices of selected variations in a grouped products. This uses Javascript to change the main product’s price to it depends heavily on theme’s HTML. If the price doesn\'t change when this option is enabled, please contact us and we can help you adjust the JS file. ', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Link to individual product', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_product_link">
                                                    <option value="yes" <?php echo esc_attr( $product_link === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open product page', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="yes_popup" <?php echo esc_attr( $product_link === 'yes_popup' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open quick view popup', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( $product_link === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Add a link to the target individual product below this selection.', 'wpc-composite-products' ); ?> If you choose "Open quick view popup", please install <a
                                                            href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woo-smart-quick-view&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                            class="thickbox" title="Install WPC Smart Quick View">WPC Smart Quick View</a> to make it work.</span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Cart & Checkout', 'wpc-composite-products' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Coupon restrictions', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_coupon_restrictions">
                                                    <option value="no" <?php echo esc_attr( $coupon_restrictions === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="composite" <?php echo esc_attr( $coupon_restrictions === 'composite' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Exclude composite', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="component" <?php echo esc_attr( $coupon_restrictions === 'component' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Exclude component products', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="both" <?php echo esc_attr( $coupon_restrictions === 'both' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Exclude both composite and component products', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Choose products you want to exclude from coupons.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Cart content count', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_cart_contents_count">
                                                    <option value="composite" <?php echo esc_attr( $cart_contents_count === 'composite' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Composite only', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="component_products" <?php echo esc_attr( $cart_contents_count === 'component_products' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Component products only', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="both" <?php echo esc_attr( $cart_contents_count === 'both' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Both composite and component products', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Hide composite name before component products', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_hide_composite_name">
                                                    <option value="yes" <?php echo esc_attr( $hide_composite_name === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( $hide_composite_name === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Hide component name before component products', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_hide_component_name">
                                                    <option value="yes" <?php echo esc_attr( $hide_component_name === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( $hide_component_name === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Hide component products on cart & checkout page', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_hide_component">
                                                    <option value="yes" <?php echo esc_attr( $hide_component === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, just show the composite', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="yes_text" <?php echo esc_attr( $hide_component === 'yes_text' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, but show component product names under the composite', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( $hide_component === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Hide component products on mini-cart', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <select name="_wooco_hide_component_mini_cart">
                                                    <option value="yes" <?php echo esc_attr( $hide_component_mini_cart === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wpc-composite-products' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( $hide_component_mini_cart === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wpc-composite-products' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Hide component products, just show the main composite on mini-cart.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_attr_e( 'Update Options', 'wpc-composite-products' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options"
                                                       value="_wooco_price_format,_wooco_selector,_wooco_exclude_hidden,_wooco_exclude_unpurchasable,_wooco_show_alert,_wooco_show_qty,_wooco_show_image,_wooco_show_price,_wooco_show_availability,_wooco_option_none_image,_wooco_option_none_image_id,_wooco_option_none_required,_wooco_checkbox,_wooco_coupon_restrictions,_wooco_cart_contents_count,_wooco_hide_composite_name,_wooco_hide_component_name,_wooco_hide_component,_wooco_hide_component_mini_cart,_wooco_change_price,_wooco_change_price_custom,_wooco_product_link"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'localization' ) { ?>
                                <form method="post" action="options.php">
									<?php wp_nonce_field( 'update-options' ); ?>
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th scope="row"><?php esc_html_e( 'General', 'wpc-composite-products' ); ?></th>
                                            <td>
												<?php esc_html_e( 'Leave blank to use the default text and its equivalent translation in multiple languages.', 'wpc-composite-products' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Option none', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="wooco_localization[option_none]"
                                                       value="<?php echo esc_attr( self::localization( 'option_none' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'No, thanks. I don\'t need this', 'wpc-composite-products' ); ?>"/>
                                                <span class="description"><?php esc_html_e( 'Text to display for showing a "Don\'t choose any product" option.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Total text', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[total]" class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'total' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Total price:', 'wpc-composite-products' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Saved text', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[saved]" class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'saved' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( '(saved [d])', 'wpc-composite-products' ); ?>"/>
                                                <span class="description"><?php esc_html_e( 'Use [d] to show the saved percentage.', 'wpc-composite-products' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Quantity label', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[qty_label]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'qty_label' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Qty:', 'wpc-composite-products' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( '"Add to cart" button labels', 'wpc-composite-products' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Shop/archive page', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <div style="margin-bottom: 5px">
                                                    <input type="text" class="regular-text"
                                                           name="wooco_localization[button_select]"
                                                           value="<?php echo esc_attr( self::localization( 'button_select' ) ); ?>"
                                                           placeholder="<?php esc_attr_e( 'Select options', 'wpc-composite-products' ); ?>"/>
                                                    <span class="description"><?php esc_html_e( 'For purchasable composites.', 'wpc-composite-products' ); ?></span>
                                                </div>
                                                <div>
                                                    <input type="text" class="regular-text"
                                                           name="wooco_localization[button_read]"
                                                           value="<?php echo esc_attr( self::localization( 'button_read' ) ); ?>"
                                                           placeholder="<?php esc_attr_e( 'Read more', 'wpc-composite-products' ); ?>"/>
                                                    <span class="description"><?php esc_html_e( 'For unpurchasable composites.', 'wpc-composite-products' ); ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Single product page', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[button_single]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'button_single' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Add to cart', 'wpc-composite-products' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Alert', 'wpc-composite-products' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Require selection', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[alert_selection]"
                                                       class="large-text"
                                                       value="<?php echo esc_attr( self::localization( 'alert_selection' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Please choose a purchasable product for the component [name] before adding this composite to the cart.', 'wpc-composite-products' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Different selection', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[alert_same]"
                                                       class="large-text"
                                                       value="<?php echo esc_attr( self::localization( 'alert_same' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Please select a different product for each component.', 'wpc-composite-products' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Minimum required', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[alert_min]"
                                                       class="large-text"
                                                       value="<?php echo esc_attr( self::localization( 'alert_min' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Please choose at least a total quantity of [min] products before adding this composite to the cart.', 'wpc-composite-products' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Maximum reached', 'wpc-composite-products' ); ?></th>
                                            <td>
                                                <input type="text" name="wooco_localization[alert_max]"
                                                       class="large-text"
                                                       value="<?php echo esc_attr( self::localization( 'alert_max' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Sorry, you can only choose at max a total quantity of [max] products before adding this composite to the cart.', 'wpc-composite-products' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_attr_e( 'Update Options', 'wpc-composite-products' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options" value="wooco_localization"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab == 'premium' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>
                                        Get the Premium Version just $29! <a
                                                href="https://wpclever.net/downloads/composite-products?utm_source=pro&utm_medium=wooco&utm_campaign=wporg"
                                                target="_blank">https://wpclever.net/downloads/composite-products</a>
                                    </p>
                                    <p><strong>Extra features for Premium Version:</strong></p>
                                    <ul style="margin-bottom: 0">
                                        <li>- Use Categories, Tags, or Attributes as the source for component options.
                                        </li>
                                        <li>- Get the lifetime update & premium support.</li>
                                    </ul>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}

				function enqueue_scripts() {
					if ( get_option( '_wooco_selector', 'ddslick' ) === 'ddslick' ) {
						wp_enqueue_script( 'ddslick', WOOCO_URI . 'assets/libs/ddslick/jquery.ddslick.min.js', array( 'jquery' ), WOOCO_VERSION, true );
					}

					if ( get_option( '_wooco_selector', 'ddslick' ) === 'select2' ) {
						wp_enqueue_style( 'select2' );
						wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', array( 'jquery' ), WOOCO_VERSION, true );
					}

					wp_enqueue_style( 'wooco-frontend', WOOCO_URI . 'assets/css/frontend.css', array(), WOOCO_VERSION );
					wp_enqueue_script( 'wooco-frontend', WOOCO_URI . 'assets/js/frontend.js', array( 'jquery' ), WOOCO_VERSION, true );
					wp_localize_script( 'wooco-frontend', 'wooco_vars', array(
							'price_decimals'           => wc_get_price_decimals(),
							'price_format'             => get_woocommerce_price_format(),
							'price_thousand_separator' => wc_get_price_thousand_separator(),
							'price_decimal_separator'  => wc_get_price_decimal_separator(),
							'currency_symbol'          => get_woocommerce_currency_symbol(),
							'trim_zeros'               => apply_filters( 'woocommerce_price_trim_zeros', false ),
							'selector'                 => get_option( '_wooco_selector', 'ddslick' ),
							'change_price'             => get_option( '_wooco_change_price', 'yes' ),
							'price_selector'           => get_option( '_wooco_change_price_custom', '' ),
							'product_link'             => get_option( '_wooco_product_link', 'no' ),
							'show_alert'               => get_option( '_wooco_show_alert', 'load' ),
							'hide_component_name'      => get_option( '_wooco_hide_component_name', 'yes' ),
							'total_text'               => self::localization( 'total', esc_html__( 'Total price:', 'wpc-composite-products' ) ),
							'saved_text'               => self::localization( 'saved', esc_html__( '(saved [d])', 'wpc-composite-products' ) ),
							'alert_min'                => self::localization( 'alert_min', esc_html__( 'Please choose at least a total quantity of [min] products before adding this composite to the cart.', 'wpc-composite-products' ) ),
							'alert_max'                => self::localization( 'alert_max', esc_html__( 'Sorry, you can only choose at max a total quantity of [max] products before adding this composite to the cart.', 'wpc-composite-products' ) ),
							'alert_same'               => self::localization( 'alert_same', esc_html__( 'Please select a different product for each component.', 'wpc-composite-products' ) ),
							'alert_selection'          => self::localization( 'alert_selection', esc_html__( 'Please choose a purchasable product for the component [name] before adding this composite to the cart.', 'wpc-composite-products' ) )
						)
					);
				}

				function admin_enqueue_scripts() {
					wp_enqueue_style( 'wooco-backend', WOOCO_URI . 'assets/css/backend.css', array(), WOOCO_VERSION );
					wp_enqueue_script( 'wooco-backend', WOOCO_URI . 'assets/js/backend.js', array(
						'jquery',
						'jquery-ui-dialog',
						'jquery-ui-sortable'
					), WOOCO_VERSION, true );
				}

				function action_links( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$settings             = '<a href="' . admin_url( 'admin.php?page=wpclever-wooco&tab=settings' ) . '">' . esc_html__( 'Settings', 'wpc-composite-products' ) . '</a>';
						$links['wpc-premium'] = '<a href="' . admin_url( 'admin.php?page=wpclever-wooco&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'wpc-composite-products' ) . '</a>';
						array_unshift( $links, $settings );
					}

					return (array) $links;
				}

				function row_meta( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$row_meta = array(
							'docs'    => '<a href="' . esc_url( WOOCO_DOCS ) . '" target="_blank">' . esc_html__( 'Docs', 'wpc-composite-products' ) . '</a>',
							'support' => '<a href="' . esc_url( WOOCO_DISCUSSION ) . '" target="_blank">' . esc_html__( 'Community support', 'wpc-composite-products' ) . '</a>',
						);

						return array_merge( $links, $row_meta );
					}

					return (array) $links;
				}

				function cart_contents_count( $count ) {
					$cart_contents_count = get_option( '_wooco_cart_contents_count', 'composite' );

					if ( $cart_contents_count !== 'both' ) {
						$cart_contents = WC()->cart->cart_contents;

						foreach ( $cart_contents as $cart_item_key => $cart_item ) {
							if ( ( $cart_contents_count === 'component_products' ) && ! empty( $cart_item['wooco_ids'] ) ) {
								$count -= $cart_item['quantity'];
							}

							if ( ( $cart_contents_count === 'composite' ) && ! empty( $cart_item['wooco_parent_id'] ) ) {
								$count -= $cart_item['quantity'];
							}
						}
					}

					return $count;
				}

				function cart_item_name( $name, $item ) {
					if ( isset( $item['wooco_parent_id'] ) && ! empty( $item['wooco_parent_id'] ) ) {
						if ( ( get_option( '_wooco_hide_component_name', 'yes' ) === 'no' ) && ! empty( $item['wooco_component'] ) ) {
							$_name = $item['wooco_component'] . ': ' . $name;
						} else {
							$_name = $name;
						}

						if ( get_option( '_wooco_hide_composite_name', 'no' ) === 'no' ) {
							if ( strpos( $name, '</a>' ) !== false ) {
								$_name = '<a href="' . get_permalink( $item['wooco_parent_id'] ) . '">' . get_the_title( $item['wooco_parent_id'] ) . '</a>' . apply_filters( 'wooco_name_separator', ' &rarr; ' ) . $_name;
							} else {
								$_name = get_the_title( $item['wooco_parent_id'] ) . apply_filters( 'wooco_name_separator', ' &rarr; ' ) . $_name;
							}
						}

						return apply_filters( 'wooco_cart_item_name', $_name, $name, $item );
					}

					return $name;
				}

				function order_formatted_line_subtotal( $subtotal, $item ) {
					if ( ! empty( $item['wooco_ids'] ) && isset( $item['wooco_price'] ) && ( $item['wooco_price'] !== '' ) ) {
						return wc_price( (float) $item['wooco_price'] * $item['quantity'] );
					}

					return $subtotal;
				}

				function cart_item_price( $price, $cart_item ) {
					if ( isset( $cart_item['wooco_ids'], $cart_item['wooco_keys'], $cart_item['wooco_price'] ) && method_exists( $cart_item['data'], 'get_pricing' ) && ( $cart_item['data']->get_pricing() !== 'only' ) ) {
						// composite
						return wc_price( $cart_item['wooco_price'] );
					}

					if ( isset( $cart_item['wooco_parent_key'] ) ) {
						// component products
						$cart_parent_key = $cart_item['wooco_parent_key'];

						if ( isset( WC()->cart->cart_contents[ $cart_parent_key ] ) && method_exists( WC()->cart->cart_contents[ $cart_parent_key ]['data'], 'get_pricing' ) && ( WC()->cart->cart_contents[ $cart_parent_key ]['data']->get_pricing() === 'only' ) ) {
							// return original price when pricing is only
							$item_product = wc_get_product( $cart_item['data']->get_id() );

							return wc_price( wc_get_price_to_display( $item_product ) );
						}
					}

					return $price;
				}

				function cart_item_subtotal( $subtotal, $cart_item = null ) {
					if ( isset( $cart_item['wooco_ids'], $cart_item['wooco_keys'], $cart_item['wooco_price'] ) && method_exists( $cart_item['data'], 'get_pricing' ) && ( $cart_item['data']->get_pricing() !== 'only' ) ) {
						// composite
						return wc_price( $cart_item['wooco_price'] * $cart_item['quantity'] );
					}

					if ( isset( $cart_item['wooco_parent_key'] ) ) {
						// component products
						$cart_parent_key = $cart_item['wooco_parent_key'];

						if ( isset( WC()->cart->cart_contents[ $cart_parent_key ] ) && method_exists( WC()->cart->cart_contents[ $cart_parent_key ]['data'], 'get_pricing' ) && ( WC()->cart->cart_contents[ $cart_parent_key ]['data']->get_pricing() === 'only' ) ) {
							// return original price when pricing is only
							$item_product = wc_get_product( $cart_item['data']->get_id() );

							return wc_price( wc_get_price_to_display( $item_product, array( 'qty' => $cart_item['quantity'] ) ) );
						}
					}

					return $subtotal;
				}

				function update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'] ) ) {
						foreach ( WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'] as $key ) {
							if ( isset( WC()->cart->cart_contents[ $key ] ) ) {
								if ( $quantity <= 0 ) {
									$qty = 0;
								} else {
									$qty = $quantity * ( WC()->cart->cart_contents[ $key ]['wooco_qty'] ?: 1 );
								}

								WC()->cart->set_quantity( $key, $qty, false );
							}
						}
					}
				}

				function cart_item_removed( $cart_item_key, $cart ) {
					$new_keys = [];

					foreach ( $cart->cart_contents as $cart_k => $cart_i ) {
						if ( ! empty( $cart_i['woosb_key'] ) ) {
							$new_keys[ $cart_k ] = $cart_i['woosb_key'];
						}
					}

					if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['wooco_keys'] ) ) {
						$keys = $cart->removed_cart_contents[ $cart_item_key ]['wooco_keys'];

						foreach ( $keys as $key ) {
							$cart->remove_cart_item( $key );

							if ( $new_key = array_search( $key, $new_keys ) ) {
								$cart->remove_cart_item( $new_key );
							}
						}
					}
				}

				function check_in_cart( $product_id ) {
					foreach ( WC()->cart->get_cart() as $cart_item ) {
						if ( $cart_item['product_id'] === $product_id ) {
							return true;
						}
					}

					return false;
				}

				function add_cart_item_data( $cart_item_data, $product_id ) {
					$_product = wc_get_product( $product_id );

					if ( $_product && $_product->is_type( 'composite' ) && get_post_meta( $product_id, 'wooco_components', true ) ) {
						// make sure this is a composite
						$ids = '';

						if ( isset( $_REQUEST['wooco_ids'] ) ) {
							$ids = $_REQUEST['wooco_ids'];
							unset( $_REQUEST['wooco_ids'] );
						}

						$ids = self::clean_ids( $ids );

						if ( ! empty( $ids ) ) {
							$cart_item_data['wooco_ids'] = $ids;
						}
					}

					return $cart_item_data;
				}

				function found_in_cart( $found_in_cart, $product_id ) {
					$_product = wc_get_product( $product_id );

					if ( $_product && $_product->is_type( 'composite' ) && self::check_in_cart( $product_id ) ) {
						return true;
					}

					return $found_in_cart;
				}

				function add_to_cart_validation( $passed, $product_id ) {
					$ids      = '';
					$_product = wc_get_product( $product_id );

					if ( $_product && $_product->is_type( 'composite' ) ) {
						if ( isset( $_REQUEST['wooco_ids'] ) ) {
							$ids = $_REQUEST['wooco_ids'];
						}

						$ids = self::clean_ids( $ids );
						$qty = isset( $_REQUEST['quantity'] ) ? (int) $_REQUEST['quantity'] : 1;

						if ( $items = self::get_items( $ids ) ) {
							foreach ( $items as $item ) {
								$_product = wc_get_product( $item['id'] );

								if ( ! $_product ) {
									wc_add_notice( esc_html__( 'One of the component products is unavailable.', 'wpc-composite-products' ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

									return false;
								}

								if ( $_product->is_type( 'variation' ) ) {
									$attributes = $_product->get_variation_attributes();

									foreach ( $attributes as $attribute ) {
										if ( empty( $attribute ) ) {
											wc_add_notice( sprintf( esc_html__( '"%s" is un-purchasable.', 'wpc-composite-products' ), esc_html( $_product->get_name() ) ), 'error' );
											wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

											return false;
										}
									}
								}

								if ( $_product->is_type( 'variable' ) || $_product->is_type( 'composite' ) ) {
									wc_add_notice( sprintf( esc_html__( '"%s" is un-purchasable.', 'wpc-composite-products' ), esc_html( $_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

									return false;
								}

								if ( ! $_product->is_in_stock() || ! $_product->is_purchasable() ) {
									wc_add_notice( sprintf( esc_html__( '"%s" is un-purchasable.', 'wpc-composite-products' ), esc_html( $_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

									return false;
								}

								if ( ! $_product->has_enough_stock( $item['qty'] * $qty ) ) {
									wc_add_notice( sprintf( esc_html__( '"%s" has not enough stock.', 'wpc-composite-products' ), esc_html( $_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

									return false;
								}

								if ( $_product->is_sold_individually() && self::check_in_cart( $item['id'] ) ) {
									wc_add_notice( sprintf( esc_html__( 'You cannot add another "%s" to your cart.', 'wpc-composite-products' ), esc_html( $_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

									return false;
								}

								if ( $_product->managing_stock() ) {
									$qty_in_cart = WC()->cart->get_cart_item_quantities();

									if ( isset( $qty_in_cart[ $_product->get_stock_managed_by_id() ] ) && ! $_product->has_enough_stock( $qty_in_cart[ $_product->get_stock_managed_by_id() ] + $item['qty'] * $qty ) ) {
										wc_add_notice( sprintf( esc_html__( '"%s" has not enough stock.', 'wpc-composite-products' ), esc_html( $_product->get_name() ) ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

										return false;
									}
								}

								if ( post_password_required( $item['id'] ) ) {
									wc_add_notice( sprintf( esc_html__( '"%s" is protected and cannot be purchased.', 'wpc-composite-products' ), esc_html( $_product->get_name() ) ), 'error' );
									wc_add_notice( esc_html__( 'You cannot add this composite products to the cart.', 'wpc-composite-products' ), 'error' );

									return false;
								}
							}
						}
					}

					return $passed;
				}

				function add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
					if ( ! empty( $cart_item_data['wooco_ids'] ) && ( $items = self::get_items( $cart_item_data['wooco_ids'] ) ) ) {
						self::add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
					}
				}

				function restore_cart_item( $cart_item_key ) {
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wooco_ids'] ) ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'] );

						$product_id = WC()->cart->cart_contents[ $cart_item_key ]['product_id'];
						$quantity   = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];

						if ( $items = self::get_items( WC()->cart->cart_contents[ $cart_item_key ]['wooco_ids'] ) ) {
							self::add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
						}
					}
				}

				function add_to_cart_items( $items, $cart_item_key, $product_id, $quantity ) {
					if ( apply_filters( 'wooco_exclude_components', false ) ) {
						return;
					}

					// save current key associated with wooco_parent_key
					WC()->cart->cart_contents[ $cart_item_key ]['wooco_key'] = $cart_item_key;

					// add child products
					$count = 0; // for same component product

					foreach ( $items as $item ) {
						$count ++;
						$item_id        = $item['id'];
						$item_qty       = $item['qty'];
						$item_price     = $item['price'];
						$item_component = $item['component'];
						$item_product   = wc_get_product( $item['id'] );

						if ( $item_product && ( 'trash' !== $item_product->get_status() ) && ( $item_id > 0 ) && ( $item_qty > 0 ) ) {
							$item_variation_id = 0;
							$item_variation    = array();

							if ( $item_product instanceof WC_Product_Variation ) {
								// ensure we don't add a variation to the cart directly by variation ID
								$item_variation_id = $item_id;
								$item_id           = $item_product->get_parent_id();
								$item_variation    = $item_product->get_variation_attributes();
							}

							// add to cart
							$item_data = array(
								'wooco_pos'        => $count,
								'wooco_qty'        => $item_qty,
								'wooco_price'      => $item_price,
								'wooco_component'  => $item_component,
								'wooco_parent_id'  => $product_id,
								'wooco_parent_key' => $cart_item_key
							);

							$item_key = WC()->cart->add_to_cart( $item_id, $item_qty * $quantity, $item_variation_id, $item_variation, $item_data );

							if ( empty( $item_key ) ) {
								// can't add the composite product
								if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'] ) ) {
									$keys = WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'];

									foreach ( $keys as $key ) {
										// remove all components
										WC()->cart->remove_cart_item( $key );
									}

									// remove the composite
									WC()->cart->remove_cart_item( $cart_item_key );
								}
							} elseif ( ! isset( WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'] ) || ! in_array( $item_key, WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'], true ) ) {
								// save current key
								WC()->cart->cart_contents[ $item_key ]['wooco_key'] = $item_key;
								// add keys
								WC()->cart->cart_contents[ $cart_item_key ]['wooco_keys'][] = $item_key;
							}
						}
					}
				}

				function before_mini_cart_contents() {
					WC()->cart->calculate_totals();
				}

				function before_calculate_totals( $cart_object ) {
					if ( ! defined( 'DOING_AJAX' ) && is_admin() ) {
						// This is necessary for WC 3.0+
						return;
					}

					$cart_contents = $cart_object->cart_contents;
					$new_keys      = [];

					foreach ( $cart_contents as $cart_k => $cart_i ) {
						if ( ! empty( $cart_i['wooco_key'] ) ) {
							$new_keys[ $cart_k ] = $cart_i['wooco_key'];
						}
					}

					foreach ( $cart_contents as $cart_item_key => $cart_item ) {
						// child product qty
						if ( ! empty( $cart_item['wooco_parent_key'] ) ) {
							$parent_new_key = array_search( $cart_item['wooco_parent_key'], $new_keys );

							// remove orphaned components
							if ( ! $parent_new_key || ! isset( $cart_contents[ $parent_new_key ] ) || ( isset( $cart_contents[ $parent_new_key ]['wooco_keys'] ) && ! in_array( $cart_item_key, $cart_contents[ $parent_new_key ]['wooco_keys'] ) ) ) {
								unset( $cart_contents[ $cart_item_key ] );
								continue;
							}

							// sync quantity
							if ( ! empty( $cart_item['wooco_qty'] ) ) {
								WC()->cart->cart_contents[ $cart_item_key ]['quantity'] = $cart_item['wooco_qty'] * $cart_contents[ $parent_new_key ]['quantity'];
							}
						}

						// child product price
						if ( ! empty( $cart_item['wooco_parent_id'] ) ) {
							$parent_product = wc_get_product( $cart_item['wooco_parent_id'] );

							if ( $parent_product && $parent_product->is_type( 'composite' ) && method_exists( $parent_product, 'get_pricing' ) ) {
								if ( $parent_product->get_pricing() === 'only' ) {
									$cart_item['data']->set_price( 0 );
								} else {
									if ( $cart_item['variation_id'] > 0 ) {
										$_product = wc_get_product( $cart_item['variation_id'] );
									} else {
										$_product = wc_get_product( $cart_item['product_id'] );
									}

									$new_price = false;
									$_price    = apply_filters( 'wooco_product_original_price', $_product->get_price(), $_product );

									if ( isset( $cart_item['wooco_price'] ) && ( $cart_item['wooco_price'] !== '' ) ) {
										$new_price = true;
										$_price    = self::get_new_price( $_price, $cart_item['wooco_price'] );
									}

									if ( $discount = self::get_discount( get_post_meta( $cart_item['wooco_parent_id'], 'wooco_discount_percent', true ) ) ) {
										$new_price = true;
										$_price    = $_price * ( 100 - $discount ) / 100;
									}

									if ( $new_price ) {
										// set new price for child product
										$cart_item['data']->set_price( (float) $_price );
									}
								}
							}
						}

						// main product price
						if ( ! empty( $cart_item['wooco_ids'] ) && $cart_item['data']->is_type( 'composite' ) && method_exists( $cart_item['data'], 'get_pricing' ) && ( $cart_item['data']->get_pricing() !== 'only' ) ) {
							$price = $cart_item['data']->get_pricing() === 'include' ? wc_get_price_to_display( $cart_item['data'] ) : 0;

							if ( ! empty( $cart_item['wooco_keys'] ) ) {
								foreach ( $cart_item['wooco_keys'] as $key ) {
									if ( isset( $cart_contents[ $key ] ) ) {
										$_product = $cart_contents[ $key ]['data'];
										$_price   = apply_filters( 'wooco_product_original_price', $_product->get_price(), $_product );

										if ( isset( $cart_contents[ $key ]['wooco_price'] ) && ( $cart_contents[ $key ]['wooco_price'] !== '' ) ) {
											$_price = self::get_new_price( $_price, $cart_contents[ $key ]['wooco_price'] );
										}

										if ( $discount = self::get_discount( get_post_meta( $cart_item['product_id'], 'wooco_discount_percent', true ) ) ) {
											$_price = $_price * ( 100 - $discount ) / 100;
										}

										$price += wc_get_price_to_display( $_product, array(
											'price' => $_price,
											'qty'   => $cart_contents[ $key ]['wooco_qty']
										) );
									}
								}
							}

							WC()->cart->cart_contents[ $cart_item_key ]['wooco_price'] = $price;

							if ( $cart_item['data']->get_pricing() === 'exclude' ) {
								$cart_item['data']->set_price( 0 );
							}
						}
					}
				}

				function cart_item_visible( $visible, $item ) {
					if ( isset( $item['wooco_parent_id'] ) ) {
						return false;
					}

					return $visible;
				}

				function cart_item_class( $class, $item ) {
					if ( isset( $item['wooco_parent_id'] ) ) {
						$class .= ' wooco-cart-item wooco-cart-child wooco-item-child';
					} elseif ( isset( $item['wooco_ids'] ) ) {
						$class .= ' wooco-cart-item wooco-cart-parent wooco-item-parent';

						if ( get_option( '_wooco_hide_component', 'no' ) !== 'no' ) {
							$class .= ' wooco-hide-component';
						}
					}

					return $class;
				}

				function get_item_data( $item_data, $cart_item ) {
					if ( empty( $cart_item['wooco_ids'] ) ) {
						return $item_data;
					}

					$items_str = array();

					if ( $items = self::get_items( $cart_item['wooco_ids'] ) ) {
						foreach ( $items as $item ) {
							if ( ( get_option( '_wooco_hide_component_name', 'yes' ) === 'no' ) && ! empty( $item['component'] ) ) {
								$items_str[] = apply_filters( 'wooco_order_component_product_name', $item['component'] . ': ' . $item['qty'] * $cart_item['quantity'] . ' × ' . get_the_title( $item['id'] ), $item, $cart_item );
							} else {
								$items_str[] = apply_filters( 'wooco_order_component_product_name', $item['qty'] * $cart_item['quantity'] . ' × ' . get_the_title( $item['id'] ), $item, $cart_item );
							}
						}
					}

					if ( ! empty( $items_str ) ) {
						$item_data[] = array(
							'key'     => esc_html__( 'Components', 'wpc-composite-products' ),
							'value'   => apply_filters( 'wooco_order_component_product_names', implode( '; ', $items_str ), $items ),
							'display' => '',
						);
					}

					return $item_data;
				}

				function checkout_create_order_line_item( $cart_item, $cart_item_key, $values, $order ) {
					if ( empty( $values['wooco_ids'] ) ) {
						return;
					}

					$items_str = array();

					if ( $items = self::get_items( $values['wooco_ids'] ) ) {
						foreach ( $items as $item ) {
							if ( ( get_option( '_wooco_hide_component_name', 'yes' ) === 'no' ) && ! empty( $item['component'] ) ) {
								$items_str[] = apply_filters( 'wooco_order_component_product_name', $item['component'] . ': ' . $item['qty'] . ' × ' . get_the_title( $item['id'] ), $item, $cart_item );
							} else {
								$items_str[] = apply_filters( 'wooco_order_component_product_name', $item['qty'] . ' × ' . get_the_title( $item['id'] ), $item, $cart_item );
							}
						}

						$cart_item->add_meta_data( esc_html__( 'Components', 'wpc-composite-products' ), apply_filters( 'wooco_order_component_product_names', implode( '; ', $items_str ) ) );
					}
				}

				function order_item_get_formatted_meta_data( $formatted_meta ) {
					foreach ( $formatted_meta as $key => $meta ) {
						if ( ( $meta->key === 'wooco_ids' ) || ( $meta->key === 'wooco_parent_id' ) || ( $meta->key === 'wooco_qty' ) || ( $meta->key === 'wooco_price' ) || ( $meta->key === 'wooco_component' ) ) {
							unset( $formatted_meta[ $key ] );
						}
					}

					return $formatted_meta;
				}

				function add_order_item_meta( $item, $cart_item_key, $values ) {
					if ( isset( $values['wooco_parent_id'] ) ) {
						$item->update_meta_data( 'wooco_parent_id', $values['wooco_parent_id'] );
					}

					if ( isset( $values['wooco_qty'] ) ) {
						$item->update_meta_data( 'wooco_qty', $values['wooco_qty'] );
					}

					if ( isset( $values['wooco_ids'] ) ) {
						$item->update_meta_data( 'wooco_ids', $values['wooco_ids'] );
					}

					if ( isset( $values['wooco_price'] ) ) {
						$item->update_meta_data( 'wooco_price', $values['wooco_price'] );
					}

					if ( isset( $values['wooco_component'] ) ) {
						$item->update_meta_data( 'wooco_component', $values['wooco_component'] );
					}
				}

				function hidden_order_itemmeta( $hidden ) {
					return array_merge( $hidden, array(
						'wooco_parent_id',
						'wooco_qty',
						'wooco_ids',
						'wooco_price',
						'wooco_pos',
						'wooco_component'
					) );
				}

				function before_order_itemmeta( $item_id, $item ) {
					if ( $parent_id = $item->get_meta( 'wooco_parent_id' ) ) {
						if ( ( $component = $item->get_meta( 'wooco_component' ) ) && ! empty( $component ) ) {
							echo sprintf( esc_html__( '(in %s)', 'wpc-composite-products' ), get_the_title( $parent_id ) . apply_filters( 'wooco_name_separator', ' &rarr; ' ) . $component );
						} else {
							echo sprintf( esc_html__( '(in %s)', 'wpc-composite-products' ), get_the_title( $parent_id ) );
						}
					}
				}

				function get_cart_item_from_session( $cart_item, $item_session_values ) {
					if ( isset( $item_session_values['wooco_ids'] ) && ! empty( $item_session_values['wooco_ids'] ) ) {
						$cart_item['wooco_ids']   = $item_session_values['wooco_ids'];
						$cart_item['wooco_price'] = isset( $item_session_values['wooco_price'] ) ? $item_session_values['wooco_price'] : '';
					}

					if ( isset( $item_session_values['wooco_parent_id'] ) && ! empty( $item_session_values['wooco_parent_id'] ) ) {
						$cart_item['wooco_parent_id']  = $item_session_values['wooco_parent_id'];
						$cart_item['wooco_pos']        = isset( $item_session_values['wooco_pos'] ) ? $item_session_values['wooco_pos'] : '';
						$cart_item['wooco_qty']        = isset( $item_session_values['wooco_qty'] ) ? $item_session_values['wooco_qty'] : '';
						$cart_item['wooco_price']      = isset( $item_session_values['wooco_price'] ) ? $item_session_values['wooco_price'] : '';
						$cart_item['wooco_component']  = isset( $item_session_values['wooco_component'] ) ? $item_session_values['wooco_component'] : '';
						$cart_item['wooco_parent_key'] = isset( $item_session_values['wooco_parent_key'] ) ? $item_session_values['wooco_parent_key'] : '';
					}

					return $cart_item;
				}

				function display_post_states( $states, $post ) {
					if ( 'product' == get_post_type( $post->ID ) ) {
						if ( ( $product = wc_get_product( $post->ID ) ) && $product->is_type( 'composite' ) ) {
							$count = 0;

							if ( $components = $product->get_components() ) {
								$count = count( $components );
							}

							$states[] = apply_filters( 'wooco_post_states', '<span class="wooco-state">' . sprintf( esc_html__( 'Composite (%s)', 'wpc-composite-products' ), $count ) . '</span>', $count, $product );
						}
					}

					return $states;
				}

				function cart_item_remove_link( $link, $cart_item_key ) {
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wooco_parent_key'] ) ) {
						$parent_key = WC()->cart->cart_contents[ $cart_item_key ]['wooco_parent_key'];

						if ( isset( WC()->cart->cart_contents[ $parent_key ] ) || array_search( $parent_key, array_column( WC()->cart->cart_contents, 'wooco_key', 'key' ) ) ) {
							return '';
						}
					}

					return $link;
				}

				function cart_item_quantity( $quantity, $cart_item_key, $cart_item ) {
					// add qty as text - not input
					if ( isset( $cart_item['wooco_parent_id'] ) ) {
						return $cart_item['quantity'];
					}

					return $quantity;
				}

				function product_type_selector( $types ) {
					$types['composite'] = esc_html__( 'Smart composite', 'wpc-composite-products' );

					return $types;
				}

				function product_data_tabs( $tabs ) {
					$tabs['composite'] = array(
						'label'  => esc_html__( 'Components', 'wpc-composite-products' ),
						'target' => 'wooco_settings',
						'class'  => array( 'show_if_composite' ),
					);

					return $tabs;
				}

				function product_data_panels() {
					global $post;
					$post_id = $post->ID;
					?>
                    <div id='wooco_settings' class='panel woocommerce_options_panel wooco_table'>
                        <table class="wooco_components">
                            <thead></thead>
                            <tbody>
							<?php
							$components = get_post_meta( $post_id, 'wooco_components', true );

							if ( is_array( $components ) ) {
								foreach ( $components as $component ) {
									self::component( false, $component );
								}
							} else {
								self::component( true );
							}
							?>
                            <div id="wooco_product_types" style="display: none"
                                 title="<?php esc_html_e( 'Product Type', 'wpc-composite-products' ); ?>">
								<?php
								$product_types_arr = [];

								if ( $product_types = wc_get_product_types() ) {
									foreach ( $product_types as $key => $name ) {
										$product_types_arr[] = '<strong>' . $key . '</strong> (' . $name . ')';
									}
								}

								if ( ! empty( $product_types_arr ) ) {
									echo implode( ', ', $product_types_arr );
								}
								?>
                            </div>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td>
                                    <div>
                                        <a href="#" class="wooco_add_component button">
											<?php esc_html_e( '+ Add component', 'wpc-composite-products' ); ?>
                                        </a>
                                        <a href="#" class="wooco_expand_all">
											<?php esc_html_e( 'Expand All', 'wpc-composite-products' ); ?>
                                        </a>
                                        <a href="#" class="wooco_collapse_all">
											<?php esc_html_e( 'Collapse All', 'wpc-composite-products' ); ?>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#" class="wooco_save_components button button-primary">
											<?php esc_html_e( 'Save components', 'wpc-composite-products' ); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                        <table>
                            <tr class="wooco_tr_space">
                                <th><?php esc_html_e( 'Pricing', 'wpc-composite-products' ); ?></th>
                                <td>
                                    <select id="wooco_pricing" name="wooco_pricing">
                                        <option value="only" <?php echo esc_attr( get_post_meta( $post_id, 'wooco_pricing', true ) === 'only' ? 'selected' : '' ); ?>><?php esc_html_e( 'Only base price', 'wpc-composite-products' ); ?></option>
                                        <option value="include" <?php echo esc_attr( get_post_meta( $post_id, 'wooco_pricing', true ) === 'include' ? 'selected' : '' ); ?>><?php esc_html_e( 'Include base price', 'wpc-composite-products' ); ?></option>
                                        <option value="exclude" <?php echo esc_attr( get_post_meta( $post_id, 'wooco_pricing', true ) === 'exclude' ? 'selected' : '' ); ?>><?php esc_html_e( 'Exclude base price', 'wpc-composite-products' ); ?></option>
                                    </select>
                                    <span class="woocommerce-help-tip"
                                          data-tip="<?php esc_attr_e( '"Base price" is the price set in the General tab. When "Only base price" is chosen, the total price won\'t change despite the price changes in variable components.', 'wpc-composite-products' ); ?>"></span>
                                    <span style="color: #c9356e">* <?php esc_html_e( 'Always put a price in the General tab to display the Add to Cart button. This is also the base price.', 'wpc-composite-products' ); ?></span>
                                </td>
                            </tr>
                            <tr class="wooco_tr_space">
                                <th><?php esc_html_e( 'Discount', 'wpc-composite-products' ); ?></th>
                                <td style="vertical-align: middle; line-height: 30px;">
                                    <input id="wooco_discount_percent" name="wooco_discount_percent" type="number"
                                           min="0.0001" step="0.0001"
                                           max="99.9999"
                                           value="<?php echo esc_attr( get_post_meta( $post_id, 'wooco_discount_percent', true ) ?: '' ); ?>"
                                           style="width: 80px"/>%. <span class="woocommerce-help-tip"
                                                                         data-tip="<?php esc_attr_e( 'The universal percentage discount will be applied equally on each component\'s price, not on the total.', 'wpc-composite-products' ); ?>"></span>
                                </td>
                            </tr>
                            <tr class="wooco_tr_space">
								<?php
								$min = get_post_meta( $post_id, 'wooco_qty_min', true ) ?: '';
								$max = get_post_meta( $post_id, 'wooco_qty_max', true ) ?: '';

								if ( class_exists( 'WPCleverWoopq' ) && ( get_option( '_woopq_decimal', 'no' ) === 'yes' ) ) {
									$step = '0.000001';
								} else {
									$step = '1';

									if ( ! empty( $min ) ) {
										$min = (int) $min;
									}

									if ( ! empty( $max ) ) {
										$max = (int) $max;
									}
								}
								?>
                                <th><?php esc_html_e( 'Quantity', 'wpc-composite-products' ); ?></th>
                                <td style="vertical-align: middle; line-height: 30px;">
                                    Min <input name="wooco_qty_min" type="number"
                                               min="0" step="<?php echo esc_attr( $step ); ?>"
                                               value="<?php echo esc_attr( $min ); ?>"
                                               style="width: 80px"/> Max <input name="wooco_qty_max" type="number"
                                                                                min="0"
                                                                                step="<?php echo esc_attr( $step ); ?>"
                                                                                value="<?php echo esc_attr( $max ); ?>"
                                                                                style="width: 80px"/>
                                </td>
                            </tr>
                            <tr class="wooco_tr_space">
                                <th><?php esc_html_e( 'Same products', 'wpc-composite-products' ); ?></th>
                                <td>
                                    <select id="wooco_same_products" name="wooco_same_products">
                                        <option value="allow" <?php echo esc_attr( get_post_meta( $post_id, 'wooco_same_products', true ) === 'allow' ? 'selected' : '' ); ?>><?php esc_html_e( 'Allow', 'wpc-composite-products' ); ?></option>
                                        <option value="do_not_allow" <?php echo esc_attr( get_post_meta( $post_id, 'wooco_same_products', true ) === 'do_not_allow' ? 'selected' : '' ); ?>><?php esc_html_e( 'Do not allow', 'wpc-composite-products' ); ?></option>
                                    </select> <span class="woocommerce-help-tip"
                                                    data-tip="<?php esc_attr_e( 'Allow/Do not allow the buyer to choose the same products in the components.', 'wpc-composite-products' ); ?>"></span>
                                </td>
                            </tr>
                            <tr class="wooco_tr_space">
                                <th><?php esc_html_e( 'Shipping fee', 'wpc-composite-products' ); ?></th>
                                <td>
                                    <select id="wooco_shipping_fee" name="wooco_shipping_fee">
                                        <option value="whole" <?php echo esc_attr( get_post_meta( $post_id, 'wooco_shipping_fee', true ) === 'whole' ? 'selected' : '' ); ?>><?php esc_html_e( 'Apply to the whole composite', 'wpc-composite-products' ); ?></option>
                                        <option value="each" <?php echo esc_attr( get_post_meta( $post_id, 'wooco_shipping_fee', true ) === 'each' ? 'selected' : '' ); ?>><?php esc_html_e( 'Apply to each component product', 'wpc-composite-products' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="wooco_tr_space">
                                <th><?php esc_html_e( 'Custom display price', 'wpc-composite-products' ); ?></th>
                                <td>
                                    <input type="text" name="wooco_custom_price"
                                           value="<?php echo stripslashes( get_post_meta( $post_id, 'wooco_custom_price', true ) ); ?>"/>
                                    E.g: <code>From $10 to $100</code>
                                </td>
                            </tr>
                            <tr class="wooco_tr_space">
                                <th><?php esc_html_e( 'Above text', 'wpc-composite-products' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <textarea
                                                name="wooco_before_text"><?php echo stripslashes( get_post_meta( $post_id, 'wooco_before_text', true ) ); ?></textarea>
                                    </div>
                                </td>
                            </tr>
                            <tr class="wooco_tr_space">
                                <th><?php esc_html_e( 'Under text', 'wpc-composite-products' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <textarea
                                                name="wooco_after_text"><?php echo stripslashes( get_post_meta( $post_id, 'wooco_after_text', true ) ); ?></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
					<?php
				}

				function process_meta( $post_id ) {
					if ( isset( $_POST['product-type'] ) && ( $_POST['product-type'] !== 'composite' ) ) {
						delete_post_meta( $post_id, 'wooco_components' );
					}
				}

				function process_meta_composite( $post_id ) {
					if ( isset( $_POST['wooco_components'] ) ) {
						update_post_meta( $post_id, 'wooco_components', self::format_array( $_POST['wooco_components'] ) );
					} else {
						delete_post_meta( $post_id, 'wooco_components' );
					}

					if ( isset( $_POST['wooco_pricing'] ) ) {
						update_post_meta( $post_id, 'wooco_pricing', sanitize_text_field( $_POST['wooco_pricing'] ) );
					}

					if ( isset( $_POST['wooco_discount_percent'] ) ) {
						update_post_meta( $post_id, 'wooco_discount_percent', sanitize_text_field( $_POST['wooco_discount_percent'] ) );
					}

					if ( isset( $_POST['wooco_qty_min'] ) ) {
						update_post_meta( $post_id, 'wooco_qty_min', sanitize_text_field( $_POST['wooco_qty_min'] ) );
					}

					if ( isset( $_POST['wooco_qty_max'] ) ) {
						update_post_meta( $post_id, 'wooco_qty_max', sanitize_text_field( $_POST['wooco_qty_max'] ) );
					}

					if ( isset( $_POST['wooco_same_products'] ) ) {
						update_post_meta( $post_id, 'wooco_same_products', sanitize_text_field( $_POST['wooco_same_products'] ) );
					}

					if ( isset( $_POST['wooco_shipping_fee'] ) ) {
						update_post_meta( $post_id, 'wooco_shipping_fee', sanitize_text_field( $_POST['wooco_shipping_fee'] ) );
					}

					if ( ! empty( $_POST['wooco_custom_price'] ) ) {
						update_post_meta( $post_id, 'wooco_custom_price', addslashes( $_POST['wooco_custom_price'] ) );
					} else {
						delete_post_meta( $post_id, 'wooco_custom_price' );
					}

					if ( ! empty( $_POST['wooco_before_text'] ) ) {
						update_post_meta( $post_id, 'wooco_before_text', addslashes( $_POST['wooco_before_text'] ) );
					} else {
						delete_post_meta( $post_id, 'wooco_before_text' );
					}

					if ( ! empty( $_POST['wooco_after_text'] ) ) {
						update_post_meta( $post_id, 'wooco_after_text', addslashes( $_POST['wooco_after_text'] ) );
					} else {
						delete_post_meta( $post_id, 'wooco_after_text' );
					}
				}

				function add_to_cart_form() {
					self::show_items();
					wc_get_template( 'single-product/add-to-cart/simple.php' );
				}

				function add_to_cart_button() {
					global $product;

					if ( $product && $product->is_type( 'composite' ) ) {
						echo '<input name="wooco_ids" class="wooco-ids wooco-ids-' . esc_attr( $product->get_id() ) . '" type="hidden" value=""/>';
					}
				}

				function loop_add_to_cart_link( $link, $product ) {
					if ( $product->is_type( 'composite' ) ) {
						$link = str_replace( 'ajax_add_to_cart', '', $link );
					}

					return $link;
				}

				function cart_shipping_packages( $packages ) {
					if ( ! empty( $packages ) ) {
						foreach ( $packages as $package_key => $package ) {
							if ( ! empty( $package['contents'] ) ) {
								foreach ( $package['contents'] as $cart_item_key => $cart_item ) {
									if ( ! empty( $cart_item['wooco_parent_id'] ) ) {
										if ( get_post_meta( $cart_item['wooco_parent_id'], 'wooco_shipping_fee', true ) !== 'each' ) {
											unset( $packages[ $package_key ]['contents'][ $cart_item_key ] );
										}
									}

									if ( ! empty( $cart_item['wooco_ids'] ) ) {
										if ( get_post_meta( $cart_item['data']->get_id(), 'wooco_shipping_fee', true ) === 'each' ) {
											unset( $packages[ $package_key ]['contents'][ $cart_item_key ] );
										}
									}
								}
							}
						}
					}

					return $packages;
				}

				function get_price_html( $price, $product ) {
					if ( $product->is_type( 'composite' ) ) {
						$product_id   = $product->get_id();
						$custom_price = stripslashes( get_post_meta( $product_id, 'wooco_custom_price', true ) );

						if ( ! empty( $custom_price ) ) {
							return $custom_price;
						}

						if ( $product->get_pricing() !== 'only' ) {
							switch ( get_option( '_wooco_price_format', 'from_regular' ) ) {
								case 'from_regular':
									return esc_html__( 'From', 'wpc-composite-products' ) . ' ' . wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) );
									break;
								case 'from_sale':
									return esc_html__( 'From', 'wpc-composite-products' ) . ' ' . wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_price() ) ) );
									break;
							}
						}
					}

					return $price;
				}

				function product_price_class( $class ) {
					global $product;

					if ( $product && $product->is_type( 'composite' ) ) {
						$class .= ' wooco-price-' . $product->get_id();
					}

					return $class;
				}

				function order_again_cart_item_data( $item_data, $item ) {
					if ( isset( $item['wooco_ids'] ) ) {
						$item_data['wooco_ids']         = $item['wooco_ids'];
						$item_data['wooco_order_again'] = 'yes';
					}

					if ( isset( $item['wooco_parent_id'] ) ) {
						$item_data['wooco_order_again'] = 'yes';
						$item_data['wooco_parent_id']   = $item['wooco_parent_id'];
					}

					return $item_data;
				}

				function cart_loaded_from_session() {
					foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
						if ( isset( $cart_item['wooco_order_again'], $cart_item['wooco_parent_id'] ) ) {
							WC()->cart->remove_cart_item( $cart_item_key );
						}

						if ( isset( $cart_item['wooco_order_again'], $cart_item['wooco_ids'] ) ) {
							if ( $items = self::get_items( $cart_item['wooco_ids'] ) ) {
								self::add_to_cart_items( $items, $cart_item_key, $cart_item['product_id'], $cart_item['quantity'] );
							}
						}
					}
				}

				function coupon_is_valid_for_product( $valid, $product, $coupon, $item ) {
					if ( ( get_option( '_wooco_coupon_restrictions', 'no' ) === 'both' ) && ( isset( $item['wooco_parent_id'] ) || isset( $item['wooco_ids'] ) ) ) {
						// exclude both composite and component products
						return false;
					}

					if ( ( get_option( '_wooco_coupon_restrictions', 'no' ) === 'composite' ) && isset( $item['wooco_ids'] ) ) {
						// exclude composite
						return false;
					}

					if ( ( get_option( '_wooco_coupon_restrictions', 'no' ) === 'component' ) && isset( $item['wooco_parent_id'] ) ) {
						// exclude component products
						return false;
					}

					return $valid;
				}

				function show_items( $product = null ) {
					if ( ! $product ) {
						global $product;
					}

					if ( ! $product || ! $product->is_type( 'composite' ) ) {
						return;
					}

					$df_products = isset( $_GET['df'] ) ? explode( ',', sanitize_text_field( $_GET['df'] ) ) : array();
					$product_id  = $product->get_id();
					$order       = 1;

					do_action( 'wooco_before_wrap', $product );

					if ( $components = $product->get_components() ) {
						// get settings
						$selector          = get_option( '_wooco_selector', 'ddslick' );
						$show_price        = get_option( '_wooco_show_price', 'yes' ) === 'yes';
						$show_availability = get_option( '_wooco_show_availability', 'yes' ) === 'yes';
						$show_image        = get_option( '_wooco_show_image', 'yes' ) === 'yes';

						// option none image
						$option_none_image = $option_none_image_full = wc_placeholder_img_src();

						if ( ( get_option( '_wooco_option_none_image', 'placeholder' ) === 'product' ) && ( $product_image_id = $product->get_image_id() ) ) {
							$product_image          = wp_get_attachment_image_src( $product_image_id );
							$product_image_full     = wp_get_attachment_image_src( $product_image_id, 'full' );
							$option_none_image      = $product_image[0];
							$option_none_image_full = $product_image_full[0];
						}

						if ( ( get_option( '_wooco_option_none_image', 'placeholder' ) === 'custom' ) && ( $option_none_image_id = get_option( '_wooco_option_none_image_id' ) ) ) {
							$custom_image           = wp_get_attachment_image_src( $option_none_image_id );
							$custom_image_full      = wp_get_attachment_image_src( $option_none_image_id, 'full' );
							$option_none_image      = $custom_image[0];
							$option_none_image_full = $custom_image_full[0];
						}

						echo '<div class="wooco_wrap wooco-wrap wooco-wrap-' . $product_id . '" data-id="' . $product_id . '">';

						if ( $before_text = apply_filters( 'wooco_before_text', get_post_meta( $product_id, 'wooco_before_text', true ), $product_id ) ) {
							echo '<div class="wooco_before_text wooco-before-text wooco-text">' . do_shortcode( stripslashes( $before_text ) ) . '</div>';
						}

						do_action( 'wooco_before_components', $product );
						?>
                        <div class="wooco_components wooco-components"
                             data-percent="<?php echo esc_attr( $product->get_discount() ); ?>"
                             data-min="<?php echo esc_attr( get_post_meta( $product_id, 'wooco_qty_min', true ) ); ?>"
                             data-max="<?php echo esc_attr( get_post_meta( $product_id, 'wooco_qty_max', true ) ); ?>"
                             data-price="<?php echo esc_attr( wc_get_price_to_display( $product ) ); ?>"
                             data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ); ?>"
                             data-pricing="<?php echo esc_attr( $product->get_pricing() ); ?>"
                             data-same="<?php echo esc_attr( get_post_meta( $product_id, 'wooco_same_products', true ) === 'do_not_allow' ? 'no' : 'yes' ); ?>"
                             data-checkbox="<?php echo esc_attr( get_option( '_wooco_checkbox', 'no' ) ); ?>">
							<?php
							foreach ( $components as $component ) {
								$component_type = $component['type'];

								if ( in_array( $component_type, array( 'products', 'categories', 'tags' ) ) ) {
									$component_val = $component[ $component_type ];
								} else {
									$component_val = isset( $component['other'] ) ? $component['other'] : '';
								}

								$component_default    = isset( $component['default'] ) ? (int) $component['default'] : 0;
								$component_default    = isset( $df_products[ $order - 1 ] ) ? absint( $df_products[ $order - 1 ] ) : $component_default;
								$component_default    = apply_filters( 'wooco_component_default', $component_default, $component );
								$component['default'] = $component_default;
								$component_qty        = isset( $component['qty'] ) ? (float) $component['qty'] : 1;
								$component_custom_qty = isset( $component['custom_qty'] ) && $component['custom_qty'] === 'yes';
								$component_exclude    = isset( $component['exclude'] ) ? (string) $component['exclude'] : '';
								$component_orderby    = isset( $component['orderby'] ) ? (string) $component['orderby'] : 'default';
								$component_order      = isset( $component['order'] ) ? (string) $component['order'] : 'default';
								$component_price      = isset( $component['price'] ) ? self::format_price( $component['price'] ) : '';
								$component_products   = self::get_products( $component_type, $component_val, $component_orderby, $component_order, $component_exclude, $component_default, $component_qty, $component_price, $component_custom_qty );
								$component_class      = 'wooco_component wooco_component_' . $order . ' wooco_component_type_' . $component_type . ' wooco_component_has_' . ( $component_products ? count( $component_products ) : '0' );

								if ( ! $component_products && ( $component['optional'] === 'yes' ) ) {
									// have no products and isn't required, hide it
									continue;
								}

								if ( $component['optional'] !== 'yes' ) {
									$component_class .= ' wooco_component_required';
								}

								echo '<div class="' . esc_attr( $component_class ) . '">';
								do_action( 'wooco_before_component', $component, $order );

								if ( ! empty( $component['name'] ) ) {
									echo '<div class="wooco_component_name">' . $component['name'] . '</div>';
								}

								if ( ! empty( $component['desc'] ) ) {
									echo '<div class="wooco_component_desc">' . $component['desc'] . '</div>';
								}

								if ( ! $component_products ) {
									if ( $component['optional'] !== 'yes' ) {
										// have no product and required
										?>
                                        <div class="wooco_component_product wooco_component_product_none"
                                             data-name="<?php echo esc_attr( $component['name'] ); ?>"
                                             data-id="0" data-price="0" data-regular-price="0" data-new-price="0"
                                             data-qty="<?php echo esc_attr( $component_qty ); ?>"
                                             data-required="yes"></div>
										<?php
									}
								} elseif ( ( count( $component_products ) === 1 ) && ( $component['optional'] !== 'yes' || get_option( '_wooco_checkbox', 'no' ) === 'yes' ) ) {
									// only one product and required
									$only         = array_values( $component_products )[0];
									$only_product = wc_get_product( $only['id'] );

									if ( $only_product ) {
										if ( get_option( '_wooco_product_link', 'yes' ) !== 'no' ) {
											if ( ! $only_product->is_visible() && ! apply_filters( 'wooco_hidden_product_link', false ) ) {
												$only_product_name = $only['name'];
											} else {
												$only_product_name = '<a ' . ( get_option( '_wooco_product_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link" data-id="' . $only['id'] . '" data-context="wooco"' : '' ) . ' href="' . get_permalink( $only['id'] ) . '" ' . ( get_option( '_wooco_product_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . $only['name'] . '</a>';
											}
										} else {
											$only_product_name = $only['name'];
										}
										?>
                                        <div class="wooco_component_product wooco_component_product_only"
                                             data-name="<?php echo esc_attr( $component['name'] ); ?>"
                                             data-id="<?php echo esc_attr( $only['purchasable'] === 'yes' ? $only['id'] : 0 ); ?>"
                                             data-price="<?php echo esc_attr( $only['price'] ); ?>"
                                             data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $only_product, array( 'price' => $only_product->get_regular_price() ) ) ); ?>"
                                             data-new-price="<?php echo esc_attr( $component_price ); ?>"
                                             data-qty="<?php echo esc_attr( $component['qty'] ); ?>"
                                             data-required="<?php echo esc_attr( $component['optional'] !== 'yes' ? 'yes' : 'no' ); ?>">

											<?php if ( get_option( '_wooco_checkbox', 'no' ) === 'yes' ) { ?>
                                                <div class="wooco_component_product_checkbox">
                                                    <input class="wooco-checkbox"
                                                           type="checkbox" <?php echo( apply_filters( 'wooco_component_checkbox_checked', $component['qty'], $component ) ? 'checked="checked"' : '' ); ?>
														<?php echo( apply_filters( 'wooco_component_checkbox_disabled', $component['optional'] !== 'yes', $component ) ? 'disabled' : '' ); ?>/>
                                                </div>
											<?php } ?>

											<?php if ( $show_image ) { ?>
                                                <div class="wooco_component_product_image">
													<?php echo $only_product->get_image(); ?>
                                                </div>
											<?php } ?>

                                            <div class="wooco_component_product_info">
                                                <div class="wooco_component_product_name">
													<?php
													echo $only_product_name;
													do_action( 'wooco_after_component_product_name', $only_product, $product );
													?>
                                                </div>

                                                <div class="wooco_component_product_description">
													<?php echo html_entity_decode( $only['description'] ); ?>
                                                </div>
                                            </div>

											<?php if ( $component_custom_qty ) {
												$min = 0;
												$max = 1000;

												if ( ! empty( $component['min'] ) ) {
													$min = $component['min'];
												}

												if ( ! empty( $component['max'] ) ) {
													$max = $component['max'];
												}

												if ( class_exists( 'WPCleverWoopq' ) && ( get_option( '_woopq_decimal', 'no' ) === 'yes' ) ) {
													$step = get_option( '_woopq_step' ) ?: '1';
												} else {
													$step             = '1';
													$component['qty'] = (int) $component['qty'];
													$min              = (int) $min;
													$max              = (int) $max;
												}

												$qty_input = '<div class="wooco_component_product_qty wooco-qty">';
												$qty_input .= '<span class="wooco-qty-label">' . self::localization( 'qty_label', esc_html__( 'Qty:', 'wpc-composite-products' ) ) . '</span>';
												$qty_input .= '<span class="wooco-qty-input">';
												$qty_input .= '<span class="wooco_component_product_qty_btn wooco_component_product_qty_minus wooco-minus">-</span>';
												$qty_input .= '<input class="wooco_component_product_qty_input input-text text qty" type="number" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $component['qty'] ) . '"/>';
												$qty_input .= '<span class="wooco_component_product_qty_btn wooco_component_product_qty_plus wooco-plus">+</span>';
												$qty_input .= '</span>';
												$qty_input .= '</div>';

												echo apply_filters( 'wooco_qty_input', $qty_input, $component['qty'], $step, $min, $max );
											} ?>
                                        </div>
										<?php
									}
								} else {
									$option_none_image      = apply_filters( 'wooco_option_none_img_src', $option_none_image, $component_products, $component );
									$option_none_image_full = apply_filters( 'wooco_option_none_img_full', $option_none_image_full, $component_products, $component );
									?>
                                    <div class="wooco_component_product"
                                         data-id="0" data-price="0" data-regular-price="0" data-price-html=""
                                         data-name="<?php echo esc_attr( $component['name'] ); ?>"
                                         data-new-price="<?php echo esc_attr( $component_price ); ?>"
                                         data-qty="<?php echo esc_attr( $component['qty'] ); ?>"
                                         data-required="<?php echo esc_attr( $component['optional'] !== 'yes' ? 'yes' : 'no' ); ?>">

										<?php if ( get_option( '_wooco_checkbox', 'no' ) === 'yes' ) { ?>
                                            <div class="wooco_component_product_checkbox">
                                                <input class="wooco-checkbox"
                                                       type="checkbox" <?php echo( apply_filters( 'wooco_component_checkbox_checked', $component['qty'], $component ) ? 'checked="checked"' : '' ); ?>
													<?php echo( apply_filters( 'wooco_component_checkbox_disabled', $component['optional'] !== 'yes', $component ) ? 'disabled' : '' ); ?>/>
                                            </div>
										<?php } ?>

										<?php if ( ( $selector === 'select' ) && $show_image ) { ?>
                                            <div class="wooco_component_product_image">
												<?php echo '<img src="' . $option_none_image . '"/>'; ?>
                                            </div>
										<?php } ?>

                                        <div class="wooco_component_product_selection">
                                            <select class="wooco_component_product_select"
                                                    id="<?php echo esc_attr( 'wooco_component_product_select_' . $order ); ?>">
												<?php
												$option_none = self::localization( 'option_none', esc_html__( 'No, thanks. I don\'t need this', 'wpc-composite-products' ) );

												if ( ( get_option( '_wooco_checkbox', 'no' ) === 'no' ) && ( ( $component['optional'] === 'yes' ) || ( get_option( '_wooco_option_none_required', 'no' ) === 'yes' ) ) ) {
													echo '<option value="-1" data-pid="-1" data-qty="0" data-price="" data-regular-price="" data-link="" data-price-html="" data-imagesrc="' . esc_url( $option_none_image ) . '" data-imagefull="' . esc_url( $option_none_image_full ) . '" data-availability="" data-description="' . esc_attr( apply_filters( 'wooco_option_none_description', htmlentities( wc_price( 0 ) ), $component ) ) . '">' . esc_html( apply_filters( 'wooco_option_none', $option_none, $component ) ) . '</option>';
												}

												foreach ( $component_products as $component_product ) {
													echo '<option value="' . esc_attr( $component_product['purchasable'] === 'yes' ? $component_product['id'] : 0 ) . '" data-pid="' . esc_attr( $component_product['pid'] ) . '" data-price="' . esc_attr( $component_product['price'] ) . '" data-regular-price="' . esc_attr( $component_product['regular_price'] ) . '" data-link="' . esc_url( $component_product['link'] ) . '" data-price-html="' . esc_attr( $component_product['price_html'] ) . '" data-imagesrc="' . esc_url( $component_product['image'] ) . '" data-imagefull="' . esc_url( $component_product['image_full'] ) . '" data-description="' . esc_attr( $component_product['description'] ) . '" data-availability="' . esc_attr( $component_product['availability'] ) . '" ' . ( $component_product['id'] == $component['default'] ? 'selected' : '' ) . '>' . esc_html( $component_product['name'] ) . '</option>';
												}
												?>
                                            </select>
                                        </div>

										<?php
										if ( ( $selector === 'select' ) && $show_availability ) {
											echo '<div class="wooco_component_product_availability"></div>';
										}

										if ( ( $selector === 'select' ) && $show_price ) {
											echo '<div class="wooco_component_product_price"></div>';
										}

										if ( $component_custom_qty ) {
											$min = 0;
											$max = 1000;

											if ( ! empty( $component['min'] ) ) {
												$min = $component['min'];
											}

											if ( ! empty( $component['max'] ) ) {
												$max = $component['max'];
											}

											if ( class_exists( 'WPCleverWoopq' ) && ( get_option( '_woopq_decimal', 'no' ) === 'yes' ) ) {
												$step = get_option( '_woopq_step' ) ?: '1';
											} else {
												$step             = '1';
												$component['qty'] = (int) $component['qty'];
												$min              = (int) $min;
												$max              = (int) $max;
											}

											$qty_input = '<div class="wooco_component_product_qty wooco-qty">';
											$qty_input .= '<span class="wooco-qty-label">' . self::localization( 'qty_label', esc_html__( 'Qty:', 'wpc-composite-products' ) ) . '</span>';
											$qty_input .= '<span class="wooco-qty-input">';
											$qty_input .= '<span class="wooco_component_product_qty_btn wooco_component_product_qty_minus wooco-minus">-</span>';
											$qty_input .= '<input class="wooco_component_product_qty_input input-text text qty" type="number" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $component['qty'] ) . '"/>';
											$qty_input .= '<span class="wooco_component_product_qty_btn wooco_component_product_qty_plus wooco-plus">+</span>';
											$qty_input .= '</span>';
											$qty_input .= '</div>';

											echo apply_filters( 'wooco_qty_input', $qty_input, $component['qty'], $step, $min, $max );
										}
										?>
                                    </div>
									<?php
								}

								do_action( 'wooco_after_component', $component, $order );
								echo '</div>';
								$order ++;
							}
							?>
                        </div>
						<?php
						echo '<div class="wooco_total wooco-total wooco-text"></div>';

						if ( get_option( '_wooco_show_alert', 'load' ) !== 'no' ) {
							echo '<div class="wooco_alert wooco-alert wooco-text" style="display: none"></div>';
						}

						do_action( 'wooco_after_components', $product );

						if ( $after_text = apply_filters( 'wooco_after_text', get_post_meta( $product_id, 'wooco_after_text', true ), $product_id ) ) {
							echo '<div class="wooco_after_text wooco-after-text wooco-text">' . do_shortcode( stripslashes( $after_text ) ) . '</div>';
						}

						echo '</div>';
					}

					do_action( 'wooco_after_wrap', $product );
				}

				function get_products( $type, $val, $orderby, $order, $exclude = '', $default = 0, $qty = 1, $price = '', $custom_qty = false ) {
					$has_default           = false;
					$products              = array();
					$ids                   = array_unique( array_map( 'trim', explode( ',', $val ) ) );
					$exclude_ids           = $type != 'products' ? explode( ',', $exclude ) : array();
					$exclude_hidden        = apply_filters( 'wooco_exclude_hidden', get_option( '_wooco_exclude_hidden', 'no' ) === 'yes' );
					$exclude_unpurchasable = apply_filters( 'wooco_exclude_unpurchasable', get_option( '_wooco_exclude_unpurchasable', 'yes' ) === 'yes' );

					if ( $type !== 'products' ) {
						return false;
					}

					if ( ( $orderby === 'default' ) && ( $order === 'DESC' ) ) {
						$ids = array_reverse( $ids );
					}

					if ( $orderby === 'default' ) {
						$orderby = 'post__in';
					}

					// query args
					$args = array(
						'is_wooco' => true,
						'type'     => array_merge( array( 'variation' ), array_keys( wc_get_product_types() ) ),
						'include'  => $ids,
						'orderby'  => $orderby,
						'order'    => $order,
						'limit'    => 500
					);

					// order by price
					if ( $orderby === 'price' ) {
						$args['orderby']  = 'meta_value_num';
						$args['meta_key'] = '_price';
					}

					// filter
					$args = apply_filters( 'wooco_get_products_args', $args );

					// query products
					if ( $_products = wc_get_products( $args ) ) {
						foreach ( $_products as $_product ) {
							$_product_id = $_product->get_id();

							if ( in_array( $_product_id, $exclude_ids ) ) {
								continue;
							}

							if ( ! apply_filters( 'wooco_product_visible', true, $_product ) || ( ! $_product->is_visible() && $exclude_hidden ) ) {
								continue;
							}

							if ( $_product->is_type( 'variable' ) ) {
								$children = $_product->get_children();

								if ( ! empty( $children ) ) {
									foreach ( $children as $child ) {
										if ( in_array( $child, $exclude_ids ) ) {
											continue;
										}

										$child_product = wc_get_product( $child );

										if ( ! $child_product || ( ! $child_product->variation_is_visible() && $exclude_hidden ) || ( $exclude_unpurchasable && ! self::is_purchasable( $child_product, $qty ) ) ) {
											continue;
										}

										if ( ! in_array( $type, array( 'products', 'categories', 'tags', 'types' ) ) ) {
											// taxonomy
											if ( ! has_term( $ids, $type, $child ) ) {
												continue;
											}
										}

										$products[ 'pid_' . $child ] = self::get_product_data( $child_product, $qty, $price, $custom_qty );

										if ( $child == $default ) {
											$has_default = true;
										}
									}
								}
							} else {
								if ( $exclude_unpurchasable && ! self::is_purchasable( $_product, $qty ) ) {
									continue;
								}

								$products[ 'pid_' . $_product_id ] = self::get_product_data( $_product, $qty, $price, $custom_qty );

								if ( $_product_id == $default ) {
									$has_default = true;
								}
							}
						}

						if ( ! $has_default ) {
							// add default product
							if ( $product_default = wc_get_product( $default ) ) {
								if ( self::is_purchasable( $product_default, $qty ) || ! $exclude_unpurchasable ) {
									$products = array( 'pid_' . $default => self::get_product_data( $product_default, $qty, $price, $custom_qty ) ) + $products;
								}
							}
						}
					}

					if ( ! empty( $products ) ) {
						return $products;
					}

					return false;
				}

				function is_purchasable( $product, $qty ) {
					return $product->is_purchasable() && $product->is_in_stock() && $product->has_enough_stock( $qty ) && ( 'trash' !== $product->get_status() );
				}

				function get_product_data( $product, $qty = 1, $price = '', $custom_qty = false ) {
					// settings
					$show_price        = get_option( '_wooco_show_price', 'yes' ) === 'yes';
					$show_availability = get_option( '_wooco_show_availability', 'yes' ) === 'yes';
					$show_image        = get_option( '_wooco_show_image', 'yes' ) === 'yes';
					$show_qty          = get_option( '_wooco_show_qty', 'yes' ) === 'yes';

					if ( $show_image ) {
						if ( $product->get_image_id() ) {
							$_img          = wp_get_attachment_image_src( $product->get_image_id() );
							$_img_full     = wp_get_attachment_image_src( $product->get_image_id(), 'full' );
							$_img_src      = $_img[0];
							$_img_full_src = $_img_full[0];
						} else {
							$_img_src = $_img_full_src = wc_placeholder_img_src();
						}
					} else {
						$_img_src = $_img_full_src = '';
					}

					$_price         = apply_filters( 'wooco_product_original_price', $product->get_price(), $product );
					$_price_display = wc_get_price_to_display( $product, array( 'price' => $_price ) );
					$_price_html    = $product->get_price_html();

					if ( $price !== '' ) {
						// new price
						$_new_price = self::get_new_price( $_price, $price );

						if ( $_new_price !== (float) $_price ) {
							$_price_display = wc_get_price_to_display( $product, array( 'price' => $_new_price ) );
							$_price_html    = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $_price ) ), $_price_display );
						}
					}

					if ( ! $custom_qty && $show_qty ) {
						$_name = $qty . ' &times; ' . $product->get_name();
					} else {
						$_name = $product->get_name();
					}

					$_description = '';

					if ( $show_price ) {
						$_description .= '<span>' . $_price_html . '</span>';
					}

					if ( $show_availability ) {
						$_description .= '<span>' . wc_get_stock_html( $product ) . '</span>';
					}

					return apply_filters( 'wooco_product_data', array(
						'id'            => $product->get_id(),
						'pid'           => $product->is_type( 'variation' ) && $product->get_parent_id() ? $product->get_parent_id() : 0,
						'purchasable'   => apply_filters( 'wooco_product_purchasable', ( self::is_purchasable( $product, $qty ) ? 'yes' : 'no' ), $product, $qty, $price ),
						'link'          => apply_filters( 'wooco_product_link', ( ! $product->is_visible() && ! apply_filters( 'wooco_hidden_product_link', false ) ? '' : $product->get_permalink() ), $product, $qty, $price ),
						'name'          => apply_filters( 'wooco_product_name', $_name, $product, $qty, $price ),
						'price'         => apply_filters( 'wooco_product_price', $_price_display, $product, $qty, $price ),
						'regular_price' => apply_filters( 'wooco_product_regular_price', wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), $product, $qty, $price ),
						'price_html'    => apply_filters( 'wooco_product_price_html', htmlentities( $_price_html ), $product, $qty, $price ),
						'description'   => apply_filters( 'wooco_product_description', htmlentities( $_description ), $product, $qty, $price ),
						'availability'  => apply_filters( 'wooco_product_availability', htmlentities( wc_get_stock_html( $product ) ), $product, $qty, $price ),
						'image'         => apply_filters( 'wooco_product_image', $_img_src, $product, $qty, $price ),
						'image_full'    => apply_filters( 'wooco_product_image_full', $_img_full_src, $product, $qty, $price )
					) );
				}

				function export_columns( $columns ) {
					$columns['wooco_components'] = esc_html__( 'Components', 'wpc-composite-products' );

					return $columns;
				}

				function export_data( $value, $product ) {
					$value = get_post_meta( $product->get_id(), 'wooco_components', true );

					return serialize( $value );
				}

				function import_options( $options ) {
					$options['wooco_components'] = esc_html__( 'Components', 'wpc-composite-products' );

					return $options;
				}

				function import_columns( $columns ) {
					$columns['Components']       = 'wooco_components';
					$columns['components']       = 'wooco_components';
					$columns['wooco components'] = 'wooco_components';

					return $columns;
				}

				function import_process( $object, $data ) {
					if ( ! empty( $data['wooco_components'] ) ) {
						$object->update_meta_data( 'wooco_components', unserialize( $data['wooco_components'] ) );
					}

					return $object;
				}

				function format_array( $array ) {
					$formatted_array = array();

					foreach ( array_keys( $array ) as $fieldKey ) {
						foreach ( $array[ $fieldKey ] as $key => $value ) {
							$formatted_array[ $key ][ $fieldKey ] = $value;
						}
					}

					return $formatted_array;
				}

				function clean_ids( $ids ) {
					$ids = preg_replace( '/[^,.%\/0-9a-zA-Z]/', '', $ids );

					return $ids;
				}

				public static function localization( $key = '', $default = '' ) {
					$str = '';

					if ( ! empty( $key ) && ! empty( self::$localization[ $key ] ) ) {
						$str = self::$localization[ $key ];
					} elseif ( ! empty( $default ) ) {
						$str = $default;
					}

					return apply_filters( 'wooco_localization_' . $key, $str );
				}

				public static function get_items( $ids ) {
					if ( ! empty( $ids ) ) {
						$arr   = array();
						$items = explode( ',', $ids );

						if ( is_array( $items ) && count( $items ) > 0 ) {
							foreach ( $items as $item ) {
								$item_arr = explode( '/', $item );
								$arr[]    = array(
									'id'        => absint( isset( $item_arr[0] ) ? $item_arr[0] : 0 ),
									'qty'       => (float) ( isset( $item_arr[1] ) ? $item_arr[1] : 1 ),
									'price'     => isset( $item_arr[2] ) ? self::format_price( $item_arr[2] ) : '',
									'component' => isset( $item_arr[3] ) ? rawurldecode( $item_arr[3] ) : ''
								);
							}
						}

						if ( count( $arr ) > 0 ) {
							return $arr;
						}
					}

					return false;
				}

				public static function format_price( $price ) {
					// format price to percent or number
					return preg_replace( '/[^.%0-9]/', '', $price );
				}

				public static function get_new_price( $old_price, $new_price ) {
					if ( strpos( $new_price, '%' ) !== false ) {
						$calc_price = ( (float) $new_price * $old_price ) / 100;
					} else {
						$calc_price = (float) $new_price;
					}

					return $calc_price;
				}

				public static function get_discount( $number ) {
					$discount = 0;

					if ( is_numeric( $number ) && ( (float) $number < 100 ) && ( (float) $number > 0 ) ) {
						$discount = (float) $number;
					}

					return $discount;
				}
			}

			new WPCleverWooco();
		}
	}
}

if ( ! function_exists( 'wooco_notice_wc' ) ) {
	function wooco_notice_wc() {
		?>
        <div class="error">
            <p><strong>WPC Composite Products</strong> requires WooCommerce version 3.0 or greater.</p>
        </div>
		<?php
	}
}
