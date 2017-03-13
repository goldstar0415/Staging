(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('ChatService', ChatService);

  /** @ngInject */
  function ChatService($state, $rootScope, NewMessageService) {
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


    /*
     * Mark as read message
     * message {Message}
     */
    function onReadMessage(message) {
      markAsRead(message.sender_id);
      $rootScope.$apply();
    }

    /*
     * On new message event
     */
    function onNewMessage(data) {
      //TODO: update dialogs

      if ($state.current.name == 'chatRoom' && $state.params.user_id == data.user.id) {  //if user in chat
        pushToToday(data.message);
      } else {
        NewMessageService.show(data);
      }

      $rootScope.currentUser.new_messages++;
      $rootScope.$apply();
    }

    function pushToToday(message) {
      message.created_at = _convertDate(moment(message.created_at).add(utcOffset, 'm'));

      _addToMessages('Today', message);
    }


    /*
     * Group messages by creation date
     * @param chatMessages {Array<Message>}
     */
    function groupByDate(chatMessages) {
      chatMessages = chatMessages.reverse();
      messages = [];

      var groupedMessagesObject = _.groupBy(chatMessages, function (item) {
        var createdAt = moment(item.created_at).add(utcOffset, 'm');
        item.created_at = _convertDate(createdAt);
        return createdAt.format("MM.DD.YYYY");
      });

      for (var k in groupedMessagesObject) {
        var createdAt = moment(k, 'MM.DD.YYYY');

        var day = createdAt.isSame(moment(), 'day') ? 'Today' : createdAt.format('MM.DD.YYYY');
        messages.push({
          day: day,
          messages: groupedMessagesObject[k]
        });
      }

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
      date = date.format('MMM DD, YYYY H:mm A');
      return date;
    }

    /*
     * Mark incoming messages as read
     * @param user_id {number}
     */

    function markAsRead(user_id) {
      angular.forEach(messages, function (groupMessages) {
        angular.forEach(groupMessages.messages, function (message) {
          if (message.pivot.receiver_id == user_id) {
            message.is_read = true;
            $rootScope.currentUser.new_messages--;
          }
        });
      });
    }

  }

})();
