'use strict';

const SRC = require('./config').paths.src;

module.exports = [
  `${SRC}/app/index.module.js`,
  `${SRC}/app/index.config.js`,
  `${SRC}/app/index.constants.js`,
  `${SRC}/app/index.route.js`,
  `${SRC}/app/index.run.js`,

  `${SRC}/app/models/album.js`,
  `${SRC}/app/models/area.js`,
  `${SRC}/app/models/friends.js`,
  `${SRC}/app/models/message.js`,
  `${SRC}/app/models/photo.js`,
  `${SRC}/app/models/photo_comment.js`,
  `${SRC}/app/models/plan.js`,
  `${SRC}/app/models/post.js`,
  `${SRC}/app/models/spot.js`,
  `${SRC}/app/models/spot_comment.js`,
  `${SRC}/app/models/spot_photo_comment.js`,
  `${SRC}/app/models/spot_review.js`,
  `${SRC}/app/models/user.js`,

  `${SRC}/app/components/blogger_request/blogger_request.directive.js`,
  `${SRC}/app/components/claim_spot/claim_spot.directive.js`,
  `${SRC}/app/components/contacts_import/contacts_import.directive.js`,
  `${SRC}/app/components/date_range/date_range.directive.js`,
  `${SRC}/app/components/attachments/attachments.directive.js`,
  `${SRC}/app/components/facebook_events/facebook_events.directive.js`,
  `${SRC}/app/components/directives/contenttools.js`,
  `${SRC}/app/components/directives/ctrlEnter.js`,
  `${SRC}/app/components/directives/datetimepicker.js`,
  `${SRC}/app/components/directives/eye_password.js`,
  `${SRC}/app/components/directives/justified_gallery.js`,
  `${SRC}/app/components/directives/link_checker.js`,
  `${SRC}/app/components/directives/ng_match.js`,
  `${SRC}/app/components/directives/repeat_done.js`,
  `${SRC}/app/components/directives/spotType.js`,
  `${SRC}/app/components/directives/youtube_player.js`,
  `${SRC}/app/components/facebook_friends/facebook_friends.directive.js`,
  `${SRC}/app/components/filters/absoluteLink.js`,
  `${SRC}/app/components/filters/age.js`,
  `${SRC}/app/components/filters/from_now.js`,
  `${SRC}/app/components/filters/htmlLinky.js`,
  `${SRC}/app/components/filters/htmlToPlaintext.js`,
  `${SRC}/app/components/filters/short_link.js`,
  `${SRC}/app/components/filters/toTrustedHtml.js`,
  `${SRC}/app/components/filters/to_paragraphs.js`,
  `${SRC}/app/components/filters/to_timezone.js`,
  `${SRC}/app/components/geolocation_ip/ip-api.js`,
  `${SRC}/app/components/google_contacts/google_contacts.controller.js`,
  `${SRC}/app/components/invite_friends/invite_friends.directive.js`,
  `${SRC}/app/components/google_maps_api/places_service.js`,
  `${SRC}/app/components/location/location_service.js`,
  `${SRC}/app/components/location_autocomplete/autocomplete.directive.js`,
  `${SRC}/app/components/flex_items/flex_items.directive.js`,
  `${SRC}/app/components/map_sort/mapSort.directive.js`,
  `${SRC}/app/components/map/map.service.js`,
  `${SRC}/app/components/new_message/new_message.directive.js`,
  `${SRC}/app/components/new_message/new_message.service.js`,
  `${SRC}/app/components/ng_carousel/ng_carousel.directive.js`,
  `${SRC}/app/components/ng_input/activity_modal.controller.js`,
  `${SRC}/app/components/ng_input/ng_input.directive.js`,
  `${SRC}/app/components/ng_input/photos_modal.controller.js`,
  `${SRC}/app/components/photoviewer/photoviewer.directive.js`,
  `${SRC}/app/components/password_recovery/password_recovery.directive.js`,
  `${SRC}/app/components/password_recovery/password_recovery.service.js`,
  `${SRC}/app/components/permission_service/permission_service.js`,
  `${SRC}/app/components/popular_posts/popular_posts.directive.js`,
  `${SRC}/app/components/report_spot/report_spot.directive.js`,
  `${SRC}/app/components/preloader/preloader.directive.js`,
  `${SRC}/app/components/password_reset/password_reset.controller.js`,
  `${SRC}/app/components/review_input/review_input.directive.js`,
  `${SRC}/app/components/scroll_service/scroll_service.js`,
  `${SRC}/app/components/share/share.js`,
  `${SRC}/app/components/send_message/send_message.directive.js`,
  `${SRC}/app/components/sign_in/sign_in.directive.js`,
  `${SRC}/app/components/sign_in/sign_in.service.js`,
  `${SRC}/app/components/sign_up/sign_up.directive.js`,
  `${SRC}/app/components/sign_up/sign_up.service.js`,
  `${SRC}/app/components/spot_card/spot_card.directive.js`,
  `${SRC}/app/components/socket/socket.service.js`,
  `${SRC}/app/components/spot_service/spot.service.js`,
  `${SRC}/app/components/spots_modal/spots_modal.directive.js`,
  `${SRC}/app/components/stars/stars.directive.js`,
  `${SRC}/app/components/user_hints/user_hints.directives.js`,
  `${SRC}/app/components/users_modal/users_modal.directive.js`,
  `${SRC}/app/components/truncated/truncated.directive.js`,
  `${SRC}/app/components/user_service/user.service.js`,
  `${SRC}/app/components/map_partials/hints/hints.directive.js`,
  `${SRC}/app/components/map_partials/saveSelection/save_selection.controller.js`,
  `${SRC}/app/components/map_popups/blog_popup/blog_popup.directive.js`,
  `${SRC}/app/components/map_popups/confirm_box/confirm.js`,
  `${SRC}/app/components/map_popups/post_popup/post_popup.js`,
  `${SRC}/app/components/map_popups/spot_map_modal/spot_map_modal.controller.js`,
  `${SRC}/app/components/map_popups/spot_popup/spot_popup.controller.js`,
  `${SRC}/app/components/map_popups/spot_popup/spot_popup.js`,
  `${SRC}/app/components/navigation/header/header.directive.js`,
  `${SRC}/app/components/navigation/side_menu/side_menu.directive.js`,
  `${SRC}/app/components/navigation/header/bloodhound-search.directive.js`,
  `${SRC}/app/modules/chat/chat.service.js`,
  `${SRC}/app/modules/spot/review/review.directive.js`,
  `${SRC}/app/components/async_loader_service/async_loader.service.js`,
  `${SRC}/app/modules/spot/spot.controller.js`, // fixme: use lazy-load
];
