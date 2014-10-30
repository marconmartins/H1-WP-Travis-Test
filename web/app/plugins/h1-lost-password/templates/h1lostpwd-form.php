<form name="lostpasswordform" id="lostpasswordform" action="#" method="post">
<div>

	<div class="h1lostpwd-field">
		<label for="user_login" ><?php _e('Username or E-mail:','h1-lost-password') ?></label>
		<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" />
		<span class="message"></span>
	</div>

	<?php
	/**
	 * Fires inside the lostpassword <form> tags, before the hidden fields.
	 *
	 * @since 2.1.0
	 */
	do_action( 'lostpassword_form' ); ?>
	<?php /*<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" /> */ ?>
	<div class="h1lostpwd-field">
		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-blue button-large" value="<?php esc_attr_e('Get New Password','h1-lost-password'); ?>" />
		<span class="messages"></span>
	</div>

	<!-- <span class="messages"></span> -->

</div>
</form>