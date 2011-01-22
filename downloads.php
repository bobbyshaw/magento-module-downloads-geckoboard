<?php

/*
 * Retrieve download count from magentocommerce.com for a module
 */

// Include geckoboard response class
require_once('response.class.php');

// Set date format to be nice
date_default_timezone_set("UTC"); 

// Check that we have the id of the module
if(isset($_GET['id'])) {

    $module_id = $_GET['id'];

    // Get the page
    $html = makeRequest("http://www.magentocommerce.com/extension/specs/$module_id");

    // Find count and convert to geckoboard array
    $downloads = findDownloads($html);

    // write to file for use in a week
    saveDownloads($module_id, $downloads);

    // Delete old data
    cleanOldDownloads($module_id);

    // Get data from 7 days ago.
    $priorDownloads = getLastWeeksDownloads($module_id);

    // Delete data from 8 days ago
    cleanOldDownloads($module_id);

    // Put in array of values and items.
    $gecko = convertToGecko($downloads, $priorDownloads);

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
function convertToGecko($downloads, $priorDownloads) {
    $gecko = array("item" => array( array("value" => $downloads)));

    if($priorDownloads) {
        $gecko['item'][] = array("value" => $priorDownloads);
    }

    return $gecko;
}

/*
 * Save today's downloads, ready to be used in a week
 */
function saveDownloads($module_id, $downloads) {
    $filename = "cache/$module_id-" . date("y-m-d") . ".txt";
    if(!file_exists($filename)) {
        file_put_contents($filename, $downloads);
    }
}

/*
 * Get last week's downloads
 */
function getLastWeeksDownloads($module_id) {
    $filename = "cache/$module_id-" . date("y-m-d", time() - (7 * 86400)) . ".txt";
    if(file_exists($filename)) {
        return trim(file_get_contents($filename));
    }

    return false;
}

/*
 * Clean up old files.
 */
function cleanOldDownloads($module_id) {
    $filename = "cache/$module_id-" . date("y-m-d", time() - (8 * 86400)) . ".txt";
    if(file_exists($filename)) {
        unlink($filename);
    }
}
