(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('ngDatepicker', function (moment, DATE_FORMAT) {
      return {
        restrict: 'A',
        scope: {
          format: '=',
          startDate: '=',
          endDate: '=',
          model: '=ngModel',
          today: '='
        },
        link: function (s, e, a) {
          var format = s.format || DATE_FORMAT.datepicker.date;
          var placeholder = moment().format(DATE_FORMAT.date);

          if(s.today) {
            s.startDate = moment().format(DATE_FORMAT.date);
          }

          if (s.model) {
            s.model = moment(s.model).format(DATE_FORMAT.date);
          }

          $(e)
            .datetimepicker({
              value: s.model,
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
              //onSelectDate: onSelectDate,
              onShow: function () {
                this.setOptions({
                  minDate: s.startDate || false,
                  maxDate: s.endDate || false
                });
              }
            })
            .attr('placeholder', placeholder);


        }
      }
    })

    .directive('ngTimepicker', function (moment, DATE_FORMAT) {
      return {
        restrict: 'A',
        scope: {
          step: '=',
          model: '=ngModel'
        },
        link: function (s, e, a) {
          var step = s.step || 15;
          if (s.model) {
            s.model = moment(s.model).format(DATE_FORMAT.time);
          }

          $(e).datetimepicker({
            value: s.model || null,
            defaultTime: '01:00',
            datepicker: false,
            validateOnBlur: false,
            step: step,
            mask: '29:59',
            formatTime: DATE_FORMAT.datepicker.time,
            onChangeDateTime: onSelectTime
          });

          function onSelectTime(time) {
            s.model = moment(time).format(DATE_FORMAT.time);
            s.$apply();
          }
        }
      }
    })

})();
