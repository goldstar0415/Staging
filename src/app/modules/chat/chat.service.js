(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('ChatService', ChatService);

  /** @ngInject */
  function ChatService($state, $rootScope, Message) {
    var messages = [],
      dialogs = {},
      utcOffset = moment().utcOffset();

    return {
      messages: messages,
      dialogs: dialogs,
      onNewMessage: onNewMessage,
      pushToToday: pushToToday,
      markAsRead: markAsRead,
      onReadMessage: onReadMessage,
      groupByDate: groupByDate
    };


    function onReadMessage(data) {
    }

    function onNewMessage(data) {
      console.log($state);
      if ($state.current.name == 'chatRoom' && $state.params.user_id == data.user.id) {  //if user in chat
        data.message.pivot = {sender_id: data.user.id};
        pushToToday(data.message);
      } else if ($state.current.name == 'chat') {
        _.each(dialogs, function (dialog) {

        });
      } else {

      }

      $rootScope.$apply();
    }

    function pushToToday(message) {
      message.created_at = _convertDate(moment(message.created_at).add(utcOffset, 'm'));

      _addToMessages('Today', message);
    }


    function groupByDate(chatMessages) {
      console.log(chatMessages);
      messages = [];

      _.each(chatMessages, function (message) {
        var createdAt = moment(message.created_at).add(utcOffset, 'm'),
          day = createdAt.isSame(moment(), 'day') ? 'Today' : createdAt.format('MM.DD.YYYY');

        message.created_at = _convertDate(createdAt);
        _addToMessages(day, message);
      });
      console.log(messages);
      return messages;
    }

    function _addToMessages(day, message) {
      var groupDay = _.find(messages, {day: day});
      if (!groupDay) {
        groupDay = {
          day: day,
          messages: []
        };
        messages.push(groupDay);
      }
      groupDay.messages.push(message);
    }

    function _convertDate(date) {
      if (date.diff(moment(), 'hour') == 0) {
        date = date.fromNow();
      } else {
        date = date.format('MMM DD, YYYY H:mm A');
      }

      return date;
    }

    function markAsRead(user_id) {
      var countNewMessages = 0;
      angular.forEach(messages, function (groupMessages) {
        angular.forEach(groupMessages.messages, function (message) {
          if (!message.is_read && message.pivot.receiver_id == $rootScope.currentUser.id) {
            countNewMessages++;
          }
        });
      });

      if (countNewMessages > 0) {
        Message.markAsRead({
            user_id: user_id
          },
          function () {
            angular.forEach(messages, function (groupMessages) {
              angular.forEach(groupMessages.messages, function (message) {
                if (message.pivot.receiver_id == $rootScope.currentUser.id) {
                  message.is_read = true;
                }
              });
            });
          }
        );
      }
    }

  }

})();
