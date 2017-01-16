(function () {
  'use strict';

  angular
    .module('zoomtivity', [
      'ngAnimate',
      'ngCookies',
      'ngTouch',
      'ngSanitize',
      'ngResource',
      'ngMessages',
      // 'ngFileUpload',
      'ui.router',
      'ui.bootstrap',
      'dialogs.main',
      'snap',
      'angular-loading-bar',
      'luegg.directives',
      // 'ui.calendar',
      'ngTagsInput',
      'ui.select',
      'ui.utils.masks',
      'infinite-scroll',
      // 'summernote',
      'angular-carousel',
      'angularjs-dropdown-multiselect',
	    'oc.lazyLoad',
      'angular-skycons',
      'ngWebworker',
    ])
    .config(['$ocLazyLoadProvider', function ($ocLazyLoadProvider) {

      $ocLazyLoadProvider.config({
        // debug: true,
        modules: [
          {name: 'turf', files: [
            '/assets/libs/turf/turf.min.js',
          ]},
          {name: 'calendar', files: [
            'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.7.3/fullcalendar.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.7.3/fullcalendar.min.css',
            'https://cdnjs.cloudflare.com/ajax/libs/angular-ui-calendar/1.0.0/calendar.min.js',
          ]},
          {name: 'gmaps', files: [
            {type: 'js', path: 'https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAytote4odQOn_IpNkj207MXG2bF1uM2Zs'},
          ]},
          {name: 'cropper', files: versionize([
            'https://cdnjs.cloudflare.com/ajax/libs/cropper/2.3.4/cropper.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/cropper/2.3.4/cropper.min.css',
            '/app/components/crop/crop.service.js',
            '/app/components/crop/ngCropper.js',
          ])},
          {name: 'uploader', files: versionize([
            '/assets/libs/ng-file-upload/ng-file-upload.min.js',
            '/app/components/uploader/uploader.service.js',
            '/app/components/uploader/uploader.directive.js',
            '/app/components/upload_modal/upload_modal.directive.js',
          ])},
          {name: 'summernote', files: [
            '/assets/libs/summernote/summernote.js',
            '/assets/libs/summernote/summernote.css',
            '/assets/libs/summernote/plugin/specialchars/summernote-ext-specialchars.min.js',
            '/assets/libs/summernote/plugin/databasic/summernote-ext-databasic.min.js',
            '/assets/libs/summernote/plugin/databasic/summernote-ext-databasic.min.css',
            '/assets/libs/summernote/plugin/hello/summernote-ext-hello.min.js', // todo: what's the file, investigate, isn't a demo?
            versionize('/app/modules/summernote/summernote.js'),
          ]},
          {name: 'socket.io', files: [
            '/assets/libs/socket.io/socket.io.js',
          ]},
          {name: 'html2canvas', files: [
            '/assets/libs/html2canvas/html2canvas.js',
            '/assets/libs/html2canvas/html2canvas.svg.min.js',
          ]},
          {name: 'justifiedGallery', files: [
            'https://cdnjs.cloudflare.com/ajax/libs/justifiedGallery/3.6.3/js/jquery.justifiedGallery.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/justifiedGallery/3.6.3/css/justifiedGallery.min.css',
          ]},
          {name: 'jbox', files: [
            '/assets/libs/jbox/jbox.min.js',
            '/assets/libs/jbox/jBox.css',
          ]},
          {name: 'location-bloodhound', files: versionize([
            // todo: load `src/assets/sass/_location_bloodhound.scss` here
            '/app/components/location_bloodhound/location-bloodhound.directive.js',
          ])}

          // {name: 'leaflet', files: [
          //   '/assets/libs/Leaflet/leaflet.js',
          //   '/assets/libs/leaflet.pip/leaflet-pip.min.js',
          //   '/assets/libs/LeafletMarkerCluster/leaflet.markercluster.js',
          //   '/assets/libs/LeafletRoutingMachine/leaflet-routing-machine.js',
          //   '/assets/libs/Leaflet/leaflet.css',
          //   '/assets/libs/LeafletRoutingMachine/leaflet-routing-machine.css',
          //   '/assets/libs/LeafletMarkerCluster/MarkerCluster.Default.css',
          //   '/assets/libs/LeafletMarkerCluster/MarkerCluster.css',
          // ]}
        ]
      });

    }]);


})();
