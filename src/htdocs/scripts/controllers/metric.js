var Controller = Controller || {};

Controller.Metric = Controller.Metric || {};

Controller.Metric.List = function ($scope) {
    $scope.artifactList = [
        {"link": "package/cr",
         "name": "Packages"},
        {"link": "class/cr",
         "name": "Classes"},
        {"link": "method/ccn",
         "name": "Methods"}
    ];
};

Controller.Metric.Table = function( $scope, Metrics ) {
    var count = 10;

    var refresh = function(scope) {
        var artifacts = Metrics.artifacts.metrics,
            selection = artifacts.slice( 0 );

        scope.lastPage    = Math.ceil( artifacts.length / count );

        scope.pages = [];
        scope.pages.push({
            number:    Math.max( 1, scope.currentPage - 1 ),
            text:      "←",
            active:    false,
            disabled:  false
        });
        for ( var page = 1; page <= scope.lastPage; page++ ) {
            scope.pages.push({
                number:    page,
                text:      page,
                active:    false,
                disabled:  false
            });
        }
        scope.pages.push({
            number:    Math.min( scope.lastPage, scope.currentPage + 1 ),
            text:      "→",
            active:    false,
            disabled:  false
        });

        _.each( scope.pages, function( value ) {
            value.active = value.number == scope.currentPage;
        } );

        selection.sort( function ( a, b ) {
            return ( ( a[scope.sortingColumn] == b[scope.sortingColumn] ) ? 0 :
                    ( ( a[scope.sortingColumn] > b[scope.sortingColumn] ) ? 1 : -1 ) ) *
                ( scope.ascending ? 1 : -1 );
        } );

        scope.selection =  selection.slice(
            ( scope.currentPage - 1 ) * count,
            ( scope.currentPage ) * count
        );
    };

    $scope.currentPage = 1;
    $scope.sortingColumn = "value";
    $scope.ascending = false;

    $scope.$watch(
        function () {
            return Metrics.artifacts.metrics.length;
        },
        function ( newValue, oldValue, scope ) {
            refresh( scope );
        }
    );

    $scope.$watch(
        "currentPage",
        function ( newValue, oldValue, scope ) {
            refresh( scope );
        }
    );

    $scope.$watch(
        "sortingColumn + ascending",
        function ( newValue, oldValue, scope ) {
            refresh( scope );
        }
    );

    $scope.setPage = function ( pageNo ) {
        $scope.currentPage = pageNo;
    };

    $scope.setSorting = function ( column, ascending ) {
        $scope.sortingColumn = column;
        $scope.ascending = ascending;
    };
};

Controller.Metric.Selector = function ($scope, $location) {
    $scope.change = function() {
        $location.url( "/metrics/" + $scope.artifact + "/" + $scope.metric );
    };
};

Controller.Metric.Show = function ($scope, $routeParams, Metrics) {
    Metrics.get( function( metrics ) {
        metrics = new Model.Metric(metrics);

        $scope.artifact = $routeParams.artifact;
        $scope.metric = $routeParams.metric;

        switch ( $scope.artifact ) {
            case "package":
                $scope.artifacts = metrics.getPackages($routeParams.metric);
                $scope.metrics = metrics.packageMetrics;
                break;

            case "class":
                $scope.artifacts = metrics.getClasses($routeParams.metric);
                $scope.metrics = metrics.classMetrics;
                break;

            case "method":
                $scope.artifacts = metrics.getMethods($routeParams.metric);
                $scope.metrics = metrics.methodMetrics;
                break;

            default:
                throw "Unknown artifact: " + $scope.artifact;
        }

        Metrics.setArtifacts( $scope.artifacts );
    });
};
