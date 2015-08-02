var _ = require('underscore');

module.exports = function (app, io) {

  var users = [
    1,
    2,
    3
  ];

  var dialogs = [
    {
      user: {
        id: 1,
        first_name: 'Ernest',
        last_name: 'Carrol',
        avatar_url: {
          thumb: '/assets/img/icons/avatar.jpg'
        }
      },
      last_message: {
        user_id: 1,
        message: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod Lorem ipsum ',
        created_at: 'Aug 12, 4:56 PM',
        is_read: false
      }
    },
    {
      user: {
        id: 2,
        first_name: 'Minion',
        last_name: 'Verstun',
        avatar_url: {
          thumb: '/assets/img/icons/avatar.jpg'
        }
      },
      last_message: {
        user_id: 2,
        message: 'Lorem ipsum dolor sit amet',
        created_at: 'Aug 12, 4:56 PM',
        is_read: true
      }
    },
    {
      user: {
        id: 3,
        first_name: 'Josh',
        last_name: 'Test',
        avatar_url: {
          thumb: '/assets/img/icons/avatar.jpg'
        }
      },
      last_message: {
        user_id: 22,
        message: 'sed do eiusmod tempor incididunt ut labore et dolore magna',
        created_at: 'Aug 12, 5:56 PM',
        is_read: true
      }
    },
    {
      user: {
        id: 4,
        first_name: 'Test',
        last_name: 'Test',
        avatar_url: {
          thumb: '/assets/img/icons/avatar.jpg'
        }
      },
      last_message: {
        user_id: 22,
        message: 'sed do',
        created_at: 'Aug 12, 5:56 PM',
        is_read: false
      }
    }
  ];

  var messages = [
    {
      id: 1,
      user_id: 2,
      message: 'Lorem ipsum dolor sit amet, consectetur consectetu',
      created_at: '2015-08-01 17:16:20',
      is_read: true
    },
    {
      id: 2,
      user_id: 22,
      message: 'Lorem ipsum dolor sit amet',
      created_at: '2015-08-01 17:15:50',
      is_read: true
    },
    {
      id: 3,
      user_id: 2,
      message: ' consectetur consectetu',
      created_at: '2015-08-01 17:16:50',
      is_read: false
    }
  ];

  var chat = io.of('/socket').on('connection', function (socket) {

    socket.on('dialogs', function (data) {

      chat.emit('dialogs', dialogs);
    });

    socket.on('chat', function (data) {
      users.push(data);

      chat.emit('message:list', messages);
    });

    socket.on('message:send', function (data) {
      var newMessage = {
        id: Math.round(Math.random() * 1000),
        user_id: data.user_id,
        message: data.message,
        created_at: (new Date()),
        is_read: false
      };

      messages.push(newMessage);

      chat.emit('message:new', newMessage);
    });

    socket.on('message:read', function (data) {
      for(var i in messages) {
        messages[i].is_read = true;
      }

      chat.emit('message:list', messages);

    });

    socket.on('message:delete', function (data) {

      messages = _.reject(messages, {id: data});

      chat.emit('message:list', messages);

    });








    socket.on('load', function (data) {

      if (chat.clients(data).length === 0) {

        socket.emit('peopleinchat', {number: 0});
      }
      else if (chat.clients(data).length === 1) {

        socket.emit('peopleinchat', {
          number: 1,
          user: chat.clients(data)[0].username,
          avatar: chat.clients(data)[0].avatar,
          id: data
        });
      }
      else if (chat.clients(data).length >= 2) {

        chat.emit('tooMany', {boolean: true});
      }
    });

    socket.on('login', function (data) {

      if (chat.clients(data.id).length < 2) {

        socket.username = data.user;
        socket.room = data.id;
        socket.avatar = gravatar.url(data.avatar, {s: '140', r: 'x', d: 'mm'});

        socket.emit('img', socket.avatar);


        socket.join(data.id);

        if (chat.clients(data.id).length == 2) {

          var usernames = [],
            avatars = [];

          usernames.push(chat.clients(data.id)[0].username);
          usernames.push(chat.clients(data.id)[1].username);

          avatars.push(chat.clients(data.id)[0].avatar);
          avatars.push(chat.clients(data.id)[1].avatar);


          chat.in(data.id).emit('startChat', {
            boolean: true,
            id: data.id,
            users: usernames,
            avatars: avatars
          });
        }

      }
      else {
        socket.emit('tooMany', {boolean: true});
      }
    });

    socket.on('disconnect', function () {

      socket.broadcast.to(this.room).emit('leave', {
        boolean: true,
        room: this.room,
        user: this.username,
        avatar: this.avatar
      });

      socket.leave(socket.room);
    });


    socket.on('msg', function (data) {

      socket.broadcast.to(socket.room).emit('receive', {msg: data.msg, user: data.user, img: data.img});
    });
  });
};
