"use strict";

/* Controllers */

angular.module('qaReview.controllers', [])
    .controller('OverviewController', [function() {

    }])
    .controller('SourceController', [function() {

    }])
    .controller('Metrics/List',     Controller.Metric.List )
    .controller('Metrics/Selector', Controller.Metric.Selector )
    .controller('Metrics/Table',    Controller.Metric.Table )
    .controller('Metrics/Show',     Controller.Metric.Show );
