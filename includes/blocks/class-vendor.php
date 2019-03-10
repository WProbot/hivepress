<?php
/**
 * Vendor block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor block class.
 *
 * @class Vendor
 */
class Vendor extends Template {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var string
	 */
	protected static $settings = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$this->attributes['vendor'] = \HivePress\Models\Vendor::get( 265 );

		global $post;
		$post=get_post(265);
		setup_postdata($post);

		return parent::render();
	}
}
