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
          model: '=ngModel'
        },
        link: function (s, e, a) {
          var format = s.format || 'yyyy-mm-dd';
          var startDate = '1900-01-01';
          var endDate = s.endDate || moment().format('YYYY-MM-DD');

          $(e).datepicker({
            format: format,
            startDate: startDate,
            endDate: endDate
          })
            .on('changeDate', function (ev) {
              $(this).datepicker('hide');
            });

        }
      }
    }
  )
  ;

})();
