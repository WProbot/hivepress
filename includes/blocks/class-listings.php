<?php
/**
 * Listings block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings block class.
 *
 * @class Listings
 */
class Listings extends Block {

	/**
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Template type.
	 *
	 * @var string
	 */
	protected $template = 'view';

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Listings number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Listings category.
	 *
	 * @var int
	 */
	protected $category;

	/**
	 * Listings order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Featured status.
	 *
	 * @var bool
	 */
	protected $featured;

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => hivepress()->translator->get_string( 'listings' ),

				'settings' => [
					'columns'  => [
						'label'    => esc_html__( 'Columns', 'hivepress' ),
						'type'     => 'select',
						'default'  => 3,
						'required' => true,
						'order'    => 10,
						'options'  => [
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],

					'number'   => [
						'label'     => esc_html__( 'Number', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'order'     => 20,
					],

					'category' => [
						'label'    => esc_html__( 'Category', 'hivepress' ),
						'type'     => 'select',
						'options'  => 'terms',
						'taxonomy' => 'hp_listing_category',
						'default'  => '',
						'order'    => 30,
					],

					'order'    => [
						'label'   => esc_html__( 'Order', 'hivepress' ),
						'type'    => 'select',
						'order'   => 40,
						'options' => [
							''       => esc_html__( 'Date', 'hivepress' ),
							'title'  => esc_html__( 'Title', 'hivepress' ),
							'random' => esc_html_x( 'Random', 'sorting order', 'hivepress' ),
						],
					],

					'featured' => [
						'label' => hivepress()->translator->get_string( 'display_only_featured_listings' ),
						'type'  => 'checkbox',
						'order' => 50,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		global $wp_query;

		$output = '';

		// Get column width.
		$columns      = absint( $this->columns );
		$column_width = 12;

		if ( $columns > 0 && $columns <= 12 ) {
			$column_width = round( $column_width / $columns );
		}

		// Get listing query.
		$query          = $wp_query;
		$featured_query = null;

		if ( is_single() || ( hp\get_array_value( $query->query_vars, 'post_type' ) !== 'hp_listing' && ! is_tax( 'hp_listing_category' ) ) ) {

			// Set query arguments.
			$query_args = [
				'post_type'      => 'hp_listing',
				'post_status'    => 'publish',
				'posts_per_page' => absint( $this->number ),
				'no_found_rows'  => true,
			];

			// Get category.
			if ( $this->category ) {
				$query_args['tax_query'] = [
					[
						'taxonomy' => 'hp_listing_category',
						'terms'    => [ absint( $this->category ) ],
					],
				];
			}

			// Get order.
			if ( 'title' === $this->order ) {
				$query_args['orderby'] = 'title';
				$query_args['order']   = 'ASC';
			} elseif ( 'random' === $this->order ) {
				$query_args['orderby'] = 'rand';
			}

			// Get featured.
			if ( $this->featured ) {
				$query_args['meta_key']   = 'hp_featured';
				$query_args['meta_value'] = '1';
			}

			// Get cached IDs.
			$listing_ids = null;

			if ( 'random' !== $this->order ) {
				$listing_ids = hivepress()->cache->get_cache( array_merge( $query_args, [ 'fields' => 'ids' ] ), 'post/listing' );

				if ( is_array( $listing_ids ) ) {
					$query_args = [
						'post_type'      => 'hp_listing',
						'post_status'    => 'publish',
						'post__in'       => array_merge( [ 0 ], $listing_ids ),
						'posts_per_page' => count( $listing_ids ),
						'orderby'        => 'post__in',
						'no_found_rows'  => true,
					];
				}
			}

			// Query listings.
			$query = new \WP_Query( $query_args );

			// Cache IDs.
			if ( 'random' !== $this->order && is_null( $listing_ids ) && $query->post_count <= 1000 ) {
				hivepress()->cache->set_cache( array_merge( $query_args, [ 'fields' => 'ids' ] ), 'post/listing', wp_list_pluck( $query->posts, 'ID' ) );
			}
		} elseif ( 'edit' !== $this->template && get_query_var( 'hp_featured_ids' ) ) {

			// Query featured listings.
			$featured_query = new \WP_Query(
				[
					'post_type'      => 'any',
					'post_status'    => 'any',
					'post__in'       => array_map( 'absint', (array) get_query_var( 'hp_featured_ids' ) ),
					'posts_per_page' => absint( get_option( 'hp_listings_featured_per_page' ) ),
					'orderby'        => 'rand',
					'no_found_rows'  => true,
				]
			);
		}

		if ( $query->have_posts() ) {
			if ( 'edit' === $this->template ) {
				$output .= '<table class="hp-table">';
			} else {
				$output .= '<div class="hp-grid hp-block">';
				$output .= '<div class="hp-row">';
			}

			// Render featured listings.
			if ( ! is_null( $featured_query ) ) {
				while ( $featured_query->have_posts() ) {
					$featured_query->the_post();

					// Get listing.
					$listing = Models\Listing::get_by_id( get_the_ID() );

					if ( ! is_null( $listing ) ) {
						$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

						// Render listing.
						$output .= ( new Listing(
							[
								'template' => 'listing_' . $this->template . '_block',

								'context'  => [
									'listing' => $listing,
								],
							]
						) )->render();

						$output .= '</div>';
					}
				}
			}

			// Render listings.
			while ( $query->have_posts() ) {
				$query->the_post();

				// Get listing.
				$listing = Models\Listing::get_by_id( get_the_ID() );

				if ( ! is_null( $listing ) ) {
					if ( 'edit' !== $this->template ) {
						$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';
					}

					// Render listing.
					$output .= ( new Listing(
						[
							'template' => 'listing_' . $this->template . '_block',

							'context'  => [
								'listing' => $listing,
							],
						]
					) )->render();

					if ( 'edit' !== $this->template ) {
						$output .= '</div>';
					}
				}
			}

			if ( 'edit' === $this->template ) {
				$output .= '</table>';
			} else {
				$output .= '</div>';
				$output .= '</div>';
			}
		}

		// Reset query.
		wp_reset_postdata();

		return $output;
	}
}
