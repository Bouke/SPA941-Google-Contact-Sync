<?php

/**
 *
 * File is under copyright of Bouke Haarsma and can
 * only be used with written permission of the author.
 *
 * @author Bouke Haarsma <bouke@webatoom.nl>
 * @copyright 2010, Bouke Haarsma
 */

require("config.php");

$client = Zend_Gdata_ClientLogin::getHttpClient($email, $password, "cp");
$gdata = new Zend_Gdata($client);

$query = new Zend_Gdata_Query("http://www.google.com/m8/feeds/contacts/{$email}/full");
$query->setParam("group", $group);
$feed = $gdata->getFeed($query);

$xml = new SimpleXMLElement($feed->getXML(), null, null, "http://www.w3.org/2005/Atom");
$xml->registerXPathNamespace("default", "http://schemas.google.com/g/2005");

$entries = $xml->xpath("//atom:entry/default:phoneNumber[@rel != 'http://schemas.google.com/g/2005#work_fax']");
$phonebook = array();
foreach ($entries as $phone) {
	$name = $phone->xpath("../atom:title");
	$phone = preg_replace("/([^\+^0-9])/", "", $phone[0]);
	$phone = preg_replace("/^(\+31|0031)/", "0", $phone);
	$phonebook[] = sprintf("n=%s;p=%s", $name[0], $phone);
}

// field names vary between spa models
// get all field names from directory
// (thanks to Lennart van der Hoorn)
$spaDir = file_get_contents('http://{$ip}/pdir.htm');
preg_match_all('/name="([0-9]{5})"/', $spaDir, $fields);

$postdata = array();
foreach($fields as $key => $entry) {
	$postdata[$entry] = isset($phonebook[$key]) ? $phonebook[$key] : "";
}


file_get_contents("http://{$ip}/pdir.spa", false, stream_context_create(array(
   "http" => array( 
      "method"  => "POST", 
      "content" => http_build_query($postdata),
      "header"  => "Content-type: application/x-www-form-urlencoded\r\n"), 
)));
