// Magnific Popup example using a link to trigger the modal
// Replace ".open-popup" with your modal trigger
// Replace "82" with the ID of your form
$('.open-popup').magnificPopup({
  type:'inline',
  callbacks: {
    close: function() {
      var gwrf = window.gwrf_82;
      if( typeof gwrf != 'undefined' ) {
        gwrf.reloadForm();
      }
    }
  }
});

// Example of how to bind to an already initialized Magnific Popup
// Replace ".open-popup" with your modal trigger
// Replace "82" with the ID of your form
$('.open-popup').on('mfpClose', function() {
  var gwrf = window.gwrf_82;
  if( typeof gwrf != 'undefined' ) {
    gwrf.reloadForm();
  } 
});
