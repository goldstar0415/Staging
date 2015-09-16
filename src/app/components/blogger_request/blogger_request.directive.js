(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('bloggerRequest', bloggerRequest);

  /** @ngInject */
  function bloggerRequest($modal, $rootScope, SignUpService) {
    return {
      restrict: 'A',
      templateUrl: '/app/components/blogger_request/blogger_request.html',
      link: BloggerRequestLink
    };


    /** @ngInject */
    function BloggerRequestLink(scope, element, attrs, ctrl, transclude) {
      element.click(function () {
        if ($rootScope.currentUser) {
          $modal.open({
            templateUrl: 'BloggerRequestModal.html',
            controller: BloggerRequestController,
            controllerAs: 'modal'
          });
        } else {
          SignUpService.openModal('SignUpModal.html');
        }
      });
    }

    /** @ngInject */
    function BloggerRequestController($modalInstance, Post) {
      var vm = this;

      vm.close = function () {
        $modalInstance.close();
      };

      vm.submit = function () {
        Post.request({}, {body: vm.message}, function () {
          toastr.success('Request successfully send');
          vm.close();
        });
      };

    }
  }
})
();
