(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ContactUsController', ContactUsController);

  /** @ngInject */
  function ContactUsController(StaticPage) {
    var vm = this;

    vm.save = function () {
      StaticPage.contactUs({}, vm, function () {
        vm.$saved = true;
      });
    }
  }
})();
