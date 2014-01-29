<?php
/**
 * Plugin Name: Social Venues for Events Calendar Pro
 * Plugin URI: http://themeofthecrop.com
 * Description: Adds social media buttons to venue pages in Events Calendar Pro. Requires Events Calendar and Events Calendar Pro by Modern Tribe.
 * Version: 1.0.2
 * Author: Nate Wright
 * Author URI: https://github.com/NateWr
 * Requires at least: 3.8
 * Tested up to: 3.8
 *
 * Text Domain: svecp_social_venues
 * Domain Path: /languages/
 *
 * Thanks to Mike Jolley for sharing his WP Post Series plugin. The class
 * structure for his plugin was used as a basis (and learning tool) for this
 * plugin. See: https://github.com/mikejolley/wp-post-series
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

class SVECP_Social_Venues {

	// Meta data loaded for a particular venue
	public $meta_data = array();

	// List of social media services supported
	public $services = array();

	// Stylesheets to enqueue on the front-end
	public $stylesheets = array();

	// HTML output for the profile links
	public $profile_links = '';

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		// Define constants
		define( 'SVECP_VERSION', '1.0' );
		define( 'SVECP_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'SVECP_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'SVECP_TEXTDOMAIN', 'svecp_textdomain' );
		define( 'SVECP_VENUE_POST_TYPE', 'tribe_venue' );

		// Init
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'define_services' ) );
		add_action( 'init', array( $this, 'check_dependencies' ) );

		// Handle Meta Boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );

		// Frontend display
		add_filter( 'the_content', array( $this, 'add_profile_links_to_content' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		// Admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Load the plugin textdomain for localistion
	 * @since 1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( SVECP_TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
	}

	/**
	 * Define the meta data for social venues
	 * @since 1.0
	 */
	public function define_services() {

		$this->services = array(
			'facebook'	=> array(
				'label'				=> __( 'Facebook', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-facebook"></i>'
			),
			'twitter'		=> array(
				'label'				=> __( 'Twitter', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-twitter"></i>'
			),
			'google_plus'	=> array(
				'label'				=> __( 'Google Plus', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-google-plus"></i>'
			),
			'youtube'		=> array(
				'label'				=> __( 'YouTube', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-youtube"></i>'
			),
			'flickr'		=> array(
				'label'				=> __( 'Flickr', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-flickr"></i>'
			),
			'pinterest'		=> array(
				'label'				=> __( 'Pinterest', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-pinterest"></i>'
			),
			'foursquare'		=> array(
				'label'				=> __( 'Foursquare', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-foursquare"></i>'
			),
			'instagram'		=> array(
				'label'				=> __( 'Instagram', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-instagram"></i>'
			),
			'linkedin'		=> array(
				'label'				=> __( 'LinkedIn', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-linkedin"></i>'
			),
			'vimeo'		=> array(
				'label'				=> __( 'Vimeo', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-vimeo-square"></i>'
			),
			'weibo'		=> array(
				'label'				=> __( 'Weibo', SVECP_TEXTDOMAIN ),
				'html'				=> '<i class="fa fa-2x fa-weibo"></i>'
			)
		);

		$this->services = apply_filters( 'svecp_services', $this->services );

	}

	/**
	 * Check if Events Calendar Pro is activated
	 * @since 1.0
	 */
	public function check_dependencies() {
		if ( !is_plugin_active( 'events-calendar-pro/events-calendar-pro.php' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_events_calendar_pro' ) );
		}
	}

	/**
	 * Add admin notice if Events Calendar Pro is not active
	 * @since 1.0
	 */
	public function missing_events_calendar_pro() {
		?>
		<div class="error">
			<p>
				<strong>Events Calendar Pro</strong> must be installed and activated in order for <strong>Social Venues for Events Calendar Pro</strong> to work properly. Please activate Events Calendar Pro below. If you do not have it yet, it can be purchased at <a href="http://tri.be/shop/wordpress-events-calendar-pro/" title="Purchase Events Calendar Pro">Modern Tribe</a>.
			</p>
		</div>
		<?php
	}

	/**
	 * Add metaboxes for custom fields
	 * @since 1.0
	 */
	public function add_meta_boxes() {

		// Add metabox for review link
		add_meta_box(
			'svecp_social_profiles',
			__( 'Social Media Profiles', SVECP_TEXTDOMAIN ),
			array( $this, 'print_social_profiles_meta_box' ),
			SVECP_VENUE_POST_TYPE,
			'normal',
			'default'
		);

	}

	/**
	 * Load the meta data from the database
	 * @since 1.0
	 * @return array Meta data
	 */
	public function load_meta_data() {

		global $post;

		// Define and filter defaults
		$this->meta_data['layout'] = 'icons';
		$this->meta_data['profiles'] = array();

		$this->meta_data = apply_filters( 'svecp_default_metadata', $this->meta_data );

		$meta = get_post_meta( $post->ID, 'svecp' );

		if ( isset( $meta[0]['layout'] ) ) {
			$this->meta_data['layout'] = $meta[0]['layout'];
		}

		if ( isset( $meta[0]['profiles'] ) && count( $meta[0]['profiles'] ) ) {
			foreach ( $meta[0]['profiles']['networks'] as $i => $network ) {
				if ( isset( $meta[0]['profiles']['urls'][$i] ) ) {
					$this->meta_data['profiles'][] = array(
						'network' => $meta[0]['profiles']['networks'][$i],
						'label' => $meta[0]['profiles']['labels'][$i],
						'url' => $meta[0]['profiles']['urls'][$i]
					);
				}
			}
		}

		$this->meta_data = apply_filters( 'svecp_loaded_metadata', $this->meta_data );

	}

	/**
	 * Print the social profiles meta box on the edit page
	 * @since 1.0
	 */
	public function print_social_profiles_meta_box() {

		global $post;

		$this->load_meta_data();

		?>

		<input type="hidden" name="svecp_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>">

		<h3><?php _e( 'Display Settings', SVECP_TEXTDOMAIN ); ?></h3>

		<label for="svecp_icons">
			<input type="radio" id="svecp_icons" name="svecp[layout]" value="icons"<?php if ( $this->meta_data['layout'] == 'icons' ) : ?> checked<?php endif; ?>> <?php _e( 'Icons only', SVECP_TEXTDOMAIN ); ?>
		</label>
		<label for="svecp_text">
			<input type="radio" id="svecp_text" name="svecp[layout]" value="text"<?php if ( $this->meta_data['layout'] == 'text' ) : ?> checked<?php endif; ?>> <?php _e( 'Icons with labels', SVECP_TEXTDOMAIN ); ?>
		</label>

		<h3><?php _e( 'Social Profiles', SVECP_TEXTDOMAIN ); ?></h3>

		<div id="svecp-services">

			<?php
			foreach ( $this->meta_data['profiles'] as $args ) {
				echo $this->get_profile_input_template( $args );
			}
			?>

		</div>

		<p>
			<a href="#" class="svecp-add"><?php _e( 'Add new profile', SVECP_TEXTDOMAIN ); ?></a>
		</p>

		<?php
	}

	/**
	 * Generate HTML template for a service
	 * @since 1.0
	 */
	public function get_profile_input_template( $args = array() ) {

		if ( !isset( $args['network'] ) ) {
			$args['network'] = '';
		}
		if ( !isset( $args['label'] ) ) {
			$args['label'] = '';
		}
		if ( !isset( $args['url'] ) ) {
			$args['url'] = '';
		}

		ob_start();
		?>

		<div class="svecp_service">

			<label for="svecp[profiles][networks][]">
				<?php _e( 'Social Network', SVECP_TEXTDOMAIN ); ?>
			</label>
			<select name="svecp[profiles][networks][]">
			<?php foreach ( $this->services as $service_id => $service ) : ?>
				<option value="<?php echo $service_id; ?>"<?php if ( $args['network'] == $service_id ) : ?> selected<?php endif; ?>><?php echo esc_attr( $service['label'] ); ?></option>
			<?php endforeach; ?>
			</select>

			<label for="svecp[profiles][labels][]"><?php _e( 'Label', SVECP_TEXTDOMAIN ); ?></label>
			<input type="text" class="large-text" name="svecp[profiles][labels][]" value="<?php echo esc_attr( $args['label'] ); ?>">

			<label for="svecp[profiles][urls][]"><?php _e( 'Social Media Profile URL', SVECP_TEXTDOMAIN ); ?></label>
			<input type="text" class="large-text" name="svecp[profiles][urls][]" value="<?php echo esc_attr( $args['url'] ); ?>" placeholder="http://">

			<p>
				<a href="#" class="svecp-delete"><?php _e( 'Delete', SVECP_TEXTDOMAIN ); ?></a>
			</p>

		</div>

		<?php
		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Save metaboxes
	 * @param int $post_id post ID
	 * @since 1.0
	 */
	public function save_meta_boxes( $post_id ) {

		if ( !isset( $_REQUEST['svecp_nonce'] ) || !wp_verify_nonce( $_REQUEST['svecp_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Save the metadata
		if ( SVECP_VENUE_POST_TYPE == $_REQUEST['post_type'] ) {
			$cur = get_post_meta( $post_id, 'svecp', true );
			$new = $this->array_filter_recursive( $_REQUEST['svecp'], 'sanitize_text_field' );
			if ( $new && $new != $cur ) {
				update_post_meta( $post_id, 'svecp', $new );
			} elseif ( $new == '' && $cur ) {
				delete_post_meta( $post_id, 'svecp', $cur );
			}
		}

	}

	/**
	 * Add the social profile links to the venue content
	 * @param  string $content Post content
	 * @return string Ammended post content
	 * @since 1.0
	 */
	public function add_profile_links_to_content( $content ) {
		global $post;

		if ( SVECP_VENUE_POST_TYPE !== $post->post_type || !is_main_query() ) {
			return $content;
		}

		$this->load_meta_data();

		if ( !count( $this->meta_data['profiles'] ) ) {
			return $content;
		}

		$this->enqueue_assets();

		ob_start();
		?>

		<div class="svecp-icon-strip svecp-layout-<?php echo esc_attr( $this->meta_data['layout'] ); ?>">

		<?php
		foreach( $this->meta_data['profiles'] as $profile ) :
			if ( isset( $this->services[$profile['network']] ) ) :
				if( $this->meta_data['layout'] == 'icons' ) :
				?>

			<a class="svecp-icon svecp-network-<?php echo esc_attr( $profile['network'] ); ?>" href="<?php echo esc_url( $profile['url'] ); ?>" title="Follow us on <?php echo esc_attr( $profile['label'] ); ?>"><?php echo $this->services[$profile['network']]['html']; ?></a>

				<?php elseif ( $this->meta_data['layout'] == 'text' ) : ?>

			<p>
				<a class="svecp-icon svecp-network-<?php echo esc_attr( $profile['network'] ); ?>" href="<?php echo esc_url( $profile['url'] ); ?>" title="Follow us on <?php echo esc_attr( $profile['label'] ); ?>"><?php echo $this->services[$profile['network']]['html']; ?></a>
				<a class="svecp-text svecp-network-<?php echo esc_attr( $profile['network'] ); ?>" href="<?php echo esc_url( $profile['url'] ); ?>" title="Follow us on <?php echo esc_attr( $profile['label'] ); ?>">
					<?php echo $profile['label']; ?>
				</a>
			</p>

				<?php
				endif;
			endif;
		endforeach; ?>

		</div>

		<?php

		$this->profile_links = ob_get_clean();
		$this->profile_links = apply_filters( 'svecp_frontend_html', $this->profile_links );
		$content .= $this->profile_links;

		return $content;

	}

	/**
	 * Register front-end styles
	 * @since 1.0
	 */
	public function register_assets() {
		$this->stylesheets = array(
			array(
				'handle'	=> 'font-awesome',
				'url'		=> '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css'
			),
		);

		$this->stylesheets = apply_filters( 'svecp_enqueue_stylesheets', $this->stylesheets );

		foreach ( $this->stylesheets as $stylesheet ) {
			wp_register_style( $stylesheet['handle'], $stylesheet['url'] );
		}
	}

	/**
	 * Enqueue front-end styles
	 * @since 1.0
	 */
	public function enqueue_assets() {
		foreach ( $this->stylesheets as $stylesheet ) {
			wp_enqueue_style( $stylesheet['handle'] );
		}
	}

	/**
	 * Register admin scripts and styles
	 * @since 1.0
	 */
	public function enqueue_admin_assets() {
		wp_enqueue_style( 'svecp-admin-style', SVECP_PLUGIN_URL . '/assets/css/admin.css' );
		wp_enqueue_script( 'svecp-admin', SVECP_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery' ) );
		wp_localize_script(
			'svecp-admin',
			'wp_data',
			array(
				'network_template' => $this->get_profile_input_template()
			)
		);
	}

	/**
	 * Run callback on every element in array recursively
	 *
	 * Used to sanitize all values in an array
	 * @since 1.0
	 */
	public function array_filter_recursive( $arr, $callback ) {
		foreach ( $arr as &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->array_filter_recursive( $value, $callback );
			}
		}
		return array_filter( $arr, $callback );
	}

}

new SVECP_Social_Venues();