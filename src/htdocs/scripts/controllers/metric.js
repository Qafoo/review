var Controller = Controller || {};

Controller.Metric = Controller.Metric || {};

Controller.Metric.Package = function ($scope, Metrics) {
    Metrics.get( function( metrics ) {
        var metric = "cr";

        metrics = new Model.Metric(metrics);

        $scope.packageMetrics = metrics.packageMetrics;

        $scope.metric = metric;
        $scope.artifacts = metrics.getTopPackages(metric);
        $scope.metrics = metrics.packageMetrics;
    });
};
