var express = require('express'),
  app = express(),
  server = require('http').Server(app),
  io = require('socket.io')(server);
  //compress = require('compression')(),
  //sqlite3 = require('sqlite3');

//app.use(compress);
app.disable('x-powered-by');

app.use(function (req, res, next) {
    res.setHeader('Access-Control-Allow-Origin', "http://"+req.headers.host+':3000');

    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
    res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
    next();
  }
);

var livenote = {

  port : process.env.OPENSHIFT_NODEJS_PORT || 8081,

  ip : process.env.OPENSHIFT_NODEJS_IP || "127.0.0.1",

  notes: {}


};


server.listen(livenote.port, livenote.ip);


require('./config')(app, io);
require('./routes')(app, io);

console.log('Check the app at Port :' + livenote.port);
