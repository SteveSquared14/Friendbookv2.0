<?php
class UserData implements JsonSerializable {
    //private fields for users
    protected $userID, $username, $firstName, $lastName, $email, $password, $longitude, $latitude, $profileImage;

    //Constructor for the UserData class, assigns values for each row of the userData
    //database
    public function __construct($dbRow){
        $this->userID = $dbRow['id'];
        $this->username = $dbRow['username'];
        $this->firstName = $dbRow['firstName'];
        $this->lastName = $dbRow['lastName'];
        $this->email = $dbRow['email'];
        $this->password = $dbRow['password'];
        $this->longitude= $dbRow['longitude'];
        $this->latitude= $dbRow['latitude'];
        $this->profileImage = $dbRow['profileImage'];
    }

    //Implement the JsonSerializable interface because json_encode() can be buggy at time
    public function jsonSerialize(){
        return [
        'userID' => $this->userID,
        'username' => $this->username,
        'firstName' => $this->firstName,
        'lastName' => $this->lastName,
        'email' => $this->email,
        'password' => $this->password,
        'longitude' => $this->longitude,
        'latitude' => $this->latitude,
        'profileImage' => $this->profileImage,
        ];
    }

    //Accessor methods for each of the private fields go below here//
    //Get the users ID
    public function getUserID(){
        return $this->userID;
    }

    //Get the users username
    public function getUsername(){
        return $this->username;
    }

    //Get the users first name
    public function getFirstName(){
        return $this->firstName;
    }

    //Get the users last name
    public function getLastName(){
        return $this->lastName;
    }

    //Get the users email address
    public function getEmail(){
        return $this->email;
    }

    //Get the users password
    public function getPassword(){
        return $this->password;
    }

    //Get the users longitude coordinate
    public function getLongitude(){
        return $this->longitude;
    }

    //Get the users latitude
    public function getLatitude(){
        return $this->latitude;
    }

    //Get the users profile image
    public function getProfileImage(){
        return $this->profileImage;
    }
}