"use strict";

/* Controllers */

angular.module('qaReview.controllers', [])
    .controller('OverviewController', [function() {

    }])
    .controller('SourceController', [function() {

    }])
    .controller('Metrics/Package', Controller.Metric.Package )
    .controller('Metrics/Class', Controller.Metric.Class )
    .controller('Metrics/Method', Controller.Metric.Method );
