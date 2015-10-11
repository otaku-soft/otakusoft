var http = require('http');
var io = require('socket.io');
 
server = http.createServer(function(req, res){
});
server.listen(8082);
 
// socket.io
var socket = io.listen(server);
 
socket.on('connection', function(client){
	
  client.on('message', function(msg){
  	  console.log(msg);
  	  socket.send(msg);
        })

}); 