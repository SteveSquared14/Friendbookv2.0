<?php
require_once('Models/LiveSearch.php');
//require_once ('Models/UserDataSet.php');
require_once('Models/UserSession.php');
require_once('Models/SearchPagination.php');
$session = new UserSession();
$view = new stdClass();
$liveSearch = new LiveSearch();
//$userDataSet = new UserDataSet();
$view->userSearchTerm = "";

if(isset($_POST['userSearchTerm'])){
    $view->userSearchTerm = $_POST['userSearchTerm'];
    //Used for pagination down below
    $view->searchResults = $liveSearch->searchDatabase($_POST['userSearchTerm']);
    //Used for sorting/filtering done with JS/AJAX in the View
    $view->jsonSearchResults = json_encode($liveSearch->retrieveLiveUsers($_POST['userSearchTerm']));
}

$view->userSearchTerm = $_POST['userSearchTerm'];

//Use a default value as a parameter for this pagination object, as at this point in the controller
//all we want is access to the accessor methods below the declaration in order to generate
//the relevant LIMIT/OFFSET search parameter to be used later
$pagination = new SearchPagination(1250);

//Use the accessor methods to generate the LIMIT/OFFSET parameter to
//be used in the paginated DB search (below)
$firstPageResults = $pagination->getPageFirstResults();
/*if(isset($_POST['numberOfResults'])) {
    $noOfRecordsPerPage = $_POST['numberOfResults'];
}else{*/
    $noOfRecordsPerPage = $pagination->getNoOfRecordsPerPage();
/*}*/
$paginationParam = $firstPageResults . ", " . $noOfRecordsPerPage;
if(isset($_POST['userSearchTerm'])){
    $view->paginatedSearchResults = $liveSearch->paginatedSearchDatabase($_POST['userSearchTerm'], $paginationParam);
}
//Now make another SearchPagination object, passing it the dynamic size depending on how many results the previous search returned
$pagination2 = new SearchPagination(sizeof($view->searchResults));
$firstPageResults = $pagination2->getPageFirstResults();
/*if(isset($_REQUEST['numberOfResults'])){
    $noOfRecordsPerPage = $_POST['numberOfResults'];
}else{*/
    $noOfRecordsPerPage = $pagination2->getNoOfRecordsPerPage();
/*}*/
$paginationParam = $firstPageResults . ", " . $noOfRecordsPerPage;
$view->numberOfPages = $pagination2->getTotalPages();
$view->pageNumber = $pagination2->getPage();
require_once('Views/search.phtml');