(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('contentTools', contenttools);

  /* @ngInject */
  function contenttools() {
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

            initEditor();
          });
        });
      } else {
        initEditor();
      }
    }

    function initEditor() {
      // Initialise the editor
      var editor = new ContentTools.EditorApp.get();
      editor.init('[content-tools]', 'article-body');
      editor.start();
      window.onbeforeunload = null;
    }
  }


})();

