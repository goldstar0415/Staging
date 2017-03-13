(function () {
	'use strict';

	/*
	 * Controller for modal of photos
	 */
	angular
			.module('zoomtivity')
			.controller('PhotosModalController', PhotosModalController);

	/** @ngInject */
	function PhotosModalController(Album, albums, attachments, url, $modalInstance, UploaderService, API_URL) {
		var vm = this;
		vm.inRequestUpload = false;
		vm.albums = albums;
		vm.attachments = attachments;
		vm.url = url;
		vm.selectedUpload = false;
		vm.images = UploaderService.images;
		/*
		 * Open album and load photos
		 * @param id {number} id of album
		 * @param idx {number} index of album
		 */
		vm.selectAlbum = function (id, idx) {
			vm.selectedUpload = false;
			vm.selectedAlbum = vm.albums[idx];
			Album.photos({album_id: id}, function (photos) {
				vm.selectedAlbum.photos = _.filter(photos, function (photo) {
					return !_.findWhere(vm.attachments.photos, {id: photo.id});
				});
			});
		};
		/**
		 * Add photo to Uploads rather than to specific album
		 * @returns {undefined}
		 */
		vm.selectUpload = function () {
			vm.selectedUpload = true;
			vm.selectedUAlbum = false;
		};
		vm.selectAlbums = function () {
			vm.selectedAlbum = null;
			vm.selectedUpload = false;
		};
		vm.deleteImage = function (index) {
			vm.images.files.splice(index, 1);
		};
		/*
		 * Add photo to attachments
		 * @param idx {number} index of photo
		 */
		vm.addPhoto = function (idx) {
			var photo = vm.selectedAlbum.photos.splice(idx, 1);
			vm.attachments.photos.push(photo[0]);
		};

		//Close modal
		vm.close = function () {
			$modalInstance.close();
		};
		/**
		 * Upload Photos to the Uploads Album
		 */
		vm.uploadImages = function () {
			if (UploaderService.images.files.length === 0) {
				toastr.error('No images selected');
				return;
			}
			var uploadsAlbum = _.find(albums, function (el) { return el.title === 'Uploads'; });

			if (uploadsAlbum.id === undefined) {
				toastr.error('No Uploads album found');
				return;
			}

			var request = {
				_method:	'PUT',
				title:		uploadsAlbum.title,
				location:	uploadsAlbum.location,
				address:	uploadsAlbum.address,
				is_private: +uploadsAlbum.is_private
			};
			var url = vm.url || API_URL + '/albums/' + uploadsAlbum.id;
			vm.inRequestUpload = true;
			UploaderService
				.upload(url, request)
				.then(function (resp) {
					vm.images.files = [];
					vm.close();
					// get last two uploaded pics
					Album.lastUploadedPhotos({album_id: uploadsAlbum.id}, function (photos) {
						vm.attachments.photos = vm.attachments.photos.concat(photos);
						vm.inRequestUpload = false;
					});
				})
				.catch(function (resp) {
					if (resp.status == 413) {
						toastr.error('Images too large');
					} else {
						toastr.error('Upload failed');
					}
					vm.inRequestUpload = false;
				});
		};

		vm.isUploadDisabled = function() {
			return !vm.images.files || vm.images.files.length === 0 || vm.inRequestUpload;
		};
	}
})();
