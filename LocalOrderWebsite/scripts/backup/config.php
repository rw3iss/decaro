<?php

// Note: No directories should end with a slash!

$config = array(

	// Code source folder to backup:
	'codeBackupSource' 				=> '/Users/rw3iss/Sites/decaro/LocalOrderWebsite/',

	// Where to backup the code and database files:
	'backupDestination'				=> '/Volumes/USB 8GB/backup/decaro',

	// In case the first destination is unreachable or full
	'secondaryBackupDestination' 	=> '/tmp/backup/decaro',

	// Filename of the backup:
	'databaseName' 					=> 'decaro',
	// Command to execute for the backup:
	'databaseDumpCommand'			=> 'mysqldump -uroot -proot',

	// Where to log the output:
	'logLocation' 					=> '/Users/rw3iss/Sites/decaro/LocalOrderWebsite/scripts/backup/log/',

	// Suffix used for backed up filenames:
	'filenameDateFormat' 			=> 'm-d-Y_hia',
	'codeDirectory'					=> '/code/',
	'codeFileName'					=> 'LocalOrderWebsite_',
	'dataDirectory'					=> '/data/',

	// Won't copy .git folders if true:
	'ignoreGitFolder' 				=> true,

	// Log all output if enabled:
	'logEnabled'					=> true,
	// Filename for the log:
	'logFilename' 					=> 'output'
	
);