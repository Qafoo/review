var Controller = Controller || {};

Controller.Source = function ($scope, $http) {
    $http.get('/results/source_tree.js').success(function(data) {
        $scope.source = {
            tree: data,
            path: [],
            selected: null
        };
    });
};
