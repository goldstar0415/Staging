(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('ngDatepicker', function (moment) {
      return {
        restrict: 'A',
        scope: {
          format: '=',
          startDate: '=',
          endDate: '=',
          model: '=ngModel'
        },
        link: function (s, e, a) {
          var format = s.format || 'd/m/Y',
            today = moment().format('DD/MM/YYYY');
          $(e).datetimepicker({
            value: s.model || null,
            scrollMonth: false,
            scrollTime: false,
            scrollInput: false,
            timepicker: false,
            validateOnBlur: false,
            format: format,
            formatDate: format,
            minDate: s.startDate || today,
            maxDate: s.endDate || false,
            mask: true,
            closeOnDateSelect: true,
            onSelectDate: onSelectDate,
            onShow: function () {
              this.setOptions({
                minDate: s.startDate || today,
                maxDate: s.endDate || false
              });
            }
          })
            .attr('placeholder', today);

          function onSelectDate(date, $i) {
            s.model = date.dateFormat('d/m/Y');
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
            datepicker: false,
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
