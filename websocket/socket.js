const app = require('http').createServer(handler);
const io = require('socket.io')(app);
const Redis = require('ioredis');

const redis = new Redis({
	port: process.env.REDIS_PORT || 6379,
	host: process.env.REDIS_HOST || '127.0.0.1',
	db: 0
});

app.listen(process.env.PORT, () => {
    console.log('Server is running!');
});

function handler(req, res) {
    res.writeHead(200);
    res.end('');
}

let users = 0;

io.on('connection', (socket) => {
    console.log('A user connected! Current users: ' + ++users);
    socket.on('disconnect', () => {
        console.log('User disconnected');
        --users;
    });
});


redis.psubscribe('*', (err, count) => {
    //
});


redis.on('pmessage', (subscribed, channel, message) => {
    message = JSON.parse(message);
    io.emit(channel + ':' + message.event, message.data);
});
