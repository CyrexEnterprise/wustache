<?php 
namespace Cloudoki\Wustache;

use Cloudoki\Wustache\BaseLoader;
use Cloudoki\Wustache\Admin;
use Cloudoki\Wustache\Publication;

class Wustache extends BaseLoader
{
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	static $version = "0.1.0";
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = [];
		$this->filters = [];
		
		# Components
		$this->admin = new Admin ();
		$this->face = new Publication ();
		
		# Define hooks
		$this->enqueue ();
		$this->admin_hooks ();
		$this->public_hooks ();
	}
	
	/**
	 * Register all of the hooks related to files
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function enqueue () {

		$this->add_action ('admin_enqueue_scripts', $this, 'enqueue_files' );
	}
	
	/**
	 * Register the files for Wustache.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_files ()
	{
		// Wustache
		wp_register_style( 'wustache_admin_css', plugin_dir_url( __DIR__ ) . 'assets/css/wustache-admin.css', false, $this->version );
		wp_enqueue_style ( 'wustache_admin_css' );
		
		// Chosen
		wp_register_style( 'chosen_admin_css', plugin_dir_url( __DIR__ ) . '../vendor/drmonty/chosen/css/chosen.min.css', false, $this->version );
		wp_enqueue_style( 'chosen_admin_css' );
		
		// Ionic Icons
		wp_register_style( 'ionicons_admin_css', 'http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css', false, $this->version );
		wp_enqueue_style( 'ionicons_admin_css' );
		
		// Wustache
		wp_register_script( 'wustache_admin_js', plugin_dir_url( __DIR__ ) . 'assets/js/wustache-admin.js', ['jquery'], $this->version );
		wp_enqueue_script( 'wustache_admin_js' );
		
		// Chosen
		wp_register_script( 'chosen_admin_js', plugin_dir_url( __DIR__ ) . '../vendor/drmonty/chosen/js/chosen.jquery.min.js', ['jquery'], $this->version );
		wp_enqueue_script( 'chosen_admin_js' );
		
		// Draggable UI
		wp_register_script( 'draggable_admin_js', '//code.jquery.com/ui/1.11.4/jquery-ui.js', ['jquery'], $this->version );
		wp_enqueue_script( 'draggable_admin_js' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function admin_hooks() {
		

		// Actions
		
		// Load Dashboard widget
		$this->add_action ('wp_dashboard_setup', $this->admin, 'dashboard');
		
		// Load Settings field
		// add_settings_field( 'myprefix_setting-id', 'This is the setting title', 'myprefix_setting_callback_function', 'general', 'myprefix_settings-section-name', array( 'label_for' => 'myprefix_setting-id' ) );
		
		// Load Edit Post additions
		$this->add_action ('add_meta_boxes', $this->admin, 'post_edit');

		// Load social toggles on submitbox, if the setting is available
		// if (get_option('smmp_view_submitbox'))
		//	$this->add_action ('post_submitbox_misc_actions', $this->admin, 'admin_post_submitbox' );
		
		// On post update/save
		$this->add_action ('save_post', $this->admin, 'post_edit_submit');

		// On post schedule
		$this->add_action( 'transition_post_status', $this->admin, 'on_all_status_transitions', 10, 3 );

		// $this->add_action ('save_post', $this->admin, 'admin_post_submitbox_submit');
		
		
		// Load Expired Account notice
		/*try {$this->admin->validate_accounts (); }
		catch (Exception $e)
		{
			$this->add_action ('admin_notices', $this->admin, 'notice_accounts' );
		}*/
	}
	
		
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function public_hooks()
	{
		// Load Content
		$this->add_action ('the_template', $this->face, 'template_content');
		
		
	}
}



