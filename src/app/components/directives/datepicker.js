(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('ngDatepicker', function (moment) {
      return {
        restrict: 'A',
        scope: {
          format: '=',
          endDate: '=',
          model: '=ngModel',
          startView: '='
        },
        link: function (s, e, a) {
          var startView = s.stateView || 2;
          var format = s.format || 'yyyy-mm-dd';
          var startDate = '1900-01-01';
          var endDate = s.endDate || moment().format('YYYY-MM-DD');

          $(e).attr('placeholder', 'YYYY-MM-DD');
          $(e).datepicker({
            format: format,
            autoclose: true,
            startDate: startDate,
            endDate: endDate,
            keyboardNavigation: false,
            immediateUpdates: false,
            startView: startView
          });
        }
      }
    }
  )
  ;

})();
