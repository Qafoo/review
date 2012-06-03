/*global jQuery: false */

jQuery().ready(function() {
    "use strict";

    jQuery( "a.annotate" ).bind( "click", function( e ) {
        jQuery.ajax( {
            type: "POST",
            url:  "/source/annotate",
            data: {
                file:    jQuery( e.target ).data( "file" ),
                line:    jQuery( e.target ).data( "line" ),
                message: jQuery( e.target ).data( "reason" )
            }
        } );

        e.stopPropagation( true );
        return false;
    } );
} );
