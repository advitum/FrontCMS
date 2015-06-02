<fcms:partial partial="header" />
	
	<h1><fcms:edit type="plain" name="heading" /></h1>
	<fcms:edit type="image" name="lead" width="1024" height="200" crop />
	
	<fcms:flexlist name="content">
		<fcms:flexitem title="Image" name="image">
			<fcms:edit type="image" name="image" />
		</fcms:flexitem>
		<fcms:flexitem title="Text" name="text">
			<fcms:edit type="rich" name="text" />
		</fcms:flexitem>
	</fcms:flexlist>
	
	<fcms:edit type="plugin" plugin="boxes" name="plugin" />
	
<fcms:partial partial="footer" />