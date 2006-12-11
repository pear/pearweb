function showmap() {
    if (GBrowserIsCompatible()) {
        if (!map) {
            var map = new GMap2(document.getElementById("map"));
        }


        GEvent.addListener(map, "dblclick", function() {
                var center = map.getCenter();
                var latitude  = center.y.toFixed(10);
                var longitude = center.x.toFixed(10);
                document.getElementById("latitude").value = latitude;
                document.getElementById('longitude').value = longitude;
                pearweb.hide_div();

                });

        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());

        lat = document.getElementById('latitude').value;
        lng = document.getElementById('longitude').value;

        if (lat.length > 5 && lng.length > 5) {
            var point = new GLatLng(lat, lng);
            var icon = new GIcon();
            icon.image = 'http://pear.php.net/gifs/pearpoint.gif';
            icon.iconSize = new GSize(15, 20);
            icon.shadowSize = new GSize(25, 30);
            icon.iconAnchor = new GPoint(0, 0);

            map.setCenter(point, 1);
            map.addOverlay(new GMarker(point, icon));
        } else {
            map.setCenter(new GLatLng(37.4419, -122.1419), 1);
        }
    }
}
