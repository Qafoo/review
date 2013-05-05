"use strict";

/* Services */

// Demonstrate how to register services
// In this case it is a simple value service.
angular.module('qaReview.services', ['ngResource'])
    .factory('Metrics', function($resource) {
        return $resource("/results/pdepend_summary.json");
    });


