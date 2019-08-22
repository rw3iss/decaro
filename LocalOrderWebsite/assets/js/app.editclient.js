
/* Edit Clients */

app.controller.EditClientController = function($rootScope, $scope, $routeParams, $location, $timeout, ClientService, OrderService, StateList) {
    $scope.$watch('client.state', function(state) {
        if (angular.isArray(state) && state.length === 0) {
          $scope.client.state = '';
        }
    });

    //Initialize scope
    angular.extend($scope, {
        client: null,

        dateFormatString: 'M/DD/YYYY',

        stateIsValid: false,

        editingStation: null,

        saveClient: function() {
            var isValid = $scope.clientForm.$valid;

            if(isValid) {
                ClientService.saveClient($scope.client).then(function(result) {
                    $scope.client = result.data;
                    toastr.success('Client has been saved.');
                });
            } else {
                toastr.error('The form is invalid!');
            }
        },

        deleteClient: function() {
            if(confirm("Are you sure you want to delete this client? All information will be lost and it cannot be undone")) {
                ClientService.deleteClient($scope.client.id).then(function(result) {
                    toastr.warning("Client '" + $scope.client.name + "' has been deleted.'");
                    $location.path('/clients');
                });
            }
        },

        backToClients: function() {
            $location.path('/clients');
        },

        newClientStation: function() {
            $scope.editingStation = {
                id: 0,
                name: 'Untitled',
                address: '',
                address2: '',
                city: '',
                state: '',
                zipcode: '',
                phone_number: '',
                fax_number: '',
            };

            $("#stationForm #station_name").focus();
        },

        editClientStation: function(cs) {
            console.log("Editing", cs);
            $scope.editingStation = cs;
        },

        saveClientStation: function() {
            var isValid = $scope.stationForm.$valid;
            
            if(isValid) {
                $scope.editingStation.client_id = $scope.client.id;

                ClientService.saveClientStation($scope.editingStation).then(function(result) {
                    if($scope.editingStation.id == 0) {
                        $scope.editingStation = result.data;
                        $scope.client.client_stations.push($scope.editingStation);
                    } else {
                        $scope.editingStation = result.data;
                    }

                    console.log("station result", result);

                    toastr.success('Client station has been saved.');

                    $scope.editingStation = null;
                    $scope.stationForm.$setPristine(true);
                });
            } else {
                toastr.error('The form is invalid!');
            }
        },

        deleteClientStation: function() {
            if(confirm("Are you sure you want to delete this client's station? You cannot undo this.")) {
                ClientService.deleteClientStation($scope.editingStation).then(function(result) {
                    //
                    $scope.editingStation = null;
                    toastr.success('Client station has been deleted.');
                });
            }
        },

        cancelClientStation: function() {
            $scope.editingStation = null;
            $scope.stationForm.$setPristine(true);
        },

        gotoOrder: function(o) {
            $location.path("/editorder/" + o.id);
        }
    });

    //Load the editing client
    if(typeof $routeParams.id == 'undefined') {
        //new order
        ClientService.startNewClient().then(function(response) {
            $scope.client = response.data;
            console.log("new client", $scope.client);

                    $location.path('/editclient/' + $scope.client.id);

            $scope.recentOrders = null;
            $scope.payments = null;
        });
    } else {
        //get existing order
        ClientService.getClient($routeParams.id)
            .error(function(data, status, headers) {
                toastr.error("Could not find that client.<br/>Redirecting to clients listing...");
                $timeout(function() {
                    $location.path('/clients');
                }, 500);
            })
            .then(function(response) {
                $scope.client = response.data;

            });

        //load client orders
        var dateFrom = moment().subtract(7, 'd').format($scope.dateFormatString);
        var dateTo = moment().format($scope.dateFormatString);

        OrderService.getOrdersForClient($routeParams.id, dateFrom, dateTo)
            .then(function(response) {
                $scope.recentOrders = response.data;
            });

        //load client payments
        $scope.payments = [];
    }

    //$('.select2#state').select2();
    $timeout(function() {
        $scope.states = StateList.states;

        //$('.select2#state').select2();  
    }, 500);
};