<?php
namespace Dorm;

/* Global Dorm path, change this if you want */
define('DORM_PATH', realpath(dirname(__FILE__)));
define('DORM', eval('global $dorm;'));

/* Change this if you want to store your views somewhere outsie of the Dorm folder structure */
define('DORM_VIEW_PATH', DORM_PATH . '/../../views/');
//define('DORM_VIEW_PATH', DORM_PATH . '/views/');

/* Change this if you want to store your controllers somewhere outside of the Dorm folder structure */
define('DORM_CONTROLLER_PATH', DORM_PATH . '/../../controllers/');
//define('DORM_CONTROLLER_PATH', DORM_PATH . '/controllers/');

/* Change this if you want to store your models somewhere outside of the Dorm folder structure */
define('DORM_MODEL_PATH', DORM_PATH . '/../../models/');
//define('DORM_MODEL_PATH', DORM_PATH . '/models/');

/* Include DormLoader to load Dorm */
require_once(DORM_PATH . '/internal/DormLoader.php');

/* Load Dorm */
$loader = new DormLoader();
$loader->load();

?>