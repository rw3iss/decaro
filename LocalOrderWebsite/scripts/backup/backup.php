<?php

// This script will backup all of the code to a code folder, and also the mysql dump to a data folder,
// according to the configuration in config.php.

// Todo: add date-time formats to filenames

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "config.php";

global $config;

 // This will be set to whichever directory we're writing to (ie. primary backup location or 
// secondary depending upon whether the first is writable or not, ie. it exists and isn't full)
$backupDestination = '';

// Write to the local log file (if enabled). Will create a new log file each day.
function _log($msg) {
	global $config;
	if ( $config['logEnabled'] ) {
		file_put_contents( $config['logLocation'] . $config['logFilename'] . '_' . date('m-d-Y') . '.log', "\r\n" . $msg, FILE_APPEND );
	}

	echo $msg . PHP_EOL;
}

function ensureDirectories() { 
	global $config;
	global $backupDestination;

	if ( $config['logEnabled'] ) {
		// Create log file directory
		if ( !file_exists( $config['logLocation'] ) ) {
			mkdir( $config['logLocation'], 0777, true );
		}
	}

	_log( "\r\n///////////////////////////////////////////////////////////////////////////////" );

	// First try to create the backup destination, then check that it was created:
	if ( !file_exists( $config['backupDestination'] ) ) {
		mkdir( $config['backupDestination'], 0777, true ); 
	}

	// Determine which backup destination we will use:
	if ( is_writable( $config['backupDestination'] ) ) {
		$backupDestination = $config['backupDestination'];
		_log( 'Using primary backup destination: ' . $config['backupDestination'] );
	} else {
		_log( 'WARNING: Primary backup destination is not writable! => ' . $config['backupDestination'] );
		_log( 'Trying secondary backup location: ' . $config['secondaryBackupDestination'] );
			
		// Try to create the secondary backup destination, then check that it was created:
		if ( !file_exists( $config['secondaryBackupDestination'] ) ) {
			mkdir( $config['secondaryBackupDestination'], 0777, true ); 
		}

		if ( is_writable($config['secondaryBackupDestination']) ) {
			_log( 'OK. Using secondary backup destination: ' . $config['secondaryBackupDestination'] );
			$backupDestination = $config['secondaryBackupDestination'];
		} else {
			_log( 'FATAL! Secondary backup destination is not writable either! => ' . $config['secondaryBackupDestination'] );
			_log( 'EXITING!' );
			exit();
		}
	}

	// Create code backup directory
	if ( !file_exists( $backupDestination . $config['codeDirectory'] ) ) {
		// . $config['codeFileName'] . date( $config['filenameDateFormat'] ) ) ) {
		//mkdir( $backupDestination . $config['codeDirectory'] . $config['codeFileName'] . date($config['filenameDateFormat']), 0777, true );
		mkdir( $backupDestination . $config['codeDirectory'], 0777, true );
	}

	// Create database backup directory
	if ( !file_exists( $backupDestination . $config['dataDirectory'] ) ) {
		// . $config['databaseName'] . date( $config['filenameDateFormat'] ) ) ) {
		//mkdir( $backupDestination . $config['dataDirectory'] . $config['dataFileName'] . date( $config['filenameDateFormat'] ), 0777, true );
		mkdir( $backupDestination . $config['dataDirectory'], 0777, true );
	}
}

function doCodeBackup() {
	global $config;
	global $backupDestination;

	$codeDirectory = $config['codeFileName'] . date( $config['filenameDateFormat'] );
	$codeBackupDestination = $backupDestination . $config['codeDirectory'] . $codeDirectory;

	if ( !file_exists( $config['codeBackupSource'] ) ) {
		_log( 'Code source directory does not exist (' . $config['codeBackupSource' ] . '). Exiting code backup.');
		return;
	}

	// Make the code backup directory:
	mkdir( $codeBackupDestination, 0777, true );

	_log( 'Backing up code directory: ' . $config['codeBackupSource'] );

	_recursive_copy_directory( $config['codeBackupSource'], $codeBackupDestination );

	// tar the code directory
	exec( 'tar zcf ' . $codeBackupDestination . '.tar.gz ' . $codeBackupDestination );
	// remove the original directory
	exec ('rm -rf ' . $codeBackupDestination);

	_log( 'Code backup complete. (' . $codeBackupDestination . "/)\r\n" );
}

function doDatabaseBackup() {
	global $config;
	global $backupDestination;

	$dbFile = $config['databaseName'] . date( $config['filenameDateFormat'] ) . '.sql';
	$dataBackupDestination = $backupDestination . $config['dataDirectory'];

	_log( 'Backing up database: ' . $config['databaseDumpCommand'] . ' > ' . $dataBackupDestination . $dbFile );

	exec( $config['databaseDumpCommand'] . ' ' . $config['databaseName'] . ' > ' . $dataBackupDestination . $dbFile );

	// tar the data directory
	exec( 'tar zcf ' . $dataBackupDestination . $dbFile . '.tar.gz ' . $dataBackupDestination . $dbFile );
	// remove the original directory
	exec ('rm -rf ' . $dataBackupDestination . $dbFile);

	_log( 'Database backup complete. (' . $dataBackupDestination . $dbFile . ")\r\n" );
}

function _recursive_copy_directory($src, $dst) { 
	global $config;
    $dir = opendir($src); 
    @mkdir($dst); 

    while ( false !== ( $file = readdir($dir) ) ) { 
        if ( ( $file != '.' ) && ( $file != '..' ) ) {
        	// Ignore backing up .git folder if desired
        	if ( $file == '.git' && $config['ignoreGitFolder'] ) {
        		_log('Ignoring .git folder.');
        		continue;
        	}

            if ( is_dir( $src . '/' . $file ) ) { 
                _recursive_copy_directory( $src . '/' . $file, $dst . '/' . $file ); 
            } 
            else { 
                copy( $src . '/' . $file, $dst . '/' . $file ); 
            } 
        } 
    } 

    closedir( $dir ); 
} 

///////////////////////////////////////////////////////////////////////////////

ensureDirectories();

_log( "-------------------------------------------------------------------------------\r\n" .
	"Backup script started at " . date('m-d-Y h:ia') );

doCodeBackup();

doDatabaseBackup();

_log("Backup finished.\r\n");