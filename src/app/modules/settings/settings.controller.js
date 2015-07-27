(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SettingsController', SettingsController);

  /** @ngInject */
  function SettingsController($rootScope, toastr, moment, $http, API_URL) {
    var vm = this;
    vm.data = $rootScope.currentUser;

    vm.savePersonal = function () {
      $http.put(API_URL + '/settings', {
        type: 'personal',
        params: {
          first_name: vm.data.first_name,
          last_name: vm.data.last_name,
          birth_date: vm.data.birth_date,
          sex: vm.data.sex,
          time_zone: vm.data.time_zone,
          description: vm.data.description,
          address: vm.data.address,
          location: vm.data.location
        }
      });
    };
  }
})();
