<?php
/**
 * Editor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Editor component class.
 *
 * @class Editor
 */
final class Editor {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register blocks.
		add_action( 'init', [ $this, 'register_blocks' ] );

		if ( is_admin() ) {

			// Enqueue styles.
			add_action( 'admin_init', [ $this, 'enqueue_styles' ] );
		}
	}

	/**
	 * Registers blocks.
	 */
	public function register_blocks() {

		// Get blocks.
		$blocks = [];

		foreach ( hivepress()->get_blocks() as $block_name => $block ) {
			if ( $block::get_title() ) {
				$block_slug = str_replace( '_', '-', $block_name );

				$blocks[ $block_name ] = [
					'title'      => HP_CORE_NAME . ' ' . $block::get_title(),
					'type'       => 'hivepress/' . $block_slug,
					'script'     => 'hp-block-' . $block_slug,
					'attributes' => [],
					'settings'   => [],
				];

				foreach ( $block::get_settings() as $field_name => $field ) {
					$field_args = $field->get_args();

					// Add attribute.
					$blocks[ $block_name ]['attributes'][ $field_name ] = [
						'type'    => 'string',
						'default' => hp\get_array_value( $field_args, 'default' ),
					];

					// Add setting.
					$blocks[ $block_name ]['settings'][ $field_name ] = $field_args;
				}
			}
		}

		// Register blocks.
		if ( function_exists( 'register_block_type' ) ) {
			foreach ( $blocks as $block_name => $block ) {

				// Register block script.
				wp_register_script( $block['script'], HP_CORE_URL . '/assets/js/block.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], HP_CORE_VERSION, true );
				wp_localize_script( $block['script'], 'hpBlock', $block );

				// Register block type.
				register_block_type(
					$block['type'],
					[
						'editor_script'   => $block['script'],
						'render_callback' => [ $this, 'render_' . $block_name ],
						'attributes'      => $block['attributes'],
					]
				);
			}

			if ( ! empty( $blocks ) ) {
				wp_localize_script( reset( $blocks )['script'], 'hpBlocks', $blocks );
			}
		}

		// Add shortcodes.
		foreach ( array_keys( $blocks ) as $block_name ) {
			add_shortcode( 'hivepress_' . $block_name, [ $this, 'render_' . $block_name ] );
		}
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Render block HTML.
			$output = '';

			$block_name  = substr( $name, strlen( 'render' ) + 1 );
			$block_class = '\HivePress\Blocks\\' . $block_name;

			if ( class_exists( $block_class ) ) {
				$output .= ( new $block_class( $args ) )->render();
			}

			return $output;
		}
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {
		foreach ( hivepress()->get_config( 'styles' ) as $style ) {
			if ( hp\get_array_value( $style, 'editor', false ) ) {
				add_editor_style( $style['src'] );
			}
		}
	}
}
