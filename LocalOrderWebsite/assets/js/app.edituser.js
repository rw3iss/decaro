
/* Edit Users */

app.controller.EditUserController = function($rootScope, $scope, $routeParams, $location, $timeout, UserService, StateList) {

    var currentUser = UserService.getCurrentUser();
    if(currentUser.role != "ADMINISTRATOR") {
        $location.path('/');
        toastr.error("You must be an adminstrator to view that page.");
        return;
    }

    //Initialize scope
    angular.extend($scope, {
        user: null,

        stateIsValid: false,

        saveUser: function(isValid) {
            if(isValid) {
                console.log("Saving", $scope.user, $('.select2#state').val());
                UserService.saveUser($scope.user).then(function(result) {
                    console.log("Save result", result.data);
                    $scope.user = result.data;
                    toastr.success('User has been saved.');
                }); 
            } else {
                toastr.error('The form is invalid!');
            }
        },

        deleteUser: function() {
            if(confirm("Are you sure you want to delete this user? All information will be lost and it cannot be undone")) {
                UserService.deleteUser($scope.user);
            }
        },  

        backToUsers: function() {
            $location.path('/users');
        }
    });

    //Load the editing user
    if(typeof $routeParams.id == 'undefined') {
        //new order
        UserService.startNewUser().then(function(response) {
            $scope.user = response.data;
        });
    } else {
        //get existing order
        UserService.getUser($routeParams.id)
            .error(function(data, status, headers) {
                alert("Could not find that user. Redirecting to users listing...");
                $location.path('/users');
            })
            .then(function(response) {
                $scope.user = response.data;

                $timeout(function() {

                $('.select2#state').val($scope.user.state);
            },100);
                console.log('user loaded', $scope.user);
            });
    }

    UserService.getAllUserRoles().then(function(data) {
        console.log("Roles loaded", data.data);
        $scope.userRoles = data.data;
    });

    //$('.select2#state').select2();
    $timeout(function() {
        $scope.states = StateList.states;
    }, 500);
};