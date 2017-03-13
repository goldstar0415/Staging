(function () {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('ArticleCreateController', ArticleCreateController);

    /** @ngInject */
    function ArticleCreateController($state, $scope, article, categories, toastr, API_URL, UploaderService, $rootScope) {
        var vm = this;
        vm = _.extend(vm, article);
        vm.categories = categories;
        vm.images = UploaderService.images;
        vm.options = {
            height: 200,
            toolbar: [
                ['edit',['undo','redo']],
                ['headline', ['style']],
                ['style', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough', 'clear']],
                // ['fontface', ['fontname']],
                // ['textsize', ['fontsize']],
                // ['fontclr', ['color']],
                ['alignment', ['ul', 'ol', 'paragraph', 'lineheight']],
                // ['height', ['height']],
                // ['table', ['table']],
                ['insert', ['link','picture','video','hr']],
                ['view', ['fullscreen']],
                ['help', ['help']]
            ]
        };

        vm.save = save;

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

        $scope.imageUpload = function(files) {

            console.log(this);
            var url = API_URL + '/posts/upload';
            var req = {
                image: files[0]
            };

            UploaderService
                .upload(url, req, 'cover')
                .then(function (resp) {
                    //console.log($.summernote);
                    var //editor = $.summernote.eventHandler.getModule(),
                        uploaded_file_name = resp.data.image_name,
                        file_location = '/uploads/posts/'+ resp.data.image_name;
                    //console.log(file_location);
                    //console.log(uploaded_file_name);
                    vm.body += '<img src="' + resp.data.image_url + '" />'
                    //console.log($scope.editor.summernote('insertImage', file_location, uploaded_file_name));
                    //editor.insertImage($scope.editable, file_location, uploaded_file_name);
                    //console.log(resp);
                })
                .catch(function (resp) {
                    //console.log(resp)
                    _.each(resp.data, function (message) {
                        toastr.error(_.isArray(message) ? message[0] : message);
                    });
                    //editor.start();
                });

            console.log('image upload:', files);
            console.log('image upload\'s editable:', $scope.editable);
        }
    }
})();
