(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('bloggerRequest', bloggerRequest);

  /** @ngInject */
  function bloggerRequest($modal) {
    return {
      restrict: 'A',
      templateUrl: '/app/components/blogger_request/blogger_request.html',
      link: BloggerRequestLink
    };


    /** @ngInject */
    function BloggerRequestLink(scope, element, attrs, ctrl, transclude) {
      element.click(function () {
        $modal.open({
          templateUrl: 'BloggerRequestModal.html',
          controller: BloggerRequestController,
          controllerAs: 'modal'
        });
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
        })
      };

    }
  }
})
();
