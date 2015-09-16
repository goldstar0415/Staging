(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ArticleCreateController', ArticleCreateController);

  /** @ngInject */
  function ArticleCreateController($state, article, categories, toastr, API_URL, UploaderService) {
    var vm = this;
    vm = _.extend(vm, article);
    vm.categories = categories;
    vm.images = UploaderService.images;

    vm.save = save;


    function save(form) {
      var data = angular.copy(vm),
        req = {},
        url = API_URL + '/posts';

      if (form.$valid) {
        delete data.categories;

        req.payload = JSON.stringify(data);
        if (vm.id) {
          req._method = 'PUT';
          url = API_URL + '/posts/' + vm.id;
        }

        UploaderService
          .upload(url, req, 'cover')
          .then(function (resp) {
            $state.go('blog.article', {slug: resp.data.slug});
          })
          .catch(function (resp) {
            //var message = vm.images.files.length > 0 ? 'Upload failed' : 'Wrong input';
            _.each(resp.data, function (message) {
              toastr.error(message[0]);
            });
          });
      }
    }
  }
})();
