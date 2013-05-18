 angular.module('morris', []).directive('morrisDonut', function() {
    return {
        restrict: 'E',
        templateUrl: 'templates/morris/chart.html',
        replace: true,
        link: function(scope, element, attrs) {
            var divDisplay = $( "#chart" ).css( "display" ),
                divPosition = $( "#chart" ).css( "position" ),
                divParent = $( "#chart" ).parent();

            $( "#chart" )
                .css( "display", "block" )
                .css( "position", "absolute" )
                .appendTo( $( "body" ) );

            Morris.Donut({
                element: 'chart',
                data: [
                    {label: "Download Sales", value: 12},
                    {label: "In-Store Sales", value: 30},
                    {label: "Mail-Order Sales", value: 20}
                ]
            });

            $( "#chart" )
                .css( "display", divDisplay )
                .css( "position", divPosition )
                .appendTo( divParent );
        }
    };
});
