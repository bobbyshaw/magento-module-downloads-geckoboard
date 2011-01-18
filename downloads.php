<?php

/*
 * Retrieve download count from magentocommerce.com for a module
 */

require_once('response.class.php');

// Check that we have the id of the module
if(isset($_GET['id'])) {

    $module_id = $_GET['id'];

    // Get the page
    $html = makeRequest("http://www.magentocommerce.com/extension/specs/$module_id");

    // Find count and convert to geckoboard array
    $downloads = findDownloads($html);
    $gecko = convertToGecko($downloads);

    // Convert to xml/json as appropriate
    $format = isset($_POST['format']) ? (int)$_POST['format'] : 0;
    $format = ($format == 1) ? 'xml' : 'json';
    $response_obj = new Response();
    $response_obj->setFormat($format);

    $response = $response_obj->getResponse($gecko);
    echo $response;  
}

/*
 * Curl a given url and return a string
 */
function makeRequest($url) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL,$url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($c);
    curl_close($c);
   
    return $result;
}

/*
 * Takes a string of html of a module page and finds download count
 */
function findDownloads($html) {
    $matches = array();
    preg_match("'<td class\=\"titl\">Downloads</td>[^<]*<td>([^<]*)'si", $html, $matches);

    return($matches[1]);

}

/*
 * Convert to array of values within an array of items
 */
function convertToGecko($downloads) {
    return array("item" => array("value" => $downloads));
}
