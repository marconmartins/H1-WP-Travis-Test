
<h1><?php /*_e('Reset Password');*/ ?></h1>
<form name="resetpassform" id="resetpassform" action="#" method="post" autocomplete="off">
<div>

	<?php echo '<p class="reset-pass">' . __('Enter your new password below.','h1-lost-password') . '</p>'; ?>

	<span class="key-message"></span>

	<div class="h1lostpwd-field"><input type="hidden" id="user_login" value="<?php echo esc_attr( $_GET['login'] ); ?>" autocomplete="off" /></div>
	<div class="h1lostpwd-field"><input type="hidden" id="key" value="<?php echo esc_attr( $_GET['key'] ); ?>"></div>

	<div class="h1lostpwd-field">
		<label for="pass1"><?php _e('New password','h1-lost-password') ?></label>
		<input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" />
		<span class="message"></span>
	</div>

	<div class="h1lostpwd-field">
		<label for="pass2"><?php _e('Confirm new password','h1-lost-password') ?></label>
		<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" />
		<span class="message"></span>
	</div>

	<?php /*<div id="pass-strength-result" class="hide-if-no-js"><?php _e('Strength indicator'); ?></div>*/ ?>

	<?php
	/**
	 * Fires following the 'Strength indicator' meter in the user password reset form.
	 *
	 * @since 3.9.0
	 *
	 * @param WP_User $user User object of the user whose password is being reset.
	 */
	// do_action( 'resetpass_form', $user );
	?>
	<div class="h1lostpwd-field">
	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-blue button-large" value="<?php esc_attr_e('Reset Password','h1-lost-password'); ?>" /></p>

	<p class="indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).', 'h1-lost-password'); ?></p>
	</div>
</div>
</form>