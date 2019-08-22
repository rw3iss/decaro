
/* Client Listing */

app.controller.ClientListController = function($rootScope, $scope, $routeParams, $location, $timeout, ClientService) {

    //Initialize scope
    angular.extend($scope, {
        clients: [],
        filterResults: [],
        filter: {
            clientName: '',
            sort: 'name'
        },

        currentPage: 1,
        pageSize: 25,
        totalRecordCount: 0,

        sortBy: 'name',
        sortDir: 'desc',

        resetFilter: function() {
            $scope.filter = {
                clientName: '',
            }

            //$('#filter #client').select2('val', 0);
            $scope.updateResults();
        },

        filterChanged: function() {
            $scope.updateResults();
            return;
        },

        createNewClient: function() {
            $location.path('newclient');
        },

        filterClients: function(clients) {
            if(clients.length == 0 || $scope.filter.clientName == '')
                return clients;

            var searchString = $scope.filter.clientName.toLowerCase();

            return clients.filter(function(e, i, arr) {
                if(e.name.toLowerCase().indexOf(searchString) >= 0)
                    return true;
                
                return false;
            }); 
        },

        updateResults: function() {
            console.log("filter changed", $scope.sortBy, $scope.filter.sort);

            ClientService.getAllClients($scope.filter.sort, $scope.sortDir, $scope.filter, 
                {page:$scope.currentPage, resultsPerPage:$scope.pageSize}).then(function(data) {
                console.log("Loaded clients", data);
                $scope.clients = $scope.filterResults = data.data.clients;
                $scope.totalRecordCount = data.data.totalRecords;
            });
        },

        pageChangeHandler: function(num) {
            $scope.updateResults();
        },

        gotoClient: function(id) {
            $location.path('editclient/' + id);
        }
    });

    $scope.updateResults();

    //$('.select2#state').select2();
    $timeout(function() {
        $('.select2#client').select2();
        $('.select2#status').select2();  
    })
};
