(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('ngDatepicker', function () {
      return {
        restrict: 'A',
        scope: {
          format: '=',
          startDate: '=',
          endDate: '=',
          model: '=ngModel'
        },
        link: function (s, e, a) {
          var format = s.format || 'Y-m-d';
          $(e).datetimepicker({
            value: s.model || null,
            scrollMonth: false,
            scrollTime: false,
            scrollInput: false,
            timepicker: false,
            validateOnBlur: false,
            format: format,
            formatDate: format,
            minDate: s.startDate || false,
            maxDate: s.endDate || false,
            mask: true,
            closeOnDateSelect: true,
            onSelectDate: onSelectDate,
            onShow:function() {
              this.setOptions({
                maxDate: s.endDate || false,
                minDate: s.startDate || false
              });
            }
          });

          function onSelectDate(date, $i) {
            s.model = date.dateFormat('Y-m-d');
            s.$apply();
          }
        }
      }
    })

    .directive('ngTimepicker', function (moment) {
      return {
        restrict: 'A',
        scope: {
          step: '=',
          model: '=ngModel'
        },
        link: function (s, e, a) {
          var step = s.step || 15;
          $(e).datetimepicker({
            value: s.model || null,
            defaultTime: '01:00',
            datepicker:false,
            validateOnBlur: false,
            step: step,
            mask: '29:59',
            formatTime: 'H:i',
            onChangeDateTime: onSelectTime
          });

          function onSelectTime(time) {
            s.model = moment(time).format('HH:mm');
            s.$apply();
          }
        }
      }
    })

})();
