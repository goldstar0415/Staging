(function () {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('ArticleCreateController', ArticleCreateController);

    /** @ngInject */
    function ArticleCreateController($state, article, categories, toastr, API_URL, UploaderService, $rootScope) {
        var vm = this;
        vm = _.extend(vm, article);
        vm.categories = categories;
        vm.images = UploaderService.images;
        vm.options = {
            height: 200
        };

        vm.save = save;

        if (!vm.body) {
            vm.body = '<p></p><p></p><p></p><p></p><p></p><p></p>';
        }

        /*
         * Create post
         * @param form {ngForm}
         */
        function save(form) {
            var data = angular.copy(vm),
                req = {},
                url = API_URL + '/posts';

            if (form.$valid) {
                delete data.categories;
                delete data.images;
                delete data.options;
                
                //get wysiwyg content
                //var editor = ContentTools.EditorApp.get();
                //editor.save();
                //data.body = angular.element('[content-tools]').html();
                req.payload = JSON.stringify(data);
                if (vm.id) {
                    req._method = 'PUT';
                    url = API_URL + '/posts/' + vm.id;
                }

                vm.images.files.splice(0, vm.images.files.length - 1);  //save last image
                UploaderService
                    .upload(url, req, 'cover')
                    .then(function (resp) {
                        $state.go('profile_blog', {user_id: vm.user_id || $rootScope.currentUser.id });
                    })
                    .catch(function (resp) {
                        _.each(resp.data, function (message) {
                            toastr.error(_.isArray(message) ? message[0] : message);
                        });

                        //editor.start();
                    });
            }
        }
    }
})();
