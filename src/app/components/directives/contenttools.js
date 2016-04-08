(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('contentTools', contenttools);

  /* @ngInject */
  function contenttools($timeout) {
    var directive = {
      link: link,
      restrict: 'EA',
      scope: {
        model: '=ngModel'
      }
    };
    return directive;

    function link(scope, element, attrs) {

      if (!window.ContentTools) {
        $.getScript('/assets/libs/contenttools/content-tools.min.js', function () {
          $.get('/assets/libs/contenttools/content-tools.min.css', function (css) {
            $('head').append('<style>' + css + '</style>');

            $timeout(initEditor);
          });
        });
      } else {
        $timeout(initEditor);
      }
    }

    function initEditor() {
      // Initialise the editor
      var editor = new ContentTools.EditorApp.get();
      console.log($('[content-tools]'));
      editor.init('[content-tools]', 'article-body');
      editor.start();
      window.onbeforeunload = null;
    }
  }


})();

