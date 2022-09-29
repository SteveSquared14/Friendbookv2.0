<?php
require_once ('Models/UserData.php');
require_once ('Models/Database.php');
require_once ('Models/UserDataSet.php');

class LiveSearch{
    protected $_dbHandle, $_dbInstance;

    public function __construct() {
        //connect to the DB
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
        $this->userDataSet = new UserDataSet();
    }

    //Retrieves every single user from the database
    public function retrieveLiveUsers($x) {
        $paramToBind = $x . '%';
        //$sqlQuery = "SELECT * FROM Users WHERE firstName LIKE ? ORDER BY firstName";
        $sqlQuery = "SELECT * FROM Users WHERE firstName LIKE ? ORDER BY firstName";

        $statement = $this->_dbHandle->prepare($sqlQuery); // prepare a PDO statement
        $statement->bindParam(1, $paramToBind);
        $statement->execute(); // execute the PDO statement

        //Make an array to be returned
        $userData = [];
        //While there are more results from the SQL, do the following
        while ($row = $statement->fetch()) {
            //Make a new UserData object for each result
            $person = new UserData($row);

            //Transform that UserData object to an array for JSON
            $personData = array(
                'userID' => $person->getUserID(),
                'username' => $person->getUsername(),
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'profilePic' => $person->getProfileImage(),
                'friendshipStatus' => $this->userDataSet->retrieveUserStatus($person->getUserID(), $_SESSION['uid']),
            );
            //Assign the newly created JSON object to the array to be returned
            $userData[] = $personData;
        }
        return $userData;
    }

    //Retrieves every single user from the database
    public function removeImages($x) {
        $paramToBind = $x . '%';
        $sqlQuery = "SELECT * FROM Users WHERE firstName LIKE ? ORDER BY firstName";

        $statement = $this->_dbHandle->prepare($sqlQuery); // prepare a PDO statement
        $statement->bindParam(1, $paramToBind);
        $statement->execute(); // execute the PDO statement

        //Make an array to be returned
        $userData = [];
        //While there are more results from the SQL, do the following
        while ($row = $statement->fetch()) {
            //Make a new UserData object for each result
            $person = new UserData($row);

            //Transform that UserData object to an array for JSON
            $personData = array(
                'userID' => $person->getUserID(),
                'username' => $person->getUsername(),
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'friendshipStatus' => $this->userDataSet->retrieveUserStatus($person->getUserID(), $_SESSION['uid']),
            );
            //Assign the newly created JSON object to the array to be returned
            $userData[] = $personData;
        }
        return $userData;
    }

    public function retrieveModalData($x){
        $paramToBind = $x;
        $userQuery = "SELECT * FROM Users WHERE id=?";
        $statement = $this->_dbHandle->prepare($userQuery);
        $statement->bindParam(1, $paramToBind);
        $statement->execute();

        //Make an array to be returned
        $userData = [];
        //While there are more results from the SQL, do the following
        while ($row = $statement->fetch()) {
            //Make a new UserData object for each result
            $person = new UserData($row);
            //Transform that UserData object to an array for JSON
            $personData = array(
                'userID' => $person->getUserID(),
                'username' => $person->getUsername(),
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'email' =>  $person->getEmail(),
                'latitude' => $person->getLatitude(),
                'longitude' => $person->getLongitude(),
                'profilePic' => $person->getProfileImage(),
                'friendshipStatus' => $this->userDataSet->retrieveUserStatus($person->getUserID(), $_SESSION['uid']),
            );
            //Assign the newly created JSON object to the array to be returned
            $userData[] = $personData;
        }
        return $userData;
    }

    //Function used to search for users by username
    //returns an array of all users matching the search
    public function searchDatabase($searchTerm){
        $paramToBind = $searchTerm . '%';
        $sqlStatement = "SELECT * FROM Users WHERE firstName LIKE ?";

        $statement2 = $this->_dbHandle->prepare($sqlStatement); // prepare a PDO statement
        $statement2->bindParam(1, $paramToBind);
        $statement2->execute(); // execute the PDO statement
        $queryResults = [];
        while($row = $statement2->fetch()) {
            $queryResults[] = new UserData($row);
        }
        return $queryResults;
    }

    //Function used to search for users by username
    //returns an array of all users matching the search
    public function paginatedSearchDatabase($searchTerm, $paginationParam){
        $paramToBind = $searchTerm . '%';
        $sqlStatement = "SELECT * FROM Users WHERE firstName LIKE ? LIMIT $paginationParam";

        $statement2 = $this->_dbHandle->prepare($sqlStatement); // prepare a PDO statement
        $statement2->bindParam(1, $paramToBind);
        $statement2->execute(); // execute the PDO statement
        $queryResults = [];
        while($row = $statement2->fetch()) {
            $queryResults[] = new UserData($row);
        }
        return $queryResults;
    }
}
