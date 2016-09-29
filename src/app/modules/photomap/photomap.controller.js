(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotomapController', PhotomapController);

  /** @ngInject */
  function PhotomapController(albums, $stateParams, $state, dialogs, toastr, MapService) {
    var vm = this;
    vm.currentId = $stateParams.user_id;
    vm.albums = albums;
    vm.getRandomClass = getRandomClass;
    vm.flexGrid = flexGrid;

    function getRandomClass() {
        var val = Math.floor((Math.random() * 3) + 1);
        if (val === 1) {
            return 'stackone';
        } else if (val === 2) {
            return 'stacktwo';
        } else if (val === 3) {
            return 'stackthree';
        }
    }

    initMap();

    function flexGrid() {
        function checkCount(current_total, next) {
            if (current_total % 12) {
                current_total += 1;
                checkCount(current_total, next);
            } else {
                next(current_total);
            }
        }

        function addDummyElementsToItems(container) {
            var card_count = container.children.length;
            checkCount(card_count, function(final_count) {
                var dummy_element;
                for (var i = 0; i < (final_count - card_count); i++) {
                    dummy_element = document.createElement('div');
                    dummy_element.style.width = '240px';
                    // dummy_element.className = 'item-fake';
                    container.appendChild(dummy_element);
                }
            });
        }

        var grids = document.querySelectorAll('.album-images');
        grids.forEach(function(item) {
            addDummyElementsToItems(item);
        });
    }

    //create marker on map
    function createMarker(iconUrl, title, location, data) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'custom-map-icons');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      var marker = MapService.CreateMarker(location, options);
      marker.on('click', function () {
        $state.go('photos.album', {album_id: data.id, user_id: data.user_id});
      });
    }

    //show all markers on map
    function initMap() {
      var count = 0;
      for (var k in albums) {
        if (albums[k].location) {
          count++;
          createMarker(albums[k].cover.medium, albums[k].title, albums[k].location, albums[k]);
        }
      }
      if (count > 0) {
        MapService.FitBoundsOfCurrentLayer();
      }
    }

    /*
     * Delete album
     * @param item {Album} album id
     * @param idx {number} album index
     */
    vm.deleteAlbum = function (item, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete album?').result.then(function () {
        item.$delete(function () {
          toastr.info('Album successfully deleted');
          vm.albums.splice(idx, 1);
        });
      });
    }
  }
})();
