var Controller = Controller || {};

Controller.Source = function ($routeParams, $scope, Source, $http, $location, $sce) {
    Source.get( function( source ) {
        if (!$scope.source) {
            $scope.source = {
                tree: source,
                path: [],
                code: null
            };
        }

        $scope.source.path = $routeParams.path.split("/");

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
    } );

    $scope.select = function(path) {
        $location.url( "/source?path=" + path.join("/") );
    };
};

