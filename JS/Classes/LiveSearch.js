class LiveSearch{
    constructor(searchResultsParse) {
        //Parse the JSON ready for processing
        this.resultsArray = JSON.parse(searchResultsParse);
    }

    //For sorting by non-numeric (firstname, lastname, username)
    sortNaN(param, asc){
        if(asc){ //If asc value set to true, sort ascending (A - Z)
            return function(x,y){
                if( x[param].toLowerCase() > y[param].toLowerCase()){
                    return 1;
                }else if( x[param] < y[param] ){
                    return -1;
                }
                return 0;
            }
        }else{ //Asc value set to false, sort descending (Z - A)
            return function(x,y){
                if(x[param].toLowerCase() < y[param].toLowerCase()){
                    return 1;
                }else if(x[param] > y[param] ){
                    return -1;
                }
                return 0;
            }
        }
    }

    //For sorting by numeric (userID)
    sortNumeric(param, asc){
        if(asc){ //If asc value set to true, sort ascending (low to high)
            return function(x, y){
                return x[param] - y[param];
            }
        }else{ //Asc value set to false, sort descending (high to low)
            return function(x, y){
                return y[param] - x[param];
            }
        }
    }

    //Function used to construct the layout/HTML necessary to display the search results in the view once sorted
    displaySortedResults(){
        var returnHTMLString = "";
        this.resultsArray.forEach(function(person){
            if(person.friendshipStatus === "4"){
                returnHTMLString += "<tr><td colspan=\"5\">You have blocked this user, so we removed them from the results</td></tr>";
            }else{
                let imageCode = liveSearch2.imageCodeGenerator(person.profilePic);
                returnHTMLString += "<tr><td>" + person.username + "</td><td>" + person.firstName + " " + person.lastName + "</td><td>" + imageCode + "</td>";
                if(person.friendshipStatus === "2"){
                    returnHTMLString += "<td colspan='2'>You are already friends with this user<form method='post' action='/friendRequests.php' ><input type='hidden' name='hiddenUserID' value='" + person.userID + "'><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'></form></td>";
                }else if(person.friendshipStatus === "1"){
                    returnHTMLString += "<td colspan='2'>A friend request is pending<form method='post' action='friendRequests.php' ><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></form></td></tr>";
                }else{
                    returnHTMLString += "<td><form method='post' action='friendRequests.php' ><input type='submit' name='addFriend' value='Add Friend' class='btn btn-success'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></td><td><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></td></form></td></tr>";
                }
            }
        });
        returnHTMLString += "</tr>";
        return returnHTMLString;
    }

    //Function for determining if the persons profile picture is a jpg or a robohash image & generating the necessary HTML code
    //Separate function because it's used repeatedly throughout the class
    imageCodeGenerator(newProfilePicture){
        let returnPicture = "";
        if(newProfilePicture.includes(".jpg")){
            returnPicture = "<img width='40' height='40' src='Images/" + newProfilePicture + "'></div>";
        }else{
            returnPicture = "<img width='40' height='40' src='" + newProfilePicture + "'></div>";
        }
        return returnPicture;
    }

    sortFirstName(btnValue){
        //Sorting alphabetically by first name
        if(btnValue === "A - Z"){
            this.resultsArray.sort(this.sortNaN("firstName", true));
        }//Sorting reverse alphabetically by last name
        else if(btnValue === "Z - A"){
            this.resultsArray.sort(this.sortNaN("firstName", false));
        }
        return this.displaySortedResults();
    }

    sortLastName(btnValue){
        //Sorting alphabetically by last name
        if(btnValue === "A - Z"){
            this.resultsArray.sort(this.sortNaN("lastName", true));
        }//Sorting reverse alphabetically by last name
        else if(btnValue === "Z - A"){
            this.resultsArray.sort(this.sortNaN("lastName", false));
        }
        return this.displaySortedResults();
    }

    sortUsername(btnValue){
        //Sorting alphabetically by username
        if(btnValue === "A - Z"){
            this.resultsArray.sort(this.sortNaN("username", true));
        }//Sorting reverse alphabetically by last name
        else if (btnValue === "Z - A"){
            this.resultsArray.sort(this.sortNaN("username", false));
        }
        return this.displaySortedResults();
    }

    sortUserID(btnValue){
        //Sorting ascending by user id (low to high)
        if(btnValue === "Low - High"){
            this.resultsArray.sort(this.sortNumeric("userID", true));
        }//sorting descending by user id (high to low)
        else if(btnValue === "High - Low"){
            this.resultsArray.sort(this.sortNumeric("userID", false));
        }
        return this.displaySortedResults();
    }

    //Used to filter/remove friends from search results
    filterFriends(friendsParam){
        let returnHTMLString = "";
        var friendCounter = 0;
        if(friendsParam === true){
            //Removes any search results that aren't friends with the logged in user (aka, only show friends)
            this.resultsArray.forEach(function (person){
                if(person.friendshipStatus === "2"){
                    friendCounter++;
                    let imageCode = liveSearch2.imageCodeGenerator(person.profilePic);
                    returnHTMLString += "<tr><td>" + person.username + "</td><td>" + person.firstName + " " + person.lastName + "</td><td>" + imageCode + "</td>";
                    returnHTMLString += "<td colspan=\"2\">You are already friends with this user<form method=\"post\" action=\"friendRequests.php\" ><input type=\"submit\" name=\"blockUserBtn\" value=\"Block\" class=\"btn btn-danger\"><input type=\"hidden\" name=\"hiddenUserID\" value=" + person.userID + "></form></td>";
                }
            });
            //If counter equals 0, then user is not friends with anyone they searched for. So inform them
            if(friendCounter === 0){
                returnHTMLString += "<td colspan=\"4\">You are not friends with any of these results!</td>";
            }
            returnHTMLString += "</tr>";
        }//Removes any search results that are friends with the logged in user (aka, only show non-friends)
        else if(friendsParam === false){
            this.resultsArray.forEach(function (person){
                if(person.friendshipStatus === "2"){
                }else if(person.friendshipStatus === "4"){
                    returnHTMLString += "<tr><td colspan=\"5\">You have blocked this user, so we removed them from the results</td></tr>";
                }else{
                    let imageCode = liveSearch2.imageCodeGenerator(person.profilePic);
                    returnHTMLString += "<tr><td>" + person.username + "</td><td>" + person.firstName + " " + person.lastName + "</td><td>" + imageCode + "</td>";
                    if(person.friendshipStatus === "1"){
                        returnHTMLString += "<td colspan=\"2\">A friend request is pending<form method=\"post\" action=\"friendRequests.php\" ><input type=\"submit\" name=\"blockUserBtn\" value=\"Block\" class=\"btn btn-danger\"><input type=\"hidden\" name=\"hiddenUserID\" value=" + person.userID + "></form></td></tr>";
                    }else{
                        returnHTMLString += "<td><form method=\"post\" action=\"friendRequests.php\" ><input type=\"submit\" name=\"addFriend\" value=\"Add Friend\" class=\"btn btn-success\"><input type=\"hidden\" name=\"hiddenUserID\" value=\"' . $userData->getUserID() . '\"></td><td><input type=\"submit\" name=\"blockUserBtn\" value=\"Block\" class=\"btn btn-danger\"><input type=\"hidden\" name=\"hiddenUserID\" value=\"' . $userData->getUserID() . '\"></td></form></td></tr>";
                    }
                }
            });
        }
        returnHTMLString += "</tr>";
        return returnHTMLString;
    }

    //Shows the images in the search results, in case they have been removed
    showImages(){
        let returnHTMLString = "";
        this.resultsArray.forEach(function(person){
            if(person.friendshipStatus === "4"){
                returnHTMLString += "<tr><td colspan=\"5\">You have blocked this user, so we removed them from the results</td></tr>";
            }else{
                returnHTMLString += "<tr><td>" + person.username + "</td><td>" + person.firstName + " " + person.lastName + "</td>";
                if(person.friendshipStatus === "2"){
                    returnHTMLString += "<td colspan='2'>You are already friends with this user<form method='post' action='/friendRequests.php' ><input type='hidden' name='hiddenUserID' value='" + person.userID + "'><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'></form></td>";
                }else if(person.friendshipStatus === "1"){
                    returnHTMLString += "<td colspan='2'>A friend request is pending<form method='post' action='friendRequests.php' ><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></form></td></tr>";
                }else{
                    returnHTMLString += "<td><form method='post' action='friendRequests.php' ><input type='submit' name='addFriend' value='Add Friend' class='btn btn-success'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></td><td><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></td></form></td></tr>";
                }
            }
            returnHTMLString += "</tr>";
        });
        return returnHTMLString;
    }

    removeImages(searchString, secureToken, callback){
        let paramArray = {'str' : searchString, 'searchToken' : secureToken, 'removeImages' : true};
        ajaxClass.processAjax('GET', "ajaxRemoveImages.php", paramArray, true, function(returnParam){
            let returnHTMLString = "";
            let people = JSON.parse(returnParam);
            people.forEach(function(person){
                if(person.friendshipStatus === "4"){
                    returnHTMLString += "<tr><td colspan=\"5\">You have blocked this user, so we removed them from the results</td></tr>";
                }else{
                    returnHTMLString += "<tr><td>" + person.username + "</td><td>" + person.firstName + " " + person.lastName + "</td>";
                    if(person.friendshipStatus === "2"){
                        returnHTMLString += "<td colspan='2'>You are already friends with this user<form method='post' action='/friendRequests.php' ><input type='hidden' name='hiddenUserID' value='" + person.userID + "'><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'></form></td>";
                    }else if(person.friendshipStatus === "1"){
                        returnHTMLString += "<td colspan='2'>A friend request is pending<form method='post' action='friendRequests.php' ><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></form></td></tr>";
                    }else{
                        returnHTMLString += "<td><form method='post' action='friendRequests.php' ><input type='submit' name='addFriend' value='Add Friend' class='btn btn-success'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></td><td><input type='submit' name='blockUserBtn' value='Block' class='btn btn-danger'><input type='hidden' name='hiddenUserID' value='" + person.userID + "'></td></form></td></tr>";
                    }
                }
            });
            returnHTMLString += "</tr>";
            if(callback) callback(returnHTMLString);
        });
    }

    //This is used for the live search function in the header of every page
    //function to show the results of what the user has searched for
    showSearchResults(str,searchToken,callback){
        if(inputValidator.validateLiveSearch(str)){
            //Start by clearing out the old search results
            document.getElementById("results").innerHTML = "";

            var paramArray = {'str' : str, 'searchToken' : searchToken};

            //Process the ajax request
            ajaxClass.processAjax('GET', "ajaxLiveSearch.php", paramArray, true,function(returnParam){
                //Where the results will be output
                var resultsString = "";

                //Checks if the XMLHttpRequest Response contains "No suggestions!" to imply no results found
                //If true, inform the user, if not create the search result objects
                if(returnParam !== "No suggestions"){
                    //Returns the JSON array created by the PHP model/controller
                    var people = JSON.parse(returnParam);

                    //Logic to determine the necessary phase to output regarding how many results are shown in the live search
                    if(people.length>5){
                        resultsString += "<p>Showing the top 5 results</p>";
                    }else if(people.length === 1) {
                        resultsString += "<p>Showing the top result</p>";
                    }else {
                        resultsString += "<p>Showing the top " + people.length + " results</p>";
                    }

                    //For each of the users in the results variable, make a list element to display them and append it to the return area
                    let i=0;
                    people.forEach(function(obj){
                        if(i<5){
                            let imageCode = liveSearch.imageCodeGenerator(obj.profilePic);
                            resultsString += "<li class='row resultList'><div class='col-lg-4'>" + imageCode + "<div class='col-lg-4'><p>" + obj.firstName + " " + obj.lastName + "</p></div><div class='col-lg-4'><button id='" + obj.userID+ "' type='button' class='btn btn-outline-success' data-bs-toggle='modal' data-bs-target='#personModal' data-bs-userID='" + obj.userID + "'>Profile</button></div></li>";
                            i++;
                        }
                    });
                    resultsString += "<form method='post' action='search.php'><input type='hidden' name='userSearchTerm' value='" + str + "'><button class='btn btn-outline-success' type='submit'>Show all details</button></form>";

                    //if the length of the parsed json is 0, then there are no results so override the html string to be returned
                    if(people.length === 0){
                        resultsString = "<li class='row'><div class='col-lg-12'><p>No Suggestions Available!</p></div></li>";
                    }
                }
                if(callback) callback(resultsString);
            });
        }else{
            document.getElementById("results").innerHTML = "";
            return;
        }
    }

    //Used to dynamically load the profile data relating to a search result
    loadModalData(userID, modalToken, callback){
        if(inputValidator.validateCoordsToStore(userID)){
            var paramArray = {'userID' : userID, 'modalToken' : modalToken};
            ajaxClass.processAjax('GET', "ajaxModal.php", paramArray, true, function(returnParam){
                let userData = JSON.parse(returnParam);
                let modalString = "";
                let personName = "";
                userData.forEach(function (obj){
                    personName = obj.firstName + " " + obj.lastName;

                    //Makes default information that anyone can see
                    modalString = '<tr><td>Username</td><td>' + obj.username + '</td></tr>' +
                        '<tr><td>Name</td><td>' + obj.firstName + ' ' + obj.lastName + '</td></tr>';

                    //Restricts visible information depending on friendship status
                    if(obj.friendshipStatus === "2"){
                        modalString += '<tr><td>Email</td><td>' + obj.email + '</td></tr>' +
                            '<tr><td>Location</td><td>' + obj.latitude + ', ' + obj.longitude + '</td></tr>';
                    }else{
                        modalString += '<tr><td>Email</td><td>Add this user as a friend to see this information</td></tr>' +
                            '<tr><td>Location</td><td>Add this user as a friend to see this information</td></tr>';
                    }

                    //Determines if picture is robohash or jpeg
                    let imageCode = liveSearch.imageCodeGenerator(obj.profilePic);
                    modalString += '<tr><td>Profile Picture</td><td>' + imageCode + '</td></tr>';
                });
                //Need to return multiple items, so use an "associative"/object array
                let returnArray = {'personName' : personName, 'modalString' : modalString}
                if(callback) callback(returnArray);
            });
        }
    }
}