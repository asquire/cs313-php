function showCurrentLoansOption() {
	var searchLoansCheckbox = document.getElementById("searchLoans_id");
	var currentLoans = document.getElementById("currentLoans_id");
	
	if (searchLoansCheckbox.checked == true) {
		currentLoans.style.display = "block";
	}
	else {
		currentLoans.style.display = "none";
	}
}

function showScope() {
	var patronOption = document.getElementById("patronOption_id");
	var searchAllFeatures = document.getElementById("searchAllFeatures_id");
	
	if (patronOption.checked == true) {
		searchAllFeatures.style.display = "none";
	}
	else if (searchLoansCheckbox.checked == false) {
		searchAllFeatures.style.display = "block";
	}
}