<?php
require_once ('Models/UserData.php');
require_once ('Models/Database.php');
require_once ('Models/FriendData.php');

class UserDataSet {
    protected $_dbHandle, $_dbInstance;

    public function __construct() {
        //connect to the DB
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    //Checks if a friendship being requested already exists in the database
    //Included as a failsafe as the system has been designed in such a way that
    //the user is only able to add the users that they are not currently friends with. So if they can "see" and add friend button
    //then they will not be friends with that user as otherwise the add friend button would not be shown
    public function checkDuplicateFriendship($x, $y){
        $returnValue = false;
        $sql = "SELECT COUNT(friendShipID) FROM Friendships WHERE friend1ID=? AND friend2ID=?";
        $statement = $this->_dbHandle->prepare($sql); // prepare a PDO statement

        $statement->bindParam(1,$x);
        $statement->bindParam(2,$y);
        $numberOfFriendshipsArray = $statement->execute(); // execute the PDO statement
        $numberOfFriendships = 0;
        if(is_array($numberOfFriendships) === true){
            $numberOfFriendships = 1;
        }
        else{
            $numberOfFriendships = 0;
        }

        if($numberOfFriendships == 1){
            $returnValue = true;
        }
        return $returnValue;
    }

    //Retrieves every single user from the database
    public function retrieveAllUsers($x, $y) {
        $paramToBind = $x . ", " . $y;
        $sqlQuery = "SELECT * FROM Users ORDER BY id LIMIT $paramToBind";

        $statement = $this->_dbHandle->prepare($sqlQuery); // prepare a PDO statement
        $statement->execute(); // execute the PDO statement

        $dataSet = [];
        while ($row = $statement->fetch()) {
            $dataSet[] = new UserData($row);
        }
        return $dataSet;
    }

    //Retrieves a specific user from the database
    public function retrieveOneUser($x){
        $userQuery = "SELECT * FROM Users WHERE id='$x'";
        $statement = $this->_dbHandle->prepare($userQuery);
        $statement->execute();
        $returnValue = "";
        while ($row = $statement->fetch()) {
            $returnValue = new UserData($row);
        }
        return $returnValue;
    }

    //Retrieves the friendship status of a friendship between the user logged in and another specified user
    //Used for filtering "all users" page and search results
    public function retrieveUserStatus($x, $y){
        $valueToReturn = "";
        $loggedInUserID = $y;
        $userQuery="SELECT Friendships.status FROM Users, Friendships WHERE (Users.id=Friendships.friend1ID OR Users.id=Friendships.friend2ID) AND ((friend1ID=? AND friend2ID=?) OR (friend2ID=? AND friend1ID=?))";
        $statement = $this->_dbHandle->prepare($userQuery);

        $statement->bindParam(1, $loggedInUserID);
        $statement->bindParam(2, $x);
        $statement->bindParam(3, $loggedInUserID);
        $statement->bindParam(4, $x);

        $statement->execute();
        $statusArray = $statement->fetch();
        //fetch() returns false (boolean) if no value exists (ie, no friendship exists), so return -1 instead of friendship status
        if(is_bool($statusArray)){
            $valueToReturn = "-1";
        }
        else{
            $valueToReturn = $statusArray["status"];
        }
        return $valueToReturn;
    }

    //Retrieves every friendship containing the logged-in user where status is "accepted"
    public function retrieveAllFriendships($x) {
        $loggedInFriendID = $x;
        $sqlQuery1 = "SELECT * from (
              select * from Users where Users.id in (
                  select friend1ID as friend1
                  from Friendships
                  where (Friendships.friend1ID = ? or Friendships.friend2ID = ?)
                  union
                  select friend2ID as friend2
                  from Friendships
                  where (Friendships.friend1ID = ? or Friendships.friend2ID = ?)
                  )
                    and Users.id != ?
                ) ping inner join Friendships where ((friend1ID=ping.id and friend2ID=?) or (friend1ID=? and friend2ID=ping.id))";
        $statement1 = $this->_dbHandle->prepare($sqlQuery1); // prepare a PDO statement
        $statement1->bindParam(1, $loggedInFriendID);
        $statement1->bindParam(2, $loggedInFriendID);
        $statement1->bindParam(3, $loggedInFriendID);
        $statement1->bindParam(4, $loggedInFriendID);
        $statement1->bindParam(5, $loggedInFriendID);
        $statement1->bindParam(6, $loggedInFriendID);
        $statement1->bindParam(7, $loggedInFriendID);
        $statement1->execute(); // execute the PDO statement
        $dataSet = [];
        while ($row = $statement1->fetch()) {
            $dataSet[] = new FriendData($row);
        }

        return $dataSet;
    }

    //Retrieves all friendships from the DB where friendship status equals 1
    public function retrieveAllFriendRequests($x) {
        $loggedInFriendID = $x;
        $sqlQuery1 = "SELECT * from (
              select * from Users where Users.id in (
                  select friend1ID as friend1
                  from Friendships
                  where (Friendships.friend2ID = ?) AND Friendships.status=1 
                  union
                  select friend2ID as friend2
                  from Friendships
                  where (Friendships.friend2ID = ?) AND Friendships.status=1 
                  )
                    and Users.id != ?
                ) ping inner join Friendships where ((friend1ID=ping.id and friend2ID=?) or (friend1ID=? and friend2ID=ping.id))";
        $statement1 = $this->_dbHandle->prepare($sqlQuery1); // prepare a PDO statement
        $statement1->bindParam(1, $loggedInFriendID);
        $statement1->bindParam(2, $loggedInFriendID);
        $statement1->bindParam(3, $loggedInFriendID);
        $statement1->bindParam(4, $loggedInFriendID);
        $statement1->bindParam(5, $loggedInFriendID);
        $statement1->execute(); // execute the PDO statement
        $dataSet = [];
        while ($row = $statement1->fetch()) {
            $dataSet[] = new FriendData($row);
        }

        return $dataSet;
    }

    //Retrieves every friendship containing the logged-in user where status is "blocked"
    public function retrieveAllBlockedUsers($x) {
        $loggedInFriendID = $x;
        $sqlQuery1 = "SELECT * from (
              select * from Users where Users.id in (
                  select friend1ID as friend1
                  from Friendships
                  where (Friendships.friend1ID = ? or Friendships.friend2ID = ?) AND Friendships.status ='4'
                  union
                  select friend2ID as friend2
                  from Friendships
                  where (Friendships.friend1ID = ? or Friendships.friend2ID = ?) AND Friendships.status = '4'
                  )
                    and Users.id != ?
                ) ping inner join Friendships where ((friend1ID=ping.id and friend2ID=?) or (friend1ID=? and friend2ID=ping.id))";
        $statement1 = $this->_dbHandle->prepare($sqlQuery1); // prepare a PDO statement
        $statement1->bindParam(1, $loggedInFriendID);
        $statement1->bindParam(2, $loggedInFriendID);
        $statement1->bindParam(3, $loggedInFriendID);
        $statement1->bindParam(4, $loggedInFriendID);
        $statement1->bindParam(5, $loggedInFriendID);
        $statement1->bindParam(6, $loggedInFriendID);
        $statement1->bindParam(7, $loggedInFriendID);
        $statement1->execute(); // execute the PDO statement
        $dataSet = [];
        while ($row = $statement1->fetch()) {
            $dataSet[] = new FriendData($row);
        }
        return $dataSet;
    }

    //Function to find the details of the user that is logged in currently.
    //Returns an array (of size 1 as there cannot be duplicate usernames in the DB) containing
    //the details of the user, so they can edit them
    public function loadUserProfile($userProfileToLoad){
        $sqlStatement = "SELECT * FROM Users WHERE id='$userProfileToLoad'";

        $statement2 = $this->_dbHandle->prepare($sqlStatement); // prepare a PDO statement
        $statement2->execute(); // execute the PDO statement

        $queryResults = [];
        while($row = $statement2->fetch()) {
            $queryResults[] = new UserData($row);
        }
        return $queryResults;
    }

    //Used when someone logs in, returns true if username they entered is in DB, false otherwise
    public function checkUsername($x){
        $userID = $this->retrieveUserID($x);
        $usernameToCheck = $x;

        $checkStatement = "SELECT * FROM Users WHERE id='$userID'";
        $checkStatement = $this->_dbHandle->prepare($checkStatement);
        $checkStatement->execute();
        $user = new UserData($checkStatement->fetch());
        $usernameInDB = "";
        if(strpos($x, '@')){
            $usernameInDB = $user->getEmail();
        }
        else {
            $usernameInDB = $user->getUsername();
        }
        $returnValue = false;
        if (($usernameToCheck == $usernameInDB)){
            $returnValue = true;
        }

        return $returnValue;
    }

    //Used when someone logs in, returns true if password they entered is in DB, false otherwise
    public function checkPassword($y, $x){
        $userID = $this->retrieveUserID($y);
        $passwordToCheck = md5($x);
        $checkStatement="";

        $checkStatement = "SELECT * FROM Users WHERE id='$userID'";
        $checkStatement = $this->_dbHandle->prepare($checkStatement);
        $checkStatement->execute();
        $user = new UserData($checkStatement->fetch());
        $passwordInDB = $user->getPassword();

        $returnValue = false;
        if ($passwordToCheck == $passwordInDB){
            $returnValue = true;
        }

        return $returnValue;
    }

    //Retrieves the email address of a specific user
    public function retrieveEmail($x){
        $emailQuery = "SELECT email FROM Users WHERE email=?";

        $statement = $this->_dbHandle->prepare($emailQuery);
        $statement->bindParam(1, $x);
        $statement->execute();

        $emailArray = $statement->fetch();
        return $emailArray[0];
    }

    //Retrieves the UserID of a specific user
    public function retrieveUserID($x){
        $emailQuery="";
        //Logic to determine if the user has logged in with their email address or username
        //Checks if what the user inputted contains an @ as this makes it an email address
        if(strpos($x, '@') !== false) {
            //If email used, set sql where to email column
            $emailQuery = "SELECT id FROM Users WHERE email='$x'";
        }
        else{
            //If username used, set sql where to username
            $emailQuery = "SELECT id FROM Users WHERE username='$x'";
        }
        $statement = $this->_dbHandle->prepare($emailQuery);
        $statement->execute();

        $emailArray = $statement->fetch();
        return $emailArray[0];
    }

    public function imageUploadPathSet($x, $y){
        $sql = "UPDATE Users SET profileImage=? WHERE id=?";
        $statement = $this->_dbHandle->prepare($sql);
        $statement->bindParam(1, $x);
        $statement->bindParam(2, $y);
        $statement->execute();
    }

    public function changeUserDetails($userID, $a, $b, $c, $d, $e){
        $loggedInUserID = $userID;

        $usernameSQL = "UPDATE Users SET username=? WHERE id=?";
        $usernameStatement = $this->_dbHandle->prepare($usernameSQL);
        $usernameStatement->bindParam(1, $a);
        $usernameStatement->bindParam(2, $userID);

        $firstNameSQL = "UPDATE Users SET firstName=? WHERE id=?";
        $firstNameStatement = $this->_dbHandle->prepare($firstNameSQL);
        $firstNameStatement->bindParam(1, $b);
        $firstNameStatement->bindParam(2, $userID);

        $lastNameSQL = "UPDATE Users SET lastName=? WHERE id=?";
        $lastNameStatement = $this->_dbHandle->prepare($lastNameSQL);
        $lastNameStatement->bindParam(1, $c);
        $lastNameStatement->bindParam(2, $userID);

        $emailSQL = "UPDATE Users SET email=? WHERE id=?";
        $emailStatement = $this->_dbHandle->prepare($emailSQL);
        $emailStatement->bindParam(1, $d);
        $emailStatement->bindParam(2, $userID);

        $passwordSQL = "UPDATE Users SET password=? WHERE id=?";
        $passwordStatement = $this->_dbHandle->prepare($passwordSQL);
        $passwordStatement->bindParam(1, $e);
        $lastNameStatement->bindParam(2, $userID);

        $usernameStatement->execute();
        $firstNameStatement->execute();
        $lastNameStatement->execute();
        $emailStatement->execute();
        $passwordStatement->execute();
    }

    public function updateCoords($newLatitude, $newLongitude, $userID){
        $updateLatitudeSQL = "UPDATE Users SET latitude=? WHERE id=?";
        $latStatement = $this->_dbHandle->prepare($updateLatitudeSQL);
        $latStatement->bindParam(1, $newLatitude);
        $latStatement->bindParam(2, $userID);

        $updateLongitudeSQL = "UPDATE Users SET longitude=? WHERE id=?";
        $longStatement = $this->_dbHandle->prepare($updateLongitudeSQL);
        $longStatement->bindParam(1, $newLongitude);
        $longStatement->bindParam(2, $userID);
        if($latStatement->execute() && $longStatement->execute()){
            return "Coordinates Updated!";
        }else{
            return "Error: Could not update coordinates!";
        }
    }
}