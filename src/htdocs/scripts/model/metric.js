var Model = Model || {};

Model.Metric = function (metrics) {
    this.packageMetrics = metrics.packageMetrics;
    this.classMetrics = metrics.classMetrics;
    this.methodMetrics = metrics.methodMetrics;
    this.values = metrics.metrics;
};

Model.Metric.prototype.getPackages = function( metric ) {
    var metrics = _.map(
            this.values,
            function ( value, packageName ) {
                value.value = value.metrics[metric];
                value.label = packageName;

                return value;
            }
        ),
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

Model.Metric.prototype.getClasses = function( metric ) {

    var metrics = _.flatten(
            _.map(
                this.values,
                function ( value, packageName ) {
                    return _.map(
                        value.classes,
                        function ( value, className ) {
                            value.value = value.metrics[metric];
                            value.label = packageName + "\\" + className;

                            return value;
                        }
                    );
                }
            ),
            true
        ),
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

Model.Metric.prototype.getMethods = function( metric ) {

    var metrics = _.flatten(
            _.map(
                this.values,
                function ( value, packageName ) {
                    return _.flatten(
                        _.map(
                            value.classes,
                            function ( value, className ) {
                                return _.map(
                                    value.methods,
                                    function ( value, methodName ) {
                                        value.value = value.metrics[metric];
                                        value.label = packageName + "\\" + className + "::" + methodName + "()";

                                        return value;
                                    }
                                );
                            }
                        ),
                        true
                    );
                }
            ),
            true
        ),
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
