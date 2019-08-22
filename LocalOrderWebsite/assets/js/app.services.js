

app.service.ClientService = function($http, RequestService) {
    return {
        //retrieves a blank client from the server
        startNewClient: function() {
            return RequestService.request('startNewClient', {type: 'get'});
        },

        saveClient: function(client) {
            return RequestService.request('saveClient/' + client.id, {type: 'post'}, client);
        },

        getClient: function(id) {
            return RequestService.request('getClient/' + id, {type: 'get'});
        },

        deleteClient: function(id) {
            return RequestService.request('removeClient/' + id, {type: 'post'});
        },

        getAllClients: function(sortBy, sortDir, filter, pagination) {
            return RequestService.request('getAllClients', {type: 'get'}, 
                {sortBy: sortBy, sortDir: sortDir, filter: filter, pagination: pagination});
        },

        saveClientStation: function(clientStation) {
            return RequestService.request('saveClientStation/' + clientStation.id, {type: 'post'}, clientStation);
        },

        deleteClientStation: function(clientStation) {
            return RequestService.request('removeClientStation/' + clientStation.id, {type: 'post'});
        },

        getStationsForClient: function(clientId) {
            return RequestService.request('getStationsForClient/' + clientId, {type:'get'});
        }
    }
};


app.service.OrderService = function($http, RequestService) {
    return {
        //retrieves a blank order from the server
        startNewOrder: function() {
            return RequestService.request('startNewOrder', {type: 'get'});
        },

        saveOrder: function(order) {
            return RequestService.request('saveOrder/' + order.id, {type: 'post'}, order);
        },

        getOrder: function(id) {
            return RequestService.request('getOrder/' + id, {type: 'get'});
        },

        getAllOrders: function(sortBy, sortDir, filter, pagination) {
            return RequestService.request('getAllOrders', {type: 'get'}, 
                {sortBy: sortBy, sortDir: sortDir, filter: filter, pagination: pagination});
        },

        getOrdersForClient: function(clientId, dateFrom, dateTo) {
            return RequestService.request('getOrdersForClient/' + clientId, {type: 'get'}, { dateFrom: dateFrom, dateTo: dateTo });
        },

        getOrdersForClientStation: function(clientStationId, dateFrom, dateTo) {
            return RequestService.request('getOrdersForClientStation/' + clientStationId, {type: 'get'}, { dateFrom: dateFrom, dateTo: dateTo });
        },

        removeOrder: function(order) {
            return RequestService.request('removeOrder/' + order.id, {type: 'post'});
        }
    }
};


app.service.InvoiceService = function($http, RequestService) {
    return {
        startNewInvoice: function() {
            return RequestService.request('startNewInvoice', {type: 'get'});
        },

        saveInvoice: function(invoice) {
            return RequestService.request('saveInvoice/' + invoice.id, {type: 'post'}, invoice);
        },

        getInvoice: function(id) {
            return RequestService.request('getInvoice/' + id, {type: 'get'});
        },

        // DEFUNCT
        getInvoices: function(filter) {
            return RequestService.request('getInvoices', {type: 'get'},  filter);
        },

        getAllInvoices: function(sortBy, sortDir, filter, pagination) {
            return RequestService.request('getAllInvoices', {type: 'get'}, 
                {sortBy: sortBy, sortDir: sortDir, filter: filter, pagination: pagination});
        },

        removeInvoice: function(invoice) {
            return RequestService.request('removeInvoice/' + invoice.id, {type: 'post'});
        },

        generateInvoicePDF: function(invoice) {
            return RequestService.request('generateInvoicePDF/' + invoice.id, {type: 'post'});
        },

        removeInvoice: function(invoice) {
            return RequestService.request('removeInvoice/' + invoice.id, {type: 'post'});
        }
    }
};


app.service.UserService = function($http, $q, RequestService) {
    var currentUser = null;

    return {
        getCurrentUser: function() {
            return currentUser;
        },

        setCurrentUser: function(user) {
            currentUser = user;
        },

        loginUser: function(username, password) {
            var data = { username: username, password: password };

            var q = $q.defer();

            RequestService.request('loginUser', {type: 'post', suppressError: true}, data)
                .then(function(response) {
                    console.log("response", response);
                    currentUser = response.data.data;
                    q.resolve(response);
                }); 

            return q.promise;
        },

        logoutUser: function() {
            return RequestService.request('logoutUser', {type: 'post'});
        },

        //retrieves a blank user from the server
        startNewUser: function() {
            return RequestService.request('startNewUser', {type: 'get'});
        },

        saveUser: function(user) {
            return RequestService.request('saveUser/' + user.id, {type: 'post'}, user);
        },

        getUser: function(id) {
            return RequestService.request('getUser/' + id, {type: 'get'});
        },

        getAllUsers: function(sortBy, sortDir) {
            return RequestService.request('getAllUsers', {type: 'get'}, {sortBy: sortBy, sortDir: sortDir});
        },

        getAllUserRoles: function() {
            return RequestService.request('getAllUserRoles', {type: 'get'});
        }
    }
};


app.service.SettingsService = function($http, RequestService) {
    return {
        saveSetting: function(setting) {
            return RequestService.request('saveSetting/' + setting.id, {type: 'post'}, setting);
        },

        getSetting: function(id) {
            return RequestService.request('getSetting/' + id, {type: 'get'});
        },

        getAllSettings: function(sortBy, sortDir) {
            return RequestService.request('getAllSettings', {type: 'get'}, {sortBy: sortBy, sortDir: sortDir});
        },

        removeSetting: function(setting) {
            return RequestService.request('removeSetting/' + setting.id, {type: 'post'});
        }
    }
};


/* makes api requests */

app.service.RequestService = function($http) {
    return {
        baseUrl: '/service/',
        
        //returns a promise
        request: function (url, options, data) {
            var me = this;
            var asyncVal = true;
            var opts = { type: 'get', async: true };
            var data = data || {};
            url = this.baseUrl + url;

            angular.extend(opts, options);

            //if get and data exists, append to query string, using jQuery:
            if(opts.type.toLowerCase() == 'get' && typeof data != 'undefined') {
                url += '?' + $.param(data);
            }

            var http = $http({
                method: opts.type,
                url: url,
                data: data
            });

            http.then(function(response) {
                if(typeof response.data.success != 'undefined') {
                    if(response.data.success == false) {
                        var message = "Unknown service error while try to reach: " + url;

                        if(typeof response.data.message != 'undefined') {
                            message = response.data.message;
                        }

                        if(!options.suppressError) {
                            toastr.error("An error occurred! <br/>" + message);
                        }
                    }
                }
            });

            return http;
        }
    }
};

