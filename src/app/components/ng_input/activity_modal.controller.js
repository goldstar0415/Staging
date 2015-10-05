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

    /*
     * Add spot to attachments
     * @param spot {Spot}
     */
    vm.addSpot = function (spot) {
      _toggleItem(attachments.spots, spot);
    };

    /*
     * Add area to attachments
     * @param area {Area}
     */
    vm.addArea = function (area) {
      _toggleItem(attachments.areas, area);
    };

    //close modal
    vm.close = function () {
      $modalInstance.close();
    };

    /*
     * Change tab
     * @param tab {string} name of tab
     */
    vm.changeTab = function (tab) {
      vm.tab = tab;
      if (tab == 'spots' || tab == 'favorites') {
        _markAsSelected(vm[tab], attachments.spots);
      } else {
        _markAsSelected(vm.areas, attachments.areas);
      }
    };

    /*
     * When modal open mark as selected items
     * @param items {Array} spots or areas
     * @param attachments {Object}
     * @returns {Array}
     */
    function _markAsSelected(items, attachments) {
      _.each(items, function (item) {
        var isFound = _.findWhere(attachments, {id: item.id});
        item.selected = !!isFound;
      });

      return items;
    }

    /*
     * Add items to attachments or delete from attachments if already item exist
     * @param items {Array} spots or areas
     * @param attachments {Object}
     */
    function _toggleItem(items, attachment) {
      if (_.findWhere(items, {id: attachment.id})) {
        var idx = _getIndexById(items, attachment.id);
        items.splice(idx, 1);
      } else {
        items.push(attachment);
      }

      attachment.selected = !attachment.selected;
    }

    /*
     * Get index by id from items
     * @param items {Array}
     * @param id {number}
     * @returns {number}
     */
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
