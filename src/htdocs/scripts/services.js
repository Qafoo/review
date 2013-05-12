"use strict";

/* Services */

// Demonstrate how to register services
// In this case it is a simple value service.
angular.module('qaReview.services', ['ngResource'])
    .factory('Metrics', function($http, $rootScope) {
        var Metrics = {};
        Metrics.artifacts = {
            metrics: [],
            top: {}
        };

        Metrics.get = function (success) {
            $http
                .get( "/results/pdepend_summary.json" )
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


