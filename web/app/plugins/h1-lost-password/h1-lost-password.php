<?php
/**
 * @package H1 Lost Password
 * @version 1.0
 */
/*
Plugin Name: H1 Lost Password
Plugin URI:
Description: Custom WordPress Lost Password functionality.
Author: Marco Martins / H1
Version: 1.0
Author URI: http://h1.fi
*/

if ( ! class_exists( 'H1_Lost_Password' ) ) {

	class H1_Lost_Password {

		/**
		 * Path to the form template.
		 * @var string
		 */
		private $template_path;


		function __construct() {

			add_filter( 'lostpassword_url', array( $this, 'set_lost_password_url' ) );

			// Add change password shortcode
			add_shortcode( 'h1lostpwd', array( $this, 'set_shortcode' ) );

			// Ajax hooks
			add_action( 'wp_ajax_h1lostpwd_lost_password',        array( $this, 'ajax_lost_password' ) );
			add_action( 'wp_ajax_nopriv_h1lostpwd_lost_password', array( $this, 'ajax_lost_password' ) );

			add_action( 'wp_ajax_h1lostpwd_reset_password',        array( $this, 'ajax_reset_password' ) );
			add_action( 'wp_ajax_nopriv_h1lostpwd_reset_password', array( $this, 'ajax_reset_password' ) );

		}


		/**
		 * [AJAX] Lost password.
		 */
		public function ajax_lost_password() {

			$errors = $this->retrieve_password( $_POST['user_login'] );

			/**
			 * Fires before the lost password form.
			 *
			 * @since 1.5.1
			 */
			do_action( 'lost_password' );

			$response = array();

			if ( is_object( $errors ) ) {
				$response['status'] = 'error';

				foreach ( $errors->errors as $k => $v ) {
					$field = 'user_login';
					$response['message'] = array( $field => $v );
				}
			}
			else {
				$response = array(
					'status'  => 'success',
					'message' => __( 'You will receive a link to create a new password via email.', 'h1-lost-password' )
				);
			}

			$user_login = isset( $_POST['user_login'] ) ? wp_unslash( $_POST['user_login'] ) : '';

			echo json_encode( $response );

			die();
		}


		/**
		 * [AJAX] Reset password.
		 */
		public function ajax_reset_password() {

			$user = check_password_reset_key( $_POST['key'], $_POST['login'] );

			// Throw errors if the key is not valid.
			if ( is_wp_error( $user ) ) {

				foreach( $user->errors as $key => $value ) {

					if ( 'invalid_key' === $key ) {
						echo json_encode( array(
							'status' => 'error',
							'message' => array(
								'key' => __( 'Sorry, that key does not appear to be valid.', 'h1-lost-password' ) ),
						) );

					}
					else if ( 'expired_key' === $key ) {
						echo json_encode( array(
							'status' => 'error',
							'message' => array(
								'key' => __( 'Sorry, that key has expired. Please try again.', 'h1-lost-password' ),
						) ) );

					}

					die();
				}
			}

			$errors = new WP_Error();

			if ( '' === $_POST['pass1'] ) {
				$errors->add( 'pass1', __( 'The password is invalid.', 'h1-lost-password' ) );
			}

			if ( '' === $_POST['pass2'] ) {
				$errors->add( 'pass2', __( 'The password is invalid.', 'h1-lost-password' ) );
			}

			if ( $_POST['pass1'] !== '' && $_POST['pass1'] != $_POST['pass2'] ) {
				$errors->add( 'pass1', __( 'The passwords do not match.', 'h1-lost-password' ) );
			}

			/**
			 * Fires before the password reset procedure is validated.
			 *
			 * @since 3.5.0
			 *
			 * @param object           $errors WP Error object.
			 * @param WP_User|WP_Error $user   WP_User object if the login and reset key match. WP_Error object otherwise.
			 */
			do_action( 'validate_password_reset', $errors, $user );

			if ( is_object( $errors ) && count( $errors->errors) > 0 ) {
				$response['status'] = 'error';

				foreach ( $errors->errors as $k => $v ) {
					$response['message'][ $k ] = $v;
				}

				echo json_encode( $response );
				die();
			}


			if ( ( ! $errors->get_error_code() ) && isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {

				reset_password( $user, $_POST['pass1'] );

				echo json_encode( array(
					'status'  => 'success',
					'message' => __( 'Your password has been reset.', 'h1-lost-password' ),
					)
				);

				die();
			}

		}


		/**
		 * Set the lost password shortcode: [h1lostpwd]
		 * @param  array $atts Shortcode attributes.
		 * @return string      Change password form HTML.
		 */
		public function set_shortcode( $atts ) {

			// Redirect user to frontpage if they are already logged in.
			if ( is_user_logged_in() ) {
				wp_redirect( home_url('/') );
			}

			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'lostpassword';

			if ( 'lostpassword' == $action ) {
				// Set form template path
				$default_path = __DIR__ . '/templates/h1lostpwd-form.php';
				$this->template_path = apply_filters( 'h1lostpwd_set_lost_password_template_path', $default_path );
				$user_login = '';
			}
			if ( ( 'rp' == $action ) || ( 'resetpassword' == $action ) ) {
				$default_path = __DIR__ . '/templates/h1lostpwd-reset-form.php';
				$this->template_path = apply_filters( 'h1lostpwd_set_reset_password_template_path', $default_path );
			}

			$this->set_ajax_url();

			$this->enqueue_scripts();

			ob_start();

			include( $this->template_path );

			$html = ob_get_clean();

			return $html;
		}


		/**
		 * Replace default WordPress lost password url with custom URL.
		 *
		 * @param string $url Lost password URL.
		 * @return string     New lost password URL.
		 */
		public function set_lost_password_url( $url ) {
			return home_url( '/lost-password/' );
		}


		/**
		 * Handles sending password retrieval email to user.
		 *
		 * @uses $wpdb WordPress Database object
		 *
		 * @return bool|WP_Error True: when finish. WP_Error on error
		 */
		function retrieve_password( $user_login ) {

			global $wpdb, $wp_hasher;

			$errors = new WP_Error();

			if ( empty( $user_login ) ) {
				$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.', 'h1-lost-password' ) );
			}
			else if ( strpos( $user_login, '@' ) ) {

				$user_data = get_user_by( 'email', trim( $user_login ) );

				if ( empty( $user_data ) ) {
					$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.', 'h1-lost-password' ) );
				}
			}
			else {
				$login = trim( $user_login );
				$user_data = get_user_by( 'login', $login );
			}

			/**
			 * Fires before errors are returned from a password reset request.
			 *
			 * @since 2.1.0
			 */
			do_action( 'lostpassword_post' );

			if ( $errors->get_error_code() ) {
				return $errors;
			}

			if ( ! $user_data ) {
				$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or e-mail.', 'h1-lost-password' ) );
				return $errors;
			}

			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;

			/**
			 * Fires before a new password is retrieved.
			 *
			 * @since 1.5.0
			 * @deprecated 1.5.1 Misspelled. Use 'retrieve_password' hook instead.
			 *
			 * @param string $user_login The user login name.
			 */
			do_action( 'retreive_password', $user_login );
			/**
			 * Fires before a new password is retrieved.
			 *
			 * @since 1.5.1
			 *
			 * @param string $user_login The user login name.
			 */
			do_action( 'retrieve_password', $user_login );

			/**
			 * Filter whether to allow a password to be reset.
			 *
			 * @since 2.7.0
			 *
			 * @param bool true           Whether to allow the password to be reset. Default true.
			 * @param int  $user_data->ID The ID of the user attempting to reset a password.
			 */
			$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

			if ( ! $allow ) {
				return new WP_Error( 'no_password_reset', __( 'Password reset is not allowed for this user', 'h1-lost-password' ) );
			}
			else if ( is_wp_error( $allow ) ) {
				return $allow;
			}

			// Generate something random for a password reset key.
			$key = wp_generate_password( 20, false );

			/**
			 * Fires when a password reset key is generated.
			 *
			 * @since 2.5.0
			 *
			 * @param string $user_login The username for the user.
			 * @param string $key        The generated password reset key.
			 */
			do_action( 'retrieve_password_key', $user_login, $key );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

			$default_path = __DIR__ . '/templates-mail/h1lostpwd-rp.php';
			$this->template_path = apply_filters( 'h1lostpwd_retrieve_password_mail_template_path', $default_path );

			ob_start();

			include( $this->template_path );

			$html = ob_get_clean();

			if ( is_multisite() )
				$blogname = $GLOBALS['current_site']->site_name;
			else
				// The blogname option is escaped with esc_html on the way into the database in sanitize_option
				// we want to reverse this for the plain text arena of emails.
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

			$title = sprintf( __('[%s] Password Reset', 'h1-lost-password'), $blogname );

			/**
			 * Filter the subject of the password reset email.
			 *
			 * @since 2.8.0
			 *
			 * @param string $title Default email title.
			 */
			$title = apply_filters( 'retrieve_password_title', $title );
			/**
			 * Filter the message body of the password reset mail.
			 *
			 * @since 2.8.0
			 *
			 * @param string $message Default mail message.
			 * @param string $key     The activation key.
			 */
			$message = apply_filters( 'retrieve_password_message', $message, $key );
			if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) )
				wp_die( __('The e-mail could not be sent.', 'h1-lost-password') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.', 'h1-lost-password') );

			return true;
		}


		/**
		 * Enqueue plugin scripts.
		 */
		private function enqueue_scripts() {

			wp_register_script( 'h1lostpwd', plugins_url( 'js/h1lostpwd.js', __FILE__ ), array( 'jquery' ), '1.0', true );

			wp_enqueue_script( 'h1lostpwd' );

		}


		/**
		 * Set Ajax URL variable.
		 */
		private function set_ajax_url() { ?>

			<script type="text/javascript">
				var ajax_url = "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>";
			</script><?php

		}

	}

}

$h1changepwd = new H1_Lost_Password();
