<?php
// Get settings from DB
$mode        = get_option( 'tm_replace_howdy_mode' );
$values      = get_option( 'tm_replace_howdy_values' );
$replace_all = get_option( 'tm_replace_howdy_all_languages' );
$save_data   = get_option( 'tm_replace_howdy_save' );
// Set quick variables
$selected = ' selected="selected"';
$checked  = ' checked="checked"';
?>
<div class="wrap">
	<h2><?php _e( 'Replace Howdy Settings', 'tm-replace-howdy' ); ?></h2>

	<p><?php _e( 'For explanations of each mode and other helpful tips, click on "Help" in the upper right corner of this page.', 'tm-replace-howdy' ); ?></p>

	<form method="post" action="">
		<p>
			<label for="tm_rh_mode"><?php _e( 'Operating Mode:', 'tm-replace-howdy' ); ?></label>
			<br/>
			<select name="tm_rh_mode" id="tm_rh_mode">
				<option value="normal"<?php if ( $mode && $mode == 'normal' ) {
					echo $selected;
				} ?>><?php _e( 'Normal', 'tm-replace-howdy' ); ?></option>
				<option value="pooper"<?php if ( $mode && $mode == 'pooper' ) {
					echo $selected;
				} ?>><?php _e( 'Professional', 'tm-replace-howdy' ); ?></option>
				<option value="custom"<?php if ( $mode && $mode == 'custom' ) {
					echo $selected;
				} ?>><?php _e( 'Custom (see below)', 'tm-replace-howdy' ); ?></option>
			</select>
		</p>
		<p>
			<label
				for="tm_rh_list"><?php _e( 'Custom word list (custom mode only, separate items with semi-colons(;))', 'tm-replace-howdy' ); ?>
				:</label>
			<br/>
			<textarea name="tm_rh_list" rows="5" cols="30"
			          id="tm_rh_list"><?php if ( isset( $values[1] ) && is_array( $values[1] ) ) {
					echo stripslashes( implode( ';', array_diff( $values[1], $this->tm_howdy_fun ) ) );
				} ?></textarea>
		</p>

		<p>
			<label for="tm_rh_custom"><?php _e( 'Custom mode options (custom mode only)', 'tm-replace-howdy' ); ?>:</label>
			<br/>
			<select name="tm_rh_custom" id="tm_rh_custom">
				<option value="custom_plus"<?php if ( isset( $values[0] ) && $values[0] == 'custom_plus' ) {
					echo $selected;
				} ?>><?php _e( 'Custom list + our list', 'tm-replace-howdy' ); ?></option>
				<option value="custom_only"<?php if ( isset( $values[0] ) && $values[0] == 'custom_only' ) {
					echo $selected;
				} ?>><?php _e( 'Custom list only', 'tm-replace-howdy' ); ?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" name="tm_replace_howdy_all_languages" id="tm_replace_howdy_all_languages"
			       value="replace_all"<?php if ( $replace_all == 'replace_all' ) {
				echo $checked;
			} ?> />
			<label
				for="tm_replace_howdy_all_languages"><?php _e( 'Replace greetings in languages other than American English', 'tm-replace-howdy' ); ?></label>
		</p>

		<p>
			<input type="checkbox" name="tm_rh_save" id="tm_rh_save" value="delete"<?php if ( $save_data == 'delete' ) {
				echo $checked;
			} ?> />
			<label for="tm_rh_save"><?php _e( 'Delete all plugin settings when deactivated', 'tm-replace-howdy' ); ?></label>
		</p>

		<p class="submit">
			<input type="submit" class="button-primary" name="tm_replace_howdy_form"
			       value="<?php _e( 'Save Settings', 'tm-replace-howdy' ); ?>"/>
			<input type="submit" class="button-primary" name="tm_replace_howdy_form_defaults"
			       value="<?php _e( 'Reset to Defaults', 'tm-replace-howdy' ); ?>"/>
		</p>
	</form>
</div>
