



// I don't think this is used anymore...





angular.module('directives.pagination', [])
.directive('pagination', paginationDirective);

function paginationDirective() {
  return {
    restrict: 'E',
    replace: true,
    //transclude: true,
    template: getTemplate,
    link: postLink
  };

  function getTemplate(element, attr) {
    return '<div class="pagination">' +
            '<div class="location">Page {{currentPage}} of {{pageCount}}</div>' + 
            '<div class="controls">' +
              '<div class="btn btn-xs start">|&lt;</div>' +
              '<div class="btn btn-xs start">|&lt;</div>' +
              '<div class="btn btn-xs start">|&lt;</div>' +
            '<div ng-if="!item.quantity || item.quantity == 0" class="single-add">' +
              '<br-button class="item-add br-raised" ng-click="addItem(item)">Add</br-button>' +
            '</div>' +
            '<div ng-if="item.quantity > 0" class="quantity-add">' +
              '<button class="remove br-button br-raised" ng-click="removeItem(item)"><span>-</span></button>' +
              '<span class="quantity label br-button">2</span>' +
              '<button class="item-add br-button br-raised" ng-click="addItem(item)" ng-transclude></div>' +
            '</div>' +
           '</div>';
  }

  function postLink(scope, element, attr) {
    
  }
}