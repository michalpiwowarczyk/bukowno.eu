angular.module("bukownoApp").directive('showCalendar',['$compile', function($compile) {
	return {
		scope: false,
		link: function(scope, element, attr) {
			var rok = attr['calendarRok'];
			var miesiac = attr['calendarMiesiac'];
			var retHtml = scope.getCalendar(rok,miesiac);
			var calHtml = '<p class="col-xs-7 center-block kalendarz-row kalendarz-wiersz">'+retHtml+'</p>';
			var template = angular.element(calHtml);
			var linkFunction = $compile(template);
			element.append(linkFunction(scope));
		},
	}
}]);

angular.module("bukownoApp").directive('galeriaElement',function() {
	return {
		scope: false,
		link: function(scope, element, attr) {
			var item = attr['galeriaItem'];
			var count = attr['galeriaCount'];
			if(item==count) {
				if (angular.isFunction(calculateSizesAfterResize)) {
					// console.log("Ostatni kafelek galerii, układam kafelki");
					mustRender = true;						
					calculateSizesAfterResize();
				}
			}
		},
	}
});
