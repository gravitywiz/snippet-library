<?php
/**
 * Gravity Wiz // Gravity Forms // Preview Mode Tweaks
 *
 * Instruction Video: https://www.loom.com/share/3a42b0ba7452424c8dbde1fc4988a5d0
 *
 * This snippet allows you to tweak the CSS styles when using Gravity Forms Preview Mode.
 */
add_action( 'gform_preview_footer', function() {
  ?>
  <style type="text/css">

     html {
        min-height: 100%;
     }

     body {
        min-height: 100%;
        background: url( ) /* Background image URL: background: url( http://example.com/wp-content/uploads/2020/12/banner.png )Background image URL */;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center center;
        margin: 0;
     }

     body div#browser_size_info {
        display: none;
     }
    #preview_top {
        display: none;
     }

     div#preview_form_container {
        max-width: 50%;
        border-bottom: 0;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba( 0, 0, 0, 0.2 );
        margin: 2rem auto !important;
     }

     @media screen and (max-width: 800px) {
        div#preview_form_container {
           max-width: none;
           border-bottom: 0;
           border-radius: 4px;
           box-shadow: 0 2px 4px rgba( 0, 0, 0, 0.2 );
           margin: 2rem !important;
        }
     }
  </style>
  <?php
} );
