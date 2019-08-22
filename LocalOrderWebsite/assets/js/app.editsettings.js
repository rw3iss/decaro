
/* Setting Listing */

app.controller.EditSettingsController = function($rootScope, $scope, $routeParams, $location, $timeout, SettingsService, UserService) {

    var currentUser = UserService.getCurrentUser();
    if(currentUser.role != "ADMINISTRATOR") {
        $location.path('/');
        toastr.error("You must be an adminstrator to view that page.");
        return;
    }

    //Initialize scope
    angular.extend($scope, {
        settings: [],
        filter: {
            name: ''
        },

        resetFilter: function() {
            $scope.filter = {
                name: ''
            }
        },

        filterSettings: function(settings) {
            if(settings.length == 0)
                return settings;

            var result = [], filterName = $scope.filter.name;

            for(var i in settings) {
                var s = settings[i];

                if( (s.name.toLowerCase().indexOf(filterName) >= 0) || 
                    (s.value.toLowerCase().indexOf(filterName) >= 0) )
                    result.push(s);
            }

            return result;
        },

        createNewSetting: function() {
            $scope.settings.push({ id: 0, name: '', value: '', editing: true });
        },

        saveSetting: function(setting) {
            if(setting.name == '' || setting.value == '') {
                alert("Please enter both a name and a value.");
                return;
            }

            SettingsService.saveSetting(setting).then(function(data) {
                //if a new setting, update the model:
                if(setting.id == 0) {
                    var existing = $scope.settings.filter(function(e, i, arr) {
                        if(e.id == 0 && e.name == data.data.name && e.value == data.data.value) {
                            return true;
                        }
                    });

                    if(existing.length > 0) {
                        existing[0].id = data.data.id;
                    }
                }

                toastr.success("Setting has been saved.");

                setting.editing =  false;
            });
        },

        removeSetting: function(setting) {
            if(confirm("Are you sure you want to remove this setting?")) {
                SettingsService.removeSetting(setting).then(function(response) {
                    //find the setting in the scope and remove it
                    if(response.data.success == true) {
                        var result = $.map($scope.settings, function(n) {
                            if(n.id == setting.id) {
                                return;// false;
                            }
                            return n;
                        }); 

                        $scope.settings = result;

                        toastr.success("Setting has been removed.");
                    }
                });
            }
        }
    });

    $scope.settings = $rootScope.settings;

    /*
    SettingsService.getAllSettings().then(function(data) {
        $scope.settings = data.data;
        console.log("setting loaded", $scope.settings);
    });
    */

    //$('.select2#state').select2();
    $timeout(function() {
        $('.select2#status').select2();  
    })
};
