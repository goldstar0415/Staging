(function () {
	'use strict';

	/**
	 * Facebook friend import modal
	 */
	angular
			.module('zoomtivity')
			.directive('facebookFriends', FacebookFriendsDirective);

	/** @ngInject */
	function FacebookFriendsDirective() {
		return {
			restrict: 'EA',
			template: '<a ng-click="FacebookFriendsDirective.openModalInviteFind()" class="import-friends-fb"></a>',
			controller: FacebookInviteFindController,
			controllerAs: 'FacebookFriendsDirective',
			bindToController: true,
			scope: {
				spots: '='
			}
		};


		/** @ngInject */
		function FacebookInviteFindController($modal) {
			/**
			 * Open Import Facebook Followings
			 */
			function openModal() {
				$modal.open({
					templateUrl: '/app/components/facebook_friends/facebook_friends.html',
					controller: FriendsModalController,
					controllerAs: 'modal',
				});
			}

			/**
			 * Open Facebook native message send dialog to share Zoomtivity with friends
             */
			function inviteFacebookFriends() {
				setTimeout(function() {
					FB.ui({
						method: 'send',
						link: 'https://zoomtivity.com',
					});
				}, 1);
			}

			/**
			 * Open Facebook modal with Find and Invite buttons
             */
			this.openModalInviteFind = function() {
				$modal.open({
					templateUrl: '/app/components/facebook_friends/facebook_invite_find.html',
					controller: function($modalInstance) {
						this.findFacebookFriends = function() {
							$modalInstance.close();
							openModal();
						};
						this.inviteFacebookFriends = function() {
							$modalInstance.close();
							inviteFacebookFriends();
						};
					},
					controllerAs: 'fbInviteFindCtrl'
				});
			}
		}

		/**
		 * @ngInject
		 * @param friends array
		 */
		function FriendsModalController($modalInstance, API_URL, Friends, $rootScope) {
			var vm = this;
			vm.API_URL = API_URL;
			vm.friends = [];
			vm.close = close;
			vm.inRequest = false;

			/**
			 * Close modal
             */
			function close() {
				$modalInstance.close();
			}

			function findFacebookFriends() {
				FB.getLoginStatus(function (response) {
					if (response.status === 'connected') {
						//var uid = response.authResponse.userID;
						//var accessToken = response.authResponse.accessToken;
						_getFacebookFriends();
					} else {
						FB.login(_getFacebookFriends, {
							scope: 'user_friends',
							return_scopes: true
						});
					}
				});
			}

			function _getFacebookFriends() {
				FB.api("/me/friends", function(response) {
					if (response.data && Array.isArray(response.data)) {
						console.log(response.data);
						var myFacebookFriendsIds = _.pluck(response.data, 'id');

						//var iMGoingToFollowUserIds = _.difference(myFacebookFriendsIds, iAlreadyFollowUsersIds);

						response.data.length && FB.api("/", {
							ids: myFacebookFriendsIds,
							fields: "first_name,last_name,picture,devices"
						}, function (users) {
							console.log(users);
							vm.friends = users;
						});
					} else {
						toastr.error('Facebook API error');
					}
				});
			}

			this.toggleSelected = function(friend) {
				friend.selected = friend.selected === undefined ? true : !friend.selected;
			};

			this.selectNone = function() {
				selectMultiple(false);
			};

			this.selectAll = function() {
				selectMultiple(true);
			};

			function selectMultiple (boolS) {
				for (var id in vm.friends) {
					vm.friends[id].selected = boolS;
				}
			}

			this.isDisabled = function(who) {
				who = who || "unknownElementCameIn";
				switch(who) {
					case 'selectAll':
						if (vm.inRequest) {
							return true;
						}
						// if some is not checked selectAll is enabled
						for (var id in vm.friends) {
							if (!vm.friends[id].selected) {
								// enable button
								return false;
							}
						}
						// all are enabled - cant press selectAll
						return true;
					case 'add':
					case 'selectNone':
						if (vm.inRequest) {
							return true;
						}
						// if someone is checked selectNone is enabled
						for (var id in vm.friends) {
							if (vm.friends[id].selected) {
								// enable button
								return false;
							}
						}
						// all are enabled - cant press selectNone
						return true;
					default:
						// disable by default
						return true;
				}
			};

			/**
			 * Add friends from Facebook and follow
             */
			this.add = function() {
				// array of facebook ids
				var selected = [];
                for (var id in vm.friends) {
                    if (vm.friends[id].selected) {
                        selected.push(id);
                    }
                }
                vm.inRequest = true;
                Friends.followFacebook({'ids': selected, id: $rootScope.profileUser.id}).$promise
					.then(function() {
						toastr.success('Successfully followed Zoomtivity friends from Facebook: ' + selected.length, 'Import Facebook Friends');
						vm.close();
						setTimeout(function() {
							$rootScope.$broadcast('friendsMap.refresh.friends.list');
						}, 1);
					}, function(e) {
						toastr.error("Couldn't follow friends cause: "+e, 'Import Facebook Friends');
					})
					.finally(function () {
						vm.inRequest = false;
					});
			};

			findFacebookFriends();
		}
	}

})
();
