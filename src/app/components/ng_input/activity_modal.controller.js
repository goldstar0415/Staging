(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ActivityModalController', ActivityModalController);

  /** @ngInject */
  function ActivityModalController(spots, areas, attachments, $modalInstance) {
    var vm = this;
    vm.tab = 'events';
    vm.spots = _markAsSelected(spots, attachments.spots);
    vm.areas = _markAsSelected(areas, attachments.areas);
    vm.attachments = attachments;

    vm.addSpot = function (spot) {
      _toggleItem(vm.attachments.spots, spot);
    };

    vm.addArea = function (area) {
      _toggleItem(vm.attachments.areas, area);
    };

    vm.close = function () {
      $modalInstance.close();
    };

    function _markAsSelected(items, attachments) {
      _.each(attachments, function (attachment) {
        var item = _.findWhere(items, {id: attachment.id});
        item.selected = true;
      });

      return items;
    }

    function _toggleItem(items, attachment) {
      if (_.findWhere(items, {id: attachment.id})) {
        items = _.reject(items, function (item) {
          return item.id == attachment.id;
        });
      } else {
        items.push(attachment);
      }

      attachment.selected = !attachment.selected;
    }
  }
})();
