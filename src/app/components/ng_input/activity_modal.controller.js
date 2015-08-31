(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ActivityModalController', ActivityModalController);

  /** @ngInject */
  function ActivityModalController(spots, favorites, areas, attachments, $modalInstance) {
    var vm = this;
    vm.tab = 'spots';
    vm.spots = _markAsSelected(spots, attachments.spots);
    vm.favorites = _markAsSelected(favorites.data, attachments.spots);
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

    vm.changeTab = function (tab) {
      vm.tab = tab;
      if (tab == 'spots' || tab == 'favorites') {
        _markAsSelected(vm[tab], attachments.spots);
      } else {
        _markAsSelected(vm.areas, attachments.areas);
      }
    };

    function _markAsSelected(items, attachments) {
      _.each(items, function (item) {
        var isFound = _.findWhere(attachments, {id: item.id});
        item.selected = !!isFound;
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
