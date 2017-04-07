// todo: rm from backend service

var app = require('http').createServer(handler);
var io = require('socket.io')(app);
var Redis = require('ioredis');
var redis = new Redis();

app.listen(8080, function() {
    console.log('Server is running!');
});

function handler(req, res) {
    res.writeHead(200);
    res.end('');
}
var users = 0;

io.on('connection', function(socket) {
    console.log('A user connected! Current users: ' + ++users);
    socket.on('disconnect', function(){
        console.log('User disconnected');
        --users;
    });
});


redis.psubscribe('*', function(err, count) {
    //
});


redis.on('pmessage', function(subscribed, channel, message) {
    message = JSON.parse(message);
    io.emit(channel + ':' + message.event, message.data);
});
