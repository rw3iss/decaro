<?php
require_once('config.php');
require_once('SqlDatabase.php');

class Migrations {
	private $migrationsDir = "migrations";
	private $migrationsTable = "migrations";
	private $db;
	public $isInstalled = false;
	public $migrationsData = null;

	function Migrations() {
		global $config;

		$this->config = new DbConfiguration($config);
		$this->db = new SqlDatabase($this->config);

		$this->isInstalled = $this->is_installed();
		$this->migrationsData = $this->get_migrations_data();
	}

	//are we setup yet?
	public function is_installed() {
		$is_installed = false;

		$this->db->connect();
 
		$sql = sprintf("SELECT COUNT(*) AS count
        		FROM information_schema.tables 
        		WHERE table_schema = '%s'
        		AND table_name = '%s'", $this->config->dbname, $this->migrationsTable);

        $result = $this->db->fetchArray($this->db->query($sql));

        if(isset($result['count'])) {
        	if($result['count'] == 1)
        		$is_installed = true;
        }

		//$this->db->close();

		return $is_installed;
	}

	function install() {
		$this->db->connect();

		$sql = sprintf("
				CREATE TABLE IF NOT EXISTS %s (
					ran_migrations text,
					last_migration_date TIMESTAMP
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
			", $this->migrationsTable);

		$this->db->query($sql);

		$sql = sprintf("INSERT INTO %s (ran_migrations, last_migration_date) VALUES ('%s', '%s')",
					$this->migrationsTable,
					implode(',',$this->migrationsData->ran_migrations),
					date('Y-m-d H:i:s'));

		$this->db->query($sql);

		$this->isInstalled = true;

		//$this->db->close();		
	}

	function uninstall() {		
		if(!$this->isInstalled)
			return null;

		$sql = sprintf("DROP TABLE %s", $this->migrationsTable);

		$this->db->connect();
		$this->db->query($sql);
		//$this->db->close();		

		$this->isInstalled = false;
	}

	public function get_migrations_data() {
		$data = new MigrationsData();

		if(!$this->isInstalled)
			return $data;

		$this->db->connect();

		$sql = sprintf("SELECT * from %s", $this->migrationsTable);
        $result = $this->db->fetchQueryToArray($sql);

        if($result != null) {
        	$first = is_array($result) ? $result[0] : $result;

        	$data->ran_migrations = array_filter(explode(',', trim($first['ran_migrations'])));
        	$data->last_migration_date = "";
        	if(!(int)$first['last_migration_date'])
        		$data->last_migration_date = "";
        	else
        		$data->last_migration_date = $first['last_migration_date'];
        } else {
        }

        //$this->db->close();

        return $data;
	}

	public function get_all_migrations() {
		$migrations = array();

		if ($handle = opendir($this->migrationsDir)) {
		    while (false !== ($file = readdir($handle))) {
		    	if($file == '.' || $file == '..' || 
		    		!preg_match('/\Q' . '.sql' . '\E$/', $file))
		    		continue;

		        array_push($migrations, $file);
		    }
		    closedir($handle);
		}

		$indexedMigrations = array();
        foreach($migrations as $m) {
        	try {
        		$parts = preg_split('~_~', $m, 2);
	        	$indexedMigrations[$parts[0]] = $m;
	        }
	        catch(Exception $ex) {
	        	echo '<br/>Error: migration \'' . $m .'\' filename not formatted properly. Please use the format \'{ID}_name.sql\'';
	        }
        }

        ksort($indexedMigrations);

		return $indexedMigrations;
	}

	public function get_new_migrations() {	
		$newMigrations = array();	

		$this->db->connect();

		if(!$this->isInstalled)
			return $this->get_all_migrations();

		//find which migrations have already been run
		$migrations = $this->get_all_migrations();
		$ranMigrations = array();

    	//find already run migrations
		$sql = sprintf("SELECT * from %s", $this->migrationsTable);
        $result = $this->db->fetchArray($this->db->query($sql));
        if($result != null) {
        	$ranMigrations = explode(',', $result['ran_migrations']);
        } else {
        }

        //$this->db->close();

        $newMigrations = array_diff($migrations, $ranMigrations);

        //now sort the newMigrations by name
        //first we must break apart the index numbers:
        $indexedMigrations = array();
        foreach($newMigrations as $m) {
        	
        }

        ksort($newMigrations);

		return $newMigrations;
	}

	public function run_new_migrations($migrationCallback = null) {
		if(!$this->isInstalled)
			return null;

		$this->db->connect();

		//iterate through each new migration
		$newMigrations = $this->get_new_migrations();
		$ranMigrations = array();
		$lastMigrationDate = $this->migrationsData->last_migration_date;

		//open each new migration file and run it
		foreach($newMigrations as $i => $m) {
			try {
				$path = getcwd() . DIRECTORY_SEPARATOR . $this->migrationsDir . DIRECTORY_SEPARATOR . $m;

				$sql = file_get_contents($path);

				//separate out the queries:
				$queries = explode(';', $sql);

				foreach($queries as $q) {
					$q = trim($q);
					if($q == '')
						continue;

					$success = $this->db->query($q.';');

					if(!$success) {
						$ex = $this->db->getError();

						if($migrationCallback != null) {
							$migrationCallback($m, $ex);
						}

						continue;
					}
				}

				array_push($ranMigrations, $m);

				$lastMigrationDate = gmdate("Y-m-d H:i:s");
			}
			catch(Exception $ex) {
				echo 'Error running migrations! => ' . print_r($ex);
				//$this->db->close();
			}

			if($migrationCallback != null) {
				$migrationCallback($m);
			}
		}

		//$this->db->close();

		//save run migrations
		$addNewMigrations = array_merge($this->migrationsData->ran_migrations, $ranMigrations);
		
		$this->migrationsData->ran_migrations = $addNewMigrations;
		$this->migrationsData->last_migration_date = $lastMigrationDate;

		$this->save_migrations_data($this->migrationsData);
	}

	//just checks that all filenames are valid
	function validate_migrations() {
		$is_valid = true;
		$migrations = $this->get_all_migrations();

		$indexedMigrations = array();
        foreach($migrations as $m) {
        	try {
        		$parts = preg_split('~_~', $m, 2);
        		if(!is_numeric($parts[0]))
        			throw new Exception();

	        	$indexedMigrations[$parts[0]] = $m;
	        }
	        catch(Exception $ex) {
	        	$is_valid = false;
	        	echo '<br/>Error: migration \'' . $m .'\' filename not formatted properly. Please use the format \'{ID}_name.sql\'';
	        }
        }

        return $is_valid;
	}

	function save_migrations_data($md) {
		$sql = sprintf("UPDATE %s SET ran_migrations='%s', last_migration_date='%s'", 
			$this->migrationsTable,
			implode(',', $md->ran_migrations),
			$md->last_migration_date);

		$this->db->connect();
		$this->db->query($sql);
		//$this->db->close();
	}

	//cleans the database entry so that it only contains entries of files 
	//which currently exist in the migrations directory (and have already been ran)
	function clean_migrations() {
		$migrations = $this->get_all_migrations();
		$migrationsData = $this->get_migrations_data();

		$cleanedMigrations = array_intersect($migrationsData->ran_migrations, $migrations);
		$migrationsData->ran_migrations = $cleanedMigrations;

		$this->save_migrations_data($migrationsData);
		$this->migrationsData = $this->get_migrations_data();
	}
}


//encapsulates the migration database table information
class MigrationsData {
	public $ran_migrations = array();
	public $last_migration_date = '';
}



$migrations = new Migrations();


?>