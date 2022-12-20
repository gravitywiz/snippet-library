window.gform.addAction('gpaa_marker_set', function (opts) {
  var panorama = new google.maps.StreetViewPanorama(
  document.getElementById('gpaa_street_view_container_' + opts.fieldId),
    {
      position: opts.map.getCenter(),
      pov: {
        heading: 34,
        pitch: 10,
      },
    }
  );

  opts.map.setStreetView(panorama);
});



window.gform.addAction('gpaa_map_initialized', function(map, formId, fieldId) {
  var mapSel = '#gpaa_map_container_' + fieldId;
  var streetViewSel = '#gpaa_street_view_container_' + fieldId;

  if (!$(streetViewSel).length) {
    $(mapSel).after('<div id="gpaa_street_view_container_' + fieldId + '" >Select an address to see a street view.</div>');
    var divHeight = '500px';
    $(streetViewSel).css({
      // customize styling properties for the street view container here...
      height: divHeight,
      lineHeight: divHeight,
      backgroundColor: 'lightgray',
      marginTop: '1rem',
      textAlign: 'center',
      verticalAlign: 'middle',
    });
  }
});
