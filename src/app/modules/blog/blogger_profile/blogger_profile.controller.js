(function () {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('BloggerProfileController', BloggerProfileController);

    /** @ngInject */
    function BloggerProfileController(posts, Post, dialogs, MapService, toastr) {
        var vm = this;
        vm.posts = posts;
        vm.removePost = removePost;
        vm.getDate = getDate;

        showMarkers();


        /*
         * Delete post
         * @param post {Post}
         * @param idx {number} post index
         */
        function removePost(post, idx) {
            dialogs.confirm('Confirmation', 'Are you sure you want to delete post?').result.then(function () {
                Post.delete({id: post.slug},
                    function () {
                        toastr.info('Spot successfully deleted');
                        vm.posts.splice(idx, 1);
                    },
                    function () {
                        toastr.error('An error occurred during deleting');
                    }
                );
            });
        }

        //Show markers on map
        function showMarkers() {
            if (posts.length > 0) {
                MapService.drawBlogMarkers(posts, true);
            }
        }

        function getDate(date) {
            var $date = moment(date);
            return $date.format('DD') + '<br/>' + $date.format('MMM');
        }

    }
})();
