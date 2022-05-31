<?php
/*
 * YO Cookie
 * Version:           1.0.0 - 32132
 * Author:            Yo Cookie Team (YGT)
 * Date:              05/05/2018
 */

if( !defined('WPINC') || !defined("ABSPATH") ){
	die();
}

if ( isset( $_POST['submit'] ) && $_POST['submit'] ) {
	check_admin_referer( 'yo-cookie-options' );

	// clear form fields
	$this->options['enable'] 			= ( isset($_POST['enable']) && $_POST['enable'] == '1' );
	$this->options['privacy_text'] 		= isset($_POST['yo-cookie-privacy-text']) 	? wp_filter_post_kses( $_POST['yo-cookie-privacy-text'] ) : '';
	$this->options['background_color'] 	= isset($_POST['background_color']) 		? sanitize_text_field( $_POST['background_color'] ) : '';
	$this->options['text_color'] 		= isset($_POST['text_color']) 				? sanitize_text_field( $_POST['text_color'] ) : '';
	$this->options['border_color'] 		= isset($_POST['border_color']) 			? sanitize_text_field( $_POST['border_color'] ) : '';

	$this->options['transparency'] 		= isset($_POST['transparency']) 			? (int) $_POST['transparency'] : '';

	$this->options['buttom_text'] 		= isset($_POST['buttom_text']) 				? sanitize_text_field( $_POST['buttom_text'] ) : '';

	$this->options['position'] 			= isset($_POST['position']) 				? sanitize_text_field( $_POST['position'] ) : '';

	$this->options['padding'] 			= isset($_POST['padding']) 					? (int) $_POST['padding'] : '0';

	$this->options['transparency'] 			= isset($_POST['transparency']) 		? (int) $_POST['transparency'] : '100';

	if( $this->save_options() ){
		echo '<div id="message" class="updated fade"><p><strong>'.
		__('Yo Cookie settings saved.', 'yo-cookie').
		'</strong></p></div>';
	}
}



?>

<div class="wrap">
	<h1><?php _e( 'Yo Cookie options', 'yo-cookie'); ?></h1>
	<p>
		<?php _e('Here you can configure your Yo Cookie message panel', 'yo-cookie'); ?>
	</p>
	<form method="post" action="" id="yo-cookie-form">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="blogname">
							<?php _e('Cookie Message Dialog', 'yo-cookie'); ?>
						</label>
					</th>
					<td>
						<ul>
							<li>
								<label for="yo_cookie_on">
									<input type="radio" id="yo_cookie_on" name="enable" value="0" <?php checked( !$this->options['enable'] );?> />
									<strong>
										<?php _e( 'Hide', 'yo-cookie'); ?>
									</strong>
								</label>
							</li>
							<li>
								<label for="yo_cookie_off">
									<input type="radio" id="yo_cookie_off" name="enable" value="1" <?php checked( $this->options['enable'] );?> />
									<strong>
										<?php _e( 'Show', 'yo-cookie'); ?>
									</strong>
								</label>
							</li>
						</ul>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="blogname">
							<?php _e('Preview Link', 'yo-cookie'); ?>
						</label>
					</th>
					<td>
						<a href="<?php echo get_site_url(); ?>/?yocookie=1" target="_blank"><?php echo get_site_url(); ?>/?yocookie=1</a>.
						<p class="description" id="tagline-description">
							<?php _e('Please click on link to see current cookie message dialog view.', 'yo-cookie'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="background_color">
							<?php _e('Background Color', 'yo-cookie'); ?>
						</label>
					</th>
					<td>
						<input name="background_color" id="background_color" value="<?php echo $this->options['background_color']; ?>" class="regular-text jscolor {hash:true}" type="text">
						<p class="description" id="tagline-description">Please select background for the cookie dialog box</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="text_color">
							<?php _e('Text Color', 'yo-cookie'); ?>
						</label>
					</th>
					<td>
						<input name="text_color" id="text_color" value="<?php echo $this->options['text_color']; ?>" class="regular-text jscolor {hash:true}" type="text">
						<p class="description" id="tagline-description">Please select color for the cookie dialog text</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="border_color">
							<?php _e('Border Color', 'yo-cookie'); ?>
						</label>
					</th>
					<td>
						<input name="border_color" id="border_color" value="<?php echo $this->options['border_color']; ?>" class="regular-text jscolor {hash:true}" type="text">
						<p class="description" id="tagline-description">Please select color for the cookie dialog box border</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="position">
							<?php _e('Position', 'yo-cookie'); ?>
						</label>
					</th>
					<td>
						<select name="position" id="position">
						  <option value="bottom" 		<?php selected($this->options['position'], "bottom"); ?>		><?php _e('Bottom', 'yo-cookie'); ?></option>
						  <option value="bottom_left" 	<?php selected($this->options['position'], "bottom_left"); ?>	><?php _e('Bottom Left', 'yo-cookie'); ?></option>
						  <option value="bottom_right" 	<?php selected($this->options['position'], "bottom_right"); ?>	><?php _e('Bottom Right', 'yo-cookie'); ?></option>
						  <option value="top" 			<?php selected($this->options['position'], "top"); ?>			><?php _e('Top', 'yo-cookie'); ?></option>
						  <option value="top_left" 		<?php selected($this->options['position'], "top_left"); ?>		><?php _e('Top Left', 'yo-cookie'); ?></option>
						  <option value="top_right" 	<?php selected($this->options['position'], "top_right"); ?>		><?php _e('Top Right', 'yo-cookie'); ?></option>
						  <option value="centre" 		<?php selected($this->options['position'], "centre"); ?>		><?php _e('Centre', 'yo-cookie'); ?></option>
						</select>
						<p class="description" id="tagline-description"><?php __('Please select position for the cookies message box', 'yo-cookie'); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="buttom_text">
							<?php _e('Button Text', 'yo-cookie'); ?>
						</label>
					</th>
					<td>
						<input name="buttom_text" id="buttom_text" value="<?php echo $this->options['buttom_text']; ?>" class="regular-text" type="text">
						<p class="description" id="tagline-description"><?php _e('Please define here lable for the cookie message agree button', 'yo-cookie'); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="padding">
							<?php _e('padding'); ?>
						</label>
					</th>
					<td>
						<input name="padding" id="padding" value="<?php echo (int) $this->options['padding']; ?>" class="small-text" type="text"> px
						<p class="description" id="tagline-description"><?php _e('Please define here padding for the cookie message box in pixels', 'yo-cookie'); ?></p>
					</td>
				</tr>

				<tr>
				<tr>
					<th scope="row">
						<label for="transparency">
							<?php _e('Transparency'); ?>
						</label>
					</th>
					<td>
						<select name="transparency" id="transparency">
				          <option value="30" <?php selected($this->options['transparency'], "30"); ?>>30%</option>
				          <option value="40" <?php selected($this->options['transparency'], "40"); ?>>40%</option>
				          <option value="50" <?php selected($this->options['transparency'], "50"); ?>>50%</option>
				          <option value="60" <?php selected($this->options['transparency'], "60"); ?>>60%</option>
				          <option value="70" <?php selected($this->options['transparency'], "70"); ?>>70%</option>
				          <option value="80" <?php selected($this->options['transparency'], "80"); ?>>80%</option>
				          <option value="90" <?php selected($this->options['transparency'], "90"); ?>>90%</option>
				          <option value="100" <?php selected($this->options['transparency'], "100"); ?>>100%</option>
				        </select>
						<p class="description" id="tagline-description"><?php _e('Please define transprancy for the cookie message box', 'yo-cookie'); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						Editor
					</th>
					<td>
					<?php
						$settings = array(
						    'teeny' => true,
						    'textarea_rows' => 15,
						    'tabindex' => 1,
							'media_buttons' => false,
							'wpautop' => false,
						);
						wp_editor(
							$this->options['privacy_text']
							, 'yo-cookie-privacy-text', $settings);
					?>
					</td>
				</tr>
			</tbody>
		</table>

		<?php wp_nonce_field( 'yo-cookie-options' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
<script type="text/javascript">
(function ($) {
	$('input[type="range"]').rangeslider({ polyfill: false,});
	//$('.colorpicker').colpick();
})(jQuery);
</script>
