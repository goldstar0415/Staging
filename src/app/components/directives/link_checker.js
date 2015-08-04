(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('validateLink', function ($parse) {
      return {
        require: '?ngModel',
        restrict: 'A',
        link: function (scope, elem, attrs, ctrl) {
          var LINK_REGEXP = /^(http[s]?\:\/\/)?([\w\_\-]+\.)?([\w\_\-]+)\.([a-zA-Z]{2,7})(\/.+)?$/i;
          if(ctrl) {
            ctrl.$validators.link = function(modelValue) {
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
          var LINK_REGEXP = /^((http(s)?:\/\/)?)(www\.)?((youtube\.com\/)|(youtu.be\/))(((watch\?v=)[\w]+)|[\w]+)$/i;
          if(ctrl) {
            ctrl.$validators.link = function(modelValue) {
              return ctrl.$isEmpty(modelValue) || LINK_REGEXP.test(modelValue);
            };
          }
        }
      };
    })

})();
