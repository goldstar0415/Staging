(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ArticleCreateController', ArticleCreateController);

  /** @ngInject */
  function ArticleCreateController(categories, API_URL, UploaderService) {
    var vm = this;
    vm.categories = categories;
    vm.images = UploaderService.images;

    vm.save = save;


    function save(form) {
      if (form.$valid) {
        var req = {},
          url = API_URL + '/posts';

        req.payload = JSON.stringify(vm);
        if (vm.id) {
          req._method = 'PUT';
          url = API_URL + '/posts/' + vm.id;
        }

        UploaderService
          .upload(url, req)
          .then(function (resp) {

            //$state.go('spot', {spot_id: resp.data.id, user_id: resp.data.user_id});
          })
          .catch(function (resp) {
            toastr.error('Upload failed');
          });
      }
    }
  }
})();
