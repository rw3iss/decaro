
/* Client Listing */

app.controller.InvoiceListController = function($rootScope, $scope, $routeParams, $location, $timeout, InvoiceService) {

    //Initialize scope
    angular.extend($scope, {
        loading: true,

        invoices: [],
        filterResults: [],

        filter: {
            invoice_id: '',
            client_id: 0, // not used at the moment
            client_name: '',
            status: 'ALL',
            date_from: '',
            date_to: ''
        },

        currentPage: 1,
        pageSize: 25,
        totalRecordCount: 0,

        sortBy: 'date_created',
        sortDir: 'desc',

        resetFilter: function() {
            $scope.filter = {
                invoice_id: '',
                client_id: 0,
                client_name: '',
                status: 'ALL',
                date_from: '',
                date_to: ''
            }

            //$('#filter #client').select2('val', 0);

            $scope.updateResults();
        },

        createNewInvoice: function() {
            $location.path('newinvoice');
        },

        // filterInvoices: function(invoices) {
        //     if(invoices.length == 0)
        //         return invoices;

        //     var invoices = invoices.filter(function(e, i, arr) {
        //         var filterInvoiceNumber = $.trim($scope.filter.invoice_number);
        //         var filterName = $.trim($scope.filter.name.toLowerCase());

        //         var matches = true;

        //         if (filterName.length) {
        //             if (e.client)
        //                 if (e.client.name.toLowerCase().indexOf(filterName) == -1)
        //                     matches = false;
        //         }

        //         if (filterInvoiceNumber.length) {
        //             if (e.invoice_number.indexOf(filterInvoiceNumber) == -1)
        //                 matches = false;
        //         }

        //         return matches;
        //     }); 

        //     return invoices;
        // },

        filterChanged: function() {
            return $scope.updateResults();
        },

        gotoInvoice: function(id) {
            $location.path('editinvoice/' + id);
        },

        updateResults: function() {
            //console.log("Update results", $scope.pageSize, $scope.currentPage);
            InvoiceService.getAllInvoices( $scope.sortBy, $scope.sortDir, $scope.filter, 
                { page: $scope.currentPage, resultsPerPage: $scope.pageSize })
            .then(function(data) {
                $scope.invoices = $scope.filterResults = data.data.invoices;
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

    //start with all invoices in previous 30 days
    //var dateRange = {datefrom: 'now - 30 days', dateTo: 'now', page: 1 };

    // InvoiceService.getInvoices(dateRange).then(function(data) {
    //     $scope.invoices = data.data;
    // });
};
