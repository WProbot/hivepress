<?php
/**
 * Form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form block class.
 *
 * @class Form
 */
class Form extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Get form class.
		$form_class = '\HivePress\Forms\\' . $this->get_attribute( 'form_name' );

		// Create form.
		$form = new $form_class( $this->get_attributes() );

		$form->set_values( $_GET );

		return $form->render();
	}
}