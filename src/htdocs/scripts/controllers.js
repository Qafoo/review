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
    .controller('Metrics/Package',  Controller.Metric.Package )
    .controller('Metrics/Class',    Controller.Metric.Class )
    .controller('Metrics/Method',   Controller.Metric.Method );
