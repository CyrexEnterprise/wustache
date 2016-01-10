<?php
namespace Cloudoki\Wustache;

class Template
{
	/**
	 * fallback dir
	 */
	static $base_folder = 'templates/';
	
	/**
	 * working path
	 */
	public $cwd;
	
	/**
	 * Mustache object container
	 */
	public $mustache;
	
	/**
	 * Mustache model constructor
	 */
	public function __construct ($path = null, $helpers = null)
	{
		// Get default dir
		$this->cwd = $path? : get_template_directory () . '/' . get_option ("wustache_base_folder", $this->base_folder);
		
		// Load Mustache
		$this->mustache = new \Mustache_Engine (
		[
			'loader' => new \Mustache_Loader_FilesystemLoader ($this->cwd)
		]);
		
		// Add helpers
		//if ($helpers) 
		//	foreach ($helpers as $key => $helper)
			
		//		$this->mustache->addHelper($key, $helper);
				
		// Test
		//$this->mustache->addHelper ('iterate', ['images'=> function ($value) { return $value[0]; }]);
	}
	
	/**
	 *	Load template and render
	 */
	public function render ($filename, $parameters = null)
	{
		return $this->mustache
			->loadTemplate ($filename)
			->render ($parameters);
	}
}

?>