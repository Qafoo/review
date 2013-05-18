 angular.module('morris', []).directive('morrisDonut', function() {
    return {
        restrict: 'E',
        templateUrl: 'templates/morris/chart.html',
        scope: {metrics: "=metrics"},
        replace: true,
        link: function(scope, element, attrs) {
            scope.$watch( "metrics", function( metrics ) {
                var divDisplay = $( "#chart" ).css( "display" ),
                    divPosition = $( "#chart" ).css( "position" ),
                    divParent = $( "#chart" ).parent();

                if ( !metrics ) {
                    // if fetched lazy, this might still be undefined.
                    return;
                }

                $( "#chart" )
                    .css( "display", "block" )
                    .css( "position", "absolute" )
                    .appendTo( $( "body" ) );

                Morris.Donut({
                    element: 'chart',
                    data: metrics,
                    formatter: function ( value ) {
                        return value.toFixed( 2 );
                    }
                }).on( "click", function ( index, element ) {
                    console.log( index, element );
                });

                $( "#chart" )
                    .css( "display", divDisplay )
                    .css( "position", divPosition )
                    .appendTo( divParent );
            } );

        }
    };
});
