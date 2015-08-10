(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ActivityModalController', ActivityModalController);

  /** @ngInject */
  function ActivityModalController(spots, favorites, areas, attachments, $modalInstance) {
    var vm = this;
    vm.tab = 'events';
    vm.spots = _markAsSelected(spots, attachments.spots);
    vm.favorites = _markAsSelected(favorites, attachments.spots);
    vm.areas = _markAsSelected(areas, attachments.areas);

    vm.addSpot = function (spot) {
      _toggleItem(attachments.spots, spot);
    };

    vm.addArea = function (area) {
      _toggleItem(attachments.areas, area);
    };

    vm.close = function () {
      $modalInstance.close();
    };

    function _markAsSelected(items, attachments) {
      _.each(attachments, function (attachment) {
        var item = _.findWhere(items, {id: attachment.id});
        if (item) {
          item.selected = true;
        }
      });

      return items;
    }

    function _toggleItem(items, attachment) {
      if (_.findWhere(items, {id: attachment.id})) {
        var idx = _getIndexById(items, attachment.id);
        items.splice(idx, 1);
      } else {
        items.push(attachment);
      }

      attachment.selected = !attachment.selected;
    }

    function _getIndexById(items, id) {
      for (var i = 0; i < items.length; i++) {
        if (items[i].id == id) {
          return i;
        }
      }
      return null;
    }
  }
})();
