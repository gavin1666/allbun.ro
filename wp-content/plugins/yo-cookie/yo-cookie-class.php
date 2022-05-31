<?php
/*
 * YO Cookie
 * Version:           1.0.2 - 32132
 * Author:            YoTeam
 * Date:              05/05/2018
 */

if( !defined('WPINC') || !defined("ABSPATH") ){
	die();
}


class YO_COOKIE {

	private static $instance = null;

	private $options ;
	private $options_name = 'YO_COOKIE';
	private $forcedShow = 0;
	private $enable = 0;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		$this->options = get_option( $this->options_name , array() );

		if(!isset($this->options['enable'])) $this->options['enable'] = 0;

		if(!isset($this->options['privacy_text'])) $this->options['privacy_text'] = __('This website uses cookies to provide user authentication. Please indicate whether you consent to our site placing cookies on your device and agree with our Privacy Policy. To find out more, please read our Privacy and Cookie Policy');
		if(!isset($this->options['background_color'])) $this->options['background_color'] = '#ffffff';
		if(!isset($this->options['text_color'])) $this->options['text_color'] = '#000000';
		if(!isset($this->options['border_color'])) $this->options['border_color'] = '#000000';
		if(!isset($this->options['transparency'])) $this->options['transparency'] = '100';

		if(!isset($this->options['buttom_text'])) $this->options['buttom_text'] = __('Accept');

		if(!isset($this->options['position'])) $this->options['position'] =  'bottom_right';

		if(!isset($this->options['padding'])) $this->options['padding'] =  '5';

		if(isset($_GET['yocookie']) && $_GET['yocookie']==1) $this->forcedShow = 1;

		$this->enable = $this->options['enable'] ;

		$this->hooks();
	}


	private function hooks() {
		add_action( 'plugins_loaded', array( $this, 'register_text_domain' ) );

		register_activation_hook( __FILE__, 	array($this, 'activation') );
		register_deactivation_hook( __FILE__, 	array($this, 'deactivation') );

		add_action( 'wp_loaded', array( $this, 'wp_load_hooks' ) );

		if( !( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
			if( $this->enable || $this->forcedShow ){
				add_action( 'wp_footer', array($this, 'showDialog') );
				add_action( 'wp_enqueue_scripts', array($this, 'includeJavascript') );
			}
		}

		if( is_admin() && !$this->enable ) {
			add_action( 'all_admin_notices', array( $this, 'setup_notice' ) );
		}
	}

	private function options_page_url() {
		return admin_url('admin.php?page=yo_cookie_options');
	}

	public function setup_notice(){

		if( strpos( get_current_screen()->id, 'yo_cookie_options' ) !== false ) return;

		$hascaps = current_user_can( 'manage_options' );

		if( $hascaps ) {
			echo '<div class="updated fade"><p>'.
				sprintf( 
					__( 'The <em>YO Cookie</em> plugin is active, but isn\'t configured to do anything yet. Visit the <a href="%s">configuration page</a> to configure and publish cookie panel.', 'yo-cookie')
					, esc_attr( $this->options_page_url() ) 
				) . 
			'</p></div>';
		}
	}

	public function includeJavascript(){

		wp_enqueue_script( 'yo-cookie-js',
		                       YO_COOKIE_URL.'assets/cookies.js',
		                       array( 'jquery' ),
		                       YO_COOKIE_VERSION,
		                       true);
		$yoCookieData = array(
					   			'close' => __( 'Close', 'yo-cookie' ),
					   			'text' => __( 'Close', 'yo-cookie' ),
					   			'forcedShow' => $this->forcedShow ? 1 : 0,
					   			'a_value' => '10'
					   		);

		wp_localize_script( 'yo-cookie-js', 'yoCookieData', $yoCookieData );

		wp_enqueue_style( 'yo-cookie-css', YO_COOKIE_URL.'assets/cookies.css', array(),  YO_COOKIE_VERSION, 'all');
	}

	public function showDialog( ){
		?>
		<div id="blockAcceptCookies" class="acceptcookies" style="
			background-color: <?php echo $this->options['background_color'].';'; ?>
			<?php echo $this->get_position(); ?>
			<?php echo $this->get_transparency(); ?>
			">
			<div class="acceptcookies_inner" style="color: <?php echo $this->options['text_color']; ?>">
				<?php echo $this->get_text(); ?>
			</div>
		</div>
		<?php
	}
	private function get_transparency() {
		$returnStyle= '';
		$transparency = (int) $this->options['transparency'];
		return 'opacity: '.($transparency/100).'; filter: alpha(opacity='.$transparency.');';
	}

	private function get_position() {
		$returnStyle= '';

		$padding = (int) $this->options['padding'] .'px';

		switch ( $this->options['position'] ) {
			case 'bottom':
				$returnStyle .= 'bottom: '.$padding.'; left: '.$padding.'; right: '.$padding.';';
				break;

			case 'bottom_left':
				$returnStyle .= 'bottom: '.$padding.'; left: '.$padding.'; margin-right: '.$padding.';  ';
				break;

			case 'bottom_right':
				$returnStyle .= 'bottom: '.$padding.'; right: '.$padding.'; margin-left: '.$padding.';';
				break;

			case 'top':
				$returnStyle .= 'top: '.$padding.'; left: '.$padding.'; right: '.$padding.';';
				break;

			case 'top_left':
				$returnStyle .= 'top: '.$padding.'; left: '.$padding.'; margin-right: '.$padding.';';
				break;

			case 'top_right':
				$returnStyle .= 'top: '.$padding.'; right: '.$padding.'; margin-left: '.$padding.';';
				break;

			default:
				$returnStyle .= 'bottom: '.$padding.'; right: '.$padding.'px;';
				break;
		}
		return $returnStyle;
	}

	private function get_text() {
		if( strpos($this->options['privacy_text'], '@button@')===false ) $this->options['privacy_text'] .= '@button@';
		return str_replace('@button@', $this->get_buttom(), $this->options['privacy_text']);;
	}

	private function get_buttom() {
		return '<button class="btn btn-primary btn-sm" id="buttonAcceptCookies">'.$this->options['buttom_text'].'</button>';;
	}

	private function save_options() {
		update_option( $this->options_name, $this->options );
		return true;
	}

	public function register_text_domain() {
		load_plugin_textdomain( 'yo-cookie', false, YO_COOKIE_PATH . 'languages' );
	}

	public static function activation(){
		add_option( 'yo-cookie-install', 1 );
	}

	public static function deactivation(){
		delete_option('yo-cookie-install');
	}


	public function wp_load_hooks(){
		if( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'settings_menu' ) );
			add_filter( 'plugin_action_links', array( $this, 'plugin_actions_links'), 10, 2 );
		}
	}

	public function plugin_actions_links( $links, $file ) {
		static $plugin;

		if( $file == 'yo-cookie/yo-cookie.php' && current_user_can('manage_options') ) {
			array_unshift(
				$links,
				sprintf( '<a href="%s">%s</a>', esc_attr( $this->settings_page_url() ), __( 'Settings' ) )
			);
		}

		return $links;
	}

	private function settings_page_url() {
		return add_query_arg( 'page', 'yo_cookie_options', admin_url( 'options-general.php' ) );
	}

	public function settings_menu() {
		$titlePage = __( 'Yo Cookie Options', 'yo-cookie' );
		$titleMenu = __( 'Yo Cookie', 'yo-cookie' );

	    add_menu_page(
	        $titlePage,
	        $titleMenu,
	        'manage_options',
	        'yo_cookie_options',
	        array( $this, 'options' ),
			'dashicons-welcome-view-site',
	        6
		);
	}


	public function options() {

		wp_enqueue_script( 'yo-cookie-options-js',
		                       YO_COOKIE_URL.'assets/jscolor.js',
		                       array( 'jquery' ),
		                       YO_COOKIE_VERSION,
		                       true);

		include( YO_COOKIE_PATH .'yo-cookie-options.php');
	}
}
