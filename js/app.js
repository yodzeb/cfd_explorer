var popup;
var map;
var years=[];

function range(start, end, step) {
    if (start > end && step > 0) return [];
    if (start === end) return [start];
    return [start, ...range((start + step), end,step)];
}

function load() {
    var x = document.getElementById("season");
    r = range((new Date()).getFullYear()-1, 1999,-1);
    for (i in r) {
	v=r[i]
	var option = document.createElement("option");
	option.text = v+"-"+(v+1);
	option.value= v;
	x.add(option);
    }

}

var ExcelToJSON = function() {
    
    this.parseExcel = function(file) {
	var reader = new FileReader();
	
	reader.onload = function(e) {
	    var data = e.target.result;
	    var workbook = XLSX.read(data, {
		type: 'binary'
	    });
	    workbook.SheetNames.forEach(function(sheetName) {
		// Here is your object
		var XL_row_object = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
		var json_object = JSON.stringify(XL_row_object);
		convert_data(XL_row_object);
		//console.log(JSON.parse(json_object));
		jQuery( '#xlx_json' ).val( json_object );
	    })
	};

	reader.onerror = function(ex) {
	    console.log(ex);
	};

	reader.readAsBinaryString(file);
    };
};

function handleFileSelect(evt) {
    
    var files = evt.target.files; // FileList object
    var xl2json = new ExcelToJSON();
    xl2json.parseExcel(files[0]);
}

function isFloat(n){
    return Number(n) === n && n % 1 !== 0;
}
function isInt(n){
    return Number(n) === n && n % 1 === 0;
}

function convertCoord(c) {
    if (c && c!==true) {
	//console.log(c);
	if (!isFloat(c) && !isInt(c)) 
	    c = parseFloat(c.replace(",","."));
	if (c > 10000 ) {
	    c = c/1000;
	}
	else if (c > 1000) {
	    c = c/1000;
	}
    }
    if (c == 0 || c == true) {
	return 300;
    }
    return c;
}

function load_flights(/*name, club*/) {
    var name = document.getElementById("pilot_name").value;
    var club = document.getElementById("pilot_club").value;
    var surname = document.getElementById("pilot_surname").value;
    var season  = document.getElementById("season").value;
    var biplace = document.getElementById("pilot_bi").checked;
    var dept    = document.getElementById("pilot_dept").value;
    var params = {
	"season": season,
	"surname": surname,
	"name": name,
	"club": club,
	"bi"  : biplace,
	"dept": dept
    };
    get_cfd_page(params)
}

function get_cfd_page(params) {
    var http = new XMLHttpRequest();
    var url = "cgi/get_xls.php";
    var params = "name="+params["name"]+"&club="+params["club"]+"&surname="+params["surname"]+"&season="+params["season"]+(params["bi"]?"&bi=1":"")+"&dept="+params["dept"];
    document.getElementById("loading").innerHTML = "Chargement en cours...";
    http.open("GET", url+"?"+params, true);
    http.onreadystatechange = function() {//Call a function when the state changes.
	if(http.readyState == 4) {
	    document.getElementById("loading").innerHTML = "";
	    if (http.status == 200) {
		//console.log(http.responseText);
		convert_data(JSON.parse(http.responseText));
	    }
	}
    }
    http.send();
}
var all_pilots = [];
function convert_data(data) {
    var lines = [];
    var avg_lat = 0;
    var avg_lng = 0;
    var id = 0;
    var count = 0;
    
    for (f in data['raw_flights']) {
	var poline2 = [];
	//console.log ("BBB"+
	if (convertCoord(data['raw_flights'][f]["lon BD"]) != 300) {
	    avg_lng = avg_lng + convertCoord(data['raw_flights'][f]["lon BD"]);
	    avg_lat = avg_lat + convertCoord(data['raw_flights'][f]["lat BD"]);
	    count += 1;
	}
	
	poline2.push([convertCoord(data['raw_flights'][f]["lat BD"]), convertCoord(data['raw_flights'][f]["lon BD"]) ])
	poline2.push([convertCoord(data['raw_flights'][f]["lat B1"]), convertCoord(data['raw_flights'][f]["lon B1"]) ]);
	poline2.push([convertCoord(data['raw_flights'][f]["lat B2"]),  convertCoord(data['raw_flights'][f]["lon B2"]) ]);
	if (/\d/.test(data['raw_flights'][f]["lat B3"]) && /\d/.test(data['raw_flights'][f]["lon B3"]) && data['raw_flights'][f]["lat B3"] != 0 && data['raw_flights'][f]["lng B3"] != 0 ) {
	    poline2.push([convertCoord(data['raw_flights'][f]["lat B3"]), convertCoord(data['raw_flights'][f]["lon B3"]) ]);
	}
	poline2.push([convertCoord(data['raw_flights'][f]["lat BA"]),  convertCoord(data['raw_flights'][f]["lon BA"]) ])
	poly = { 'line': poline2,
		 'pilot': data['raw_flights'][f]['pilot'],
		 'date' : data['raw_flights'][f]['date'],
		 "km"   : data['raw_flights'][f]['km'],
	       }
	//lines.push(poline2);
	lines.push(poly);
	id = id+1;
    }
    /*
    sum = "<div onclick=reinit_pilot()>All ( Vols / Total / Max / Moy)</div>";
    all_pilots = [];
    var id=0;
    for (p in data["pilots"]) {
	sum += '<div onmouseover="select_pilot('+id+')" onmouseout="reinit_pilot()" onclick="select_pilot('+id+')" ondblclick="submit_post('+id+')"><span  style="background-color:'+stringToColour(p)+'">&nbsp;&nbsp;&nbsp;</span>&nbsp;';
	sum += p+" (";
	sum += data["pilots"][p]["flights"]+" / "+data["pilots"][p]["sum"]+" / "+data["pilots"][p]["max"]+" / "+data["pilots"][p]["avg"]+" )</div>";
	all_pilots.push(p);
	id+=1;
    }
    */
    sum = "<a href='javascript:reinit_pilot()' onmouseover='javascript:reinit_pilot()'>Voir tous</a><table cellspacing='0' id='pilot_list' class='table table-striped table-bordered table-sm'><thead><tr onclick=reinit_pilot() onmouseover=reinit_pilot()><th class='th-sm'>Pilotes</th><th class='th-sm'>Vols</th><th class='th-sm'>Tot</th><th class='th-sm'>Max</th><th class='th-sm'>Moy</th></tr></thead><tbody>";
    all_pilots = [];
    var id=0;
    for (p in data["pilots"]) {
	    // onmouseout="reinit_pilot()"
	sum += '<tr onmouseover="select_pilot('+id+')"  onclick="select_pilot('+id+')" ondblclick="submit_post('+id+')"><td><span  style="background-color:'+stringToColour(p)+'">&nbsp;&nbsp;&nbsp;</span>&nbsp;';
	sum += p+"</td>";
	sum += "<td>"+data["pilots"][p]["flights"]+"</td><td>"+Math.round(data["pilots"][p]["sum"])+"</td><td>"+Math.round(data["pilots"][p]["max"])+"</td><td>"+data["pilots"][p]["avg"]+"</td></tr>";
	all_pilots.push(p);
	id+=1;
    }
    sum += "</tbody></table>";

    //console.log (avg_lat);
    if (count > 0) {
	avg_lat = avg_lat / count;
	avg_lng = avg_lng / count;
    }
    display_map(lines, avg_lat, avg_lng, sum);
}

var poly_res = [];
function on_Click(e) {
    console.log (e);
    
    for (l in poly_res) {
	//console.log (poly_res[l]["poly"]["_leaflet_id"] );
	if (poly_res[l]["poly"]["_leaflet_id"] == e["sourceTarget"]["_leaflet_id"]) {
	    console.log ("got match");
	    var id=0;
	    var pilot = poly_res[l]["pilot"];
	    var date = poly_res[l]["date"];
	    console.log (poly_res[l]);
	    popup = L.popup()
		.setLatLng([e['latlng']["lat"], e['latlng']["lng"] ])
		.setContent(poly_res[l]["pilot"]+", "+poly_res[l]["km"]+" km le "+date+" <a href=\"javascript:goto_flight('"+pilot+"','"+date+"')\">CFD</a>")
		.openOn(map);
	    
	}
    }
}

var stringToColour = function(str) {
    var hash = 0;
    for (var i = 0; i < str.length; i++) {
	hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }
    var colour = '#';
    for (var i = 0; i < 3; i++) {
	var value = (hash >> (i * 8)) & 0xFF;
	colour += ('00' + value.toString(16)).substr(-2);
    }
    return colour;
}

function set_pilot_table() {
    $('#pilot_list').DataTable({
	"pageLength": 15,
	"columns": [
	    { "type": "html" },
	    { "type": "num-fmt" },
	    { "type": "num-fmt" },
	    { "type": "num-fmt" },
	    { "type": "num-fmt" }
	]
    } );
    $('.dataTables_length').addClass('bs-select');
};


function display_map (lines, x, y, sum) {
    if (lines.length == 0 )
	return;
    // initialize Leaflet
    poly_res = [];
    if (map){
	map.off()
	map.remove();
    }
    document.getElementById("map").innerHTML ="";
    map = L.map('map').setView({lon: y, lat: x}, 5);
    //L.tileLayer.provider('OpenTopoMap').addTo(map);
    
    // add the OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
	maxZoom: 19,
	opacity: 0.5,
	attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
    }).addTo(map);
    
    L.control.scale().addTo(map);

    
    var id = 0;
    for (l in lines ) {
	//console.log (lines[l]);
	discard = false;
	for (aa in lines[l]['line']) {
	    discard = discard || ( lines[l]['line'][aa][0] == 300 );
	}
	if (discard == false) {
	    var text     = lines[l]["km"]+" km le "+lines[l]["date"]+" - "+lines[l]['pilot']+' (click!)';
	    var polyline = L.polyline(lines[l]['line'], {weight:4,color: stringToColour(lines[l]["pilot"])/*'red'*/}).bindTooltip(text).on('click', on_Click);
	    poly_res.push({"poly": polyline, "pilot": lines[l]['pilot'], "display": true, "date": lines[l]['date'], "km": lines[l]['km'] }); 
	    polyline.addTo(map);
	}
	else {
	    console.log ("discard ID "+id);
	}
	id = id + 1;
    }
    document.getElementById("summary").innerHTML = sum;
    set_pilot_table();
}

function select_pilot(pp) {
    for (p in poly_res) {
	if (poly_res[p]["pilot"] != all_pilots[pp]) {
	    map.removeLayer(poly_res[p]["poly"]);
	    poly_res[p]["display"] = false;
	}
	if (poly_res[p]["pilot"] == all_pilots[pp]  && poly_res[p]["display"] == false){
	    map.addLayer(poly_res[p]["poly"]);
	    poly_res[p]["display"] = true;
	}
    }
}

function reinit_pilot() {
    console.log ("reinit")
    for (p in poly_res) {
	if (poly_res[p]["display"] == false ) {
	    map.addLayer(poly_res[p]["poly"]);
	    poly_res[p]["poly"].addTo(map);
	    poly_res[p]["display"] = true;
	}
    }
}

function goto_flight(pilot, date) {
    var http = new XMLHttpRequest();
    var url = "cgi/get_link.php";
    var params = "name="+pilot+"&date="+date;
    http.open("GET", url+"?"+params, true);
    http.onreadystatechange = function() {//Call a function when the state changes.
	if(http.readyState == 4 && http.status == 200) {
	    console.log(http.responseText);
	    if (http.responseText.match(/^\d+$/) ){
		window.open("https://parapente.ffvl.fr/cfd/liste/vol/"+http.responseText,'_blank');
	    }
	}
    }
    http.send();
    
}


function submit_post(pilot, date) {
    url = "https://parapente.ffvl.fr/cfd/selectionner-les-vols";
    var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
	action: url
    }).appendTo(document.body);
    
    pilot=all_pilots[pilot];
    pilot=pilot.replace(/^.*\s+([^\s]+)$/, "$1");
    var biplace = document.getElementById("pilot_bi").checked;
    var season  = document.getElementById('season').value;
    params = {
	"op": "Filtrer",
	"1650-1-19": "parapente",
	"1650-1-18": null,
	"1650-1-7": null,
	"1650-1-5": null,
	"1650-1-4": null,
	"1650-1-3": null,
	"1650-1-0": season,
	"1650-1-8": pilot,
	"1650-1-1"  : date,
	"1650-1-2"  : date,
	"1650-1-6"  : "",
	"1650-1-14[1]": (biplace?"1":"0"),
	
	"form_id" : "requete_filtre_form"
    }
    for (var i in params) {
	//if (params.hasOwnProperty(i)) {
	    $('<input type="hidden" />').attr({
		name: i,
		value: params[i]
	    }).appendTo(f);
	//}
    }

    f.submit();
    f.remove();
}
