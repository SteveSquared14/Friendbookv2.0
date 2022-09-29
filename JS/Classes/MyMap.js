class MyMap{
    constructor(newMyMap){
        //Class fields for the map
        this.myMap = newMyMap;
        this.epsg4326 = new OpenLayers.Projection("EPSG:4326");
        this.projectTo = new OpenLayers.Projection("EPSG:900913");
        this.myMapCoords;
        this.zoom = 16;
    }

    //Function to make a new map, and set the centre
    makeMyMap(){
        this.myMap.addLayer(new OpenLayers.Layer.OSM());
        this.projectTo = this.myMap.getProjectionObject();
        this.myMapCoords = new OpenLayers.LonLat(-2.391460, 53.442660).transform(this.epsg4326, this.projectTo);
        this.myMap.setCenter(this.myMapCoords, this.zoom);
        return this.myMap;
    }

    makeMyMapMarker(newUserID, newUsername, newFirstName, newLastName, newLongitude, newLatitude, newProfilePicture){
        //Both images and image URLs in DB, so logic to determine which the user has and amend the path
        let profilePic = "";
        if (newProfilePicture.includes(".jpg")) {
            //If true, then image is stored jpeg
            profilePic = '/Images/' + newProfilePicture;
        }
        else{
            //If false, then image is Robohash URL
            profilePic = newProfilePicture;
        }

        this.feature = new OpenLayers.Feature.Vector(
            new OpenLayers.Geometry.Point(newLongitude, newLatitude)
                .transform(this.epsg4326, this.projectTo),
            {description: '<table  class=\'table table-hover\'><thead><tr><th>Category</th><th>Value</th></tr></thead>' +
                    '<tbody>' +
                    '<tr><td>Username:</td><td>' + newUsername + '</td></tr>' +
                    '<tr><td>First Name:</td><td>' + newFirstName + '</td></tr>' +
                    '<tr><td>Last Name:</td><td>' + newLastName + '</td></tr>' +
                    '<tr><td colspan=\'2\'>' + '<button id=\'' + newUserID + '\' type=\'button\' class=\'btn btn-outline-success\' data-bs-toggle=\'modal\' data-bs-target=\'#distanceModal\' data-bs-userID=\'' + newUserID + '\'>Distance</button>' + '</td></tr>' +
                    '<tr><td colspan=\'2\'><button id=\'" + obj.userID+ "\' type=\'button\' class=\'btn btn-outline-success\' data-bs-toggle=\'modal\' data-bs-target=\'#personModal\' data-bs-userID=\'' + newUserID + '\'>Profile</button></td></tr>' +
                    '</tbody>' +
                    '</table>'},
            {externalGraphic: profilePic, graphicHeight: 30, graphicWidth: 30, graphicXOffset: -12, graphicYOffset: -25}
        );
        return this.feature;
    }

    plotUser(secondMarkerLayer, secondUserID, secureToken, userCallBack){
        if(inputValidator.validateUserID(secondUserID)){
            var paramArray = {'returnFriends' : false, 'userID' : secondUserID, 'ajaxPlotUserToken' : secureToken};
            ajaxClass.processAjax('GET', "ajaxMyMap.php", paramArray, true, function(returnParam){
                let userToPlot = JSON.parse(returnParam);
                userToPlot.forEach(function(person){
                    //passes them to the myMap class which returns a feature/marker
                    var currentUserMarker = myMap.makeMyMapMarker(person.userID, person.username, person.firstName, person.lastName, person.longitude, person.latitude, person.profilePic);
                    globalUserMarker = currentUserMarker;
                    secondMarkerLayer.addFeatures(currentUserMarker);
                });
                if(userCallBack) userCallBack(secondMarkerLayer);
            });
        }
    }

    plotMarkers(newMarkerLayer, newUserID, secureToken, callback){
        if(inputValidator.validateUserID(newUserID)){
            //Use an AJAX call to acquire the currently logged in users friends as JSON from the DB
            var paramArray = {'returnFriends' : true, 'userID' : newUserID, 'ajaxPlotUserToken' : secureToken};
            var tempMarkerLayer = newMarkerLayer;
            ajaxClass.processAjax('GET', "ajaxMyMap.php", paramArray, true, function(returnParam){
                var usersFriends = JSON.parse(returnParam);
                usersFriends.forEach(function(person){
                    //Only plot fully verified/accepted/complete friendships
                    if(person.friendshipStatus === "2"){
                        var friendMarker = myMap.makeMyMapMarker(person.userID, person.username, person.firstName, person.lastName, person.longitude, person.latitude, person.profilePic);
                        //adds the newly created feature to the temp layer and sends it to plotUser() to add the logged in user last
                        tempMarkerLayer.addFeatures(friendMarker);
                        myMap.plotUser(tempMarkerLayer, newUserID, secureToken, function(returnParam){
                            tempMarkerLayer = returnParam;
                        });
                    }
                });
                //Once completed, callback to the view - return variable is OSM Layer with all markers/features added
                if(callback) callback(tempMarkerLayer);
            });
        }
    }

    storeNewCoords(storedLat, storedLong, currentLong, currentLat, userID, trackingToken){
        if(inputValidator.validateUserID(userID)){
            if (storedLat == currentLong && storedLong == currentLat) {
            }else{
                let newAjaxClass = new AjaxClass();
                if(inputValidator.validateCoordsToStore(userID)){
                    var paramArray = {'lat' : currentLat, 'long' : currentLong, 'id' : userID, 'trackingToken' : trackingToken};
                    newAjaxClass.processAjax('GET', "ajaxCoordUpdating.php", paramArray, true, function(returnParam){
                        console.log(returnParam);
                    });
                }else{
                    console.log("Error: Validation failed, coordinates not stored!");
                }
            }
        }
    }

    //Creates a pop-up add on for each feature of a/the layer
    createMarkerPopUp(feature){
        feature.popup = new OpenLayers.Popup.FramedCloud("pop", feature.geometry.getBounds().getCenterLonLat(),
            null,
            '<div class="markerContent">' + feature.attributes.description + '</div>',
            null,
            true,
            function (){ this.controls['selector'].unselectAll();}
        );
        return feature.popup;
    }

    //Calculates the distance between logged in user and friend using ajax call so distances always accurate/up to date
    distanceCalculator(friendID, userID, secureToken, callback) {
        let paramArray = {'calcDistance': true, 'friendID': friendID, 'userID': userID, 'modalToken': secureToken};
        let returnString = "";
        ajaxClass.processAjax('GET', "ajaxModal.php", paramArray, true, function (returnParam) {
            let returnValue = JSON.parse(returnParam);
            let friend = returnValue[0];
            let loggedInUser = returnValue[1];
            let friendImage = liveSearch.imageCodeGenerator(friend.profilePic);
            let userImage = liveSearch.imageCodeGenerator(loggedInUser.profilePic);
            let distance = myMap.calcDistance(friend.longitude, friend.latitude, loggedInUser.longitude, loggedInUser.latitude);
            returnString = '<tr><td>' + userImage + '</td><td>' + '<-------------------' + '</td><td> ' + distance + 'km' + '</td><td> ' + '------------------->' + '</td><td>' + friendImage + '</td></tr>';
            if(callback) callback(returnString);
        });
    }

    //sub-function of the above, to actually work out the distance between coords - x1y1 is always logged in user, x2y2 is always the chosen friend
    calcDistance(friendLong, friendLat, userLong, userLat){
        let earthRad = 6371; // Radius of the earth in km
        let radLat = myMap.degrees2ToRadians(friendLat-userLat);  //degrees2ToRadians below
        let radLon = myMap.degrees2ToRadians(friendLong-userLong); //degrees2ToRadians below
        let a =
            Math.sin(radLat/2) * Math.sin(radLat/2) +
            Math.cos(myMap.degrees2ToRadians(userLat)) * Math.cos(myMap.degrees2ToRadians(friendLat)) *
            Math.sin(radLon/2) * Math.sin(radLon/2);
        let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        let d = earthRad * c; // Distance in km
        return d.toFixed(3);
    }

    //sub-function of above to convert degrees to radians as Earth is sphere
    degrees2ToRadians(value){
        return value * (Math.PI/180);
    }

    //Error function called when watchPosition() fails/throws an error
    locationError(error){
        console.warn('ERROR(' + error.code + '): ' + error.message);
    }
}