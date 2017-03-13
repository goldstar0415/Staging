(function () {
    'use strict';

    /*
     * Directive to send reviews
     */
    angular
        .module('zoomtivity')
        .directive('review', Review);

    /** @ngInject */
    function Review() {
        return {
            restrict: 'E',
            scope: {
                review: '=',
                spot: '='
            },
            templateUrl: '/app/modules/spot/review/review.html',
            controller: ReviewController,
            controllerAs: 'Review',
            bindToController: true
        };

        /** @ngInject */
        function ReviewController(SpotReview, $rootScope, toastr, dialogs) {
            var vm = this;
            var result = '';
            vm.edit = false;
            vm.deleted = false;
            var elem = document.createElement('textarea');
            elem.innerHTML = vm.review.message;
            var decoded = elem.value;
            vm.review.message = ($('<div>').html(decoded).text()).replace(/(\[\/?strong\])/g, '');
            vm.fullReview = false;
            

            vm.more = function() {
                vm.fullReview = true;
            }

            vm.getServiceName = function(serviceNum)
            {
                if(serviceNum)
                {
                    switch (serviceNum) {
                        case 1:
                          result = 'booking';
                          break;
                        case 2:
                          result = 'google';
                          break;
                        case 3:
                          result = 'facebook';
                          break;
                        case 4:
                          result = 'yelp';
                          break;
                        case 5:
                          result = 'hotels';
                          break;
                        case 6:
                          result = 'tripadvisor';
                          break;
                        default:
                          result = '';
                    }
                }
                return result;
            }

            vm.editReview = function(review) {
                vm.review.oldVote = review.vote;
                vm.review.oldMessage = review.message;
                vm.edit = true;
            };

            vm.cancelEditReview = function(review) {
                vm.review.vote = review.oldVote;
                vm.review.message = review.oldMessage;
                vm.edit = false;
            };

            vm.updateReview = function(review) {
              SpotReview.update({spot_id: review.spot_id, id: review.id},
                {
                  message: vm.review.message || '',
                  vote: vm.review.vote || ''
                }, function success(newReview) {
                  vm.spot.rating = newReview.spot_rating;
                  vm.edit = false;
                }, function error(resp) {
                  console.warn(resp);
                  toastr.error('Edit review failed');
                }
              );
            };

            vm.deleteReview = function(review, index) {
              dialogs.confirm('Confirmation', 'Are you sure you want to delete review?').result.then(function () {
                SpotReview.delete({spot_id: review.spot_id, id: review.id}, function success(result) {
                  vm.spot.rating = result.spot_rating;
                  delete result.spot_rating;
                  vm.spot.votes.splice(index, 1);
                  toastr.info('Review successfully deleted');
                });
              });
            };

        }
    }

})();
