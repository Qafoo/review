var Controller = Controller || {};

Controller.Source = function ($scope, $http, $sce) {
    $http.get('/results/source_tree.js').success(function(data) {
        $scope.source = {
            tree: data,
            path: [data[0].name],
            code: null
        };
    });

    $scope.select = function(path, content) {
        $scope.source.path = path;

        if (content) {
            $http.get('/results/' + content).success(function(data) {
                $scope.source.code = $sce.trustAsHtml(
                    prettyPrintOne(
                        data.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'),
                        undefined,
                        true
                    )
                )
            });
        }
    };
};

