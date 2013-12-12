var Controller = Controller || {};

Controller.Source = function ($routeParams, $scope, $http, $location, $sce) {
    if (!$scope.source) {
        $scope.source = {
            tree: null,
            path: [],
            code: null
        };
    }
    $scope.source.path = $routeParams.path.split("/");

    if (!$scope.source.tree) {
        // This should be moved into a Service or $rootScope,
        // so that it is not reloaded on every selection
        $http.get('/results/source_tree.js').success(function(data) {
            $scope.source.tree = data;
        });
    }

    $scope.$watchCollection('[source.path, source.tree]', function(source){
        if (!$scope.source.path || !$scope.source.tree) {
            return;
        }

        var findItem = function(tree, path) {
                var item = path.shift();

                for (var i = 0; i < tree.length; ++i) {
                    if (tree[i].name === item) {
                        if (tree[i].children.length > 0) {
                            return findItem(tree[i].children, path);
                        } else {
                            return tree[i];
                        }
                    }
                }

                throw "Did not find item in tree.";
            },
            item = findItem($scope.source.tree, $scope.source.path.slice());

        if (item.content) {
            $http.get('/results/' + item.content).success(function(data) {
                $scope.source.code = $sce.trustAsHtml(
                    prettyPrintOne(
                        data.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'),
                        undefined,
                        true
                    )
                )
            });
        }
    });

    $scope.select = function(path) {
        $location.url( "/source?path=" + path.join("/") );
    };
};

