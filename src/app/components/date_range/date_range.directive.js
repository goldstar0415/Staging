(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('dateRange', DateRange);

  /** @ngInject */
  function DateRange() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/date_range/date_range.html',
      scope: {
        item: '='
      },
      controller: DateRangeCtrl,
      controllerAs: 'Range',
      bindToController: true
    };

    /** @ngInject */
    function DateRangeCtrl(DATE_FORMAT) {
      var vm = this;

      console.log(vm.item);
      if (vm.item.start_date && vm.item.end_date) {
        var start_date = moment(vm.item.start_date),
          end_date  = moment(vm.item.end_date);
        vm.start_date = start_date.format(DATE_FORMAT.date);
        vm.end_date = end_date.format(DATE_FORMAT.date);

        if (vm.item.start_time) {
          vm.start_time = vm.item.start_time;
          vm.end_time = vm.item.start_time;
        } else if (start_date.format('H') != 0 || end_date.format('H') != 0) {
          vm.start_time = start_date.format(DATE_FORMAT.time);
          vm.end_time = end_date.format(DATE_FORMAT.time);
        }
      }
    }
  }
})();
