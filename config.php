<?php

/**
 *
 * File is under copyright of Bouke Haarsma and can
 * only be used with written permission of the author.
 *
 * @author Bouke Haarsma <bouke@webatoom.nl>
 * @copyright 2010, Bouke Haarsma
 */

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__)."/lib/vendor");
require("lib/vendor/Zend/Loader/Autoloader.php");
Zend_Loader_Autoloader::getInstance();


// Configure/get e-mail address
if(!file_exists(".email")) {
   echo "Please enter your e-mail address: ";
   file_put_contents(".email", getinput());
}
$email = file_get_contents(".email");


// Configure/get password; encrypted stored on disk
if(!file_exists(".password")) {
	echo "Please enter your password: ";
	file_put_contents(".password", mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $_SERVER["COMPUTERNAME"], getinput(), MCRYPT_MODE_ECB));
}
$password = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $_SERVER["COMPUTERNAME"], file_get_contents(".password"), MCRYPT_MODE_ECB);


// Test authentication
try {
   $client = Zend_Gdata_ClientLogin::getHttpClient($email, $password, "cp");
} catch(Exception $e) {
	@unlink(".password", ".email");
	die("Invalid username/password");
}


// Configure/get IP Address of SPA941
if(!file_exists(".ip")) {
   echo "Please enter your SPA941 IP Address: ";
   file_put_contents(".ip", getinput());
}
$ip = file_get_contents(".ip");


// Configure/get Group name
if(!file_exists(".group")) {
   $gdata = new Zend_Gdata($client);
   
	$query = new Zend_Gdata_Query("http://www.google.com/m8/feeds/groups/{$email}/full");
   $feed = $gdata->getFeed($query);

   $xml = new SimpleXMLElement($feed->getXML(), null, null, "http://www.w3.org/2005/Atom");

   $entries = $xml->xpath("//atom:entry/atom:title");
   foreach($entries as $key => $entry) {
   	printf("%d: %s\r\n", $key+1, $entry);
   }
   
	echo "Please select group: ";
	$group = $xml->xpath(sprintf("//atom:entry[%s]/atom:id", getinput()));
	$group = $group[0][0];
	
	file_put_contents(".group", $group);
}
$group = file_get_contents(".group");



function getinput(){
   $stdin = fopen("php://stdin", 'r');
   $input = fgets($stdin, 1024);
   $input = trim($input);
   fclose($stdin);
   return $input;
}