<?php
class ValidatorClass{
    public function __construct(){
    }

    //Returns false if param does NOT contain special characters, true otherwise
    public function checkSpecialCharacters($paramToCheck){
        $returnVal = false;
        switch($paramToCheck){
            case "!":
            case "?":
            case "<":
            case ">":
            case "/":
            case "*":
            case "$":
            case "£":
            case "&":
            case "(":
            case ")":
            case "%":
                $returnVal = true;
                break;
        }
        return $returnVal;
    }

    //returns false if param is NOT numeric, true otherwise
    public function checkNumeric($paramToCheck){
        $returnVal = false;
        if(is_numeric($paramToCheck)){
            $returnVal = true;
        }
        return $returnVal;
    }

    //returns true if length of param is <3, false otherwise
    //minimises data outputted by ajax call
    public function checkLength($paramToCheck){
        $returnVal = false;
        if(strlen($paramToCheck) < 3){
            $returnVal = true;
        }
        return $returnVal;
    }

    //Returns true if checks return necessary true/false meaning validation passed
    public function validateLiveSearch($paramToCheck){
        $returnVal = false;
        if(!$this->checkSpecialCharacters($paramToCheck) && !$this->checkNumeric($paramToCheck) && !$this->checkLength($paramToCheck)){
            $returnVal = true;
        }
        return $returnVal;
    }

    public function validateUserID($paramToCheck){
        $returnVal = false;
        if(!$this->checkSpecialCharacters($paramToCheck) && $this->checkNumeric($paramToCheck)){
            $returnVal = true;
        }
        return $returnVal;
    }
}