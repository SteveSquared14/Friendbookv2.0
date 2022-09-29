<?php
require_once ('Models/MyMap.php');
require_once ('Models/ValidatorClass.php');
$myMap = new MyMap();
$validator = new ValidatorClass();
session_start();
$errorData = new stdClass();
$plottingToken = "";
$returnValue = "";

if(isset($_SESSION['ajaxPlotUserToken'])){
    $plottingToken = $_SESSION['ajaxPlotUserToken'];
}

if(!isset($_REQUEST['ajaxPlotUserToken']) || $_REQUEST['ajaxPlotUserToken'] != $plottingToken){
    $errorData->error = "Alert: URL Token has expired";
}else{
    //Check if user id is valid - protects against url parameter manipulation on back end
    if($validator->validateUserID($_REQUEST['userID'])){
        if(isset($_REQUEST['userID'])){
            //if false then get json for logged in user
            if($_REQUEST['returnFriends'] === "false"){
                $returnValue = $myMap->jsonRetrieveOneUser($_REQUEST['userID']);
            }//if true then get json for logged-in users friends
            elseif ($_REQUEST['returnFriends'] === "true"){
                $returnValue = $myMap->jsonRetrieveAllFriendships($_REQUEST['userID']);
            }
        }
    }else{
        $returnValue = "Alert: Parameter manipulation attempted!";
    }
}
echo json_encode($returnValue);