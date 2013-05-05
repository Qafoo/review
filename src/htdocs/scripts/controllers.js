"use strict";

/* Controllers */

angular.module('qaReview.controllers', [])
    .controller('OverviewController', [function() {

    }])
    .controller('SourceController', [function() {

    }])
    .controller('MetricsController', function($scope, Metrics) {
        Metrics.get( function( metrics ) {
            var metrics = new Metric(metrics),
                values = metrics.getTopClasses("cr", 50);

            $scope.metrics = values.metrics;
            $scope.top = values.top;
        });
    });
