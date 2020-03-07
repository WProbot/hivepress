<?php
/**
 * Vendors view page template.
 *
 * @template vendors_view_page
 * @description Vendors page in view context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendors view page template class.
 *
 * @class Vendors_View_Page
 */
class Vendors_View_Page extends Page_Sidebar_Left {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [
						'blocks' => [
							'page_header' => [
								'type'       => 'container',
								'tag'        => 'header',
								'_order'     => 5,

								'attributes' => [
									'class' => [ 'hp-page__header' ],
								],

								'blocks'     => [
									'vendor_search_form' => [
										'type'   => 'vendor_search_form',
										'_order' => 10,
									],
								],
							],
						],
					],

					'page_sidebar'   => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'vendor_filter_form'   => [
								'type'       => 'form',
								'form'       => 'vendor_filter',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-form--narrow', 'hp-widget', 'widget' ],
								],
							],

							'page_sidebar_widgets' => [
								'type'   => 'widgets',
								'area'   => 'hp_vendors_view_sidebar',
								'_order' => 20,
							],
						],
					],

					'page_content'   => [
						'blocks' => [
							'vendors_container' => [
								'type'   => 'results',
								'_order' => 10,

								'blocks' => [
									'page_topbar'       => [
										'type'       => 'container',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-page__topbar' ],
										],

										'blocks'     => [
											'vendor_count' => [
												'type'   => 'result_count',
												'_order' => 10,
											],

											'vendor_sort_form' => [
												'type'   => 'form',
												'form'   => 'vendor_sort',
												'_order' => 20,

												'attributes' => [
													'class' => [ 'hp-form--pivot' ],
												],
											],
										],
									],

									'vendors'           => [
										'type'    => 'vendors',
										'columns' => 2,
										'_order'  => 20,
									],

									'vendor_pagination' => [
										'type'   => 'part',
										'path'   => 'page/pagination',
										'_order' => 30,
									],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}