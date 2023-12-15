# Gravity Perks // Advanced Select // Show Remove Button for Drop Downs
https://gravitywiz.com/documentation/gravity-forms-advanced-select/

Advanced Select will automatically display a remove button for items in a Multi Select field. Use this snippet to activate a remove button for Drop Down fields too.
 
## Instructions

1. Install this snippet with our free [Custom JavaScript](https://gravitywiz.com/gravity-forms-custom-javascript/) plugin.
   
    ```js
    gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {	
        if ( gpadvs.formId == GFFORMID ) {
            settings.plugins.remove_button = {
                title:'Remove this item',
            }
        }
        return settings;
    } );
    ```

2. Add the following CSS via a `<style>` in an HTML field or wherever you include custom CSS.

    ```css
    .gfield--type-select .ts-wrapper.plugin-remove_button .item .remove {
        position: absolute;
        border: 0 !important;
        right: 2rem;
    }

    .gfield--type-select .ts-wrapper.plugin-remove_button .item .remove:hover {
        background: none;
    }
    ```
