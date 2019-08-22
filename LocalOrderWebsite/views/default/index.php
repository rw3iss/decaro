<?php dorm()->response->responsive_view('shared/header'); ?>

<?php 
	if(isset($show_nav)) { 
		if($show_nav) {
			dorm()->response->responsive_view('nav'); 
		}; 
	} else {
	 	dorm()->response->responsive_view('nav'); 
	} 
?>

<ng-view></ng-view>

<?php dorm()->response->responsive_view('shared/footer'); ?>