<?php

require_once('response.class.php');


if(isset($_GET['id'])) {

    $module_id = $_GET['id'];

    $html = makeRequest("http://www.magentocommerce.com/extension/specs/$module_id");

    $downloads = findDownloads($html);

    $gecko = convertToGecko($downloads);

    $format = isset($_POST['format']) ? (int)$_POST['format'] : 0;
    $format = ($format == 1) ? 'xml' : 'json';
    $response_obj = new Response();
    $response_obj->setFormat($format);

    $response = $response_obj->getResponse($gecko);
    echo $response;  
}

function makeRequest($url) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL,$url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($c);
    curl_close($c);
   
    return $result;
}

function findDownloads($html) {
    $matches = array();
    preg_match("'<td class\=\"titl\">Downloads</td>[^<]*<td>([^<]*)'si", $html, $matches);

    return($matches[1]);

}

function convertToGecko($downloads) {
    return array("item" => array("value" => $downloads));
}
