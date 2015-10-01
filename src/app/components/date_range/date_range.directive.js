(function () {
  'use strict';


  /*
   * Directive for show formated spot dates
   */
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

      if (vm.item.start_date && vm.item.end_date) {
        var start_date = moment(vm.item.start_date),
          end_date = moment(vm.item.end_date);
        vm.start_date = start_date.format(DATE_FORMAT.date);
        vm.end_date = end_date.format(DATE_FORMAT.date);

        //convert times if they exists
        if (vm.item.start_time && vm.item.start_time != "12:00 am" || vm.item.end_time != "12:00 am") {
          vm.start_time = vm.item.start_time;
          vm.end_time = vm.item.end_time;
        }
      }
    }
  }
})();
