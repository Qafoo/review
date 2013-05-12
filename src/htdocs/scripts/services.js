"use strict";

/* Services */

// Demonstrate how to register services
// In this case it is a simple value service.
angular.module('qaReview.services', ['ngResource'])
    .factory('Metrics', function($http, $rootScope) {
        var Metrics = function () {
            this.artifacts = {
                metrics: [],
                top: {}
            };
        };

        Metrics.prototype.get = function (success) {
            $http
                .get( "/results/pdepend_summary.json" )
                .success( success )
                .error( function( data, status, headers, config ) {
                    alert( "Failed fetching JSON results." );
                });
        };

        Metrics.prototype.setArtifacts = function( artifacts ) {
            this.artifacts = artifacts;
        };

        Metrics.prototype.getArtifacts = function() {
            return this.artifacts;
        };

        return new Metrics();
    });


