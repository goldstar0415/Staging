(function() {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('blogpopup', BlogPopup);

  /** @ngInject */
  function BlogPopup() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/map_popups/blog_popup/blog_popup.html',
      scope: {
        post: '='
      },
      controller: BlogPopupController,
      controllerAs: 'BlogPopup',
      bindToController: true
    };

    /** @ngInject */
    function BlogPopupController($scope, $rootScope, $location) {
      var vm = this;
      vm.goto = goto;

      function goto(path) {
          $location.path("/article/" + path);
      }
    }
  }
})();
