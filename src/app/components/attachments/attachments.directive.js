(function () {
  'use strict';

  /*
   * Directive for message attachments (spots, areas, links)
   */
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
