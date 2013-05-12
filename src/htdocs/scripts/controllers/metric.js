var Controller = Controller || {};

Controller.Metric = Controller.Metric || {};

Controller.Metric.List = function ($scope) {
    $scope.artifactList = [
        {"link": "package",
         "name": "Packages"},
        {"link": "class",
         "name": "Classes"},
        {"link": "method",
         "name": "Methods"}
    ];
};

Controller.Metric.Table = function( $scope, Metrics ) {
    var count = 10,
        refresh = function(scope) {
            var artifacts = Metrics.artifacts.metrics.sort( function ( a, b ) {
                    return ( a[scope.sortingColumn] - b[scope.sortingColumn] ) *
                        ( scope.ascending ? -1 : 1 );
                } );

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

            scope.$parent.selection = artifacts.slice(
                ( scope.currentPage - 1 ) * count,
                ( scope.currentPage ) * count
            );
        };

    $scope.currentPage = 1;
    $scope.sortingColumn = "name";
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

Controller.Metric.Package = function ($scope, Metrics) {
    Metrics.get( function( metrics ) {
        var metric = "cr";

        metrics = new Model.Metric(metrics);

        $scope.metric = metric;
        $scope.artifacts = metrics.getPackages(metric);
        $scope.metrics = metrics.packageMetrics;

        Metrics.setArtifacts( $scope.artifacts );
    });
};

Controller.Metric.Class = function ($scope, Metrics) {
    Metrics.get( function( metrics ) {
        var metric = "cr";

        metrics = new Model.Metric(metrics);

        $scope.metric = metric;
        $scope.artifacts = metrics.getClasses(metric);
        $scope.metrics = metrics.classMetrics;

        Metrics.setArtifacts( $scope.artifacts );
    });
};

Controller.Metric.Method = function ($scope, Metrics) {
    Metrics.get( function( metrics ) {
        var metric = "ccn";

        metrics = new Model.Metric(metrics);

        $scope.metric = metric;
        $scope.artifacts = metrics.getMethods(metric);
        $scope.metrics = metrics.methodMetrics;

        Metrics.setArtifacts( $scope.artifacts );
    });
};
