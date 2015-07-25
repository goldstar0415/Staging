(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotomapController', PhotomapController);

  /** @ngInject */
  function PhotomapController(albums, $stateParams) {
    var vm = this;
    vm.currentId = $stateParams.user_id;
    vm.albums = albums;
  }
})();
