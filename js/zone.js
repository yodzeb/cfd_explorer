var toolbox = undefined;
var drawItems = undefined;

function create_form_handler(event) {
    var layer = event.layer;
    var content = getPopupContent(layer);
    if (content !== null) {
	layer.bindPopup(content);
    }
    drawnItems.addLayer(layer);
}

function click_form_handler(event){
    var layers = event.layers,
	content = null;
    layers.eachLayer(function(layer) {
	content = getPopupContent(layer);
	if (content !== null) {
	    layer.setPopupContent(content);
	}
    });
}

function getPopupContent(layer) {
    // Marker - add lat/long
    if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {
	return strLatLng(layer.getLatLng());
	// Circle - lat/long, radius
    } else if (layer instanceof L.Circle) {
	var center = layer.getLatLng(),
	    radius = layer.getRadius();
	return "Center: "+strLatLng(center)+"<br />"
	    +"Radius: "+_round(radius, 2)+" m";
	// Rectangle/Polygon - area
    } else if (layer instanceof L.Polygon) {
	var latlngs = layer._defaultShape ? layer._defaultShape() : layer.getLatLngs(),
	    area = L.GeometryUtil.geodesicArea(latlngs);
	return "Area: "+L.GeometryUtil.readableArea(area, true);
	// Polyline - distance
    } else if (layer instanceof L.Polyline) {
	var latlngs = layer._defaultShape ? layer._defaultShape() : layer.getLatLngs(),
	    distance = 0;
	if (latlngs.length < 2) {
	    return "Distance: N/A";
	} else {
	    for (var i = 0; i < latlngs.length-1; i++) {
		distance += latlngs[i].distanceTo(latlngs[i+1]);
	    }
	    return "Distance: "+_round(distance, 2)+" m";
	}
    }
    return null;
};


function toggle_toolbox() {
    if (!toolbox) {
	console.log("toolbox");
	drawnItems = L.featureGroup().addTo(map);

	toolbox=new L.Control.Draw({
/*	    edit: {
		featureGroup: drawnItems,
		poly: {
		    allowIntersection: false
		}
	    },
*/
	    draw: {
		polygon: {
		    allowIntersection: false,
		    showArea: true
		}
	    }
	});
	
	map.addControl(toolbox);
	map.on(L.Draw.Event.CREATED, create_form_handler );
	map.on(L.Draw.Event.EDITED,  click_form_handler  );
	
    }
    else {
	map.removeControl(toolbox);
	toolbox=undefined;
    }
}
