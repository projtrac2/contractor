let map;
let markersArray = [];
let polyline = null;
let coordinates = [];

function initMap() {
  map = new google.maps.Map(document.getElementById('map'), {
    center: { lat: 0.633948, lng: 35.048561 },
    zoom: 13,
    mapTypeId: 'satellite'
  });
}