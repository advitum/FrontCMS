<fcms:partial partial="header" />
	
	<h1><fcms:edit type="plain" name="heading" /></h1>
	
	<fcms:edit type="rich" name="content" />
	
	<fcms:edit type="plugin" name="contact-form" plugin="contactform" fields='[{
		"label": "Name",
		"placeholder": "Your name",
		"required": true,
		"error": "Please enter your name."
	}, {
		"label": "E-mail",
		"placeholder": "Your e-mail address.",
		"required": true,
		"error": "Please enter your e-mail address."
	}, {
		"label": "Phone",
		"placeholder": "Your phone number"
	}, {
		"label": "Message",
		"type": "textarea",
		"placeholder": "Your message",
		"required": true,
		"error": "Please enter a message."
	}]' />
	
<fcms:partial partial="footer" />