(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotCreateController', SpotCreateController);

  /** @ngInject */
  function SpotCreateController(spot, $stateParams, $state, $modal, toastr, $scope, MapService, UploaderService, CropService, $timeout, moment, API_URL, $http, DATE_FORMAT, categories) {
    var vm = this;
    var coverName = null;
    var isChangedCover = false;

    vm.deletedImages = [];
    vm.edit = $state.current.edit || false;
    vm.restrictions = {
      links: 5,
      tags: 7,
      locations: 20,
      images: 10,
      video: 5
    };
    vm.type = 'event';
    vm.selectCover = false;
    vm.crop = {
      width: 512,
      height: 512
    };
    vm.firstload = true;
    vm.category_id = '';
    vm.startDate = moment().toDate();
    vm.categories = {};

    _.each(categories.data, function (item) {
      vm.categories[item.name] = item.categories;
    });
    console.log(spot);
    vm.is_private = vm.edit ? +spot.is_private : 1;

    //vars
    vm.tags = [];
    vm.links = [];
    vm.youtube_links = [];
    vm.locations = [];
    vm.images = UploaderService.images;
    vm.spotTypes = [{
      name: 'Event',
      value: 'event'
    }, {
      name: 'To-Do',
      value: 'todo'
    }, {
      name: 'Food',
      value: 'food'
    }, {
      name: 'Shelter',
      value: 'shelter'
    }];

    $scope.$watch('SpotCreate.images.files.length', function () {
      vm.checkFilesRestrictions();
    });
    $scope.$watch('SpotCreate.start_date', function () {
      if (!vm.firstload) {
        if (vm.start_date) {
          var start = moment(vm.start_date, DATE_FORMAT.date);
          var end = moment(vm.end_date, DATE_FORMAT.date);

          if (start.isAfter(end, 'seconds')) {
            vm.end_date = '';
          }
        }
      } else {
        vm.firstload = false;
      }
    });
    $scope.$watch('SpotCreate.type', function () {
      if (!vm.edit) {
        vm.category_id = '';
        vm.start_date = null;
        vm.start_time = null;
        vm.end_date = null;
        vm.end_time = null;
      }
    });


    function filterLocations() {
      var array = _.reject(vm.locations, function (item) {
        return item.isDelete || false;
      });

      array = _.map(array, function (item) {
        return {location: item.location, address: item.address};
      });

      return array;
    }

    function filterTags() {
      var array = _.map(vm.tags, function (item) {
        return item.name;
      });

      return array;
    }

    function rejectOldFiles() {
      return _.reject(vm.images.files, function (item) {
        return item.id ? true : false;
      })
    }

    //submit form
    vm.create = function (form) {
      form.$submitted = true;

      if (vm.newLocation && vm.newLocation.address) {
        vm.addLocation(vm.newLocation);
      }
      if (form.$valid && vm.category_id !== '' && vm.locations.length > 0 && (!vm.cropCover || vm.saveCrop)) {
        var tags = filterTags();
        var locations = filterLocations();
        var request = {};
        request.title = vm.title;
        request.description = vm.description;
        request.spot_type_category_id = vm.category_id;
        request.is_private = vm.is_private;

        if (vm.cover && isChangedCover) {
          request.cover = vm.cover;
        }
        if (vm.newLink || (vm.links && vm.links.length > 0)) {
          if (!vm.links) {
            vm.links = [];
          }
          if (vm.newLink) {
            vm.links.push(vm.newLink);  //TODO: add validations
          }
          request.web_sites = vm.links;
        }
        if (vm.newYoutubeLink || (vm.youtube_links && vm.youtube_links.length > 0)) {
          if (!vm.youtube_links) {
            vm.youtube_links = [];     //TODO: add validations
          }
          if (vm.newYoutubeLink) {
            vm.youtube_links.push(vm.newYoutubeLink);
          }
          request.videos = vm.youtube_links;
        } else {
			// no videos in the Spot (could be deleted)
			request.videos = [];
		}
        if (locations.length > 0) {
          request.locations = locations;
        }
        if (tags.length > 0) {
          request.tags = tags;
        }
        if (vm.edit) {
          request.deleted_files = vm.deletedImages;
        }
        if (vm.type === 'event') {
          request.start_date = moment(vm.start_date + ' ' + vm.start_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.datepicker.time).format(DATE_FORMAT.backend);
          request.end_date = moment(vm.end_date + ' ' + vm.end_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.datepicker.time).format(DATE_FORMAT.backend);

          if (request.start_date == 'Invalid date' || request.end_date == 'Invalid date') {
            toastr.error('Wrong dates');
            return;
          }
        }
        var url = API_URL + '/spots';
        var req = {};
        req.payload = JSON.stringify(request);
        if (vm.edit) {
          req._method = 'PUT';
          url = API_URL + '/spots/' + $stateParams.spot_id;
        }

        if (request.locations && request.locations.length > 0) {

          vm.images.files = rejectOldFiles();
          vm.loading = true;
          UploaderService
            .upload(url, req)
            .then(function (resp) {
              if (request.is_private == '1') {
                toastr.success('Spot successfully saved');
              } else {
                toastr.info('Your submittal is under review and will be posted shortly.');
              }

              if (vm.type != 'event') {
                $timeout(function () {
                  $state.go('spots', {user_id: resp.data.user_id});
                }, 3000);
              } else {
                $state.go('spot', {spot_id: resp.data.id, user_id: resp.data.user_id});
              }
            })
            .catch(function (resp) {
              vm.loading = false;
              console.log(resp);

              if (resp.status == 413) {
                toastr.error('Images too large');
              } else {
                toastr.error('Save error');
              }
            });
        }
      } else {
        console.log(vm.newLocation, vm.locations);
        if (!vm.title) {
          toastr.error('Title is required!');
        } else if (!vm.category_id) {
          toastr.error('Category is required!');
        } else if (vm.locations.length == 0) {
          toastr.error('Location is required!');
        } else if (vm.type === 'event' && !vm.start_date) {
          toastr.error('Start date is required!');
        } else if (vm.type === 'event' && !vm.end_date) {
          toastr.error('End date is required!');
        } else if (vm.cropCover && !vm.saveCrop) {
          toastr.error('Please save cover');
        }
      }

    };

    //links
    vm.addLink = function (validLink) {
      if (vm.links.length >= vm.restrictions.links) {
        toastr.error('You can\'t add more than ' + vm.restrictions.links + ' links');
        return;
      }
      if (validLink && vm.newLink) {
        vm.links.unshift(vm.newLink);
        console.log(vm.links);
        vm.newLink = null;
      } else {
        toastr.error('Link is not valid');
        vm.newLink = null;
      }
    };
    vm.removeLink = function (index) {
      vm.links.splice(index, 1);
    };
    //tags
    vm.onTagsAdd = function (q, w, e) {
      if (vm.tags.length < vm.restrictions.tags) {
        return true;
      } else {
        toastr.error('You can\'t add more than ' + vm.restrictions.tags + ' tags');
        return false;
      }
    };
    //location
    vm.addLocation = function (item) {
      if (item && item.address && item.location) {
        var item = angular.copy(item);

        vm.locations.unshift(item);
        vm.newLocation = {};
      } else {
        toastr.error('You can\'t add empty location');
        vm.newLocation = {};
      }
    };
    vm.removeLocation = function (index) {
      var marker = vm.locations[index].marker;
      MapService.RemoveMarker(marker);
      vm.locations[index].isDelete = true;
    };

    //photos
    vm.checkFilesRestrictions = function () {
      if (vm.images.files.length > vm.restrictions.images) {
        toastr.error('You can\'t add more than ' + vm.restrictions.images + ' photos');
        var l = vm.images.files.length - vm.restrictions.images;
        vm.images.files.splice(vm.restrictions.images, l);
      }
      if (vm.images.files.length > 0 && !vm.cover) {
        _setCover(vm.images.files[0]);
      }
    };

    function _setCover(image, id) {
      $timeout(function() {
        if (typeof image === 'string') {
          vm.cropCover = image;
          coverName = id;
        } else if (image.photo_url) {
          vm.cropCover = image.photo_url.original;
          coverName = image.id;
        } else {
          var reader = new FileReader();
          reader.onloadend = function () {
            vm.cropCover = reader.result;
            $scope.$apply();
          };
          coverName = image.name;
          reader.readAsDataURL(image);
        }
      });
    }

    vm.deleteImage = function (idx, id) {
      if (id) {
        if (vm.images.files[idx].id == coverName) {
          vm.cropCover = '';
          vm.cover = null;
        }

        vm.deletedImages.push(id);
        vm.images.files.splice(idx, 1);
      } else {
        if (vm.images.files[idx].name == coverName) {
          vm.cropCover = '';
          vm.cover = null;
        }
        vm.images.files.splice(idx, 1);
      }
    };

    vm.changeCover = function (image) {
      if (vm.selectCover) {
        angular.element('#cover_cancel_button').addClass('ng-hide');  //quirk. without it the "cancel" button disappears 1 second

        if (image.photo_url) {
          _setCover(image.photo_url.original, image.id);
        } else {
          _setCover(image);
        }

        vm.selectCover = false;
        vm.saveCrop = false;
      }
    };

    vm.saveCover = function () {
      vm.saveCrop = true;
      isChangedCover = true;
    };

    vm.InvalidTag = function (tag) {
      console.log(vm.tags, tag.name);
      if (tag.name.length > 64) {
        toastr.error('Your tag is too long. Max 64 symbols.');
      } else if (_.find(vm.tags, {name: tag.name})) {
        toastr.error('Tag with this name has already been added');
      } else {
        toastr.error('Invalid input.');
      }
    };
    //videos
    vm.addYoutubeLink = function (validLink) {
      if (validLink && vm.newYoutubeLink) {
        if (vm.youtube_links.indexOf(vm.newYoutubeLink) == -1) {
          vm.youtube_links.unshift(vm.newYoutubeLink);
          vm.newYoutubeLink = '';
        } else {
          toastr.error('Video with this link has already been added');
        }
      } else {
        toastr.error('Link is not valid');
        vm.newYoutubeLink = '';
      }
    };
    vm.removeYoutubeLink = function (index) {
      vm.youtube_links.splice(index, 1);
    };

    //load data for spot editing
    vm.convertSpot = function () {
      //TODO: add array to display all kinds of images (attachments from albums, photos for upload, and old photos)
      var data = spot;
      vm.type = data.category.type.name.toLowerCase();
      vm.title = data.title;
      vm.description = data.description;
      vm.links = data.web_sites || [];
      vm.youtube_links = data.videos || [];
      vm.category_id = data.spot_type_category_id;
      vm.tags = data.tags || [];
      vm.cover = data.cover_url.original;
      vm.locations = data.points;
      if (data.photos) {
        for (var k in data.photos) {
          vm.images.files.push(data.photos[k]);
        }
      }
      if (data.start_date && data.end_date) {
        vm.start_date = data.start_date;
        vm.end_date = data.end_date;

        if (data.start_date.indexOf('00:00:00') === -1 || data.end_date.indexOf('00:00:00') === -1) {
          vm.start_time = data.start_date;
          vm.end_time = data.end_date;
        }
      }

      _setCover(data.cover_url.original, data.id);
    };


    if (vm.edit) {
      vm.convertSpot();
      vm.saveCrop = true;
    }
  }
})
();
