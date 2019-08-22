
/* Order Listing */

app.controller.OrderListController = function($rootScope, $scope, $routeParams, $location, $timeout, OrderService, ClientService) {

    //Initialize scope
    angular.extend($scope, {
        loading: true,

        orders: [],
        filterResults: [],

        filter: {
            client_id: 0,
            order_number: '',
            status: 'ALL',
            date_from: '',
            date_to: '',
            sort: 'date_created'
        },

        currentPage: 1,
        pageSize: 25,
        totalRecordCount: 0,

        sortBy: 'date_created',
        sortDir: 'desc',

        dateFormatString: 'M/DD/YYYY',

        resetFilter: function() {
            $scope.filter = {
                client_id: 0,
                order_number: '',
                status: 'ALL',
                date_from: '',
                date_to: '',
                sort: 'date_created'
            }

            $('#filter #client').select2('val', 0);
            
            $scope.updateResults();
        },

        createNewOrder: function() {
            $location.path('/neworder');
        },

        sortResults: function(sortBy) {
             if($scope.sortBy == sortBy) {
                if($scope.sortDir == 'asc')
                    $scope.sortDir = 'desc';
                else
                    $scope.sortDir = 'asc';
            } else {
                $scope.sortBy = sortBy;
                $scope.sortDir = 'desc';
            }

            $scope.updateResults();
        },

        updateResults: function() {
            console.log("Update", $scope.filter.sort);

            //console.log("Update results", $scope.pageSize, $scope.currentPage);
            OrderService.getAllOrders($scope.filter.sort, $scope.sortDir, $scope.filter, 
                { page: $scope.currentPage, resultsPerPage: $scope.pageSize })
            .then(function(data) {
                $scope.orders = $scope.filterResults = data.data.orders;
                $scope.totalRecordCount = data.data.totalRecords;
                $scope.loading = false;
            })
            .catch(function(error) {
                $scope.loading = false;
                toastr.error(error);
            })
        },

        pageChangeHandler: function(num) {
            if (num)
                $scope.currentPage = num;
            $scope.updateResults();
        },

        filterChanged: function() {
            return $scope.updateResults();
        },

        gotoOrder: function(id) {
            $location.path('editorder/' + id);
        },

        selectDateFrom: function() {
            $('#filterDateFromInput').select();
        },

        selectDateTo: function() {
            $('#filterDateToInput').select();
        }
    });

    //add triggers to filter changes:
    $('.datetimepicker#datefrom').datetimepicker({
        }).on('dp.change', function(e) {
          var date = e.date.format($scope.dateFormatString);
          $scope.filter.date_from = date;
          $scope.$apply();
          $scope.updateResults();
        });

    $('.datetimepicker#dateto').datetimepicker({
        }).on('dp.change', function(e) {
          var date = e.date.format($scope.dateFormatString);
          $scope.filter.date_to = date;
          $scope.$apply();
          $scope.updateResults();
        });

    $scope.updateResults();

    //load clients/shippers for dropdown
    ClientService.getAllClients('name', 'desc', null, null).then(function(data) {
        $scope.clients = data.data.clients;
    });

    $timeout(function() {
        $('#filter .datetimepicker#datefrom').datetimepicker({
            pickTime: false
        }).on('dp.change', function(e) {
          $scope.filter.datefrom = e.date.format($scope.dateFormatString);
          $scope.$apply();
        });

        $('#filter .datetimepicker#dateto').datetimepicker({
            pickTime: false
        }).on('dp.change', function(e) {
          $scope.filter.dateto = e.date.format($scope.dateFormatString);
          $scope.$apply();
        });

    });
};
