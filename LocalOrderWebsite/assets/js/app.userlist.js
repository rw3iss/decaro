
/* User Listing */

app.controller.UserListController = function($rootScope, $scope, $routeParams, $location, $timeout, UserService) {

    var currentUser = UserService.getCurrentUser();
    if(currentUser.role != "ADMINISTRATOR") {
        $location.path('/')
        toastr.error("You must be an adminstrator to view that page.");
        return;
    }

    //Initialize scope
    angular.extend($scope, {
        users: [],
        filter: {
            name: '',
            status: 'ALL'
        },

        resetFilter: function() {
            $scope.filter = {
                name: '',
                status: 'ALL'
            }
        },

        createNewUser: function() {
            $location.path('newuser');
        },

        filterUsers: function(users) {
            if(users.length == 0)
                return users;

            return users.filter(function(e, i, arr) {
                var filterName = $scope.filter.name.toLowerCase();

                if(e.username.toLowerCase().indexOf(filterName) >= 0)
                    return true;
                
                if(e.firstname.toLowerCase().indexOf(filterName) >= 0)
                    return true;
                
                if(e.lastname.toLowerCase().indexOf(filterName) >= 0)
                    return true;
                
                return false;
            }); 
        },

        gotoUser: function(id) {
            $location.path('edituser/' + id);
        }
    });

    UserService.getAllUsers().then(function(data) {
        $scope.users = data.data;
    });

    UserService.getAllUserRoles().then(function(data) {
        $scope.userRoles = data.data;
    });

    //$('.select2#state').select2();
    $timeout(function() {
        $('.select2#status').select2();  
    })
};
