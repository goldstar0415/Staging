'use strict';

const config = require('./config');
const SRC = config.paths.src;
const BOWER = config.paths.bower;

module.exports = [
  `/${BOWER}/angular-dialog-service/dist/dialogs.min.css`,
  `/${BOWER}/angular-snap/angular-snap.css`,
  `/${BOWER}/angular-ui-select/dist/select.css`,
  `/${BOWER}/animate.css/animate.css`,
  `/${BOWER}/bootstrap-datepicker/dist/css/bootstrap-datepicker.css`,
  `/${BOWER}/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css`,
  `/${BOWER}/ng-tags-input/ng-tags-input.min.css`,
  `/${BOWER}/toastr/toastr.css`,
  `/${BOWER}/Ionicons/css/ionicons.css`,
  `/${BOWER}/bootstrap-modal/css/bootstrap-modal.css`,
  `/${BOWER}/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css`,
  `/${BOWER}/angular-carousel/dist/angular-carousel.css`,
  `${SRC}/assets/libs/Leaflet/leaflet.css`,
  `${SRC}/assets/libs/LeafletRoutingMachine/leaflet-routing-machine.css`,
  `${SRC}/assets/libs/LeafletMarkerCluster/MarkerCluster.Default.css`,
  `${SRC}/assets/libs/LeafletMarkerCluster/MarkerCluster.css`,
  `${SRC}/assets/libs/bootstrap-tooltip/bootstrap-tooltip.css`,
  `${SRC}/assets/libs/datetimepicker/jquery.datetimepicker.css`,
  `${SRC}/assets/libs/jcarousel/jcarousel.css`,
  `${SRC}/assets/css/bootstrap-select.min.css`,
  `${SRC}/assets/sass/_media.css`,
  `${SRC}/assets/sass/main.css`,
  `${SRC}/assets/css/main.css`,
];
