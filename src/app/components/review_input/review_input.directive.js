(function () {
    'use strict';

    /*
     * Directive to send message
     */
    angular
        .module('zoomtivity')
        .directive('reviewInput', reviewInput);

    /** @ngInject */
    function reviewInput() {
        return {
            restrict: 'E',
            scope: {
                item: '=',
                vote: '=',
                review: '=',
                onSubmit: '&',
                onFocus: '&',
                maxlength: '=',
                mobile: '='
            },
            templateUrl: '/app/components/review_input/review_input.html',
            controller: ReviewInputController,
            controllerAs: 'ReviewInput',
            bindToController: true
        };

        /** @ngInject */
        function ReviewInputController($scope, Spot, SpotReview, $rootScope, $http, API_URL, dialogs) {
            var vm = this;
            vm.edit = !(vm.item.is_rated);
            vm.rate = vm.vote = (vm.item.auth_rate) ? vm.item.auth_rate.vote : null;
            vm.review = (vm.item.auth_rate) ? vm.item.auth_rate.message : null;

            /*
             * Set rating input
             */
            vm.setRating = function () {
                vm.vote = vm.rate;
            };

            /*
             * Submit form
             * @param form {ngForm} angular form object
             */
            vm.submit = function (form) {
                if (form.$valid) {
                    if(!vm.item.is_rated) {
                        vm.postReview();
                    }
                    else {
                        vm.updateReview();
                    }
                    
                    form.$submitted = false;


                }
            };

            vm.editShow = function () {
                vm.edit = true;
            }

            vm.editCancel = function () {
                vm.vote = vm.rate = vm.item.auth_rate.vote;
                vm.review = vm.item.auth_rate.message;
                vm.edit = false;
            }

            //send new review for spot

            vm.postReview = function () {
                SpotReview.save({spot_id: vm.item.id},
                    {
                        message: vm.review || '',
                        vote: vm.vote || ''
                    }, function success(review) {
                        vm.item.is_rated = true;
                        vm.item.rating = review.spot_rating;
                        delete review.spot_rating;
                        vm.item.auth_rate = review;
                        vm.edit = false;
                    }, function error(resp) {
                        console.warn(resp);
                        toastr.error(resp.statusText || 'Send review failed');
                    }
                )
            }
            
            vm.updateReview = function() {
                SpotReview.update({spot_id: vm.item.id, id: vm.item.auth_rate.id},
                    {
                        message: vm.review || '',
                        vote: vm.vote || ''
                    }, function success(review) {
                        vm.item.is_rated = true;
                        vm.item.rating = review.spot_rating;
                        delete review.spot_rating;
                        vm.item.auth_rate = review;
                        vm.edit = false;
                    }, function error(resp) {
                        console.warn(resp);
                        toastr.error('Edit review failed');
                    }
                );
            }

            /*
             * Delete review
             * @param review {SpotReview}
             * @param idx {number} review index
             */
            vm.deleteReview = function(review) {
                dialogs.confirm('Confirmation', 'Are you sure you want to delete review?').result.then(function () {
                    SpotReview.delete({spot_id: vm.item.id, id: review.id}, function success(result) {
                        vm.item.rating = result.spot_rating;
                        delete result.spot_rating;
                        vm.item.auth_rate = false;
                        vm.vote = '';
                        vm.review = '';
                        vm.rate = '';
                        vm.item.is_rated = false;
                        vm.edit = true;
                        toastr.info('Review successfully deleted');
                    });
                });
            }

        }
    }

})();
