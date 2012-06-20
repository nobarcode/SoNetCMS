function showSelectorCalendar(e, monthFieldName, dayFieldName, yearFieldName) {
	
  	x = e.pageX;
   	y = e.pageY;

	x += 10;
	y += 10;
	
	$('#calendar_container').css({
		
		position:'absolute',
		left: x + 'px',
		top: y + 'px'
		
	});
	
	month = $(monthFieldName).val();
	year = $(yearFieldName).val();
	
	regenerateSelectorCalendar(month, year, monthFieldName, dayFieldName, yearFieldName);
	
}

function regenerateSelectorCalendar(month, year, monthFieldName, dayFieldName, yearFieldName) {
	
	$.post("/ajaxShowDateSelectCalendar.php", {getMonth: month, getYear: year, monthFieldName: monthFieldName, dayFieldName: dayFieldName, yearFieldName: yearFieldName}, function(data) {
		
		$("#calendar_container").html(data);
		
	}).complete(function() {
		
		$('#calendar_container').show();
		
		windowDimensions = {width: $(window).width(), height: $(window).height()};
		
		calendarDimensions = {width: $('#calendar_container').width(), height: $('#calendar_container').height()};
		calendarPosition = $('#calendar_container').position();
		
		//add total width and height of element to its position
		elementX = calendarDimensions.width + calendarPosition.left;
		elementY = calendarDimensions.height + calendarPosition.top;
		
		
		//check if total width + position is > available viewport, if it is subtract the difference
		if(elementX > windowDimensions.width) {
			
			moveToX = calendarPosition.left - (elementX - windowDimensions.width);
			
		} else {
			
			moveToX = calendarPosition.left;
			
		}
		
		//check if total height + position is > available viewport, if it is subtract the difference
		if(elementY > windowDimensions.height) {
			
			moveToY = calendarPosition.top - (elementY - windowDimensions.height);
			
		} else {
			
			moveToY = calendarPosition.top;
			
		}
		
		$('#calendar_container').css({
			
			position:'absolute',
			left: moveToX + 'px',
			top: moveToY + 'px'
			
		});
		
	});
	
}

function hideSelectorCalendar() {
	
	$('#calendar_container').hide();
	
}

function selectDate(monthFieldName, dayFieldName, yearFieldName, month, day, year) {
	
	$(monthFieldName).val(month);
	$(dayFieldName).val(day);
	$(yearFieldName).val(year);
	$('#calendar_container').hide();
	
}