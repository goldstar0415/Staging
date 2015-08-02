var io = require('socket.io').listen(8082),
  redis = require('redis'),
  _ = require('underscore');


var channelPrefix = 'zoomtivity:user:session:';


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
      id: 22,
      first_name: 'Josh',
      last_name: 'Test',
      avatar_url: {
        thumb: '/assets/img/icons/avatar.jpg'
      }
    },
    last_message: {
      user_id: 23,
      message: 'sed do eiusmod tempor incididunt ut labore et dolore magna',
      created_at: 'Aug 12, 5:56 PM',
      is_read: true
    }
  },
  {
    user: {
      id: 23,
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
    user_id: 23,
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
    user_id: 23,
    message: ' consectetur consectetu',
    created_at: '2015-08-01 17:16:50',
    is_read: false
  }
];

io.sockets.on('connection', function (socket) {
  var sessionId = socket.handshake.query.id || null;
  var listenEvent = channelPrefix + sessionId + ':socket:' + socket.id;

  //try {
    var redisClient = redis.createClient();
    redisClient.psubscribe(listenEvent + '*');

    //Listen to redis event
    redisClient.on("pmessage", function (pattern, channel, message) {
      console.log('pmessage: ', arguments);

      socket.emit(channel.replace(listenEvent + ':', ''), message);
    });


    //If redis disconnect == remove subscriber and close connection
    redisClient.on('disconnect', function () {
      console.log('redis disconnect! ');

      redisClient.unsubscribe();
      redisClient.end();
      redisClient.quit();
    });

    //Listen to socketIO event
    //socket.on('url', function (data) {
    //  var pub = redis.createClient();
    //  var count = 0;
    //
    //  pub.keys('zoomtivity:user:*:session:' + sessionId, function (err, keys) {
    //    if (err) {
    //      return console.log(err);
    //    }
    //    count = keys.length;
    //    if (count > 0) {
    //      pub.set(listenEvent, data.url);
    //    }
    //
    //    pub.quit();
    //  });
    //});

    socket.on('disconnect', function () {
      console.log('socket disconnect! ');

      var pub = redis.createClient();
      pub.del(listenEvent);
      pub.quit();

      //close redis client
      redisClient.unsubscribe();
      redisClient.quit();

      socket.leave(socket.room);
    });
  //} catch (ex) {
  //  console.log("" + ex);
  //}


  /////////
  /////////
  /////////

  socket.on('initUser', function(userId){
    if (userId) {
      socket.room = userId;
      socket.join(userId);

    }
    console.log("create room: " + userId);
  });

  socket.on('dialogs', function (data) {

    socket.emit('dialogs', dialogs);
  });

  socket.on('chat', function (data) {

    socket.emit('message:list', messages);
  });

  socket.on('message:send', function (data) {
    var newMessage = {
      id: Math.round(Math.random() * 1000),
      user_id: data.user_id,
      sender_id: data.sender_id,
      message: data.message,
      created_at: (new Date()),
      is_read: false
    };

    messages.push(newMessage);

    socket.emit('message:new', newMessage);
    socket.broadcast.to(data.user_id).emit('message:new', newMessage);
  });

  socket.on('message:read', function (data) {
    for (var i in messages) {
      if (messages[i].user_id == socket.room) {
        messages[i].is_read = true;
      }
    }

    socket.emit('message:list', messages);

  });

  socket.on('message:delete', function (data) {

    messages = _.reject(messages, {id: data});

    socket.emit('message:list', messages);

  });

});


//var express = require('express'),
//  app = express(),
//  server = require('http').Server(app),
//  io = require('socket.io')(server),
//  Redis = require('ioredis'),
//  redis = new Redis(),
//  compress = require('compression')();
//
//app.use(compress);
//app.disable('x-powered-by');
//
////app.use(function (req, res, next) {
////    res.setHeader('Access-Control-Allow-Origin', "http://"+req.headers.host+':3000');
////
////    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
////    res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
////    next();
////  }
////);
//
//var config = {
//  port : process.env.OPENSHIFT_NODEJS_PORT || 8081,
//  ip : process.env.OPENSHIFT_NODEJS_IP || "127.0.0.1"
//};
//
//redis.on('message', function(channel, message) {
//  console.log('Redis: Message on ' + channel + ' received!');
//  console.log(message);
//  message = JSON.parse(message);
//  io.emit(channel, message.payload)
//});
//
//
//server.listen(config.port, config.ip);
//
//
//require('./routes')(app, io);
//
//console.log('Check the app at Port :' + config.port);
