"use strict";

// Declare app level module which depends on filters, and services
angular.
    module('qaReview', ['qaReview.filters', 'qaReview.services', 'qaReview.directives', 'qaReview.controllers']).
    config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'templates/home.html',
            controller: 'OverviewController'
        });
        $routeProvider.when('/source',{
            templateUrl: 'templates/404.html',
            controller: 'SourceController'
        });
        $routeProvider.when('/metrics',{
            templateUrl: 'templates/metrics.html',
            controller: 'MetricsController'
        });
        $routeProvider.otherwise({
            templateUrl: 'templates/404.html'
        });
    }]);
