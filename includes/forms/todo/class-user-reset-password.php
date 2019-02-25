<?php
/**
 * User reset password form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User reset password form class.
 *
 * @class User_Reset_Password
 */
class User_Reset_Password extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		// Set fields.
		$this->set_fields(
			[
				'password' => [
					'label'      => esc_html__( 'New Password', 'hivepress' ),
					'type'       => 'password',
					'min_length' => 6,
					'required'   => true,
					'order'      => 10,
				],

				'username' => [
					'type'     => 'hidden',
					'required' => true,
					'default'  => sanitize_user( hp_get_array_value( $_GET, 'username' ) ),
				],

				'key'      => [
					'type'     => 'hidden',
					'required' => true,
					'default'  => sanitize_text_field( hp_get_array_value( $_GET, 'key' ) ),
				],
			]
		);
	}

	/**
	 * Submits form.
	 */
	public function submit() {
		parent::submit();

		if ( ! is_user_logged_in() ) {

			// Get user.
			$user = check_password_reset_key( $this->get_value( 'key' ), $this->get_value( 'username' ) );

			if ( ! is_wp_error( $user ) ) {

				// Reset password.
				reset_password( $user, $this->get_value( 'password' ) );

				// Authenticate user.
				wp_signon(
					[
						'user_login'    => $this->get_value( 'username' ),
						'user_password' => $this->get_value( 'password' ),
						'remember'      => true,
					],
					is_ssl()
				);

				// Send email.
				wp_password_change_notification( $user );
			} else {
				$this->errors[] = esc_html__( 'Password reset link is expired or invalid.', 'hivepress' );
			}
		}

		return empty( $this->errors );
	}
}