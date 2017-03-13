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

      vm.hasAnyAttachments = hasAnyAttachments;

      function hasAnyAttachments() {
        var result = false;
        if (!_.isObject(vm.items)) {
          return result;
        }
        ['album_photos', 'areas', 'links', 'plans', 'spots'].forEach(function(prop) {
          if (_.isArray(vm.items[prop]) && vm.items[prop].length > 0) {
            result = true;
          }
        });
        return result;
      }

    }

  }
})();
