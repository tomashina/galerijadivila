<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


	  

$dbHost = getenv('OPENCART_DB_HOST') ?: 'localhost';
$dbUser = getenv('OPENCART_DB_USERNAME') ?: '';
$dbPassword = getenv('OPENCART_DB_PASSWORD') ?: '';
$dbName = getenv('OPENCART_DB_DATABASE') ?: '';
$dbPort = (int) (getenv('OPENCART_DB_PORT') ?: 3306);

$link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);

	if (!$link) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}

	

		//update product table
	$query = "
	UPDATE oc_product t1 
INNER JOIN oc_manufacturer t2 
ON t1.manufacturer_id = t2.manufacturer_id
SET t1.jan = t2.name WHERE t2.abeceda = '' ";

$result = mysqli_query($link, $query) or die ("Error in query: $query. ".mysqli_error($link));



	$query = "
	UPDATE oc_product t1 
INNER JOIN oc_manufacturer t2 
ON t1.manufacturer_id = t2.manufacturer_id
SET t1.jan = t2.abeceda WHERE t2.abeceda != '' ";


	$result = mysqli_query($link, $query) or die ("Error in query: $query. ".mysqli_error($link));
	
	

echo 'done';
	

		//close connection
mysqli_close($link);
