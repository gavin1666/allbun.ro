/*
 * YO Cookie
 * Version:           1.0.0 - 32132
 * Author:            Yo Cookie Team (YGT)
 * Date:              05/05/2018
 */

 
(function() {
    var cookieName = "yo-cookies-accept",
        debugCookieBlock = 0;

    var blockAcceptCookies = document.getElementById("blockAcceptCookies");

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    function setCookie() {
        document.cookie = cookieName + "=1; expires=Thu, 18 Dec 2021 12:00:00 UTC; path=/";
        blockAcceptCookies.style.display = "none";
    }

    var buttonAcceptCookies = document.getElementById("buttonAcceptCookies");

    buttonAcceptCookies.onclick = function() {
        setCookie();
        if (debugCookieBlock) console.log('buttonAcceptCookies click');
    };

    var opacityValue = 1;

    blockAcceptCookies.onmouseover = function(){
        opacityValue = blockAcceptCookies.style.opacity;
        blockAcceptCookies.style.opacity = 1;
    }

    blockAcceptCookies.onmouseout = function(){
        blockAcceptCookies.style.opacity = opacityValue;
    }

    var accept_cookies = getCookie(cookieName);

    if (accept_cookies == "1" && yoCookieData.forcedShow == 0) {
        if (debugCookieBlock) console.log('blockAcceptCookies is now hided');
    } else {
        blockAcceptCookies.style.display = "block";
        if (debugCookieBlock) console.log('blockAcceptCookies is now showing');
    }

})();
