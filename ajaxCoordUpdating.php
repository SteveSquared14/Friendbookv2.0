<?php
require_once("Models/UserDataSet.php");
require_once ("Models/LiveSearch.php");
require_once ('Models/ValidatorClass.php');
header('Content-type: application/json; charset=UTF-8;');
$userDataSet = new UserDataSet();
$liveSearch = new LiveSearch();
$validator = new ValidatorClass();
session_start();
$sessionTrackingToken = "";
$returnValue = "";

//If relevant session token set, then assign value to variable
if(isset($_SESSION["ajaxTrackingToken"])){
    $sessionTrackingToken = $_SESSION["ajaxTrackingToken"];
}

//If relevant session token not set OR the token in the AJAX/XMLHTTPRequest doesnt match that of the session token, throw error
if(!isset($_REQUEST["trackingToken"]) || $_REQUEST["trackingToken"] != $sessionTrackingToken){
    $returnValue = "Invalid Secure Token!";
}else{
    //check if user id is valid - protects against url parameter manipulation on back end
    if($validator->validateUserID($_REQUEST['id'])){
        $returnValue = $userDataSet->updateCoords($_REQUEST['lat'], $_REQUEST['long'], $_REQUEST['id']);
    }else{
        $returnValue = "Alert: Parameter manipulation attempted!";
    }
}
echo $returnValue;