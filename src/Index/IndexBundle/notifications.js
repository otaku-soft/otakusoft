var http = require('http');
var io = require('socket.io');
server = http.createServer(function(req, res){});
server.listen(8082);
var socket = io.listen(server);
var clients = {};
function deleteClient(clients,key1,key2)
{
	if (typeof(clients[key1]) !=='undefined' &&  typeof(clients[key1][key2]) !=='undefined' )
	{
		var counter = 0;
		delete clients[key1][key2];
		for(var key in clients[key1]) 
		counter++;
		if (counter == 0)
		delete clients[key1]; 
	}
}
socket.on('connection', function(client)
{
	setTimeout(function()
	{ 
		for(var key in clients)
	 	{
			for(var key2 in clients[key])
	 		{
	 			if (typeof(socket.to(key2).connected[key2]) ==='undefined') 
	 			deleteClient(clients,key,key2);
	 		}
	 	}
		socket.emit("clients",clients); 
	}, 60000);
	client.on('setUserId',function(userid)
	{
		if(typeof(clients[userid[0]])==='undefined') 
	    clients[userid[0]] = {};
		clients[userid[0]][userid[1]] = userid[1];
			
		for(var key in clients[userid[0]]) 
		{
			if (typeof(socket.to(key).connected[key]) ==='undefined') 
			deleteClient(clients,userid[0],key);
		}	
		console.log(clients);
		socket.to(userid[1]).emit('clients',clients);
	});
	client.on('removeUserId',function(userid)
	{
		deleteClient(clients,userid[0],userid[1]);
		console.log(clients);
	});	
	client.on('message', function(msg)
	{
		if (msg.indexOf("notifications") != -1)
		{
			userid = msg.replace("notifications","");
			for(var key in clients[userid]) 
			{
			  	sessionid = key;
				console.log("notification sent" + msg);
				socket.to(sessionid).emit('message',msg);
		  	}			
		}
		else if (msg.indexOf("message") != -1)
		{
			userid = msg.replace("message","");
			for(var key in clients[userid]) 
			{
				sessionid = key;
				console.log("private message sent" + msg);
				socket.to(sessionid).emit('message',msg);
			}
		}		
		else
		{
			ids = msg.split(",");
			for (i = 0; i < ids.length;i++)
			{
				userid = ids[i];
				for(var key in clients[userid]) 
				{
					sessionid = key;
					displaymessage = "notifications" + msg;
					console.log("notification sent" + displaymessage);
					socket.to(sessionid).emit('message',displaymessage);
				}
			}
		}
	})
}); 