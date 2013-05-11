"use strict";

/* Controllers */

angular.module('qaReview.controllers', [])
    .controller('OverviewController', [function() {

    }])
    .controller('SourceController', [function() {

    }])
    .controller('MetricsController', function($scope, Metrics) {
        Metrics.get( function( metrics ) {
            var metric = "cr",
                metrics = new Metric(metrics);

            $scope.packageMetrics = metrics.packageMetrics;

            $scope.metric = metric;
            $scope.artifacts = metrics.getTopPackages(metric);
            $scope.metrics = metrics.packageMetrics;
        });
    });
