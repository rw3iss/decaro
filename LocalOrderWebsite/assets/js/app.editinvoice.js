
/* Edit Clients */

app.controller.EditInvoiceController = function($rootScope, $scope, $routeParams, $location, $timeout, $filter, InvoiceService, ClientService, OrderService, UserService, StateList) {

    //Initialize scope
    angular.extend($scope, {
        invoice: null,
        orders: [],
        filter: {},
        stateIsValid: false,
        ordersLoaded: false,
        dateFormatString: 'M/D/YYYY',

        saveInvoice: function(isValid) {
            if(isValid) {  
                //grab selected order ids:
                var orders = $('.select-orders table tbody tr td .order-select:checked');
                var orderIds = [];

                orders.each(function(i, el) {
                    var id = parseInt($(el).attr('name').replace('cb_', ''));
                    orderIds.push(id);
                });

                $scope.invoice.order_ids = orderIds;
                $scope.invoice.date_from = moment($scope.invoice.date_from).format($scope.dateFormatString);
                $scope.invoice.date_to = moment($scope.invoice.date_to).format($scope.dateFormatString);
                $scope.invoice.date_due_by = moment($scope.invoice.date_due_by).format($scope.dateFormatString);
                $scope.invoice.invoice_date = moment($scope.invoice.invoice_date).format($scope.dateFormatString);

                InvoiceService.saveInvoice($scope.invoice).then(function(result) {
                  if (typeof result.data.success != 'undefined' && result.data.success == false) {
                  } else {
                        $scope.invoice.id = result.data.id;
                        toastr.success('Invoice has been saved.');
                    }
                });
            } else {
                toastr.error('The form is invalid!');
            }
        },

        deleteInvoice: function() {
            if(confirm("Are you sure you want to delete this invoice? All information will be lost and it cannot be undone")) {
                InvoiceService.removeInvoice($scope.invoice)
                    .then(function(response) {
                       if (response.data.success == true) {
                          toastr.warning("Invoice has been deleted.");
                          $location.path('/invoices');
                       }
                    });
            }
        },

        backToInvoices: function() {
            $location.path('/invoices');
        },

        clientChanged: function() {
            $scope.loadStationsForClient();
            //$scope.invoice.client_station_id = null;
            console.log("client changed, station:", $scope.client_station_id);
            $scope.orders = [];
        },

        clientStationChanged: function() {
            if ($scope.invoice.client_station_id == null || $scope.invoice.client_station_id == 0) {
                $scope.orders = [];
                return;
            }
            
           $scope.loadOrdersForClientStation();
        },

        loadStationsForClient: function() {
            var clientId = $scope.invoice.client_id;
            if(clientId == 0) {
                $scope.clientStations = [];
                return;
            }

            ClientService.getStationsForClient(clientId).then(function(response) {
                $scope.clientStations = response.data;

                $scope.loadOrdersForClientStation();
            });
        },

        loadOrdersForClientStation: function() {
            if ($scope.invoice.client_station_id == null || $scope.invoice.client_station_id == 0) {
                return;
            }
            
            var clientStationId = $scope.invoice.client_station_id;
            var dateFrom = moment($scope.invoice.date_from).format($scope.dateFormatString),
                dateTo = moment($scope.invoice.date_to).format($scope.dateFormatString);

            //change list of incoices
            OrderService.getOrdersForClientStation(clientStationId, dateFrom, dateTo).then(function(result) {
                $scope.orders = result.data;
                $scope.ordersLoaded = true;

                $timeout(function() {
                    for(var i in $scope.invoice.order_ids) {
                        var oid = parseInt($scope.invoice.order_ids[i]);
                        var occ = $('.select-orders table tbody tr.order_' + oid);
                        var oc = occ.find('td .order-select');
                        if(oc.length > 0) {
                            occ.addClass('checked');
                            oc.attr('checked', true);
                        }
                    }
                });
            });
        },

        orderIsInList: function(orderId) {
            for(var i in $scope.invoice.order_ids) {
                var oid = parseInt($scope.invoice.order_ids[i]);
                if(oid == orderId)
                    return true;
            }
            return false;
        },

        selectAllOrders: function() {
            $('.select-orders table tbody tr').addClass('checked');
            var orders = $('.select-orders table tbody tr td .order-select').prop('checked', true);
        },

        deselectAllOrders: function() {
            $('.select-orders table tbody tr').removeClass('checked');
            var orders = $('.select-orders table tbody tr td .order-select').prop('checked', false);
        },

        generateInvoicePDF: function() {
            //alert('Generating invoice: ' + $rootScope.domainUri + "service/generateInvoicePDF/" + $scope.invoice.id);
            window.open($rootScope.domainUri + "service/generateInvoicePDF/" + $scope.invoice.id);
            //InvoiceService.generateInvoicePDF($scope.invoice);
        },

        generateManifestPDF: function() {
            window.open($rootScope.domainUri + "service/generateManifestPDF/" + $scope.invoice.id);
            //InvoiceService.generateInvoicePDF($scope.invoice);
        },

        orderSelected: function(order, event) {
            var tgt = $(event.target);
            console.log("Target", tgt);
            if(tgt.hasClass('order-select') || tgt.hasClass('view-order'))
                return;

            var occ =  $('.select-orders table tbody tr.order_' + order.id);
            var oc = occ.find('.order-select');

            if(oc.is(':checked')) {
                oc.attr('checked', false);
                occ.removeClass('checked');
                for(var i in $scope.invoice.order_ids) {
                    var oid = parseInt($scope.invoice.order_ids[i]);
                    if(oid == order.id) {
                        $scope.invoice.order_ids.splice(i,1);
                    }
                }
            }
            else {
                oc.attr('checked', true);
                occ.addClass('checked');
                $scope.invoice.order_ids.push(order.id);
            }
        },

        calculateInvoiceTotal: function() {
            if(!$scope.invoice) {
                return 0;
            }

            var total = 0;
            var orders = $('.select-orders table tbody tr td .order-select:checked');
            var orderIds = [];

            orders.each(function(i, el) {
                var id = parseInt($(el).attr('name').replace('cb_', ''));
                orderIds.push(id);
            });

            var matchedOrders = $.grep($scope.orders, function(e) {
                var id = parseInt(e.id);
                return $.inArray(id, orderIds) != -1;
            });

            for(var i in matchedOrders) {
                var o = matchedOrders[i];
                if(o.pod_total) {
                    total += parseFloat(o.pod_total.replace('$', '').replace(',',''));
                }
            }

            $scope.invoice.total = $filter('currency')(total, '$');

            return $scope.invoice.total;
        }
    });

    //Load the editing client
    if(typeof $routeParams.id == 'undefined') {
        //new invoice
        $scope.invoice = {
            id: 0,
            client_id: 0,
            order_ids: [],
            date_from: moment().subtract(14, 'd').format($scope.dateFormatString),
            date_to: moment().format($scope.dateFormatString),
            total: 0,
            date_due_by: moment().subtract(14, 'd').format($scope.dateFormatString),
            invoice: moment().subtract(13, 'd').format($scope.dateFormatString),
            date_paid: null
        }

        /*
        InvoiceService.startNewInvoice().then(function(response) {
            $scope.invoice = response.data;
        });
        */
    } else {
        //get existing order
        InvoiceService.getInvoice($routeParams.id)
            .error(function(data, status, headers) {
                toastr.error("Could not find that invoice.<br/>Redirecting to invoice listing...");
                $timeout(function() {
                    $location.path('/invoices');
                }, 500);
            })
            .then(function(response) {
                $scope.invoice = response.data;

                //format datetimes
                /*
                $scope.invoice.date_from = moment($scope.invoice.date_from).format($scope.dateFormatString);
                $scope.invoice.date_to = moment($scope.invoice.date_to).format($scope.dateFormatString);
                $scope.invoice.date_due_by = moment($scope.invoice.date_due_by).format($scope.dateFormatString);
                */
                //console.log("Invoice", $scope.invoice);

                //$('.select2#state').select2();
                $timeout(function() {
                    $scope.states = StateList.states;

                    var dm = moment($scope.invoice.date_from);
                    console.log("date from", dm);
                    $scope.invoice.date_from = dm.format($scope.dateFormatString);

                    dm = moment($scope.invoice.date_to);
                    $scope.invoice.date_to = dm.format($scope.dateFormatString);

                    dm = moment($scope.invoice.date_due_by);
                    $scope.invoice.date_due_by = dm.format($scope.dateFormatString);

                    dm = moment($scope.invoice.invoice_date);
                    $scope.invoice.invoice_date = dm.format($scope.dateFormatString);
                });

                $scope.clientChanged();
            });
    }


    $timeout(function() {

        var dp = $('.datepicker#dateFrom').datetimepicker({
            pickTime: false
        }).on('dp.change', function(e) {

          $scope.invoice.date_from = e.date.format($scope.dateFormatString);
          $scope.$apply();

          $scope.loadOrdersForClientStation();
        });

        $('.datepicker#dateTo').datetimepicker({
            pickTime: false
        }).on('dp.change', function(e) {
          $scope.invoice.date_to = e.date.format($scope.dateFormatString);
          $scope.$apply();

          $scope.loadOrdersForClientStation();
        });

        $('.datepicker#invoiceDueBy').datetimepicker({
            pickTime: false
        }).on('dp.change', function(e) {
          $scope.invoice.date_due_by = e.date.format($scope.dateFormatString);
          $scope.$apply();
        });

        $('.datepicker#invoiceDate').datetimepicker({
            pickTime: false
        }).on('dp.change', function(e) {
          $scope.invoice.invoice_date = e.date.format($scope.dateFormatString);
          $scope.$apply();
        });

    }, 500);

    //load clients/shippers for dropdown
    ClientService.getAllClients('name', 'desc', null, null).then(function(data) {
        $scope.clients = data.data.clients;
    });

};