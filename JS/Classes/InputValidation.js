class InputValidation {
    constructor() {
    }

    //Returns true if param does not contain special characters, false otherwise
    checkSpecialCharacters(paramToCheck) {
        let returnVal = false;
        switch (paramToCheck) {
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
                returnVal = true;
                break;
        }
        return returnVal;
    }

    //returns true if param NOT numeric, false otherwise
    checkNumeric(paramToCheck) {
        let returnVal = false;
        if (isNaN(paramToCheck)) {
            returnVal = true;
        }
        return returnVal;
    }

    //Returns true if param length greater than or equal to 3, false otherwise
    checkLength(paramToCheck) {
        let returnVal = false;
        if (paramToCheck.length >= 3) {
            returnVal = true;
        }
        return returnVal;
    }

    //Returns true if all sub-functions are passed, false otherwise
    validateLiveSearch(paramToCheck){
        let returnVal = false;
        //if all the following have been passed successfully, return true as input has been validated
        if(this.checkSpecialCharacters(paramToCheck) === false && this.checkNumeric(paramToCheck) === true && this.checkLength(paramToCheck) === true) {
            returnVal = true;
        }
        return returnVal;
    }

    //Returns true if userID has not been manipulated to contain special characters & is integer, false otherwise
    validateCoordsToStore(paramToCheck){
        let returnVal = false;
        if(this.checkSpecialCharacters(paramToCheck) === false && this.checkNumeric() === true){
            returnVal = true;
        }
        return returnVal;
    }

    //Used to validate the user id for ajax calls where necessary
    validateUserID(paramToCheck){
        let returnVal = false;
        if(!this.checkSpecialCharacters(paramToCheck) && this.checkNumeric()){
            returnVal = true;
        }
        return returnVal;
    }
}