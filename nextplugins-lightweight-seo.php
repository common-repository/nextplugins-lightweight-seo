<?php
/**
 * Plugin Name: NextPlugins Lightweight SEO
 * Plugin URI: https://www.nextplugins.com/lightweight-seo
 * Description: Lightweight SEO plugin for WordPress.
 * Version: 1.0.1
 * Author: NextPlugins
 * Requires at least: 4.4
 * Author URI: https://www.nextplugins.com
 * Text Domain: nextplugins-lightweight-seo
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Next_Plugins_Lightweight_SEO {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.1';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	private $post_types = array('post', 'page');

	private $taxonomies = array('category');

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		$this->load_plugin_textdomain();

		if(defined('NP_LIGHTWEIGHT_SEO_POSTS'))
		{
			$post_types = explode(',', NP_LIGHTWEIGHT_SEO_POSTS);

			$post_types = array_map('trim', $post_types);

			if(count($post_types) > 0)
			{
				$this->post_types = $post_types;
			}
		}

		if(defined('NP_LIGHTWEIGHT_SEO_TAXONOMY'))
		{
			$taxonomies = explode(',', NP_LIGHTWEIGHT_SEO_TAXONOMY);

			$taxonomies = array_map('trim', $taxonomies);

			if(count($taxonomies) > 0)
			{
				$this->taxonomies = $taxonomies;
			}
		}

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'add_seo_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_meta_box_data' ), 10, 3 );
			$this->add_taxonomy_fields();
		}
		else
		{
			add_action( 'wp_head', array( $this, 'add_post_meta_tags' ) , 2 );
			add_action( 'wp_head', array( $this, 'add_taxonomy_meta_tags' ) , 2 );
		}
	}

	public function add_seo_meta_box() {
		$screens = $this->post_types;
		foreach ( $screens as $screen ) {
			add_meta_box(
				'next_plugins_lightweight_seo',
				__( 'NextPlugins Lightweight SEO', 'nextplugins-lightweight-seo' ),
				array( $this, 'build_meta_box' ),
				$screen
			);
		}
	}

	public function add_taxonomy_fields()
	{
		$screens = $this->taxonomies;
		foreach ( $screens as $screen ) {
			if(!taxonomy_exists($screen)) continue;

			add_action( $screen.'_add_form_fields', array($this, 'taxonomy_add_meta_fields'), 10 );
			add_action( $screen.'_edit_form_fields', array($this, 'taxonomy_edit_meta_fields'), 10, 2 );

			add_action( 'created_'.$screen, array($this, 'taxonomy_save_taxonomy_meta'), 10, 2 );
			add_action( 'edited_'.$screen, array($this, 'taxonomy_save_taxonomy_meta'), 10, 2 );
		}
	}

	function build_meta_box( $post ) {
		$description = get_post_meta( $post->ID, '_meta_description', true );

		$keywords = get_post_meta( $post->ID, '_meta_keywords', true );
		?>

		<label><?php _e( 'Meta Description', 'nextplugins-lightweight-seo' ); ?></label>

		<textarea name="seo_meta_description" style="width: 100%"><?php echo esc_html($description) ?></textarea>

		<label><?php _e( 'Meta Keywords', 'nextplugins-lightweight-seo' ); ?></label>

		<textarea name="seo_meta_keywords" style="width: 100%"><?php echo esc_html($keywords) ?></textarea>

		<?php
	}

	public function save_meta_box_data( $post_id, $post, $update ) {
		$post_type = get_post_type($post_id);
		if( !in_array($post_type, $this->post_types) ) return;

		//skip autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$description = strip_tags(trim( $_REQUEST['seo_meta_description'] ));
		$keywords    = strip_tags(trim( $_REQUEST['seo_meta_keywords'] ));

		update_post_meta( $post_id, '_meta_description', sanitize_text_field( $description ) );
		update_post_meta( $post_id, '_meta_keywords', sanitize_text_field( $keywords ) );
	}

	public function taxonomy_add_meta_fields( $taxonomy ) {
		?>
		<div class="form-field term-group">
			<label><?php echo __( 'NextPlugins Lightweight SEO', 'nextplugins-lightweight-seo' ).': '; _e( 'Meta Description', 'nextplugins-lightweight-seo' ); ?></label>
			<textarea name="seo_taxonomy_meta_description" cols="40" rows="2"></textarea>
		</div>

		<div class="form-field term-group">
			<label><?php echo __( 'NextPlugins Lightweight SEO', 'nextplugins-lightweight-seo' ).': '; _e( 'Meta Keywords', 'nextplugins-lightweight-seo' ); ?></label>
			<textarea name="seo_taxonomy_meta_keywords" cols="40" rows="2"></textarea>
		</div>
		<?php
	}

	public function taxonomy_edit_meta_fields( $term, $taxonomy ) {

		$description = get_term_meta( $term->term_id, '_meta_description', true );

		$keywords = get_term_meta( $term->term_id, '_meta_keywords', true );

		?>
		<tr class="form-field term-group-wrap">
			<th scope="row">
				<label><?php echo __( 'NextPlugins Lightweight SEO', 'nextplugins-lightweight-seo' ).': '; _e( 'Meta Description', 'nextplugins-lightweight-seo' ); ?></label>
			</th>
			<td>
				<textarea name="seo_taxonomy_meta_description" cols="40" rows="2"><?php echo esc_html($description); ?></textarea>
			</td>
		</tr>
		<tr class="form-field term-group-wrap">
			<th scope="row">
				<label><?php echo __( 'NextPlugins Lightweight SEO', 'nextplugins-lightweight-seo' ).': '; _e( 'Meta Keywords', 'nextplugins-lightweight-seo' ); ?></label>
			</th>
			<td>
				<textarea name="seo_taxonomy_meta_keywords" cols="40" rows="2"><?php echo esc_html($keywords); ?></textarea>
			</td>
		</tr>
		<?php
	}

	public function taxonomy_save_taxonomy_meta( $term_id, $tag_id ) {
		$description = strip_tags(trim( $_REQUEST['seo_taxonomy_meta_description'] ));
		$keywords    = strip_tags(trim( $_REQUEST['seo_taxonomy_meta_keywords'] ));

		update_term_meta( $term_id, '_meta_description', sanitize_text_field( $description ) );
		update_term_meta( $term_id, '_meta_keywords', sanitize_text_field( $keywords ) );
	}

	public function add_post_meta_tags() {
		if ( is_single() ) {
			global $post;
			if( !in_array($post->post_type, $this->post_types) ) return;

			$description = get_post_meta( $post->ID, '_meta_description', true );
			$keywords = get_post_meta( $post->ID, '_meta_keywords', true );
			echo '<meta name="description" content="' . esc_attr(trim($description)) . '" />' . "\n";
			echo '<meta name="keywords" content="' . esc_attr(trim($keywords)) . '" />' . "\n";

			echo '<meta property="og:site_name" content="'.esc_attr(get_bloginfo('name')).'"/>' . "\n";
			echo '<meta property="og:title" content="'.esc_attr(trim($post->post_title)).'"/>' . "\n";
			echo '<meta property="og:description" content="'.esc_attr(trim($description)).'"/>' . "\n";
			echo '<meta property="og:url" content="'.esc_url(get_post_permalink($post->ID)).'"/>' . "\n";

			echo '<meta name="twitter:card" content="summary">' . "\n";
			echo '<meta name="twitter:title" content="'.esc_attr(trim($post->post_title)).'">' . "\n";
			echo '<meta name="twitter:description" content="'.esc_attr(trim($description)).'">' . "\n";

			$image['url'] = get_the_post_thumbnail_url($post->ID, 'medium_large');
			$meta = wp_get_attachment_metadata(get_post_thumbnail_id($post->ID));
			if(!empty($meta))
			{
				$image['width'] = $meta['sizes']['medium_large']['width'];
				$image['height'] = $meta['sizes']['medium_large']['height'];
			}

			$image = apply_filters('next_plugins_lightweight_seo_post_image', $image, $post);

			if(!empty($image['url'])) {
				echo '<meta property="og:image" content="'.esc_url($image['url']).'" />' . "\n";
				echo '<meta name="twitter:image" content="'.esc_url($image['url']).'" />' . "\n";
			}

			if(!empty($image['width'])) {
				echo '<meta property="og:image:width" content="'.esc_attr($image['width']).'"/>' . "\n";
			}

			if(!empty($image['height'])) {
				echo '<meta property="og:image:height" content="'.esc_attr($image['height']).'"/>' . "\n";
			}
		}
	}

	public function add_taxonomy_meta_tags() {
		if ( is_tax() ) {
			$term = get_queried_object();
			if( !in_array($term->taxonomy, $this->taxonomies) ) return;

			$description = get_term_meta( $term->term_id, '_meta_description', true );
			$keywords = get_term_meta( $term->term_id, '_meta_keywords', true );
			echo '<meta name="description" content="' . esc_attr(trim($description)) . '" />' . "\n";
			echo '<meta name="keywords" content="' . esc_attr(trim($keywords)) . '" />' . "\n";

			echo '<meta property="og:site_name" content="'.esc_attr(get_bloginfo('name')).'"/>' . "\n";
			echo '<meta property="og:title" content="'.esc_attr(trim($term->name)).'"/>' . "\n";
			echo '<meta property="og:description" content="'.esc_attr(trim($description)).'"/>' . "\n";
			echo '<meta property="og:url" content="'.esc_url(get_term_link( $term )).'"/>' . "\n";

			echo '<meta name="twitter:card" content="summary">' . "\n";
			echo '<meta name="twitter:title" content="'.esc_attr(trim($term->name)).'">' . "\n";
			echo '<meta name="twitter:description" content="'.esc_attr(trim($description)).'">' . "\n";


			$image = apply_filters('next_plugins_lightweight_seo_term_image', null, $term);

			if(!empty($image['url'])) {
				echo '<meta property="og:image" content="'.esc_url($image['url']).'" />' . "\n";
				echo '<meta name="twitter:image" content="'.esc_url($image['url']).'" />' . "\n";
			}

			if(!empty($image['width'])) {
				echo '<meta property="og:image:width" content="'.esc_attr($image['width']).'"/>' . "\n";
			}

			if(!empty($image['height'])) {
				echo '<meta property="og:image:height" content="'.esc_attr($image['height']).'"/>' . "\n";
			}
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'nextplugins-lightweight-seo' );

		load_textdomain( 'nextplugins-lightweight-seo', trailingslashit( WP_LANG_DIR ) . 'plugins/nextplugins-lightweight-seo-' . $locale . '.mo' );
		load_plugin_textdomain( 'nextplugins-lightweight-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

add_action( 'init', array( 'Next_Plugins_Lightweight_SEO', 'get_instance' ), 100 );