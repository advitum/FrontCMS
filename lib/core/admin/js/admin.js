$(document).ready(function() {
	$('#fcmsOpenPageTree').click(function(e) {
		e.preventDefault();
		$('body').toggleClass('fcmsPageTreeClosed');
	});
});