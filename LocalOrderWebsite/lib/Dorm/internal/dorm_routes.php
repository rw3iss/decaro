<?php
/**
 * /dorm/lib/core/dorm_routes.php - Defined static routes used internally by the dorm system.
 *
 */

/* Include the publically defined static routes */
require_once(DORM_PATH . '/dorm_routes.php');

global $dorm_routes;

/* Create static routes for specific dorm backend requests */
$dorm_routes['/dorm'] = '/internal/controllers/dormadmincontroller/index';
$dorm_routes['/api'] = '/internal/controllers/dormrestcontroller/request';

$dorm_routes['/dorm/install'] = '/internal/controllers/dormadmincontroller/install';

/* Tests page */
$dorm_routes['/dorm/tests'] = '/internal/controllers/dormadmincontroller/runTests';

?>
