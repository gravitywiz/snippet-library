<script type="text/javascript">
    /**
     * Gravity Wiz // Gravity Forms // Disable Submission when Pressing Enter
     * https://gravitywiz.com/disable-submission-when-pressing-enter-for-gravity-forms/
     */
    jQuery(document).on( 'keypress', '.gform_wrapper', function (e) {
        var code = e.keyCode || e.which;
        if ( code == 13 && ! jQuery( e.target ).is( 'textarea,input[type="submit"],input[type="button"]' ) ) {
            e.preventDefault();
            return false;
        }
    } );
</script>
