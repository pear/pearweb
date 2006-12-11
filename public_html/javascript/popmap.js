// {{{ Disclaimer
/**
 * @author: David Coallier <davidc@php.net>
 * @package pearweb
 * @version $Id: popmap.js,v 1.1 2006-12-11 21:02:55 arnaud Exp $
 */
// }}}
// {{{ Browsers detection.
var IE      = navigator.userAgent.indexOf("MSIE")       != -1;
var Opera   = navigator.userAgent.indexOf("Opera")      != -1;
var Mozilla = navigator.userAgent.indexOf("Mozilla")    != -1;
var Safari  = navigator.userAgent.indexOf("Safari")     != -1; 
// }}}
// {{{ Compatibility
/**
 * Compatibility with elder browsers.
 */
if (!String.fromCharCode) {
    String.prototype.fromCharCode = function(code) {
        var h = code.toString(16);
        if (h.length == 1) {
            h = '0' + h;
        }
        return unescape('%' + h);
    }
}
if (!String.charCodeAt) {
    String.prototype.charCodeAt = function(str) {
        for (i = 1, h; i < 256; i++) {
            if (String.fromCharCode(i) == str.charAt(i)) {
                return i;
            }
        } 
    }
}
// http://www.crockford.com/javascript/remedial.html
if (!Array.splice) {
    Array.prototype.splice = function(s, d) {
        var max = Math.max,
        min = Math.min,
        a = [], // The return value array
        e,  // element
        i = max(arguments.length - 2, 0),   // insert count
        k = 0,
        l = this.length,
        n,  // new length
        v,  // delta
        x;  // shift count

        s = s || 0;
        if (s < 0) {
            s += l;
        }
        s = max(min(s, l), 0);  // start point
        d = max(min(typeof d == 'number' ? d : l, l - s), 0);    // delete count
        v = i - d;
        n = l + v;
        while (k < d) {
            e = this[s + k];
            if (!e) {
                a[k] = e;
            }
            k += 1;
        }
        x = l - s - d;
        if (v < 0) {
            k = s + i;
            while (x) {
                this[k] = this[k - v];
                k += 1;
                x -= 1;
            }
            this.length = n;
        } else if (v > 0) {
            k = 1;
            while (x) {
                this[n - k] = this[l - k];
                k += 1;
                x -= 1;
            }
        }
        for (k = 0; k < i; ++k) {
            this[s + k] = arguments[k + 2];
        }
        return a;
    }
}
if (!Array.push) {
    Array.prototype.push = function() {
        for (var i = 0, startLength = this.length; i < arguments.length; i++) {
            this[startLength + i] = arguments[i];
        }
        return this.length;
    }
}
if (!Array.pop) {
    Array.prototype.pop = function() {
        return this.splice(this.length - 1, 1)[0];
    }
}
// }}}
// {{{ variables
var matchCounter = new Array();
// }}}
// {{{ Anonymous class pearweb
// Configuration:
var pearweb = {};
// }}}
// {{{ pearweb functions
// {{{ function pearweb
/**
 * Onload function..  this starts everything up once the page has finished loading
 */
pearweb.pearweb = function() {
	pearweb.create_map_div();
};
// {{{ function display_map
/**
 * Display the map
 */
pearweb.display_map = function (e) {
	//ad_data = pearweb.get_ad_data(keyword);
    
	eventX = IE ? event.clientX : e.clientX
	eventY = IE ? event.clientY : e.clientY

	popup = document.getElementById('pearweb_map');

	var content = '';

	var left = IE ? eventX + document.body.scrollLeft : e.pageX - window.pageXOffset;
	var top  = IE ? document.body.scrollTop  : e.pageY - window.pageYOffset;
    
    var maxY = IE ? document.body.clientHeight : window.innerHeight;
    var maxX = IE ? document.body.clientWidth  : window.innerWidth; 
    
    var maxDivWidth  = new Number('410');
    var maxDivHeight = new Number('410');

    if (IE) {
        if (eventY <= (maxDivHeight)) {
            top = top;
        } else {
            if ((maxY/(top)) < 0.3) {
                top -= maxDivHeight;
            } else {
                top -= maxDivHeight;
            } 
        }
        if ((left + maxDivWidth) > maxX) {
            left = ((maxX-maxDivWidth)-30);
        } else {
            if ((maxX < (left+maxDivWidth)) && (maxX < 410)) {
                left = ((maxX-maxDivWidth) / 2);
            }
        }
        top = top - maxDivHeight;
    } else {
        if ((top + maxDivHeight) > maxY) {
            top = (maxY-maxDivHeight)-10;
        }
        
        if ((left + maxDivWidth) > maxX) {
            left = ((maxX - maxDivWidth)-30); 
        } else {
            if ( (maxX < (left+maxDivWidth)) && (maxX < 480)) {
                left = ( ( maxX - maxDivWidth) / 2);
            }
        }
    }

	var position = IE ? "absolute" : "fixed";
	var html = '<div class="header" style="background-color: #babdb6; text-align: right; border: 1px solid black;width: 410px; height: 350px;';
        //html += ' display:inline; ';
        html += 'position:' + position + '; left:'+left+'px; top:'+top+'px;">';
		html += '<table width="100%" height="5%">';
		html += ' <tr><td style="text-position: left;">Double click to choose location<a href="#" style="background-color: gray; color: white;" onclick="pearweb.hide_div();">[X]</a></td></tr>';
		html += '</table><div id="map" class="map" style="width: 100%; height: 95%">\n';
		html += '</div>';
        html += '<div id="message"></div>';
        html += '</div>';
	popup.innerHTML = html;

	if (window.opera) {
		popup.style.position="absolute";
	}

	popup.style.visibility="visible";
	popup.style.display="";
};
// }}}
// {{{ function hide_ad
/**
 * Hide the ad
 */
pearweb.hide_ad = function(delay) {
	if(delay) {
		hide_timer = setInterval('pearweb.hide_div();',delay);
	} else {
		pearweb.hide_div();
	}
};
// }}}
// {{{ function hide_div
pearweb.hide_div = function() {
    if (window.opera) {
    	popup.style.position="fixed";
    }
    popup = document.getElementById('pearweb_map');
    popup.style.visibility="hidden";
    popup.display = 'none';
    GUnload();
};
// }}}

// {{{  function create_map_div
pearweb.create_map_div = function() {    
	var div = document.createElement('div');
	div.setAttribute('id', 'pearweb_map');
    div.setAttribute('style', 'position:absolute;visibility:hidden;');
	document.body.appendChild(div);
};
// }}} 
// {{{ window events handling
// Trigger the highlight using the onload handler.
if (window.attachEvent) {
	window.attachEvent('onload', pearweb.pearweb);
} else if (window.addEventListener) {
	window.addEventListener('load', pearweb.pearweb, true);
} else {
	var __onload = window.onload;
	window.onload = function() {
		pearweb.pearweb();
		__onload();
	};
}
// }}}
