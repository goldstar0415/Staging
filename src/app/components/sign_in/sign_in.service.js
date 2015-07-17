(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignInService', SignInService);

  /** @ngInject */
  function SignInService($modal) {
    return {
      openModal: openModal,
      userLogin: userLogin
    };

    function userLogin() {

    }

    function openModal(controller) {
      $modal.open({
        templateUrl: 'SignInModal.html',
        controller: controller
        //size: 'lg'
      });
    }

  }

})();
