const express = require("express");
var http = require("http");
const cors = require("cors");
const app = express();
const port = 5000;
var server = http.createServer(app);
var io = require("socket.io")(server, {
    cors: {
        origin: "*"
    }
});

app.use(express.json());
app.use(cors());

let onlineUsers = [];

const addOnlineUser = (userId, socketId) => {
    !onlineUsers.some((onlineUser) => onlineUser.userId === userId) &&
        onlineUsers.push({ userId, socketId });
};

const removeOnlineUser = (socketId) => {
    onlineUsers = onlineUsers.filter(onlineUser=> onlineUser.socketId !== socketId)
}

const getUser = (userId) => {
    return onlineUsers.find(user=>user.userId === userId);
}

io.on("connection", (socket) => {
    console.log("connected");
    console.log(socket.id)

    //add online user
    socket.on('onlineUser', (userId) => {
        addOnlineUser(userId,socket.id);
        io.emit('getOnlineUsers',onlineUsers);
    });

    //send and get message
    socket.on('sendMessage',({senderId,receiverId,text})=>{
        const user = getUser(receiverId);
        io.to(user.socketId).emit('getMessage',{
            senderId,
            text
        });
    });

    //remove online user
    socket.on('disconnect',()=>{
        removeOnlineUser(socket.id);
        io.emit('getOnlineUsers',onlineUsers);
    })

});

server.listen(port, '0.0.0.0', () => {
    console.log("server started");
});
