/** 
 * custom search javascript functions 
 * 
 * based on scripts from http://www.howtocreate.co.uk/jslibs
 **/

var FS_INCLUDE_NAMES = 0, FS_EXCLUDE_NAMES = 1, FS_INCLUDE_IDS = 2, FS_EXCLUDE_IDS = 3, FS_INCLUDE_CLASSES = 4, FS_EXCLUDE_CLASSES = 5;

function getFormString( formRef, oAndPass, oTypes, oNames ) {
	if( oNames ) {
		oNames = new RegExp((( oTypes > 3 )?'\\b(':'^(')+oNames.replace(/([\\\/\[\]\(\)\.\+\*\{\}\?\^\$\|])/g,'\\$1').replace(/,/g,'|')+(( oTypes > 3 )?')\\b':')$'),'');
		var oExclude = oTypes % 2;
	}
	for( var x = 0, oStr = '', y = false; formRef.elements[x]; x++ ) {
		if( formRef.elements[x].type ) {
			if( oNames ) {
				var theAttr = ( oTypes > 3 ) ? formRef.elements[x].className : ( ( oTypes > 1 ) ? formRef.elements[x].id : formRef.elements[x].name );
				if( ( oExclude && theAttr && theAttr.match(oNames) ) || ( !oExclude && !( theAttr && theAttr.match(oNames) ) ) ) { continue; }
			}
			var oE = formRef.elements[x]; var oT = oE.type.toLowerCase();
			if( oT == 'text' || oT == 'textarea' || ( oT == 'password' && oAndPass ) || oT == 'datetime' || oT == 'datetime-local' || oT == 'date' || oT == 'month' || oT == 'week' || oT == 'time' || oT == 'number' || oT == 'range' || oT == 'email' || oT == 'url' ) {
				oStr += ( y ? ',' : '' ) + oE.value.replace(/%/g,'%p').replace(/,/g,'%c');
				y = true;
			} else if( oT == 'radio' || oT == 'checkbox' ) {
				oStr += ( y ? ',' : '' ) + ( oE.checked ? '1' : '' );
				y = true;
			} else if( oT == 'select-one' ) {
				oStr += ( y ? ',' : '' ) + oE.selectedIndex;
				y = true;
			} else if( oT == 'select-multiple' ) {
				for( var oO = oE.options, i = 0; oO[i]; i++ ) {
					oStr += ( y ? ',' : '' ) + ( oO[i].selected ? '1' : '' );
					y = true;
				}
			}
		}
	}
	return oStr;
}

function recoverInputs( formRef, oStr, oAndPass, oTypes, oNames ) {
	if( oStr ) {
		oStr = oStr.split( ',' );
		if( oNames ) {
			oNames = new RegExp((( oTypes > 3 )?'\\b(':'^(')+oNames.replace(/([\\\/\[\]\(\)\.\+\*\{\}\?\^\$\|])/g,'\\$1').replace(/,/g,'|')+(( oTypes > 3 )?')\\b':')$'),'');
			var oExclude = oTypes % 2;
		}
		for( var x = 0, y = 0; formRef.elements[x]; x++ ) {
			if( formRef.elements[x].type ) {
				if( oNames ) {
					var theAttr = ( oTypes > 3 ) ? formRef.elements[x].className : ( ( oTypes > 1 ) ? formRef.elements[x].id : formRef.elements[x].name );
					if( ( oExclude && theAttr && theAttr.match(oNames) ) || ( !oExclude && ( !theAttr || !theAttr.match(oNames) ) ) ) { continue; }
				}
				var oE = formRef.elements[x]; var oT = oE.type.toLowerCase();
				if( oT == 'text' || oT == 'textarea' || ( oT == 'password' && oAndPass ) || oT == 'datetime' || oT == 'datetime-local' || oT == 'date' || oT == 'month' || oT == 'week' || oT == 'time' || oT == 'number' || oT == 'range' || oT == 'email' || oT == 'url' ) {
					oE.value = oStr[y].replace(/%c/g,',').replace(/%p/g,'%');
					y++;
				} else if( oT == 'radio' || oT == 'checkbox' ) {
					oE.checked = oStr[y] ? true : false;
					y++;
				} else if( oT == 'select-one' ) {
					oE.selectedIndex = parseInt( oStr[y] );
					y++;
				} else if( oT == 'select-multiple' ) {
					for( var oO = oE.options, i = 0; oO[i]; i++ ) {
						oO[i].selected = oStr[y] ? true : false;
						y++;
					}
				}
			}
		}
	}
}

function retrieveCookie( cookieName ) {
	/* retrieved in the format
	cookieName4=value; cookieName3=value; cookieName2=value; cookieName1=value
	only cookies for this domain and path will be retrieved */
	var cookieJar = document.cookie.split( "; " );
	for( var x = 0; x < cookieJar.length; x++ ) {
		var oneCookie = cookieJar[x].split( "=" );
		if( oneCookie[0] == escape( cookieName ) ) { return oneCookie[1] ? unescape( oneCookie[1] ) : ''; }
	}
	return null;
}

function setCookie( cookieName, cookieValue, lifeTime, path, domain, isSecure ) {
	if( !cookieName ) { return false; }
	if( lifeTime == "delete" ) { lifeTime = -10; } //this is in the past. Expires immediately.
	/* This next line sets the cookie but does not overwrite other cookies.
	syntax: cookieName=cookieValue[;expires=dataAsString[;path=pathAsString[;domain=domainAsString[;secure]]]]
	Because of the way that document.cookie behaves, writing this here is equivalent to writing
	document.cookie = whatIAmWritingNow + "; " + document.cookie; */
	document.cookie = escape( cookieName ) + "=" + escape( cookieValue ) +
		( lifeTime ? ";expires=" + ( new Date( ( new Date() ).getTime() + ( 1000 * lifeTime ) ) ).toGMTString() : "" ) +
		( path ? ";path=" + path : "") + ( domain ? ";domain=" + domain : "") + 
		( isSecure ? ";secure" : "");
	//check if the cookie has been set/deleted as required
	if( lifeTime < 0 ) { if( typeof( retrieveCookie( cookieName ) ) == "string" ) { return false; } return true; }
	if( typeof( retrieveCookie( cookieName ) ) == "string" ) { return true; } return false;
}

function get_cookies_array() {

    var cookies = { };

    if (document.cookie && document.cookie != '') {
        var split = document.cookie.split(';');
        for (var i = 0; i < split.length; i++) {
            var name_value = split[i].split("=");
            name_value[0] = name_value[0].replace(/^ /, '');
            //cookies[decodeURIComponent(name_value[0])] = decodeURIComponent(name_value[1]);
            cookies[unescape(name_value[0])] = unescape(name_value[1]);
        }
    }

    return cookies;

}

/** Custom function to save and load search queries **/

function saveQuery() {
    var sName = document.getElementById("nameQuery").value;
    sName='customReport_'+sName;
    setCookie(sName,getFormString(document.forms.searchForm,true),3600000000); /* Looooong valid cookie */
    loadButtons();
 }

 function loadQuery(name) {
    recoverInputs(document.forms.searchForm,retrieveCookie('customReport_'+name),true);
 }

 function deleteQuery(name) {
    setCookie( 'customReport_'+name, "", "delete");
    loadButtons();
 }

 function loadButtons() {
    var cookies = get_cookies_array();
    var container = document.getElementById("loadButtons");
    container.innerHTML = "";

    for(var name in cookies) {
    	searchIndex = /(customReport_)/g;
    	isCustomReportCookie = searchIndex.test( name );
    	
    	if (isCustomReportCookie != false) {
    		
    		name = name.substring(13);
    		
            var load = document.createElement("a");

            load.setAttribute("href","#");
            load.setAttribute("onclick","loadQuery(\'"+name+"\')");
            load.innerHTML = "Load "+name;

            container.appendChild(load);
            
            var span = document.createElement("span");
            span.innerHTML = " | ";
            container.appendChild(span);

            var remove = document.createElement("a");

            remove.setAttribute("href","#");
            remove.setAttribute("onclick","deleteQuery(\'"+name+"\')");
            remove.innerHTML = "Delete "+name;

            container.appendChild(remove);
            container.appendChild(document.createElement("br"));
        }
    }
}
