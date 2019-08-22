<?php
/* DormInjector - used as a wrapper for the OrnoDI container */

namespace Dorm;

class DormInjector {
	private $_container;

	/* initializes the Orno Dependency Injector and sets up the container paths */
	function __construct() {
		global $dorm;

		/* Orno Dependency Injector container */
		$container = new \Orno\Di\Container;

		/* DatabaseAdapter */
		$container->add('DatabaseAdapter', function() use ($dorm) {
			$connString = sprintf("mysql:host=%s;dbname=%s", $dorm->config['db_host'], $dorm->config['db_name']);
		    return new Data\PdoDatabaseAdapter($connString, $dorm->config['db_username'], $dorm->config['db_password']);
		});

		/* IdentityMap */
		$container->add('IdentityMap', function() {
			return new Data\IdentityMap();
		});

		/* Users */
		$container->add('UserMapper', function() use ($container) {
			$db = $container->get('DatabaseAdapter');
			$identityMap = $container->get('IdentityMap');
		    return new \DeCaro\Data\UserMapper($db, $identityMap);
		});

		$container->add('UserRepository', function() use ($container) {
			$mapper = $container->get('UserMapper');
		    return new \DeCaro\Repositories\UserRepository($mapper);
		});

		/* Clients */
		$container->add('ClientMapper', function() use ($container) {
			$db = $container->get('DatabaseAdapter');
			$identityMap = $container->get('IdentityMap');
		    return new \DeCaro\Data\ClientMapper($db, $identityMap);
		});

		$container->add('ClientStationMapper', function() use ($container) {
			$db = $container->get('DatabaseAdapter');
			$identityMap = $container->get('IdentityMap');
		    return new \DeCaro\Data\ClientStationMapper($db, $identityMap);
		});

		$container->add('ClientRepository', function() use ($container) {
			$mapper = $container->get('ClientMapper');
			$stationMapper = $container->get('ClientStationMapper');
		    return new \DeCaro\Repositories\ClientRepository($mapper, $stationMapper);
		});

		$container->add('ClientStationRepository', function() use ($container) {
			$mapper = $container->get('ClientStationMapper');
		    return new \DeCaro\Repositories\ClientStationRepository($mapper);
		});

		/* Orders */
		$container->add('OrderMapper', function() use ($container) {
			$db = $container->get('DatabaseAdapter');
			$identityMap = $container->get('IdentityMap');
		    return new \DeCaro\Data\OrderMapper($db, $identityMap);
		});

		$container->add('OrderRepository', function() use ($container) {
			$mapper = $container->get('OrderMapper');
			$clientMapper = $container->get('ClientMapper');
		    return new \DeCaro\Repositories\OrderRepository($mapper, $clientMapper);
		});

		/* Invoices */
		$container->add('InvoiceMapper', function() use ($container) {
			$db = $container->get('DatabaseAdapter');
			$identityMap = $container->get('IdentityMap');
		    return new \DeCaro\Data\InvoiceMapper($db, $identityMap);
		});

		$container->add('InvoiceRepository', function() use ($container) {
			$mapper = $container->get('InvoiceMapper');
			$clients = $container->get('ClientRepository');
		    return new \DeCaro\Repositories\InvoiceRepository($mapper, $clients);
		});

		/* Settings */
		$container->add('SettingMapper', function() use ($container) {
			$db = $container->get('DatabaseAdapter');
			$identityMap = $container->get('IdentityMap');
		    return new \DeCaro\Data\SettingMapper($db, $identityMap);
		});

		$container->add('SettingRepository', function() use ($container) {
			$mapper = $container->get('SettingMapper');
		    return new \DeCaro\Repositories\SettingRepository($mapper);
		});

		$this->_container = $container;
	}

	public function get($alias) {
		return $this->_container->get($alias);
	}
}

?>
