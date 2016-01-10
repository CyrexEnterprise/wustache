<?php 
namespace Cloudoki\Wustache;

class Activator
{
	/**
	 * Add Flow Drive core functionalities.
	 * Activator adds Flow Drive basic option defaults.
	 */
	public static function activate ()
	{
		// Create or update options
		self::generate_options ();
	}
	
	/**
	 * Disable Flow Drive core functionalities.
	 */
	public static function deactivate ()
	{
	}
	
	/**
	 *	Add The Flow Drive Wordpress Options
	 */
	public static function generate_options ()
	{
		// Location options
		add_option("wustache_base_folder", 'templates');
	}
}
