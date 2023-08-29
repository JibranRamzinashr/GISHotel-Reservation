<?php
include "functions.php";
if(isset($_POST["addHotel"])) {
    if(add($_POST) <= 0) {
        echo "<script>alert('Error')</script>";
    }
}
$_GET=
$places = query("SELECT * FROM places");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <style>
        #map {
            height: 100%;
            
        }
        .custom-map-control-button {
            background-color: #fff;
            border: 0;
            border-radius: 2px;
            box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
            margin: 10px;
            padding: 0 0.5em;
            /* font: 400 18px Roboto, Arial, sans-serif; */
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            height: 40px;
            width: 40px;
            cursor: pointer;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .pac-card {
            background-color: #fff;
            position: absolute;
            left: 10%;
            top: 50%;
            transform: translate(-50%, -50%);
            border: 0;
            border-radius: 2px;
            box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
            padding: 0 0.5em;
            margin: 10px;
            font: 400 18px Roboto, Arial, sans-serif;
            overflow: hidden;
            font-family: Roboto;
            width: 300px;
            padding: 0;
        }
        #pac-container {
            padding-bottom: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #pac-input {
            background-color: #fff;
            font-family: Roboto;
            font-size: 15px;
            font-weight: 300;
            padding: 0 11px 0 13px;
            text-overflow: ellipsis;
            width: 70%;
            height: 40px;
            outline: none;
        }
        #pac-input:focus {
            border-color: #FF0000;
        }

        #title {
            color: #fff;
            background-color: #FF0000;
            font-size: 25px;
            font-weight: 500;
            padding: 6px 12px;
            box-sizing: border-box;
            width: 100%;
        }
        #pac-container label {
            width: 80%;
            color: grey;
        }
        #button {
            background-color: #FF0000;
            color: white;
            border: 0;
            padding: 10px;
            box-sizing: border-box;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
        }
        #editMarker {
            position: fixed;
            width: 30px;
            height: 40px;
            z-index: 999;
            display: none;
        }

        form {
            display: flex;
            flex-direction: column;
        }
        form button {
            align-self: center;
        }
    </style>
</head>
<body>
    <img src="hotel-loc.png" id="editMarker" draggable="false">
    <div id="map"></div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBq5_gCfn5vlroLlUUdiY7BnkXumIql3tU&callback=initMap&libraries=places&v=weekly" defer></script>
    <script>
        let map;
        let service;
        let infoWindow;
        let marker;
        let directionsService;
        let directionsRenderer;

        const geoLocationOptions = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0,
        }
        let isAddingPlace =  false;
        const editMarker = document.getElementById("editMarker");
        const places = <?php echo json_encode($places) ?>;

        document.addEventListener("mousemove", e => {
            if(!isAddingPlace)
                return;
            editMarker.style.left = e.pageX + 1.5 + "px";
            editMarker.style.top = e.pageY + 1.5 + "px";
        })

        function initMap() {
          const cikarang = new google.maps.LatLng(-6.284580222282055, 107.17083907811998);
          const card = document.createElement("div");
          card.className = "pac-card";
          card.innerHTML = `
            <div id="pac-container">
                <div id="title">Controls</div>
                <br>
                <button id="button" style="font-size: 25px" onclick="addHotel(event)">Add Hotel Location</button>
                <button id="button" style="font-size: 25px; background-color: tomato; display: none;" onclick="cancelPlace(event)">Cancel Location</button>
            </div>
          `
        
          infoWindow = new google.maps.InfoWindow();
          map = new google.maps.Map(document.getElementById("map"), {
            center: cikarang,
            zoom: 15,
          });
          marker = new google.maps.Marker({
            map,
            position: cikarang,
            visible: false
          });
          directionsService = new google.maps.DirectionsService();
          directionsRenderer = new google.maps.DirectionsRenderer();
          directionsRenderer.setMap(map);


          // Widgets
          const locationButton = document.createElement("button");
          const locationImage = document.createElement("img");
          locationImage.width = 40;
          locationImage.height = 40;
          locationImage.src = "https://media.istockphoto.com/id/1261917621/vector/map-pin-icon-for-your-web-site-and-mobile-app.jpg?s=612x612&w=0&k=20&c=kpqmtnYMH1A1Oyn39shNEbwyhn9gYc1tFsMY2B9Xodo=";
          
          locationButton.append(locationImage);
          locationButton.classList.add("custom-map-control-button");
          map.controls[google.maps.ControlPosition.TOP_RIGHT].push(locationButton);
          locationButton.addEventListener("click", () => {
                // Try HTML5 geolocation.
                if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        google.maps.event.addListener(marker, "click", () => {
                            infoWindow.setPosition(pos);
                            infoWindow.setContent("Your current location.");
                            infoWindow.open(map);
                         })
                        map.setCenter(pos);
                        marker.setPosition(pos);
                        marker.setVisible(true);
                    },
                    () => {
                        handleLocationError(true, infoWindow, map.getCenter());
                    }, 
                    geoLocationOptions
                );
                } else {
                    // Browser doesn't support Geolocation
                    handleLocationError(false, infoWindow, map.getCenter());
                }
            })
            
            map.addListener("click", (mapsMouseEvent) => {
                if(!isAddingPlace)
                    return;
                const pos = mapsMouseEvent.latLng.toJSON();
                console.log(pos);
                infoWindow.setPosition(pos);
                infoWindow.setContent(`
                    <form action="" method="POST">
                        <input type="hidden" name="lat" value="${pos.lat}">
                        <input type="hidden" name="lng" value="${pos.lng}">

                        <label for="title">Place name: </label>
                        <input type="text" name="name">
                        <br>
                        
                        <label for="title">Ratings: </label>
                        <span><input type="number" name="ratings" min="0" max="5">/5</span>
                        <br>
                        <button id="button" name="addHotel" type="submit">Add</button>
                    </form>
                    `);
                infoWindow.open(map);
            });

            map.controls[google.maps.ControlPosition.RIGHT_CENTER].push(card);
            // const autocomplete = new google.maps.places.Autocomplete(autcompleteInput, autocompleteOptions);
            // autocomplete.bindTo("bounds", map);
            findNearbyHotel();
            if(places)
                places.forEach(place => {
                    const obj = {
                        geometry: {
                            location: new google.maps.LatLng(place.lat, place.lng)
                        },
                        name: place.name,
                        ratings: place.ratings
                    }
                    console.log(obj)
                    createMarker(obj);
                });
        }

        function addHotel(e) {
            console.log("ADDING");
            e.target.nextElementSibling.style.display = "block";
            e.target.style.display = "none";
            isAddingPlace = true;
            editMarker.style.display = "block"
        }
        
        function cancelPlace(e) {
            e.target.previousElementSibling.style.display = "block";
            e.target.style.display = "none";
            isAddingPlace = false;
            editMarker.style.display = "none"
        }

        function createMarker(place) {
            if (!place.geometry || !place.geometry.location)
                return;
            console.log("creating markers");
            const marker = new google.maps.Marker({
                map,
                position: place.geometry.location,
            });

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const { lat, lng } = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    console.log(position);
                    const rad = function(x) {
                        return x * Math.PI / 180;
                    };

                    const getDistance = function(p1, p2) {
                        var R = 6378137; // Earth’s mean radius in meter
                        var dLat = rad(p2.lat() - p1.lat);
                        var dLong = rad(p2.lng() - p1.lng);
                        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                            Math.cos(rad(p1.lat)) * Math.cos(rad(p2.lat())) *
                            Math.sin(dLong / 2) * Math.sin(dLong / 2);
                        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        var d = R * c;
                        return d; // returns the distance in meter
                    };
                    const distance = getDistance({ lat, lng }, place.geometry.location);
                    
                    google.maps.event.addListener(marker, "click", () => {
                        infoWindow.setPosition(place.geometry.location);
                        infoWindow.setContent(`
                        <b>${place.name}</b> 
                        <br>
                        <span>Rating ${place.ratings || 0}/5 </span>
                        <br>
                        <span>${Math.floor(distance)} meters away from you.</span>
                        <br>
                        <button id="button" onclick="getDirection({ lat: ${lat}, lng: ${lng} }, { lat: ${place.geometry.location.lat()}, lng: ${place.geometry.location.lng()} })">Get Directions</button>
                        <button id="button" onclick="">Delete</button>
                        `);
                        infoWindow.open(map);
                    },
                    geoLocationOptions
                    );

                },
                () => {
                    handleLocationError(true, infoWindow, map.getCenter());
                },
                geoLocationOptions
            );
        }

        function findNearbyHotel() {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    var request = {
                        location: pos,
                        radius: '3000',
                        type: ['hotel']
                    };
            
                    service = new google.maps.places.PlacesService(map);
                    service.nearbySearch(request, nearbySearchCallback);
                },
                () => {
                    handleLocationError(true, infoWindow, map.getCenter());
                },
                geoLocationOptions
            );
        }
        function getDirection(p1, p2) {
            directionsService
            .route({
                origin: {
                    query: `${p1.lat},${p1.lng}`
                },
                destination: {
                    query: `${p2.lat},${p2.lng}`
                },
                travelMode: google.maps.TravelMode.DRIVING,
            })
            .then((response) => {
                directionsRenderer.setDirections(response);
            })
            .catch((e) => window.alert("Directions request failed due to " + e));
        }

        function nearbySearchCallback(results, status) {
            if (status == google.maps.places.PlacesServiceStatus.OK) {
                for (var i = 0; i < results.length; i++) {
                    createMarker(results[i]);
                }
            }
        }
        function handleLocationError(browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos);
            infoWindow.setContent(
                browserHasGeolocation
                ? "Error: The Geolocation service failed."
                : "Error: Your browser doesn't support geolocation."
            );
            infoWindow.open(map);
        }

        
        window.initMap = initMap;
    </script>
</body>
</html>