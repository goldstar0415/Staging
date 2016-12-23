(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(config)
    .animation('.mapResize', mapResizeAnimation);

  /** @ngInject */
  function config($logProvider, dialogsProvider, toastr, $httpProvider, cfpLoadingBarProvider, snapRemoteProvider, $locationProvider, DEBUG, JS_CONSOLE_KEY, $translateProvider) {
    // Enable log
    $logProvider.debugEnabled(DEBUG);
    $locationProvider.html5Mode({enabled: true, requireBase: false});

    $httpProvider.defaults.withCredentials = true;

    // don't cache 'lazy' templates
    $httpProvider.interceptors.push(function($q) {
      return {
        'request': function (config) {
          var rgx = /^\/app\/\S+\.html$/i;
          if (window.GIT_REVISION != '__GULP_GIT_REVISION__' && _.isObject(config) && config.url && rgx.test(config.url + '')) {
            config.url = config.url + '?' + window.GIT_REVISION;
          }
          return config || $q.when(config);
        }
      };
    });

    dialogsProvider.setSize('sm');

    // toastr
    toastr.options.timeOut = 2000;
    toastr.options.extendedTimeOut = 0;
    toastr.options.positionClass = 'toast-top-right';
    toastr.options.preventDuplicates = true;
    toastr.options.progressBar = true;
    toastr.options.onShown = function () {
      //TODO: make smart margin of top
    };


    // snap
    var disable = "";
    var touchToDrag = false;
    var windowWidth = angular.element(window).width();
    if (windowWidth < 992) {
      touchToDrag: true;
      disable = "right";
    } else {
      touchToDrag: false;
      disable = "left";
    }

    snapRemoteProvider.globalOptions = {
      disable: disable,
      hyperextensible: false,
      maxPosition: 230,
      minPosition: -230,
      minDragDistance: 40,
      touchToDrag: touchToDrag,
      tapToClose: true
    };

    // loading bar
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = true;
    cfpLoadingBarProvider.latencyThreshold = 100;

    $translateProvider.useSanitizeValueStrategy('sanitize');
    $translateProvider.preferredLanguage('en');
    $translateProvider.translations('en', {
      DIALOGS_YES: 'Yes',
      DIALOGS_NO: 'No'
    });

    moment.locale('en', {
      relativeTime : {
        future : 'in %s',
        past : '%s ago',
        s : 'moments',
        m : 'a minute',
        mm : '%d minutes',
        h : 'an hour',
        hh : '%d hours',
        d : 'a day',
        dd : '%d days',
        M : 'a month',
        MM : '%d months',
        y : 'a year',
        yy : '%d years'
      }
    });

    //enable js console
    //if (DEBUG && windowWidth < 992) {
    //  var script = angular.element('<script>')
    //    .attr('src', 'http://jsconsole.com/remote.js?' + JS_CONSOLE_KEY);
    //  angular.element('head').append(script);
    //}
  }

  /** @ngInject */
  function mapResizeAnimation() {
    return {
      setClass: function (element, addedClass, removedClass, doneFn) {
        if (window.map) {
          window.map.invalidateSize();
        }
        doneFn();
      }
    }

  }

})();
