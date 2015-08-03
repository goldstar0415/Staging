(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('ChatService', ChatService);

  /** @ngInject */
  function ChatService($state, $rootScope) {
    var messages = {},
      dialogs = {},
      utcOffset = moment().utcOffset();

    return {
      messages: messages,
      dialogs: dialogs,
      onNewMessage: onNewMessage,
      onReadMessage: onReadMessage,
      groupByDate: groupByDate
    };


    function onReadMessage(data) {
    }

    function onNewMessage(data) {
      console.log($state);
      if ($state.current.name == 'chatRoom' && $state.params.user_id == data.user.id) {  //if user in chat
        data.message.pivot = {sender_id: data.user.id};
        data.message.created_at = _convertDate(moment(data.message.created_at).add(utcOffset, 'm'));

        messages['Today'] = messages['Today'] || [];
        messages['Today'].push(data.message)
      } else if ($state.current.name == 'chat') {
        _.each(dialogs, function (dialog) {

        });
      } else {

      }

      $rootScope.$apply();
    }


    function groupByDate(chatMessages) {
      console.log(chatMessages);
      messages = {};

      _.each(chatMessages.data, function (message) {
        var createdAt = moment(message.created_at).add(utcOffset, 'm'),
          day = createdAt.isSame(moment(), 'day') ? 'Today' : createdAt.format('MM.DD.YYYY');

        message.created_at = _convertDate(createdAt);
        messages[day] = messages[day] || [];
        messages[day].push(message);
      });
      console.log(messages);
      return messages;
    }

    function _convertDate(date) {
      if (date.diff(moment(), 'hour') == 0) {
        date = date.fromNow();
      } else {
        date = date.format('MMM DD, YYYY H:mm A');
      }

      return date;
    }

  }

})();
