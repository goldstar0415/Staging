(function () {
  'use strict';

  /*
   * Add link validator to inputs
   */
  angular
    .module('zoomtivity')
    .directive('validateLink', function ($parse) {
      return {
        require: '?ngModel',
        restrict: 'A',
        link: function (scope, elem, attrs, ctrl) {
          var LINK_REGEXP = /^(https?:\/\/)?([\w\_\-]+\.)?([\w\_\-\.?]+)\.([a-z]{2,7}\.?)(\/[\w\_\-\.]*)*\/?$/i; 
          // Old regexp:
          // /^(http[s]?\:\/\/)?([\w\_\-]+\.)?([\w\_\-\.?]+)\.([a-zA-Z]{2,7})(\/.+)?$/i
          if (ctrl) {
            ctrl.$validators.link = function (modelValue) {
              return ctrl.$isEmpty(modelValue) || LINK_REGEXP.test(modelValue);
            };
          }
        }
      };
    })
    .directive('youtubeLink', function ($parse) {
      return {
        require: '?ngModel',
        restrict: 'A',
        link: function (scope, elem, attrs, ctrl) {
          var LINK_REGEXP = /^((http(s)?:\/\/)?)(www\.)?((youtube\.com\/((watch\?v=)[\S]+))|(youtu.be\/([\S]+)))$/i;
          if (ctrl) {
            ctrl.$validators.link = function (modelValue) {
              return ctrl.$isEmpty(modelValue) || LINK_REGEXP.test(modelValue);
            };
          }
        }
      };
    })

})();
