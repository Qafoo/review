var Controller = Controller || {};

Controller.Source = function ($routeParams, $scope, Source, $http, $location, $sce, $timeout, $anchorScroll) {
    Source.get( function( source ) {
        if (!$scope.source) {
            $scope.source = {
                tree: source,
                path: [],
                code: null
            };
        }

        $scope.source.path = $routeParams.path.replace(/^\//, '').split("/");

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
                var highlightedSource = prettyPrintOne(
                        data.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'),
                        undefined,
                        true
                    ),
                    lineNum = 1;

                // Add line number anchors
                highlightedSource = $(highlightedSource).find("li").each(function(key, element) {
                    $(element).attr("id", "L" + lineNum++);
                }).end().get(0).outerHTML;

                $scope.source.code = $sce.trustAsHtml(highlightedSource);

                $timeout(function() {
                    $('#' + $location.hash()).addClass("selected");
                    $anchorScroll();
                }, 0);
            });
        }
    } );

    $scope.select = function(path) {
        $location.url( "/source?path=" + path.join("/") );
    };
};

