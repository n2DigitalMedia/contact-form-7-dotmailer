<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
  Plugin Name: N2 Dotmailer Contact Form 7  Plugin
  Description: Integrate Contact Form 7 forms with dotmailer. Push certain fields into dotmailer that have a set name prefix of "dm_".
  Version: 1.0.0
  Author: N2 Digital Media
  Author URI: http://www.n2digitalmedia.com/
 */


if ( ! class_exists( 'DMCF7PLUGIN' ) ) :

/**
 * Main DMCF7PLUGIN Class
 *
 * @class DMCF7PLUGIN
 * @version	1.0.0
 */
final class DMCF7PLUGIN {

	/**
	 * @var DMCF7PLUGIN The single instance of the class
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main DMCF7PLUGIN Instance
	 *
	 * Ensures only one instance of DMCF7PLUGIN is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return DMCF7PLUGIN - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since 0.1.0
	 */
	function __construct() {
		global $dm_cf7_plugin;

		/* Set up an empty class for the global $dm_cf7_plugin object. */
		$dm_cf7_plugin = new stdClass;

		/* Auto-load classes on demand. */
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->constants();

		$this->includes();

		add_action( 'init', array( $this, 'init'), 0 );

		add_action( 'admin_menu', array( $this, 'setup_settings'), 0);

	}

	/**
	 * Defines constants used by the plugin.
	 *
	 * @since 0.1.0
	 */
	function constants() {

		// Set the version number of the plugin.
		define( 'DM_CF7_PLUGIN_VERSION', '1.0.0' );

		// Set the database version number of the plugin.
		define( 'DM_CF7_PLUGIN_DB_VERSION', 1 );

		// Set constant path to the plugin directory.
		define( 'DM_CF7_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		// Set constant path to the plugin URL.
		define( 'DM_CF7_PLUGIN_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

		// Set the constant path to the includes directory.
		define( 'DM_CF7_PLUGIN_INCLUDES', DM_CF7_PLUGIN_DIR . trailingslashit( 'includes' ) );

		// Set the constant path to the libraries directory.
		define( 'DM_CF7_PLUGIN_LIBRARIES', DM_CF7_PLUGIN_DIR . trailingslashit( 'libraries' ) );

		// Set the constant path to the admin directory.
		define( 'DM_CF7_PLUGIN_ADMIN', DM_CF7_PLUGIN_DIR . trailingslashit( 'admin' ) );

	}

	/**
	 * Includes the initial files needed by the plugin.
	 *
	 * @since 0.1.0
	 */
	private function includes() {

		// Load the plugin functions file
		include_once( DM_CF7_PLUGIN_INCLUDES . 'core-functions.php' );

		include_once( DM_CF7_PLUGIN_INCLUDES . 'class-form-handler.php' );

		include_once( DM_CF7_PLUGIN_INCLUDES . 'class-dotmailer-connect.php');

		// Admin Set-up - Required to set-up admin options, custom meta boxes etc.
		if ( is_admin() ) {
			include_once( DM_CF7_PLUGIN_INCLUDES . 'admin/class-admin.php' );
		}

	}

	/**
	 * Init when WordPress Initialises.
	 */
	public function init() {

		// Before init action
		do_action( 'before_dm_cf7_plugin_init' );

		//add_action('customize_save_after', 'dm_cf7_plugin_hash_password');
		add_filter('update_option_dotmailer_password', 'dm_cf7_plugin_hash_password', 0, 2);

		// Init action
		do_action( 'dm_cf7_plugin_init' );


	}

	/**
	 * Auto-load classes on demand.
	 *
	 * @param mixed $class
	 * @return void
	 */
	private function autoload( $class ) {

		$class = strtolower( $class );

		if ( strpos( $class, 'n2dmcf7_' ) === 0 ) {

			$path = $this->plugin_path() . '/includes/';
			$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

			if ( is_readable( $path . $file ) ) {
				include_once( $path . $file );
				return;
			}
		}

		if ( strpos( $class, 'n2dmcf7_admin' ) === 0 ) {

			$path = $this->plugin_path() . '/includes/admin/';
			$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

			if ( is_readable( $path . $file ) ) {
				include_once( $path . $file );
				return;
			}
		}

		if ( strpos( $class, 'n2dmcf7_settings' ) === 0 ) {

			$path = $this->plugin_path() . '/includes/admin/settings/';
			$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

			if ( is_readable( $path . $file ) ) {
				include_once( $path . $file );
				return;
			}
		}

	}

	/**
	 * Setup plugin settings page.
	 */
	public function setup_settings() {

		$dm_settings = n2dmcf7_get_options();

		// Add settings sections and fields for general settings for the plugin
		add_settings_section(
		  'n2dmcf7_dotmailer_settings',
		  __('Dotmailer Credentials', 'dm_cf7_plugin'),
		  'n2dmcf7_dotmailer_settings_intro',
		  'n2dmcf7-dotmailer-settings'
		);

		// username
	 	add_settings_field(
			'dotmailer_username',
			__( 'Username:', 'dm_cf7_plugin' ),
			'n2dmcf7_dotmailer_text_input',
			'n2dmcf7-dotmailer-settings',
			'n2dmcf7_dotmailer_settings',
			array(
				'id'	=> 'dotmailer_username',
				'name' 	=> 'n2dmcf7_dotmailer_settings[dotmailer_username]',
				'class' => 'medium-text',
				'value' => isset($dm_settings["dotmailer_username"]) ? $dm_settings["dotmailer_username"] : ''
				)
		);

	 	// password
		add_settings_field(
			'dotmailer_password',
			__( 'Password:', 'dm_cf7_plugin' ),
			'n2dmcf7_dotmailer_password_input',
			'n2dmcf7-dotmailer-settings',
			'n2dmcf7_dotmailer_settings',
			array(
				'id'	=> 'dotmailer_password',
				'name' 	=> 'n2dmcf7_dotmailer_settings[dotmailer_password]',
				'class' => 'medium-text',
				'value' => isset($dm_settings["dotmailer_password"]) ? $dm_settings["dotmailer_password"] : ''
				)
		);

		//list address books
		add_settings_field(
			'dotmailer_addressbooks',									// ID (Required)
			__( 'Available Addressbooks:', 'dm_cf7_plugin' ),			// Title (Required)
			'n2dmcf7_dotmailer_addressbooks',	// Callback Function (Required)
			'n2dmcf7-dotmailer-settings',		// Page (Required)
			'n2dmcf7_dotmailer_settings'		// Section (Optional)
			// array(														// Args (Optional)
			// 	'id'	=> 'dotmailer_addressbooks',
			// 	'name' 	=> 'n2dmcf7_dotmailer_settings[dotmailer_addressbooks]',
			// 	'class' => 'medium-text'
			// 	//'value' => isset($dm_settings["dotmailer_addressbooks"]
			// 	)
		);

		/**
		 * outputs general sections intro text.
		 *
		 */
		function n2dmcf7_dotmailer_settings_intro() {

		 	//echo '<p>'.__( 'Main settings', 'dm_cf7_plugin' ).'</p>';

		}

		/**
		 * outputs settings text input.
		 *
		 */
		function n2dmcf7_dotmailer_text_input($field) {

		    echo '<input type="text" name="'. esc_attr( $field['name'] ) .'" class="'. esc_attr( $field['class'] ) .'" value="'.  esc_attr( $field['value'] ) .'" />';

		}

		function n2dmcf7_dotmailer_password_input($field) {

		    echo '<input type="password" name="'. esc_attr( $field['name'] ) .'" class="'. esc_attr( $field['class'] ) .'" value="'.  esc_attr( $field['value'] ) .'" />';

		}

		function n2dmcf7_dotmailer_addressbooks() {
			$dm_settings = n2dmcf7_get_options();
			$dm_username = $dm_settings["dotmailer_username"];
        	$dm_password = $dm_settings["dotmailer_password"];

        	$dm_connection = new N2CF7_DotMailerConnect($dm_username,$dm_password);

		    if(isset($dm_connection)){
		    	
		    	if($dm_connection->username != '' && $dm_connection->password != ''){
		    		$address_books = $dm_connection->listAddressBooks();
		    		foreach($address_books as $book){
		    			?>
		    			<div class="addressbook">
		    				<p><b>ID:</b> <?php echo $book->ID; ?> - 
		    					<b>Name: </b><?php echo $book->Name; ?></p>
						</div>
		    			<?php
		    		}

		    	}
		    	
		    }

		}

		register_setting( 
			'n2dmcf7-settings-group',
			'n2dmcf7_dotmailer_settings'
		);

		add_options_page(
			__('n2dmcf7', 'n2dmcf7'), 
			__('Dotmailer Contact Form 7 Plugin Settings','n2dmcf7'), 
			'manage_options', 
			'n2dmcf7dotmailer-settings', 
			'N2DMCF7_Settings::output'
		);

	}

	/* Helper functions */

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url() );
	}

}

endif;

/**
 * Returns the main instance of  to prevent the need to use globals.
 *
 * @since  1.0
 * @return DMCF7PLUGIN
 */
function N2DMCF7() {
	return DMCF7PLUGIN::instance();
}

// Global for backwards compatibility.
$GLOBALS['dm_cf7_plugin'] = N2DMCF7();

