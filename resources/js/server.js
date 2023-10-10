let app = require("express")();
let http = require("http").Server(app);
const io = require("socket.io")(http);
const port = 4000;

http.listen(port, () => {
    console.log(`Listening to port *:${port}`);
});

io.on("connection", (socket) => {
    socket.on("sendData", function (data) {
        io.emit("sendData", data);
        console.log(data);
    }); // listen to the event
});
io.on("connect_error", (err) => {
    console.log(`connect_error due to ${err.message}`);
});
