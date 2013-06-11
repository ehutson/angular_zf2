'use strict';

var app = angular.module('application', ["ngResource"]);

app.config(function($routeProvider) {
    $routeProvider.when('/', {
        templateUrl: 'application/list.html',
        controller: 'ListController'
    });
    $routeProvider.otherwise({redirectTo: '/'});
});


app.directive('sorted', function() {
    return {
        scope: true,
        transclude: true,
        template: '<a ng-click="do_sort()" ng-transclude></a>' +
            '<span ng-show="do_show(true)">&nbsp;<i class="icon-chevron-down"></i></span>' +
            '<span ng-show="do_show(false)">&nbsp;<i class="icon-chevron-up"></i></span>',
        controller: function($scope, $element, $attrs) {
            $scope.sort = $attrs.sorted;
            $scope.do_sort = function() {
                $scope.sort_by($scope.sort);
            };
            $scope.do_show = function(asc) {
                return (asc !== $scope.sort_desc) && ($scope.sort_order === $scope.sort);
            };
        }
    };
});

app.factory('PostItem', function($resource) {
    return $resource('/AngularTable/public/api/:id', {id: '@id'}, {update: {method: 'PUT'}, query: {method: 'GET', isArray: false}});
});


app.controller('ListController', function($scope, $location, PostItem) {
    $scope.title = "Posts";

    $scope.sql = '';
    $scope.query = '';
    $scope.sort_order = "id";
    $scope.sort_desc = false;
    $scope.page_size = 15;
    $scope.page_number = 1;
    $scope.total_count = 0;

    $scope.search = function() {
        PostItem.query({q: $scope.query, size: $scope.page_size, page: $scope.page_number, 
            sort_order: $scope.sort_order, sort_desc: $scope.sort_desc}, function(posts) {
            $scope.posts = posts.data;
            $scope.total_count = posts.count;
            $scope.sql = posts.sql;
        });
    };

    $scope.hasNext = function() {
        return ((($scope.page_number + 1) * $scope.page_size) < $scope.total_count);
    }

    $scope.next = function() {
        $scope.page_number++;
        $scope.search();
    }

    $scope.hasPrev = function() {
        return $scope.page_number > 1;
    }

    $scope.prev = function() {
        if ($scope.page_number > 1) {
            $scope.page_number--;
        } else {
            $scope.page_number = 1;
        }
        $scope.search();
    }

    $scope.reset = function() {
        $scope.page_size = 15;
        $scope.posts = [];
        $scope.search();
    };

    $scope.sort_by = function(ord) {
        if ($scope.sort_order === ord) {
            $scope.sort_desc = !$scope.sort_desc;
        } else {
            $scope.sort_desc = false;
        }
        $scope.sort_order = ord;
        $scope.reset();
    };

    $scope.reset();

});