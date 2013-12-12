"use strict";

/* Services */

// Demonstrate how to register services
// In this case it is a simple value service.
var services = angular.module('qaReview.services', ['ngResource']);

services.factory('Metrics', function($http) {
    var Metrics = {};
    Metrics.artifacts = {
        metrics: [],
        top: {}
    };

    Metrics.get = function (success) {
        $http
            .get( "/results/pdepend_summary.json", {cache: true} )
            .success( success )
            .error( function( data, status, headers, config ) {
                alert( "Failed fetching JSON results." );
            });
    };

    Metrics.setArtifacts = function( artifacts ) {
        this.artifacts = artifacts;
    };

    Metrics.getArtifacts = function() {
        return this.artifacts;
    };

    return Metrics;
});

services.factory('Source', function($http, $rootScope) {
    var Source = {};
    Source.get = function (success) {
        $http
            .get( "/results/source_tree.js", {cache: true} )
            .success( success )
            .error( function( data, status, headers, config ) {
                alert( "Failed fetching JSON results." );
            });
    };

    return Source;
});

