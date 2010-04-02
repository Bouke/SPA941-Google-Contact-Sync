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

/**
 * writing to spa941
 * from http://www.dumaisnet.ca/index.php?p=spa941app
 */
$fields = Array("43311","43503","43439","44655","44591","44783","44719","44911","44847","45039",
"44975","44143","44079","44271","44207","44399","44335","44527","44463","37487","37423","37615",
"37551","37743","37679","37871","37807","36975","36911","37103","37039","37231","37167","37359",
"37295","38511","38447","38639","38575","38767","38703","38895","38831","37999","37935","38127",
"38063","38255","38191","38383","38319","39535","39471","39663","39599","39791","39727","39919",
"39855","39023","38959","39151","39087","39279","39215","39407","39343","40559","40495","40687",
"40623","40815","40751","40943","40879","40047","39983","40175","40111","40303","40239","40431",
"40367","33391","33327","33519","33455","33647","33583","33775","33711","32879","32815","33007",
"32943","33135","33071","33263","33199","34415");

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
