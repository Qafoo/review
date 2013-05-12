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
    $scope.watch(
        "Metrics.artifacts",
        function ( artifacts, oldArtifacts, $scope ) {
            $scope.artifacts = artifacts;
            $scope.noOfPages = $scope.artifacts.metrics.length / 10;

            $scope.currentPage = 1;
            $scope.maxSize = 5;

            $scope.setPage = function (pageNo) {
                $scope.currentPage = pageNo;
            };
        }
    );
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
