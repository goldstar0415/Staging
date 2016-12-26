'use strict';

const config = require('./config');

const SRC = config.paths.src;
const BOWER = config.paths.bower;

module.exports = [
  `${BOWER}/angular-animate/angular-animate.js`,
  `${BOWER}/angular-translate/angular-translate.js`,
  `${BOWER}/angular-dialog-service/dist/dialogs.min.js`,
  `${BOWER}/angular-dialog-service/dist/dialogs-default-translations.min.js`,
  `${BOWER}/br-validations/releases/br-validations.js`,
  `${BOWER}/string-mask/src/string-mask.js`,
  `${BOWER}/angular-input-masks/angular-input-masks-standalone.min.js`,
  `${BOWER}/angular-messages/angular-messages.js`,
  `${BOWER}/angular-scroll-glue/src/scrollglue.js`,
  `${BOWER}/snapjs/snap.js`,
  `${BOWER}/angular-snap/angular-snap.js`,
  `${BOWER}/angular-ui-select/dist/select.js`,
  `${BOWER}/bootstrap-datepicker/js/bootstrap-datepicker.js`,
  `${BOWER}/moment-timezone/builds/moment-timezone-with-data-2010-2020.js`,
  `${BOWER}/ng-tags-input/ng-tags-input.min.js`,
  `${BOWER}/ngInfiniteScroll/build/ng-infinite-scroll.js`,
  `${BOWER}/jQuery.dotdotdot/src/jquery.dotdotdot.min.umd.js`,
  `${BOWER}/bootstrap-modal/js/bootstrap-modal.js`,
  `${BOWER}/bootstrap-modal/js/bootstrap-modalmanager.js`,
  `${BOWER}/jquery-mousewheel/jquery.mousewheel.js`,
  `${BOWER}/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.js`,
  `${BOWER}/angular-carousel/dist/angular-carousel.js`,
  `${BOWER}/skycons/skycons.js`,
  `${BOWER}/angular-skycons/angular-skycons.min.js`,
  `${BOWER}/ng-webworker/src/ng-webworker.min.js`,
  `${SRC}/assets/libs/screenfull/screenfull.min.js`,
  `${SRC}/assets/libs/Leaflet/leaflet.js`,
  `${SRC}/assets/libs/leaflet.pip/leaflet-pip.min.js`,
  `${SRC}/assets/libs/LeafletMarkerCluster/leaflet.markercluster.js`,
  `${SRC}/assets/libs/LeafletRoutingMachine/leaflet-routing-machine.js`,
  `${SRC}/assets/libs/angular-bootstrap.iml/ui-bootstrap-tpls.js`,
  `${SRC}/assets/libs/angularjs-dropdown-multiselect/angularjs-dropdown-multiselect.min.js`,
  `${SRC}/assets/libs/concave_hull/concavehull.min.js`,
  `${SRC}/assets/libs/datetimepicker/jquery.datetimepicker.js`,
  `${SRC}/assets/libs/bootstrap-tooltip/bootstrap-tooltip.js`,
  `${SRC}/assets/libs/screenfull/screenfull.min.js`,
];
