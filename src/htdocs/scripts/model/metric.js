var Model = Model || {};

Model.Metric = function (metrics) {
    this.packageMetrics = metrics.packageMetrics;
    this.classMetrics = metrics.classMetrics;
    this.methodMetrics = metrics.methodMetrics;
    this.values = metrics.metrics;
};

Model.Metric.prototype.getTopPackages = function( metric, count ) {
    var metrics = _.sortBy(
            _.map(
                this.values,
                function ( value, name ) {
                    value.value = value.metrics[metric];
                    value.name  = name;

                    return value;
                }
            ),
            function ( value ) {
                return -value.value;
            }
        ).slice( 0, count || 1024 ),
        top = _.max(
            metrics,
            function ( value ) {
                return value.value;
            }
        );

    return {
        metrics: metrics,
        top: top
    };
};
