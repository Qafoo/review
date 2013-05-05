function Metric(metrics) {
    this.classMetrics = metrics.classMetrics,
    this.methodMetrics = metrics.methodMetrics,
    this.values = metrics.metrics;
}

Metric.prototype.getTopClasses = function( metric, count ) {
    var metrics = _.sortBy(
            _.map(
                this.values,
                function ( value, className ) {
                    value.value = value.metrics[metric];
                    value.class = className;

                    return value;
                }
            ),
            function ( value ) {
                return -value.value;
            }
        ).slice( 0, count || 10 ),
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

