// Run jQuery in no-conflict mode for WP development.
jQuery( document ).ready(function($) {

    // Initate jscolor.install on widget-added.
    $( document ).on( 'widget-added', function ( event, widget ) {
        // Load jscolor.
        jscolor.install();
    });

    // Initate jscolor.install on widget update(save).
    $( document ).on( 'widget-updated', function ( event, widget ) {
        // Load jscolor.
        jscolor.install();
    });

    /* This check happens all the time, not only on update or add.
        If background color checkbox is checked, then disable background color input. */
    $( document ).on('change','.default_background_color', function (  ) {
        $( 'div#button-color-container input[type="text"]' ).prop( "disabled", this.checked ? true : false );
    });
    // If text color checkbox is checked, then disable text color input.
    $( document ).on('change','.default_text_color', function (  ) {
        $( 'div#button-text-container input[type="text"]' ).prop( "disabled", this.checked ? true : false );
    });

});
