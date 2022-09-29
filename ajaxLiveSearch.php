<?php
require_once("Models/LiveSearch.php");
require_once("Models/ValidatorClass.php");
header('Content-Type: application/json; charset=UTF-8;');

session_start(); //Start a session
$errorData = new stdClass();
$searchSessionToken = "";
//If the session token is set, pass its value to a varable for comparing
if(isset($_SESSION["ajaxSearchToken"])){
    $searchSessionToken = $_SESSION["ajaxSearchToken"];
}
//If token coming from XMLHTTPRequest isnt set OR it doesnt match the value from SESSION, throw error
if(!isset($_REQUEST["searchToken"]) || $_REQUEST["searchToken"] != $searchSessionToken){
    $errorData->error = "Alert: URL Token has expired";
    echo json_encode($errorData->error);
}else{
    //Confirmed token is valid, so check for parameter tampering after ajax call
    $validator = new ValidatorClass();
    if($validator->validateLiveSearch($_REQUEST["str"])){
        //Parameter validation passed, process necessary results
        $dataset = new LiveSearch();
        $searchResults = $dataset->retrieveLiveUsers($_REQUEST["str"]);
        //Encode the data as json for use with JavaScript
        echo json_encode($searchResults);
    }else{
        $errorData->error = "Alert: Parameter manipulation attempted!";
        echo json_encode($errorData->error);
    }
}