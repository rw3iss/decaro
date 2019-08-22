
var app = app || { directive: {}, factory: {}, service: {}, controller: {}, filter: {} };

angular.module('decaro', ['ngRoute', 'ngAnimate', 'ngCookies', 'angularUtils.directives.dirPagination']).
    config(function($routeProvider, $locationProvider, $httpProvider) {
        //$locationProvider.html5Mode({require});

        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
 
      /**
       * The workhorse; converts an object to x-www-form-urlencoded serialization.
       * @param {Object} obj
       * @return {String}
       */ 
      var param = function(obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for(name in obj) {
          value = obj[name];

          if(value instanceof Array) {
            for(i=0; i<value.length; ++i) {
              subValue = value[i];
              fullSubName = name + '[' + i + ']';
              innerObj = {};
              innerObj[fullSubName] = subValue;
              query += param(innerObj) + '&';
            }
          }
          else if(value instanceof Object) {
            for(subName in value) {
              subValue = value[subName];
              fullSubName = name + '[' + subName + ']';
              innerObj = {};
              innerObj[fullSubName] = subValue;
              query += param(innerObj) + '&';
            }
          }
          else if(value !== undefined && value !== null)
            query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
      };

        // Override $http service's default transformRequest
        $httpProvider.defaults.transformRequest = [function(data) {
            return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
        }];

        $routeProvider.when('/', {
            templateUrl: '/partial/home.html',
            controller: app.controller.HomeController,
        });

        $routeProvider.when('/login', {
            templateUrl: '/partial/login.html',
            controller: app.controller.LoginController,
        });

        /* orders */
        $routeProvider.when('/neworder', {
            templateUrl: '/partial/editorder.html',
            controller: app.controller.EditOrderController,
        })

        $routeProvider.when('/editorder/:id', {
            templateUrl: '/partial/editorder.html',
            controller: app.controller.EditOrderController,
        })

        $routeProvider.when('/orders', {
            templateUrl: '/partial/orderlist.html',
            controller: app.controller.OrderListController,
        })

        /* clients */
        $routeProvider.when('/newclient', {
            templateUrl: '/partial/editclient.html',
            controller: app.controller.EditClientController,
        })

        $routeProvider.when('/editclient/:id', {
            templateUrl: '/partial/editclient.html',
            controller: app.controller.EditClientController,
        })

        $routeProvider.when('/clients', {
            templateUrl: '/partial/clientlist.html',
            controller: app.controller.ClientListController,
        })

        /* invoicing */
        $routeProvider.when('/newinvoice', {
            templateUrl: '/partial/editinvoice.html',
            controller: app.controller.EditInvoiceController,
        })

        $routeProvider.when('/editinvoice/:id', {
            templateUrl: '/partial/editinvoice.html',
            controller: app.controller.EditInvoiceController,
        })

        $routeProvider.when('/invoices', {
            templateUrl: '/partial/invoicelist.html',
            controller: app.controller.InvoiceListController,
        })

        /* manifests (same as invoices for now) */
        $routeProvider.when('/newmanifest', {
            templateUrl: '/partial/editinvoice.html',
            controller: app.controller.EditInvoiceController,
        })

        $routeProvider.when('/editmanifest/:id', {
            templateUrl: '/partial/editinvoice.html',
            controller: app.controller.EditInvoiceController,
        })

        $routeProvider.when('/manifests', {
            templateUrl: '/partial/invoicelist.html',
            controller: app.controller.InvoiceListController,
        })

        /* users */
        $routeProvider.when('/newuser', {
            templateUrl: '/partial/edituser.html',
            controller: app.controller.EditUserController,
        })

        $routeProvider.when('/edituser/:id', {
            templateUrl: '/partial/edituser.html',
            controller: app.controller.EditUserController,
        })

        $routeProvider.when('/users', {
            templateUrl: '/partial/userlist.html',
            controller: app.controller.UserListController,
        })

        /* settings */
        $routeProvider.when('/settings', {
            templateUrl: '/partial/editsettings.html',
            controller: app.controller.EditSettingsController,
        })

        .otherwise({redirectTo: '/'})
      }).
  directive(app.directive).
  factory(app.factory).
  service(app.service).
  controller(app.controller).
  filter(app.filter);

angular.module('app.config', []).
    value('appName', 'Blobs').
    value('configHttpCacheName', 'BLOBS-CACHE');

angular.module('app.caches', ['blobs.config']).
    factory('HttpCache', function($cacheFactory, configHttpCacheName) {
        return $cacheFactory(configHttpCacheName);
    });

//do some startup stuff
angular.module('decaro').run(function($rootScope, $location, $cookies, SettingsService, UserService) {
    //get the current user
    $rootScope.domainUri = "http://internal.decarotrucking.com/";

    var user = UserService.getCurrentUser();
    if(user == null) {
        //look for the user in a cookie
        if(typeof $cookies.userlogin != 'undefined') {
            var userlogin = JSON.parse($cookies.userlogin);
            UserService.setCurrentUser(userlogin);
        } else {
            $location.path('/login');
        }

        /*
        .then(function(response) {
            $rootScope.currentUser = response.data;
            console.log("user loaded", $rootScope.currentUser);
        });
        */
    }

    //load global settings for every page
    SettingsService.getAllSettings().then(function(response) {
        $rootScope.settings = response.data;
    });

    $rootScope.$on('$viewContentLoaded', function(){ 
        //scroll to the top of the page every view change
        $("html, body").animate({ scrollTop: 0 }, "fast");
    });
});

app.factory.StateList = function() {
    return {
        states: {
            "AL": "Alabama",
            "AK": "Alaska",
            "AZ": "Arizona",
            "AR": "Arkansas",
            "CA": "California",
            "CO": "Colorado",
            "CT": "Connecticut",
            "DE": "Delaware",
            "DC": "District Of Columbia",
            "FL": "Florida",
            "GA": "Georgia",
            "GU": "Guam",
            "HI": "Hawaii",
            "ID": "Idaho",
            "IL": "Illinois",
            "IN": "Indiana",
            "IA": "Iowa",
            "KS": "Kansas",
            "KY": "Kentucky",
            "LA": "Louisiana",
            "ME": "Maine",
            "MD": "Maryland",
            "MA": "Massachusetts",
            "MI": "Michigan",
            "MN": "Minnesota",
            "MS": "Mississippi",
            "MO": "Missouri",
            "MT": "Montana",
            "NE": "Nebraska",
            "NV": "Nevada",
            "NH": "New Hampshire",
            "NJ": "New Jersey",
            "NM": "New Mexico",
            "NY": "New York",
            "NC": "North Carolina",
            "ND": "North Dakota",
            "OH": "Ohio",
            "OK": "Oklahoma",
            "OR": "Oregon",
            "PW": "Palau",
            "PA": "Pennsylvania",
            "RI": "Rhode Island",
            "SC": "South Carolina",
            "SD": "South Dakota",
            "TN": "Tennessee",
            "TX": "Texas",
            "UT": "Utah",
            "VT": "Vermont",
            "VA": "Virginia",
            "WA": "Washington",
            "WV": "West Virginia",
            "WI": "Wisconsin",
            "WY": "Wyoming"
        }
    }
};

//Main app controller
app.controller.HomeController = function($rootScope, $scope, $location, $http) {
    angular.extend($scope, {
        gotoNewOrder: function() {
            $location.path('/neworder');
        },

        gotoOrderList: function() {
            $location.path('/orders');
        },

        gotoNewClient: function() {
            $location.path('/newclient');
        },

        gotoClientList: function() {
            $location.path('/clients');
        },

        gotoNewInvoice: function() {
            $location.path('/newinvoice');
        },

        gotoInvoiceList: function() {
            $location.path('/invoices');
        },

        gotoNewManifest: function() {
            $location.path('/newmanifest');
        },

        gotoManifestList: function() {
            $location.path('/manifests');
        }

    });
};

//Main app controller
app.controller.LoginController = function($rootScope, $scope, $location, $http, UserService) {
    //console.log("Login controller", UserService);

    angular.extend($scope, {
        username: '',
        password: '',

        tryLogin: function(value) {
            $scope.login();
        }, 

        login: function() {
            UserService.loginUser($scope.username, $scope.password).then(function(response) {
                if(response.data.error) {
                    toastr.error(response.data.message);
                } else {
                    toastr.success("Success! Logging you in " + response.data.data.firstname + '...');
                    $location.path('/manage');
                    window.location.href = "manage";
                }
            });
        }
    });
};

//Main app controller
app.controller.NavigationController = function($rootScope, $scope, $location, $http, UserService) {
    angular.extend($scope, {
        currentUser: UserService.getCurrentUser(),
        logout: function() {
            UserService.logoutUser().then(function(response) {
                window.location.href = "/";
            });
        }
    });
};

//setup toastr
toastr.options.showEasing = 'swing';
toastr.options.hideEasing = 'linear';
toastr.options.positionClass = 'toast-bottom-right';

$(document).ready(function() {
    $.nonbounce();
});
