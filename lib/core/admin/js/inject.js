$(document).ready(function() {
	$('.fcmsEditable[data-type="rich"]').tinymce({
		inline: true,
		language: parent.language.substr(0, 2),
		hidden_input: false,
		menubar: false,
		relative_urls: false,
		toolbar: 'styleselect | undo redo | bold italic | link unlink | bullist numlist | superscript subscript',
		plugins: 'fcmslink',
		style_formats: [{
			title: languageString('Paragraph'),
			block: 'p'
		}, {
			title: languageString('Headline') + ' 1',
			block: 'h1'
		}, {
			title: languageString('Headline') + ' 2',
			block: 'h2'
		}],
		valid_styles: {
			'*': ''
		}
	});
});