(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('ngDatepicker', function (moment, DATE_FORMAT) {
      return {
        restrict: 'A',
        scope: {
          startDate: '=',
          endDate: '=',
          model: '=ngModel',
          today: '='
        },
        link: function (s, e, a) {
          var format = DATE_FORMAT.datepicker.date;
          var placeholder = moment().format(DATE_FORMAT.date);

          if (s.today) {
            s.startDate = moment().format(DATE_FORMAT.date);
          }

          if (s.model) {
            s.model = moment(s.model).format(DATE_FORMAT.date);
          }

          $(e).datetimepicker({
            value: s.model || '',
            scrollMonth: false,
            scrollTime: false,
            scrollInput: false,
            timepicker: false,
            validateOnBlur: false,
            format: format,
            formatDate: format,
            minDate: s.startDate || false,
            maxDate: s.endDate || false,
            closeOnDateSelect: true,
            onSelectDate: onSelectDate,
            onShow: function () {
              this.setOptions({
                minDate: s.startDate || false,
                maxDate: s.endDate || false
              });
            }
          })
            .attr('placeholder', placeholder);

          $(e).on('keydown', function (e) {
            if (e.which != 8 && e.which != 13) {
              e.preventDefault();
            }

            if (e.which == 8) {
              s.model = '';
              s.$apply();
            }
          });

          function onSelectDate(ct, $i) {
            s.model = moment(ct).format(DATE_FORMAT.date);
          }


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
            s.model = moment(s.model).format(DATE_FORMAT.datepicker.time);
          }

          $(e).datetimepicker({
            value: s.model || null,
            defaultTime: '01:00',
            datepicker: false,
            validateOnBlur: false,
            step: step,
            formatTime: DATE_FORMAT.datepicker.time,
            onChangeDateTime: onSelectTime
          });

          $(e).on('keydown', function (e) {
            if (e.which != 8 && e.which != 13) {
              e.preventDefault();
            }

            if (e.which == 8) {
              s.model = '';
              s.$apply();
            }
          });

          function onSelectTime(time) {
            s.model = moment(time).format(DATE_FORMAT.datepicker.time);
            s.$apply();
          }
        }
      }
    });


  //Extension to support moment inside datepicker
  Date.parseDate = function (input, format) {
    return moment(input, format).toDate();
  };
  Date.prototype.dateFormat = function (format) {
    return moment(this).format(format);
  };

})();
