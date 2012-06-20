//File Size by Type Sorter
jQuery.fn.dataTableExt.oSort['file-size-asc']  = function(a,b) {
	
	var x = a.substring(0,a.length - 2).toLowerCase();
	var y = b.substring(0,b.length - 2).toLowerCase();
	
	var x_unit = getUnit(a);
	var y_unit = getUnit(b);

	x = parseInt( x * x_unit);
	y = parseInt( y * y_unit);
	
	return ((x < y) ? -1 : ((x > y) ?  1 : 0));
	
};

jQuery.fn.dataTableExt.oSort['file-size-desc'] = function(a,b) {
	
	var x = a.substring(0,a.length - 2).toLowerCase();
	var y = b.substring(0,b.length - 2).toLowerCase();
	
	var x_unit = getUnit(a);
	var y_unit = getUnit(b);

	x = parseInt( x * x_unit);
	y = parseInt( y * y_unit);
	
	return ((x < y) ?  1 : ((x > y) ? -1 : 0));
	
};

function getUnit(a) {
	
	if (a.substring(a.length - 2, a.length) == "kb") {
		
		unit = 1024;
		
	} else if (a.substring(a.length - 2, a.length) == "mb") {
		
		unit = 1024 * 1024;
		
	} else if (a.substring(a.length - 2, a.length) == "gb") {
		
		unit = 1024 * 1024 * 1024;
		
	} else {
		
		unit = 1;
		
	}
	
	return unit;
	
}

//Date and Time Sorter - Expects US-style Date and Time: mm/dd/yyyy hh:mm
jQuery.fn.dataTableExt.oSort['us_date-asc']  = function(a,b) {
	
	var houra;
	var hourb;
	
	regex = /(.*)\/(.*)\/(.*) (.*):(.*) (.*)/;
	var dateElementA = regex.exec(a);
	var dateElementB = regex.exec(b);
	
	if (dateElementA) {
		
		var tmp_houra = parseInt(dateElementA[4]);
		
		if (dateElementA[6].toUpperCase() == "PM") {
			
			if (tmp_houra < 12) {
				
				tmp_houra += 12;
				houra = tmp_houra.toString();
				
			} else {
				
				houra = dateElementA[4];
				
			}
			
		}
		
		if (dateElementA[6].toUpperCase() == "AM") {
			
			if (tmp_houra == 12) {
				
				houra = '00';
				
			} else {
				
				houra = dateElementA[4];
				
			}
			
		}
		
		//You can move the array references around to change the date and time format:
		//[3] = yyyy; [1] = mm; [2] = dd; hour(a|b) = hour; [5] = minutes
		var x = parseInt(dateElementA[3] + dateElementA[1] + dateElementA[2] + houra + dateElementA[5]);
		
	} else {
		
		var x = 0;
		
	}
	
	if (dateElementB) {
		
		var tmp_hourb = parseInt(dateElementB[4]);
		
		if (dateElementB[6].toUpperCase() == "PM") {
			
			if (tmp_hourb < 12) {
				
				tmp_hourb += 12;
				hourb = tmp_hourb.toString();
				
			} else {
				
				hourb = dateElementB[4];
				
			}
			
		}
		
		if (dateElementB[6].toUpperCase() == "AM") {
			
			if (tmp_hourb == 12) {
				
				hourb = '00';
				
			} else {
				
				hourb = dateElementB[4];
				
			}
			
		}
		
		//You can move the array references around to change the date and time format:
		//[3] = yyyy; [1] = mm; [2] = dd; hour(a|b) = hour; [5] = minutes
		var y = parseInt(dateElementB[3] + dateElementB[1] + dateElementB[2] + hourb + dateElementB[5]);
		
	} else {
		
		var y = 0;
		
	}
	
	return ((x < y) ? -1 : ((x > y) ?  1 : 0));
	
};

jQuery.fn.dataTableExt.oSort['us_date-desc'] = function(a,b) {

	var houra;
	var hourb;
	
	regex = /(.*)\/(.*)\/(.*) (.*):(.*) (.*)/;
	var dateElementA = regex.exec(a);
	var dateElementB = regex.exec(b);
	
	if (dateElementA) {
		
		var tmp_houra = parseInt(dateElementA[4]);
		
		if (dateElementA[6].toUpperCase() == "PM") {
			
			if (tmp_houra < 12) {
				
				tmp_houra += 12;
				houra = tmp_houra.toString();
				
			} else {
				
				houra = dateElementA[4];
				
			}
			
		}
		
		if (dateElementA[6].toUpperCase() == "AM") {
			
			if (tmp_houra == 12) {
				
				houra = '00';
				
			} else {
				
				houra = dateElementA[4];
				
			}
			
		}
		
		//You can move the array references around to change the date and time format:
		//[3] = yyyy; [1] = mm; [2] = dd; hour(a|b) = hour; [5] = minutes
		var x = parseInt(dateElementA[3] + dateElementA[1] + dateElementA[2] + houra + dateElementA[5]);
		
	} else {
		
		var x = 0;
		
	}
	
	if (dateElementB) {
		
		var tmp_hourb = parseInt(dateElementB[4]);
		
		if (dateElementB[6].toUpperCase() == "PM") {
			
			if (tmp_hourb < 12) {
				
				tmp_hourb += 12;
				hourb = tmp_hourb.toString();
				
			} else {
				
				hourb = dateElementB[4];
				
			}
			
		}
		
		if (dateElementB[6].toUpperCase() == "AM") {
			
			if (tmp_hourb == 12) {
				
				hourb = '00';
				
			} else {
				
				hourb = dateElementB[4];
				
			}
			
		}
		
		//You can move the array references around to change the date and time format:
		//[3] = yyyy; [1] = mm; [2] = dd; hour(a|b) = hour; [5] = minutes
		var y = parseInt(dateElementB[3] + dateElementB[1] + dateElementB[2] + hourb + dateElementB[5]);
		
	} else {
		
		var y = 0;
		
	}
	
	return ((x < y) ? 1 : ((x > y) ?  -1 : 0));
	
};

jQuery.fn.dataTableExt.oSort['image_dimensions-asc'] = function(a,b) {
	
	regex = /([0-9]+)([x])([0-9]+)/;
	var x = regex.exec(a);
	var y = regex.exec(b);
	
	if (x) {
		
		x = parseInt(x[1]) * parseInt(x[3]);
		
	} else {
		
		x = 0;
		
	}
	
	if (y) {
		
		y = parseInt(y[1]) * parseInt(y[3]);
		
	} else {
		
		y = 0;
		
	}
	
	return ((x < y) ? -1 : ((x > y) ?  1 : 0));
	
};

jQuery.fn.dataTableExt.oSort['image_dimensions-desc'] = function(a,b) {
	
	regex = /([0-9]+)([x])([0-9]+)/;
	var x = regex.exec(a);
	var y = regex.exec(b);

	if (x) {
		
		x = parseInt(x[1]) * parseInt(x[3]);
		
	} else {
		
		x = 0;
		
	}
	
	if (y) {
		
		y = parseInt(y[1]) * parseInt(y[3]);
		
	} else {
		
		y = 0;
		
	}
	
	return ((x < y) ?  1 : ((x > y) ? -1 : 0));
	
};