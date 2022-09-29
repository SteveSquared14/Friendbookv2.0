class AjaxClass{
    constructor() {
        //Set up a new XMLHTTPRequest variable
        this.xml = new XMLHttpRequest();
    }

    //Takes each value of the parameterArray and constructs the necessary URL for XMLHTTPRequest
    prepareURL(newURLAddress, parameterArray){
        var completeUrl = newURLAddress + "?";
        var i = 0;
        for(var paramName in parameterArray){
            if(i === 0){ //If first parameter in array, then no need to prepend a &
                completeUrl += paramName + "=" + parameterArray[paramName];
                i++;
            }else if(i > 0){ //Prepend every subsequent parameter in URL with a &
                completeUrl += "&" + paramName + "=" + parameterArray[paramName];
                i++;
            }
        }
        return completeUrl;
    }

    //Uses the return value/complete URL from the above and executes it. Only returns via callback
    //if readyState is OK (4) AND status is success (200)
    processAjax(method, address, urlParameters, booleanOption, callback){
        let finalisedUrl = this.prepareURL(address, urlParameters);
        this.xml.open(method, finalisedUrl, booleanOption);
        this.xml.send();
        this.xml.onreadystatechange = function(){
            if(this.readyState === 4 && this.status === 200){
                if(callback) callback(this.responseText);
            }
        };
    }
}