/*jshint esversion: 6 */

var app = require('http').createServer();

// Se tiverem problemas com "same-origin policy" deverão activar o CORS.

// Aqui, temos um exemplo de código que ativa o CORS (alterar o url base) 

// var app = require('http').createServer(function(req,res){
// Set CORS headers
//  res.setHeader('Access-Control-Allow-Origin', 'http://---your-base-url---');
//  res.setHeader('Access-Control-Request-Method', '*');
//  res.setHeader('Access-Control-Allow-Methods', 'UPGRADE, OPTIONS, GET');
//  res.setHeader('Access-Control-Allow-Credentials', true);
//  res.setHeader('Access-Control-Allow-Headers', req.header.origin);
//  if ( req.method === 'OPTIONS' || req.method === 'UPGRADE' ) {
//      res.writeHead(200);
//      res.end();
//      return;
//  }
// });

// NOTA: A solução correta depende da configuração do próprio servidor, 
// e alguns casos do próprio browser.
// Assim sendo, não se garante que a solução anterior funcione.
// Caso não funcione é necessário procurar/investigar soluções alternativas

var io = require('socket.io')(app);

var LoggedUsers = require('./loggedusers.js');

app.listen(8080, function(){
    console.log('listening on *:8080');
});

// ------------------------
// Estrutura dados - server
// ------------------------

// loggedUsers = the list (map) of logged users. 
// Each list element has the information about the user and the socket id
// Check loggedusers.js file

let loggedUsers = new LoggedUsers();

io.on('connection', function (socket) {
   	
	socket.on('user_enter', function (user) {
		if (user !== undefined && user !== null) {
			if (user.shift_active == "1") {
				socket.join('usertype_' + user.type);			
				socket.emit('user_started_shift', user);
			}
			loggedUsers.addUserInfo(user, socket.id);
		}
	});

	socket.on('user_exit', function (user) {
		if (user !== undefined && user !== null)  {
			socket.leave('usertype_' + user.type);
			loggedUsers.removeUserInfoByID(user.id);
		}
	});

	socket.on('start_shift', function (user) {
		if (user !== undefined && user !== null) {
			socket.join('usertype_' + user.type);
			socket.emit('user_started_shift', user);
		}
	});

	socket.on('end_shift', function (user) {
		if (user !== undefined && user !== null)  {
			socket.leave('usertype_' + user.type);
			socket.emit('user_ended_shift', user);
		}
	});

	socket.on('new_dish_order', function (dishName, userInfo) {
		if (userInfo !== undefined) {
			let channelName = 'usertype_cook';
			io.sockets.to(channelName).emit('msg_server_new_dish_order', userInfo.name +' add a new order for the following item: ' + dishName);
		}
	});

	socket.on('refresh', function (sourceUser, destUser, orderId, assingedToCook = false) {
		let userInfo = loggedUsers.userInfoByID(destUser);
		let socket_id = userInfo !== undefined ? userInfo.socketID : null; 

		if (socket_id !== null) {
			io.to(socket_id).emit('refresh_orders', sourceUser);
			if (assingedToCook) {
				io.to(socket_id).emit("msg_server_dish_assigned_to_cook", sourceUser, orderId);
			}
		} 
	});

	socket.on('refreshPrepared', function (sourceUser, destUser, orderId) {
		let userInfo = loggedUsers.userInfoByID(destUser);
		let socket_id = userInfo !== undefined ? userInfo.socketID : null; 

		if (socket_id !== null) {
			io.to(socket_id).emit('refresh_prepared_orders', sourceUser);
			io.to(socket_id).emit('msg_server_dish_prepared', "The order: " + orderId + " was marked as PREPARED by the cook: " + sourceUser.name);
		} 

	});

	socket.on('msg_global', function (msg, userInfo) {
		if(userInfo !== undefined) {
			socket.broadcast.to("usertype_manager").emit('msg_global_from_server', userInfo.name +' ('+userInfo.type+'): "' + msg + '"');
		}
  	});

  	socket.on('waiter-inform-cooks-new-order', function (sourceUser) {
		if (sourceUser !== undefined && sourceUser.type=='waiter') {
			io.sockets.to('usertype_cook').emit('inform_alterations_unsigned_orders');
		}
	});

	socket.on('inform-cooks-assing-order', function (sourceUser) {
		if (sourceUser !== undefined && sourceUser.type=='cook') {
			io.sockets.to('usertype_cook').emit('inform_alterations_unsigned_orders');
		}
	});

	socket.on('inform-orders-meal-summary', function (sourceUser,mealId) {

		let userInfo = loggedUsers.userInfoByID(sourceUser);
		let socket_id = userInfo !== undefined ? userInfo.socketID : null; 

		if (socket_id !== null) {
			io.to(socket_id).emit('refresh_orders_summary', mealId);
		}

  	});

	socket.on('notPaidInvoiceMeal', function () {
		console.log('Invoice and meal marked as not paid ');
		io.sockets.to('usertype_manager').emit('refresh_invoice_meals');
	});

	socket.on('createdNewInvoice', function (invoice, meal) {
		
		io.sockets.emit('refresh_invoices');
		io.sockets.to('usertype_cashier').emit('new_pending_invoice', invoice);
		io.sockets.to('usertype_manager').emit('meal_terminated', meal);
	});


	socket.on('invoicePaid', function (user, invoice) {
		socket.emit('refresh_invoices');
		io.sockets.to('usertype_manager').emit('invoice_paid', user, invoice);
	});

 	

});

