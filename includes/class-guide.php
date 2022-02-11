<?php

namespace InnocodeWPGuide;

use Error;
use WP_Query;

/**
 * Class Guide
 *
 * @package InnocodeWPGuide
 */
class Guide
{
	const POST_TYPE = 'wp_guide';
	const TAXONOMY = 'wp_guide_posts';
	const OPTION = 'wp_guide_post_types';
	const SORTING_SETTINGS = 'wp_guide_sorting';
	const GENERAL_SETTINGS = 'wp_guide_settings';
	const SORTING_PAGE = 'guide_sorting_page';
	const SETTINGS_PAGE = 'guide_settings_page';

	/**
	 * Link functions with WP hooks
	 */
	public static function register()
	{
		add_action( 'init', [ get_called_class(), 'register_guide_post_type' ] );
		add_action( 'init', [ get_called_class(), 'register_screen_taxonomy' ] );
		add_action( 'init', [ get_called_class(), 'get_post_types_with_editor' ] );
		add_action( 'admin_enqueue_scripts', [ get_called_class(), 'admin_enqueue_scripts' ] );
		add_filter( 'wp_terms_checklist_args', [ get_called_class(), 'set_checked_ontop_default' ], 10 );
		add_filter( 'manage_' . static::POST_TYPE . '_posts_columns', [ get_called_class(), 'add_admin_column' ] );
		add_action( 'manage_' . static::POST_TYPE . '_posts_custom_column' , [ get_called_class(), 'show_admin_columns' ], 10, 2 );
		add_action( 'add_option', [ get_called_class(), 'add_taxonomies' ], 10, 2 );
		add_action( 'update_option', [ get_called_class(), 'manage_taxonomies' ], 10, 3 );
		add_action( 'admin_menu', [ get_called_class(), 'add_sorting_page' ] );
		add_action( 'admin_menu', [ get_called_class(), 'add_settings_page' ] );
		add_action( 'admin_init', [ get_called_class(), 'register_settings' ] );
		add_filter( 'rest_' . static::POST_TYPE . '_query', [ get_called_class(), 'filter_rest_request' ], 10, 2 );
		add_filter( 'rest_' . static::TAXONOMY . '_query', [ get_called_class(), 'filter_rest_tax_request' ], 10, 2 );
	}

	/**
	 * Register and enqueue admin scripts and styles
	 */
	public static function admin_enqueue_scripts()
	{
		$dir = dirname( INNOCODE_WP_GUIDE_PLUGIN_PATH );
		$script_asset_path = "$dir/build/index.asset.php";

		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to run `npm start` or `npm run build` for the "innocode/wp-guide" plugin first.'
			);
		}

		$index_js     = 'build/index.js';
		$script_asset = require( $script_asset_path );
		wp_enqueue_script(
			'innocode-wp-guide-js',
			plugins_url( $index_js, INNOCODE_WP_GUIDE_PLUGIN_PATH ),
			array_merge( $script_asset['dependencies'], [ 'wp-edit-post' ]),
			$script_asset['version']
		);
		wp_add_inline_script(
			'innocode-wp-guide-js',
			"var guideOrder = " . json_encode( get_option( static::SORTING_SETTINGS ) ) . ';',
			'before'
		);

		$editor_css = 'build/index.css';
		wp_enqueue_style(
			'innocode-wp-guide-style',
			plugins_url( $editor_css, INNOCODE_WP_GUIDE_PLUGIN_PATH ),
			[],
			filemtime( "$dir/$editor_css" )
		);

		if( get_current_screen()->base == static::POST_TYPE . '_page_' . static::SORTING_PAGE ) {
			$sorting_asset = require( "$dir/build/sorting.asset.php" );

			wp_enqueue_script(
				'wp-guide-sorting-js',
				plugins_url( 'build/sorting.js', INNOCODE_WP_GUIDE_PLUGIN_PATH ),
				$sorting_asset['dependencies'],
				$sorting_asset['version']
			);
			wp_enqueue_style(
				'wp-manual-sorting-css',
				plugins_url( 'build/sorting.css', INNOCODE_WP_GUIDE_PLUGIN_PATH ),
				[],
				$sorting_asset['version']
			);
		}
	}

	/**
	 * Register post type Guide
	 */
	public static function register_guide_post_type()
	{
		$show_in_menu = true;

		if( is_multisite() && ! is_main_site() ) {
			switch_to_blog( 1 );
			$options = get_option( static::GENERAL_SETTINGS, [] );
			$show_in_menu = ! isset( $options[ 'from-main-site' ] ) || $options[ 'from-main-site'] != 'true';
			restore_current_blog();
		}

		register_post_type( static::POST_TYPE, [
			'labels'                    => [
				'name'                  => esc_html__( 'Guides', 'innocode-wp-guide' ),
				'singular_name'         => esc_html__( 'Guide', 'innocode-wp-guide' ),
				'menu_name'             => esc_html__( 'Guide', 'innocode-wp-guide' ),
				'name_admin_bar'        => esc_html__( 'Guide', 'innocode-wp-guide' ),
				'add_new'               => esc_html__( 'Add new guide', 'innocode-wp-guide' ),
				'add_new_item'          => esc_html__( 'Add new guide', 'innocode-wp-guide' ),
				'edit_item'             => esc_html__( 'Edit guide', 'innocode-wp-guide' ),
				'new_item'              => esc_html__( 'New guide', 'innocode-wp-guide' ),
				'view_item'             => esc_html__( 'View guide', 'innocode-wp-guide' ),
				'search_items'          => esc_html__( 'Search in guides', 'innocode-wp-guide' ),
				'not_found'             => esc_html__( 'No guides found', 'innocode-wp-guide' ),
				'not_found_in_trash'    => esc_html__( 'No guides found in trash', 'innocode-wp-guide' ),
				'all_items'             => esc_html__( 'Guides', 'innocode-wp-guide' )
			],
			'public'                    => false,
			'show_ui'                   => is_super_admin(),
			'show_in_menu'              => $show_in_menu,
			'show_in_rest'				=> true,
			'menu_icon'                 => 'dashicons-book',
			'menu_position'             => 3,
			'rewrite'                   => false,
			'supports'                  => [
				'title', 'editor', 'revisions'
			],
			'capabilities' => [
				'edit_post'          => is_multisite() ? 'manage_sites' : 'manage_options',
				'read_post'          => is_multisite() ? 'manage_sites' : 'manage_options',
				'delete_post'        => is_multisite() ? 'manage_sites' : 'manage_options',
				'edit_posts'         => 'edit_posts',
				'edit_others_posts'  => is_multisite() ? 'manage_sites' : 'manage_options',
				'delete_posts'       => is_multisite() ? 'manage_sites' : 'manage_options',
				'publish_posts'      => is_multisite() ? 'manage_sites' : 'manage_options',
				'read_private_posts' => is_multisite() ? 'manage_sites' : 'manage_options'
			]
		] );
	}

	/**
	 * Register taxonomy for post type Guide
	 */
	public static function register_screen_taxonomy()
	{
		register_taxonomy( static::TAXONOMY, [ static::POST_TYPE ],[
			'labels'                => [
				'name'              => esc_html__( 'Guide types', 'innocode-wp-guide' ),
				'singular_name'     => esc_html__( 'Guide type', 'innocode-wp-guide' ),
				'search_items'      => esc_html__( 'Search in guide types', 'innocode-wp-guide' ),
				'all_items'         => esc_html__( 'All guide types', 'innocode-wp-guide' ),
				'view_item '        => esc_html__( 'View guide type', 'innocode-wp-guide' ),
				'parent_item'       => esc_html__( 'Parent guide type', 'innocode-wp-guide' ),
				'parent_item_colon' => esc_html__( 'Parent guide type:', 'innocode-wp-guide' ),
				'edit_item'         => esc_html__( 'Edit guide type', 'innocode-wp-guide' ),
				'update_item'       => esc_html__( 'Update guide type', 'innocode-wp-guide' ),
				'add_new_item'      => esc_html__( 'Add new guide type', 'innocode-wp-guide' ),
				'new_item_name'     => esc_html__( 'New guide type title', 'innocode-wp-guide' ),
				'menu_name'         => esc_html__( 'Guide types', 'innocode-wp-guide' )
			],
			'public'                => false,
			'show_ui'               => is_super_admin(),
			'show_in_menu'          => false,
			'show_in_rest'			=> true,
			'hierarchical'          => true,
			'rewrite'               => false,
			'capabilities' => [
				'manage_terms'      => 'do_not_allow',
				'edit_terms'        => 'edit_posts',
				'delete_terms'      => 'do_not_allow',
				'assign_terms'      => 'edit_posts'
			]
		] );
	}

	/**
	 *
	 */
	public static function get_post_types_with_editor()
	{
		if( is_super_admin() ) {
			$supported_post_types = array_filter( array_intersect(
					array_values( get_post_types( [ 'show_in_rest'	=> 1 ] ) ),
					array_values( get_post_types_by_support( 'editor' ) )
			), function( $post_type ) {
				return static::is_excluded_post_type( $post_type );
			} );

			update_option( static::OPTION, $supported_post_types, false );
		}
	}

	/**
	 * Check if post type is excluded
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public static function is_excluded_post_type( string $post_type ): bool
	{
		return ! in_array( $post_type, [
			'wp_block',
			'wp_template',
			static::POST_TYPE
		] );
	}

	/**
	 * Remove default top position for checked taxonomies
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function set_checked_ontop_default( array $args ): array
	{
		if( isset( $args[ 'taxonomy' ] ) && $args[ 'taxonomy' ] == static::TAXONOMY && ! isset( $args[ 'checked_ontop' ] ) ) {
			add_filter( 'get_terms_args', [ get_called_class(), 'set_terms_args' ], 10, 2 );
			$args[ 'checked_ontop' ] = false;
		}

		return $args;
	}

	/**
	 * Set terms arguments
	 *
	 * @param array $args
	 * @param array $taxonomies
	 *
	 * @return array
	 */
	public static function set_terms_args( array $args, array $taxonomies ): array
	{
		$args[ 'orderby' ] = 'term_id';

		return $args;
	}

	/**
     * Add new column in admin guides list
     *
	 * @param array $columns
	 *
	 * @return array
	 */
	public static function add_admin_column( array $columns ): array
    {
		$position = 2;
		$new_column = [ static::TAXONOMY => esc_html__( 'Post types', 'innocode-wp-guide' ) ];

		return array_slice( $columns, 0, $position ) + $new_column + array_slice( $columns, $position );
	}

	/**
     * Show column's data in guides list
     *
	 * @param string $colname
	 * @param int    $tab_id
	 */
	public static function show_admin_columns( string $colname, int $tab_id )
    {
		$terms = wp_get_object_terms( $tab_id , static::TAXONOMY );

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach( $terms as $index => $term ) {

			    $term_url = add_query_arg( [ static::TAXONOMY => $term->slug ], $_SERVER['REQUEST_URI'] );
				?><a href="<?= $term_url ?>"><?= $term->name ?></a><?php

                if( $index < ( count( $terms ) - 1 ) ) {
					echo ', ';
				}
			}
		}
	}

	/**
	 * Add new taxonomies when option created
	 *
	 * @param string $option
	 * @param        $value
	 */
	public static function add_taxonomies( string $option, $value )
	{
		static::manage_taxonomies( $option, '',  $value );
	}

	/**
	 * Manage taxonomies when option with post types updated
	 *
	 * @param string $option
	 * @param        $old_value
	 * @param        $value
	 */
	public static function manage_taxonomies( string $option, $old_value, $value )
	{
		if( $option == static::OPTION && is_array( $value ) ) {
			foreach ( $value as $post_type ) {

				if( ! term_exists( $post_type, static::TAXONOMY ) ) {
					$args = [
						'slug' => $post_type
					];
					$title = get_post_type_object( $post_type )->labels->singular_name;
					wp_insert_term( $title, static::TAXONOMY, $args );
				}
			}
		}
	}

	/**
	 * Register setting for Guide sorting page
	 */
	public static function register_settings()
	{
		register_setting( static::SORTING_SETTINGS, static::SORTING_SETTINGS );
		register_setting( static::GENERAL_SETTINGS, static::GENERAL_SETTINGS );
	}

	/**
	 * Add Guide sorting page
	 */
	public static function add_sorting_page()
	{
		add_submenu_page(
			'edit.php?post_type=' . static::POST_TYPE,
			esc_html__( 'Guide sorting', 'innocode-wp-guide' ),
			esc_html__( 'Guide sorting', 'innocode-wp-guide' ),
			is_multisite() ? 'manage_sites' : 'manage_options',
			static::SORTING_PAGE,
			[ get_called_class(), 'render_sorting_page' ]
		);
	}

	/**
	 * Add Guide settings page
	 */
	public static function add_settings_page()
	{
		if( is_main_site() ) {
			add_submenu_page(
				'edit.php?post_type=' . static::POST_TYPE,
				esc_html__( 'Guide settings', 'innocode-wp-guide' ),
				esc_html__( 'Guide settings', 'innocode-wp-guide' ),
				'manage_sites',
				static::SETTINGS_PAGE,
				[ get_called_class(), 'render_settings_page' ]
			);
		}

	}

	/**
	 * Render Guide sorting page
	 */
	public static function render_sorting_page()
	{
		?><div class="wrap">
		<h1><?= esc_html__( 'Guide sorting', 'innocode-wp-guide' ) ?></h1>
		<p>Please use drag and drop to sort guides.</p>
		<form method="post" action="<?= admin_url( 'options.php' ) ?>">
			<?php
			settings_fields( static::SORTING_SETTINGS );
			do_settings_sections( static::SORTING_SETTINGS );
			$order = get_option( static::SORTING_SETTINGS, [] );

			if( $screens = get_terms( static::TAXONOMY, [ 'parent' => 0, 'fields' => 'id=>name' ] ) ) : ?>
				<div class="guide_ordering">
					<?php foreach ( $screens as $term_id => $name ) {
						static::render_sorting_screen( $name, $term_id, $order[ $term_id ] ?? '' );
					} ?>
				</div>
			<?php endif ?>
			<?php submit_button() ?>
		</form>
		</div><?php
	}

	/**
	 * Render Guide settings page
	 */
	public static function render_settings_page()
	{
		?><div class="wrap">
		<h1><?= esc_html__( 'Guide settings', 'innocode-wp-guide' ) ?></h1>
		<form method="post" action="<?= admin_url( 'options.php' ) ?>">
			<?php
			settings_fields( static::GENERAL_SETTINGS );
			do_settings_sections( static::GENERAL_SETTINGS );
			$options = get_option( static::GENERAL_SETTINGS, [] );
			$checked = ( isset( $options[ 'from-main-site' ] ) && $options['from-main-site'] == 'true' ? ' checked="checked"' : '');
			?>
			<h2 class="title"><?= esc_html__( 'Multisite settings', 'innocode-wp-guide' ) ?></h2>
			<label for="from-main-site">
				<input name="<?= static::GENERAL_SETTINGS ?>[from-main-site]" type="checkbox" id="from-main-site" value="true" <?= $checked ?>>
				<?= esc_html__( 'Show guides from main site', 'innocode-wp-guide' ) ?>
			</label>
			<?php submit_button() ?>
		</form>
		</div><?php
	}

	/**
	 * @param string $screen_name
	 * @param int    $screen_id
	 * @param string $order
	 */
	public static function render_sorting_screen( string $screen_name, int $screen_id, string $order )
	{
		?>
		<div class="screen">
			<div class="screen-inner-wrapper">
				<h2><?= $screen_name ?></h2>
				<input id="guides-order-<?= $screen_id ?>" type="hidden" name="<?= static::SORTING_SETTINGS ?>[<?= $screen_id ?>]" value="<?= $order ?>" />
				<?php
				$posts = get_posts( [
					'post_type' 		=> static::POST_TYPE,
					'posts_per_page'	=> 10,
					'tax_query'			=> [
						[
							'taxonomy'  => static::TAXONOMY,
							'field'     => 'term_id',
							'terms'     => $screen_id
						]
					],
					'fields'			=> 'ids'
				] );

				if( $posts && is_array( $posts) ) :
					?><ul class="sortable-guides" data-screen-id="<?= $screen_id ?>"><?php
					$order = explode( ',', $order );

					// Display guides which were already ordered
					if( $order ) {
						foreach ( $order as $id ) {
							if( in_array( $id, $posts ) ) {
								static::render_sorting_guide( $id );
								$posts = array_diff( $posts, [ $id ] );
							}
						}
					}

					// Display new guides, which are not sorted
					foreach ( $posts as $id ) {
						static::render_sorting_guide( $id );
					}

					?></ul><?php
				endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * @param int $id
	 */
	public static function render_sorting_guide( int $id )
	{
		?><li data-id="<?= $id ?>"><?= get_the_title( $id ) ?></li><?php
	}

	/**
	 * @param $args
	 * @param $request
	 *
	 * @return mixed
	 */
	public static function filter_rest_request( $args, $request )
	{
		if( isset( $request->get_params()[ 'fromMainSite' ] ) ) {
			switch_to_blog( 1 );
		}

		if ( isset( $request->get_params()[ 'sorted' ] ) ) {
			$term_id = $request->get_params()[ 'wp_guide_posts' ][ 0 ] ?? 0;
			$sorted_ids = explode( ',', get_option( static::SORTING_SETTINGS, [] )[ $term_id ] ?? '' );
			$temp_args = wp_parse_args( [
					'post__not_in'	=> $sorted_ids,
					'fields'		=> 'ids'
			], $args );
			$args[ 'post__in' ] = array_merge(
					$sorted_ids,
					( new WP_Query( $temp_args ) )->posts
			);
			$args[ 'orderby' ] = 'post__in';
		}

		return $args;
	}

	/**
	 * @param $args
	 * @param $request
	 *
	 * @return mixed
	 */
	public static function filter_rest_tax_request( $args, $request )
	{
		if( isset( $request->get_params()[ 'fromMainSite' ] ) ) {
			switch_to_blog( 1 );
		}

		return $args;
	}
}
