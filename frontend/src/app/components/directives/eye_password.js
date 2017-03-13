(function () {
  'use strict';

  /*
   Directive to make visible password when user click on eye
   */
  angular
    .module('zoomtivity')
    .directive('eyePassword', function () {
      return function (s, e, a) {
        e.on('click', function () {
          var $input = $(this).prev('input'),
            type = $input.attr('type') == 'password' ? 'text' : 'password';
          $input.attr('type', type);
        });
      }
    }
  )
  ;

})();
