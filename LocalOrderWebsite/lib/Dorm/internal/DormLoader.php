<?php
/* DormLoader - Contains all base methods for loading and initializing the Dorm framework */

namespace Dorm;

// Is the Dorm path set correctly?
if ( ! defined('DORM_PATH') || ! is_dir(DORM_PATH))
{
	exit("Error obtaining Dorm's directory path. Please ensure that /dorm/dorm_config.php 
		is included in your script. If it is, please open and correct the DORM_PATH variable.");
}

/* Include the internal configuration */
require_once(DORM_PATH . '/internal/dorm_config.php');

/* Helper methods for convenience */
require_once(DORM_PATH . '/internal/dorm_helpers.php');

/* Include the AutoLoader class */
require_once(DORM_PATH . '/internal/DormAutoLoader.php');

/* Start the AutoLoader */
$loader = new DormAutoLoader();

$loader->registerNamespaces($namespacePaths);

/**
 * Main Loader class which initializes and bootstraps Dorm.
 *
 */
class DormLoader {

	/**
	 * Initializes and bootstraps the Dorm object.
	 * @return Instance of the global Dorm object.
	 */
	public function load() {
		global $dorm;
		global $dorm_config;

		if($dorm != null) {
			throw new DormException("Dorm is already loaded.");
		}

		//global Dorm object which will encapsulate all underlying plugins and libraries
		$dorm = new Models\Dorm();
		
		//store system configuration
		$dorm->config = $dorm_config;

		/*
		 * First, setup basic faculties that don't require a Dorm installation
		 * and then check for the Dorm installation 
		 */

		$dorm->loader = $this;

		/* Wrapper for our dependency injector container */
		$dorm->di = new DormInjector();

		//Setup the router
		$dorm->router = new DormRouter();

		//Setup basic input retrieval
		$dorm->input = new DormInput();

		//Setup basic response handlers
		$dorm->response = new DormResponseHandler();

		//Setup basic error handlers
		$dorm->error = new DormErrorHandler();

		/* Store the current request details */
		$uriBase = explode('?', $_SERVER['REQUEST_URI'], 2);
		$dorm->request = new Models\DormRequest($uriBase[0], $_SERVER["REQUEST_METHOD"] ?: 'GET');

		/* Basic setup is finished, now move on to building and request fulfillment */

		//First, check if Dorm is installed
		if(!dorm_is_installed() && $dorm_config['use_dorm_cms'] == true) {
			if($request->uri != '/dorm/install')
				dorm_redirect('/dorm/install');
			else
				$dorm->router->routeRequest($dorm->request);

			return;
		} else {
			if($dorm->request->uri != '/dorm/install' && $dorm_config['use_dorm_cms'] == true)
				dorm_redirect('/dorm');
		}

		/* Basic setup is finished, now move on to building and request fulfillment */
		self::_buildDorm();

		$dorm->router->routeRequest($dorm->request);

		return $dorm;
	}

	/**
	 * Populates the global Dorm object with necessary libraries.
	 * TODO: Pull this object as a singleton from a Cache.
	 */
	private function _buildDorm() {
		global $dorm;
		global $dorm_config;
		global $dorm_routes;

		$dorm->cache = new DormCache();

		// Init and load any requested autoload plugins
		foreach($dorm_config['autoload_plugins'] as $plugin) {
			$this->loadPlugin($plugin);
		}

		//Dorm is now built.
		return $dorm;
	}

	public function loadPlugin($plugin) {
		global $dorm;
		
		$pluginPath = DORM_PATH . '/plugins/' . $plugin;

		if(!file_exists($pluginPath)) {
			die('Could not find plugin to autoload in the /dorm/plugins directory: ' . $pluginPath);
		} else {
			if( isset($dorm->{$plugin}) ) {
				//The plugin is conflicting with another library.
				die("Error: Object '" . $plugin . "' is already defined on the Dorm object. 
					The plugin should use another name. You can correct this by changing the 
					plugin's folder name at: " . $pluginPath . ", the plugin's class name
					defined in the plugin.php file, and also the autoload name defined in: " . 
					DORM_PATH . "/dorm_config.php");
			} else {
				//Okay, include the plugin's main plugin.php file
				$path = DORM_PATH . '/plugins/' . $plugin . '/plugin.php';
				require_once($path);

				//Initialize the plugin object, first trying without a namespace:
				try {
					$po = new $plugin();
				} catch(Exception $ex) {
					$pluginName = "\Dorm\Plugins" . $plugin;
					$po = new $pluginName();
				}

				//If the plugin defines a specific key, we will use this to reference the plugin
				//on the Dorm object. Otherwise, we just reference the plugin usings its name.
				if($po->plugin_key != '') {
					if( isset($dorm->{$po->plugin_key}) ) {
						die("Error: The plugin '" . $plugin . "' defines a plugin_key which is
							already in use: '" . $po->plugin_key . ". Please change the
							plugin_key name in the plugin.php file located at: " . $pluginPath);
					} else {
						$dorm->{$po->plugin_key} = $po;
					}
				} else {
					//Reference the plugin using its name
					$dorm->{$plugin} = $po;
				}

				//Tell the plugin to initialize itself
				$po->on_load();
			}
		}
	}
}

?>
