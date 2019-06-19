var livehouseElem = document.querySelector('[name=livehouse_id]');
var dateElem = document.querySelector('[name=date_occurred]');
var areaElem = document.querySelector('[name=area_id]');

function initChange(elem) {
	elem.addEventListener('change', function(event) {
		var livehouseValue = livehouseElem.value;
		var dateValue = dateElem.value;
		var areaValue = areaElem.value;
		var newLocation = '/lives/';
		
		if(isNaN(livehouseValue) === false && livehouseValue != '') {
			newLocation = newLocation + '&livehouse_id=' + livehouseValue;
		}
		if(isNaN(areaValue) === false && areaValue != '') {
			newLocation = newLocation + '&area_id=' + areaValue;
		}
		if(isNaN(dateValue) === false && dateValue != '') {
			newLocation = newLocation + '&date_occurred=' + dateValue;
		}
		
		if(newLocation != '/lives/') {
			window.location = newLocation;
		}
	});
}

initChange(livehouseElem);
initChange(areaElem);
initChange(dateElem);