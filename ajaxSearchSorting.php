<?php
include_once("Models/LiveSearch.php");
header('Content-Type: application/json; charset=UTF-8;');
$liveSearch = new LiveSearch();

//Declare a variable of arbitrary type to be used for storing sorted search results
$sortedSearchResults = "";

//The term to be used for the "LIKE" part of the SQL - AKA what the user types
$likeTerm = $_REQUEST['searchTerm'];
//The value of the sorting/filtering button the user has clicked
$sortingOption = $_REQUEST['chosenFilter'];
//The type of sorting/filtering (firstname, lastname, username, userid) chosen by the user
$searchType = $_REQUEST['searchType'];

//Logic to determine what type of sort/filter the user has clicked
if($searchType === "firstName"){
    $sortedSearchResults = $liveSearch->sortByFirstName($likeTerm, $sortingOption);
}elseif ($searchType === "lastName"){
    $sortedSearchResults = $liveSearch->sortByLastName($likeTerm, $sortingOption);
}elseif ($searchType === "username"){
    $sortedSearchResults = $liveSearch->sortByUserName($likeTerm, $sortingOption);
}elseif ($searchType === "userID"){
    $sortedSearchResults = $liveSearch->sortByUserID($likeTerm, $sortingOption);
}

echo json_encode($sortedSearchResults);