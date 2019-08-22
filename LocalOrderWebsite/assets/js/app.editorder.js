/* Edit Order */
app.controller.EditOrderController = function($rootScope, $scope, $routeParams, $location, $timeout, $filter, OrderService, ClientService, StateList) {

   //Initialize scope
   angular.extend($scope, {
      order: null,
      orderLoaded: false,
      orderSaved: false, // if it's been committed at all (necessary for printing)

      clients: [], //for shipper dropdown
      states: StateList.states, //for states dropdown

      dateCreatedEditText: 'Change',
      dateFormatString: 'M/D/YYYY h:MMA',

      saveOrder: function(isValid) {
         if (!isValid) {
            toastr.warning('Warning: Some required fields are missing.');
         }

         if (true) { //isValid) {
            OrderService.saveOrder($scope.order).then(function(result) {
               if (typeof result.data.success != 'undefined' && result.data.success == false) {} else {
                  toastr.success('Order has been saved.');

                  if ($scope.order.id == 0) {
                     $location.path('/editorder/' + result.data.id);
                     return;
                  }

                  // var dm = moment($scope.order.date_created);
                  // $scope.order.date_created = dm.format($scope.dateFormatString);
                  // var dm = moment($scope.order.ready_time);
                  // $scope.order.ready_time = dm.format($scope.dateFormatString);
                  // var dm = moment($scope.order.close_time);
                  // $scope.order.close_time = dm.format($scope.dateFormatString);

                  // if($scope.order.pod_date) {
                  //   var dm = moment($scope.order.pod_date);
                  //   $scope.order.pod_date = dm.format($scope.dateFormatString);
                  // }
               }
            });
         } else {
            toastr.error('Please fill in all required fields!');
         }
      },

      deleteOrder: function() {
         if (confirm("Are you sure you want to delete this order? All information will be lost and cannot be undone.")) {
            OrderService.removeOrder($scope.order).then(function(response) {
               if (response.data.success == true) {
                  toastr.warning("Order has been deleted.");
                  $location.path('/orders');
               }
            });
         }
      },

      generateOrderPDF: function() {
         window.open($rootScope.domainUri + "service/generateOrderPDF/" + $scope.order.id);
      },

      backToOrders: function() {
         $location.path('/orders');
      },

      orderStatusChanged: function() {
         if ($scope.order.status != 'COMPLETE') {
            //clear out POD entries
            //$scope.order.pod_signature = '';
            //$scope.order.pod_date = null;
            //$scope.order.pod_total = null;
         }
      },

      togglePayment: function(type) {
         $scope.order.payment_type == type;
      },

      clientChanged: function() {
         var clientId = $scope.order.client_id;
         ClientService.getStationsForClient(clientId).then(function(response) {
            $scope.clientStations = response.data;

            // if (typeof $routeParams.id == 'undefined') {
            $('#client_station').val(0);
            //} else {
               //console.log("SET", $scope.order.client_station_id);
               //$('#client_station').val($scope.order.client_station_id);
            //}

            $scope.orderForm.client_station.$dirty = false;
         });
      },

      clientStationChanged: function() {
         $scope.paymentTypeChanged();
      },

      //modified the order number, depending on OrderID+ClientID+Date
      updateOrderNumber: function() {
         if ($scope.order.id != 0) {
            var number = $scope.order.client_id;
            number += '-' + $scope.order.id;

            var dm = moment($scope.order.date_created);

            number += '-' + dm.format("YYMMDD");
            $scope.order.order_number = number;
         }
      },

      paymentTypeChanged: function() {
         $scope.order.third_party_address = '';
         $scope.order.third_party_address2 = '';
         $scope.order.third_party_city = '';
         $scope.order.third_party_state = '0';
         $('#thirdPartyState').val(0); //select2('val', 0);
         $scope.order.third_party_zipcode = '';
         $scope.orderForm.third_party_address.$dirty = false;
         $scope.orderForm.third_party_address2.$dirty = false;
         $scope.orderForm.third_party_city.$dirty = false;
         $scope.orderForm.third_party_state.$dirty = false;
         $scope.orderForm.third_party_zipcode.$dirty = false;

         //find station address
         var stationId = $scope.order.client_station_id;
         var station;

         if ($scope.clientStations != null) {
            var station = $.grep($scope.clientStations, function(e) {
               return e.id == stationId;
            });

            if (station == null) {
               toastr.error("An error occurred trying to find the address for the client station. Please contact technical support.");
            }

            if (Object.prototype.toString.call(station) === '[object Array]') {
               station = station[0];
            }
         }

         if (station == null)
            return;

         //if prepaid, set shipper origin to client's address
         if ($scope.order.payment_type == "prepaid") {
            $scope.order.origin_address = station.address;
            $scope.order.origin_address2 = station.address2;
            $scope.order.origin_city = station.city;
            $scope.order.origin_state = station.state;
            $('#originState').val(station.state); //select2('val', station.state);
            $scope.order.origin_zipcode = station.zipcode;
            $scope.order.destination_address = "";
            $scope.order.destination_address2 = "";
            $scope.order.destination_city = "";
            $scope.order.destination_state = "0";
            $('#destinationState').val(0); //select2('val', 0);
            $scope.order.destination_zipcode = "";
         } else if ($scope.order.payment_type == "thirdparty") {
            $scope.order.origin_address = "";
            $scope.order.origin_address2 = "";
            $scope.order.origin_city = "";
            $scope.order.origin_state = "0";
            $('#originState').val(0); //select2('val', 0);
            $scope.order.origin_zipcode = "";
            $scope.order.destination_address = "";
            $scope.order.destination_address2 = "";
            $scope.order.destination_city = "";
            $scope.order.destination_state = "0";
            $('#destinationState').val(0); //select2('val', 0);
            $scope.order.destination_zipcode = "";
         } else if ($scope.order.payment_type == "collect") {
            $scope.order.origin_address = "";
            $scope.order.origin_address2 = "";
            $scope.order.origin_city = "";
            $scope.order.origin_state = "0";
            $('#originState').val(0); //select2('val', 0);
            $scope.order.origin_zipcode = "";
            $scope.order.destination_address = station.address;
            $scope.order.destination_address2 = station.address2;
            $scope.order.destination_city = station.city;
            $scope.order.destination_state = station.state;
            $('#destinationState').val(0); //select2('val', station.state);
            $scope.order.destination_zipcode = station.zipcode;
         }

         $scope.orderForm.origin_state.$dirty = false;
         $scope.orderForm.destination_state.$dirty = false;
      },

      podPriceChanged: function() {
         if ($scope.order.pod_total) {
            var price = $scope.order.pod_total.replace(/\$/g, '');
            $scope.order.pod_total = $filter('currency')(price, '$');
         }
      },

      removeShipperAddress: function() {
         $scope.order.shipper_enabled = 0;
         $scope.order.origin_address = "";
         $scope.order.origin_address2 = "";
         $scope.order.origin_city = "";
         $scope.order.origin_state = "0";
      }

   });

   //Load the editing order
   if (typeof $routeParams.id == 'undefined') {
      //new order
      OrderService.startNewOrder().then(function(order) {
         $scope.order = order.data;
         $scope.order.date_created = moment().format($scope.dateFormatString);
         $scope.order.shipper_enabled = 0;
         $scope.order.delivery_type = 0;
         $('.select2#delivery_type').val(0); //select2('val', 0);
         //$scope.initSelects();
      });
   } else {
      //get existing order
      var order = OrderService.getOrder($routeParams.id)
         .error(function(data, status, headers) {
            toastr.error("Could not find that order.<br/>Redirecting to orders listing...");
            $location.path('/orders');
         })
         .then(function(response) {
            $scope.order = response.data;
            $scope.orderSaved = true;
            //console.log("got invoice", $scope.order.description.replace('\n','newline') );
            $scope.clientChanged();
            //$('#client_station').select2('val', $scope.order.client_station_id);

            var dm = moment($scope.order.date_created);
            $scope.order.date_created = dm.format($scope.dateFormatString);

            dm = moment($scope.order.ready_time);
            $scope.order.ready_time = dm.format($scope.dateFormatString);

            dm = moment($scope.order.close_time);
            $scope.order.close_time = dm.format($scope.dateFormatString);

            if ($scope.order.pod_date != null) {
               dm = moment($scope.order.pod_date);
               $scope.order.pod_date = dm.format($scope.dateFormatString);
            }

            //$scope.initSelects();

            $('.select2#client').val($scope.order.client_id); //select2('val', $scope.order.client_id);
            $('#select2client_station').val($scope.order.client_station_id);
            $('.select2#delivery_type').val($scope.order.delivery_type); //select2('val', $scope.order.delivery_type);
         });
   }

   //Initialize the select2 select boxes
   $('.datetimepicker#datecreated').datetimepicker({}).on('dp.change', function(e) {
      $scope.order.date_created = e.date.format($scope.dateFormatString);
      $scope.$apply();
   });

   $('.datetimepicker#readytime').datetimepicker({}).on('dp.change', function(e) {
      $scope.order.ready_time = e.date.format($scope.dateFormatString);
      $scope.$apply();
   });

   $('.datetimepicker#closetime').datetimepicker({}).on('dp.change', function(e) {
      $scope.order.close_time = e.date.format($scope.dateFormatString);
      $scope.$apply();
   });

   $('.datetimepicker#podDate').datetimepicker({}).on('dp.change', function(e) {
      $scope.order.pod_date = e.date.format($scope.dateFormatString);
      //console.log('date change', $scope.order.ready_time);
      $scope.$apply();
   });

   //$('.datetimepicker#datecreated').data('DateTimePicker').setDate($scope.order.date_created);

   $scope.orderLoaded = true;

   //Initialize the date pickers
   /*
   $('#datepicker').datepicker({
       selectedDate: new Date(),
       onDateChanged: function(date) {
           var dm = moment(date);
           $scope.order.date_created = dm.format("MM/DD/YYYY H:mm:ss a");

           $scope.dateCreatedEditText = 'Change';

           $scope.$apply();

           var c = $(this.container);
           window.setTimeout(function() {
               c.fadeOut(350);
           }, 150);
       }
   });
   /* */

   $('.select2#status').on("change", function() {
      $scope.orderStatusChanged();
   });

   //load clients/shippers for dropdown
   ClientService.getAllClients('name', 'desc', null, null).then(function(data) {
      $scope.clients = data.data.clients;

      $timeout(function() {
         //$('.select2#shipper').select2({});
      });
   });

};