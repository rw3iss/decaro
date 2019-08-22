<?php


/* FOR DEBUGGING ONLY */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/New_York');

// Includes the public site configuration
require_once(DORM_PATH . '/dorm_config.php');

// Defines which characters are allowed on URL requests to the system
$dorm_config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-#';

$dorm_config['dorm_directory'] = 'lib/Dorm';

// uri shortcut for frontend assets
define('DORM_URI', $_SERVER['HTTP_HOST'] . '/' . $dorm_config['dorm_directory']);

/**
 * namespacePaths - defines the internal namespaces used by Dorm. 
 * Do not modify this.
 *
 * @var array
 */
$namespacePaths = array(
	'Dorm' 			=> array(DORM_PATH . '/internal', DORM_MODEL_PATH, DORM_PATH . '/internal/models'),
	'Dorm\Models' 	=> DORM_PATH . '/internal/models',
	'Dorm\Controllers' 	=> DORM_CONTROLLER_PATH,
	'Dorm\Plugins' 	=> DORM_PATH . '/internal/plugins',
	'Dorm\Data' 	=> DORM_PATH . '/internal/database',
	'Dorm\Util' 	=> DORM_PATH . '/internal/Util',

	'Orno\Di' 	=> DORM_PATH . '/internal/thirdparty/OrnoDI',
	
	/* client specific */
	'DeCaro\Data' 	=> DORM_PATH . '/../Domain/Data',
	'DeCaro\Repositories' 	=> DORM_PATH . '/../Domain/Repositories',
	'DeCaro\Models' 	=> DORM_MODEL_PATH,
);

?>