(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('attachments', Attachments);

  /** @ngInject */
  function Attachments() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/attachments/attachments.html',
      scope: {
        items: '='
      },
      controller: AttachmentsController,
      controllerAs: 'Attachments',
      bindToController: true
    };

    /** @ngInject */
    function AttachmentsController() {
      var vm = this;


    }

  }
})();
