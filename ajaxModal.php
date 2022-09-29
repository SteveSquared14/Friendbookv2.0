<?php
require_once('Models/LiveSearch.php');
require_once ('Models/ValidatorClass.php');
require_once ('Models/MyMap.php');
header('Content-Type: application/json; charset=UTF-8;');

session_start(); //Start a session
$errorData = new stdClass();
$validator = new ValidatorClass();
$modalSessionToken = "";
$returnValue = "";
//If the session token is set, pass its value to a varable for comparing
if(isset($_SESSION["modalToken"])){
    $modalSessionToken = $_SESSION["modalToken"];
}
//If token coming from XMLHTTPRequest isnt set OR it doesnt match the value from SESSION, throw error
if(!isset($_REQUEST["modalToken"]) || $_REQUEST["modalToken"] != $modalSessionToken){
    $errorData->error = "Alert: URL Token has expired";
    echo json_encode($errorData->error);
}else{
    //check if user id is valid - protects against url parameter manipulation on back end
    if($validator->validateUserID($_REQUEST['userID'])){
        //If below is set, then the request is to generate a modal to display distance between friends
        if(isset($_REQUEST['calcDistance'])){
            $myMap = new MyMap();
            $returnValue = $myMap->calculateDistance($_REQUEST['friendID'], $_REQUEST['userID']);
        }//If not set, then the request is to generate a modal to display a users profile data
        else{
            $liveSearch = new LiveSearch();
            $returnValue = $liveSearch->retrieveModalData($_REQUEST['userID']);
        }
    }else{
        $returnValue = "Alert: Parameter manipulation attempted!";
    }
}
echo json_encode($returnValue);