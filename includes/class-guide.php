<?php

namespace InnocodeWPGuide;

use Error;

/**
 * Class Guide
 *
 * @package InnocodeWPGuide
 */
final class Guide
{
	const POST_TYPE = 'wp_guide';
	const TAXONOMY = 'wp_guide_posts';
	const OPTION = 'wp_guide_post_types';

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
	}

	/**
	 * Register and enqueue admin scripts and styles
	 */
	public static function admin_enqueue_scripts()
	{
		$dir = dirname( WP_GUIDE_PLUGIN_PATH );
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
				plugins_url( $index_js, WP_GUIDE_PLUGIN_PATH ),
				array_merge( $script_asset['dependencies'], [ 'wp-edit-post' ]),
				$script_asset['version']
		);

		$editor_css = 'build/index.css';
		wp_register_style(
				'innocode-wp-guide-style',
				plugins_url( $editor_css, WP_GUIDE_PLUGIN_PATH ),
				[],
				filemtime( "$dir/$editor_css" )
		);
	}

	/**
	 * Register post type Guide
	 */
	public static function register_guide_post_type()
	{
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
			'show_in_menu'              => defined( 'INNOCODE_WP_MANUAL' ) ? 'edit.php?post_type=wp_manual_help_tab' : true,
			'show_in_rest'				=> true,
			'menu_icon'                 => 'dashicons-book',
			'menu_position'             => 3,
			'rewrite'                   => false,
			'supports'                  => [
				'title', 'editor', 'revisions'
			],
			'capabilities' => [
				'edit_post'          => 'manage_sites',
				'read_post'          => 'manage_sites',
				'delete_post'        => 'manage_sites',
				'edit_posts'         => 'edit_posts',
				'edit_others_posts'  => 'manage_sites',
				'delete_posts'       => 'manage_sites',
				'publish_posts'      => 'manage_sites',
				'read_private_posts' => 'manage_sites'
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
}
