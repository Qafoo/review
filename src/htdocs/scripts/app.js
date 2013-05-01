"use strict";

// Declare app level module which depends on filters, and services
angular.
    module('qaReview', ['qaReview.filters', 'qaReview.services', 'qaReview.directives', 'qaReview.controllers']).
    config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'templates/home.html',
            controller: 'MyCtrl1'
        });
        $routeProvider.when('/source',{
            templateUrl: 'templates/partial2.html',
            controller: 'MyCtrl2'
        });
        $routeProvider.otherwise({redirectTo: '/'});
    }]);
