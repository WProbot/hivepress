<?php
/**
 * User register email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User register email class.
 *
 * @class User_Register
 */
class User_Register extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Registration Complete', 'hivepress' ),
				'body'    => hp_sanitize_html( __( "Hi, %user_name%! Thank you for registering, here's your password: %user_password%", 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
