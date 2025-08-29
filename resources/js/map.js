!(function (o) {
    Array.prototype.forEach ||
        (o.forEach =
            o.forEach ||
            function (o, e) {
                for (var t = 0, r = this.length; t < r; t++) t in this && o.call(e, this[t], t, this);
            });
})(Array.prototype);
var mapObject,
    marker,
    markers = [],
    markersData = {
        Marker: [
            {
                location_latitude: 48.866024,
                location_longitude: 2.340041,
                imgURL: "https://via.placeholder.com/120x120",
                jobURL: "job-detail.html",
                jobTitle: "Technical Content Writer",
                jobLocation: "London, UK",
                jobType: "Part Time",
            },
            {
                location_latitude: 48.86856,
                location_longitude: 2.349427,
                imgURL: "https://via.placeholder.com/120x120",
                jobURL: "job-detail.html",
                jobTitle: "WordPress Developer",
                jobLocation: "London, UK",
                jobType: "Full Time",
            },
            {
                location_latitude: 48.870824,
                location_longitude: 2.333005,
                imgURL: "assets/img/c11.png",
                jobURL: "job-detail.html",
                jobTitle: "Product Designer",
                jobLocation: "London, UK",
                jobType: "Part Time",
            },
            {
                location_latitude: 48.864642,
                location_longitude: 2.345837,
                imgURL: "https://via.placeholder.com/120x120",
                jobURL: "job-detail.html",
                jobTitle: "IOS App Developer",
                jobLocation: "London, UK",
                jobType: "Part Time",
            },
            {
                location_latitude: 48.861753,
                location_longitude: 2.338402,
                imgURL: "https://via.placeholder.com/120x120",
                jobURL: "job-detail.html",
                jobTitle: "Web & PHP Developer",
                jobLocation: "London, UK",
                jobType: "Part Time",
            },
            {
                location_latitude: 48.872111,
                location_longitude: 2.345151,
                imgURL: "https://via.placeholder.com/120x120",
                jobURL: "job-detail.html",
                jobTitle: "Technical Content Writer",
                jobLocation: "London, UK",
                jobType: "Part Time",
            },
            {
                location_latitude: 48.865881,
                location_longitude: 2.341507,
                imgURL: "https://via.placeholder.com/120x120",
                jobURL: "job-detail.html",
                jobTitle: "Android Developer",
                jobLocation: "London, UK",
                jobType: "Part Time",
            },
            {
                location_latitude: 48.867236,
                location_longitude: 2.34361,
                imgURL: "https://via.placeholder.com/120x120",
                jobURL: "job-detail.html",
                jobTitle: "Technical Content Writer",
                jobLocation: "London, UK",
                jobType: "Part Time",
            },
        ],
    },
    mapOptions = {
        zoom: 15,
        center: new google.maps.LatLng(48.867236, 2.34361),
        mapTypeId: google.maps.MapTypeId.satellite,
        mapTypeControl: !1,
        mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU, position: google.maps.ControlPosition.LEFT_CENTER },
        panControl: !1,
        panControlOptions: { position: google.maps.ControlPosition.TOP_RIGHT },
        zoomControl: !0,
        zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_BOTTOM },
        scrollwheel: !1,
        scaleControl: !1,
        scaleControlOptions: { position: google.maps.ControlPosition.TOP_LEFT },
        streetViewControl: !0,
        streetViewControlOptions: { position: google.maps.ControlPosition.LEFT_TOP },
    };
for (var key in ((mapObject = new google.maps.Map(document.getElementById("map"), mapOptions)), markersData))
    markersData[key].forEach(function (o) {
        (marker = new google.maps.Marker({ position: new google.maps.LatLng(o.location_latitude, o.location_longitude), map: mapObject, icon: "assets/img/marker.png" })),
            void 0 === markers[key] && (markers[key] = []),
            markers[key].push(marker),
            google.maps.event.addListener(marker, "click", function () {
                closeInfoBox(), getInfoBox(o).open(mapObject, this), mapObject.setCenter(new google.maps.LatLng(o.location_latitude, o.location_longitude));
            });
    });
function hideAllMarkers() {
    for (var o in markers)
        markers[o].forEach(function (o) {
            o.setMap(null);
        });
}
function closeInfoBox() {
    $("div.infoBox").remove();
}
function getInfoBox(o) {
    return new InfoBox({
        content:
            '<div class="map-popup-wrap"><div class="map-popup"><div class="jbr-wrap text-left border rounded"><div class="cats-box rounded bg-white d-flex align-items-start justify-content-between px-3 py-4"><div class="cats-box rounded bg-white d-flex align-items-start"><div class="text-center"><img src="' +o.imgURL +'" class="img-fluid" width="45" alt=""></div><div class="cats-box-caption px-2"><h4 class="fs-sm mb-0 ft-medium"><a href="' +o.jobURL +'">' +o.jobTitle +'</a></h4><div class="d-block mb-2 position-relative"><span class="text-muted medium"><i class="lni lni-map-marker mr-1"></i>' +o.jobLocation +'</span><span class="muted medium ml-2 text-warning"><i class="lni lni-briefcase mr-1"></i>' +o.jobType +'</span></div></div></div></div></div></div></div>',
        disableAutoPan: !1,
        maxWidth: 0,
        pixelOffset: new google.maps.Size(10, 92),
        closeBoxMargin: "",
        closeBoxURL: "assets/img/close.png",
        isHidden: !1,
        alignBottom: !0,
        pane: "floatPane",
        enableEventPropagation: !0,
    });
}
function onHtmlClick(o, e) {
    google.maps.event.trigger(markers[o][e], "click");
}
new MarkerClusterer(mapObject, markers[key]);
