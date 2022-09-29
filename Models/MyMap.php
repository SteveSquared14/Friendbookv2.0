<?php
require_once ('Models/UserData.php');
require_once ('Models/Database.php');
require_once ('Models/UserDataSet.php');

class MyMap{
    protected $_dbHandle, $_dbInstance;

    public function __construct() {
        //connect to the DB
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    //Retrieves every friendship containing the logged-in user where status is "accepted"
    public function jsonRetrieveAllFriendships($x) {
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
        $friends = [];
        while ($row = $statement1->fetch()) {
            $friend = new FriendData($row);
            $friendData = array(
                'userID' => $friend->getUserID(),
                'username' => $friend->getUsername(),
                'firstName' => $friend->getFirstName(),
                'lastName' => $friend->getLastName(),
                'longitude' => $friend->getLongitude(),
                'latitude' => $friend->getLatitude(),
                'profilePic' => $friend->getProfileImage(),
                'friendshipStatus' => $friend->getFriendshipStatusID(),
            );
            $friends[] = $friendData;
        }
        return $friends;
    }

    //Retrieves a specific user from the database
    public function jsonRetrieveOneUser($x){
        $paramToBind = $x;
        $userQuery = "SELECT * FROM Users WHERE id=?";
        $statement = $this->_dbHandle->prepare($userQuery);
        $statement->bindParam(1, $paramToBind);
        $statement->execute();
        $returnValue = [];
        while ($row = $statement->fetch()) {
            $person = new UserData($row);
            $personData = array(
                'userID' => $person->getUserID(),
                'username' => $person->getUsername(),
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'profilePic' => $person->getProfileImage(),
                'latitude' => $person->getLatitude(),
                'longitude' => $person->getLongitude(),
            );
            $returnValue[] = $personData;
        }
        return $returnValue;
    }

    public function calculateDistance($friendID, $userID){
        $returnValue = [];

        $friend = $this->jsonRetrieveOneUser($friendID);
        $user = $this->jsonRetrieveOneUser($userID);

        $returnValue[] = $friend[0];
        $returnValue[] = $user[0];

        return $returnValue;
    }
}