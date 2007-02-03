/**
 * This file is used to display the markers above
 * the developers icons on the javascript map.
 *
 * @package pearweb
 * @author  David Coallier <davidc@php.net>
 * @version CVS: $Id: peardev_map.js,v 1.6 2007-02-03 00:28:17 cellog Exp $
 */

var map = document.getElementById('peardev_map');

if (map) {
    var gmapped = GMap2(document.getElementById('peardev_map'));
}
// {{{ var baseIcon
/**
 * The base icon created with
 * google's help ;-)
 */
var baseIcon              = new GIcon();

baseIcon.iconSize         = new GSize(20, 20);
baseIcon.shadowSize       = new GSize(37, 34);
baseIcon.iconAnchor       = new GPoint(0, 0);
baseIcon.infoWindowAnchor = new GPoint(9, 2);
baseIcon.infoShadowAnchor = new GPoint(18, 25);
// }}}
// {{{ public function createMarker
/**
 * Create a marker
 *
 * This function creates a marker with the 
 * picture in pngs/dev.png. It creates
 * a new marker, then return the *object*
 * marker.
 *
 * @todo Change the icon.image variable to the
 *       pearweb installation (s/pearweb/pear.php.net/g)
 *
 * @access public
 * @param  float   point     The point to place the marker
 *
 * @param  string  textvalue The text to display for a user
 *                           in the window above the marker
 *                           when the marker is clicked on.
 *
 * @return object  marker    The new marker with it's position
 *                           properties, etc.
 */
function createMarker(point, textvalue)
{

    var icon   = new GIcon(baseIcon);
    icon.image = 'http://pear.php.net/pngs/dev.png';
    
    var marker = new GMarker(point, icon);

    GEvent.addListener(marker, 'click', function() {
            marker.openInfoWindowHtml(textvalue);
    });
    
    return marker;
}
// }}}
// {{{ public function showFullMap
/**
 * Show the full map
 *
 * This function will take the points
 * set by the map/index.php script
 * process them, make the gmap, attach 
 * it to the peardev_map div, and display
 * on the page.
 *
 * @access public
 */
function showfullmap()
{
    if (GBrowserIsCompatible()) {

        if (!gmapped) {
            var gmapped = new GMap2(document.getElementById('peardev_map'));
        }
        
        gmapped.addControl(new GLargeMapControl());
        gmapped.addControl(new GMapTypeControl());
        gmapped.setCenter (new GLatLng(37.4419, -122.1419), 3);

        for (var i = 0; i < points.length; i++) {
            
            /**
             * The html in the marker
             */
            var email = '';
            var username = points[i][2];
            var page     = points[i][3];
            
            if (!points[i][4]) {
                email = 'Hidden Email';
            } else {
                email = '<a href="mailto: ' + points[i][4] + '">' + points[i][4] + '</a>';
            }
            
            var peardevdesc = "User: "  + username + "<br />";
            peardevdesc    += "Page: <a href='/user/" + page + "'>" + page + "</a><br />";

            var newpoint = new GLatLng(points[i][0], points[i][1]);
            gmapped.addOverlay(createMarker(newpoint, peardevdesc));
        }
    }
}
// }}}
