<?php

if (!$link = mysql_connect('localhost', 'root', '')) {
    echo 'Could not connect to mysql';
    exit;
}

if (!mysql_select_db('decaroaccess', $link)) {
    echo 'Could not select database';
    exit;
}

$sql    = 'SELECT * FROM CUSTOMER order by name asc';
$result = mysql_query($sql, $link);

if (!$result) {
    echo "DB Error, could not query the database\n";
    echo 'MySQL Error: ' . mysql_error();
    exit;
}


$clients = array();

while ($row = mysql_fetch_assoc($result)) {
    //echo print_r($row);

    $originalName = $row['NAME'];
    $address1 = $row['ADDRESS1'];
    $address2 = $row['ADDRESS2'];
    $city = $row['CITY'];
    $state = $row['STATE'];
    $zip = $row['ZIP'];
    $contact = $row['CONTACT'];

    //"normalize" the name:
    $nameNormalized = "";
    $parPos = strrpos($originalName, '(');
   	$parName = "";
    if($parPos) {
    	$nameNormalized = strtolower(trim(substr($originalName, 0, $parPos)));
    	$parName = trim(substr($originalName, $parPos, strlen($originalName)));
    } else {
    	$nameNormalized = strtolower(trim($originalName));
    }

    //this is the parent client name which we'll create an individual client entry for
    $clientName = strtoupper($nameNormalized);

    //echo "PAR: " . $nameNormalized . ' --> ' . $parName . '<br/>';

    $clientStations = array();
    if(array_key_exists($clientName, $clients)) {
    	$clientStations = $clients[$clientName];
    }

    //got our parent client array, now look for existing stations
    $stationName = strtoupper($nameNormalized) . ' ' . strtoupper($parName);
    if(array_key_exists($stationName, $clientStations)) {
    	//create a unique station name
    	$stationName = getUniqueStationName($clientStations, $stationName);
    }

    //see if client exists or create a new entry to obtain a client id
    $client = findExistingClient($clientName);
    if(!$client) {
    	$client = createNewClient($clientName);
    }

    $stationData = array('client_id' => $client['id'], 'name' => $stationName, 
    	'address' => $address1, 'address2' => '', 'city' => $city, 'state' => $state, 'zipcode' => $zip,
    	'phone' => $address2, 'contact' => $contact);

    $clientStations[$stationName] = $stationData; 

    $clients[$clientName] = $clientStations;
    //grab name, remove parenthesis, then check for existing name in array.
    //if existing name, check entries. if any entry has the same exact name, make a new entry wi
}

insertClientStations($clients);

function insertClientStations($clients) {
	global $link;
	$i = 0;
	foreach($clients as $name => $stations) {
		//echo $name . '<br/>';
		foreach($stations as $cs) {
			$i++;
			if($cs['phone'] == null || $cs['phone'] == '')
				$cs['phone'] = $cs['contact'];
			$sql = sprintf("INSERT INTO client_stations (client_id, name, address, address2, city, state, zipcode, phone_number, contact) " .
				"VALUES (%s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
					$cs['client_id'], mysql_real_escape_string($cs['name']), mysql_real_escape_string($cs['address']),
					mysql_real_escape_string($cs['address2']), mysql_real_escape_string($cs['city']),
					mysql_real_escape_string($cs['state']), $cs['zipcode'], $cs['phone'], mysql_real_escape_string($cs['contact']));
			
			$result = mysql_query($sql, $link);
		}
	}
}

function findExistingClient($name) {
	global $link;
	$sql    = "SELECT * FROM clients where name='". mysql_real_escape_string($name) . "'";
	$result = mysql_query($sql, $link);

	if (!$result) {
		return null;
	}

	$row = mysql_fetch_assoc($result);
	//echo "GOT CLIENT: " . print_r($row)."<br/>";
	return $row;
}

function createNewClient($name) {
	global $link;
	$sql    = "INSERT INTO clients (name) VALUES ('". mysql_real_escape_string($name) . "')";
	$result = mysql_query($sql, $link);

	$client = findExistingClient($name);
	if($client['id'] == null || $client['id'] == '') {
	    echo 'MySQL Error: ' . mysql_error() .'<br/>';
	}
	return $client;
}

function getUniqueStationName($stations, $name) {
	$suffixes = array('B', 'C', 'D', 'E', 'F', 'G', 'H');
	$suffix = 0;

	while(true) {
		$tryName = $name . ' (' . $suffixes[$suffix] . ')';
		if(array_key_exists($tryName, $stations)) {
			$suffix++;
		} else {
			return $tryName;
		}
	}
}

mysql_free_result($result);
?>
