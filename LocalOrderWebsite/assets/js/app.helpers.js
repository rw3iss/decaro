
var app = app || { directive: {}, factory: {}, service: {}, controller: {}, filter: {} };

app.directive.validateFloat = function () {
    var FLOAT_REGEXP = /^\-?(\d+)?((\.|\,)\d+)?$/;
    
    return {
        require: "ngModel",
        link: function(scope, elm, attrs, ngModelController){
 
            ngModelController.$parsers.unshift(function(viewValue) {
                if (viewValue == '') {
                    ngModelController.$setValidity('float', true);
                    return viewValue;
                }
                
                if (FLOAT_REGEXP.test(viewValue)) {
                    ngModelController.$setValidity('float', true);
                    return parseFloat(viewValue.replace(',', '.'));
                } 
                
                ngModelController.$setValidity('float', false);
                return undefined;
            });
        }
    };
};

app.directive.pagination = function () {
    return {
        restrict: 'E',
        scope: {
          currentPage: '=',
          pageTotal: '='
        },
        replace: true,
        template: function() {
            '<div class="pagination">' +
                '<div class="current">Page 1 of 2</div>' +
                '<div class="controls">' +
                    '<div class="first">First</div>' +
                    '<div class="'
                '</div>' +
            '</div>'
        },
        
        link: function(scope, elm, attrs) {

        }
    };
};

app.filter.formatDate = function() {
    return function(input, format) {
        if(typeof format == 'undefined')
            format = 'M/D/YYYY h:MM A';

        return moment(input).format(format);
    }
};

app.filter.formatPrice = function($filter) {
    return function(input, format) {
        if(!input) return;
        var price = input.replace(/\$/g, '').replace(',','');
        return $filter('currency')(price, '$');
    }
};

app.filter.camelcase = function() {
    return function(input) {
        if(input == null)
            return null;
        return input.charAt(0).toUpperCase() + input.slice(1).toLowerCase();
    }
};


app.filter.iif = function () {
   return function(input, trueValue, falseValue) {
        return input ? trueValue : falseValue;
   };
};
