<fcms:partial partial="header" />
	
	<h1><fcms:edit type="plain" name="heading" /></h1>
	
	<fcms:flexlist name="content">
		<fcms:flexitem title="Text" name="text">
			<fcms:edit type="rich" name="text" />
		</fcms:flexitem>
		<fcms:flexitem title="Text + Image (left)" name="text-image-left">
			<div class="leftFigure">
					<figure>
						<fcms:edit type="image" name="image" width="500" />
					</figure>
					<div>
						<fcms:edit type="rich" name="text" />
					</div>
				</div>
		</fcms:flexitem>
		<fcms:flexitem title="Text + Image (right)" name="text-image-right">
			<div class="rightFigure">
					<figure>
						<fcms:edit type="image" name="image" width="500" />
					</figure>
					<div>
						<fcms:edit type="rich" name="text" />
					</div>
				</div>
		</fcms:flexitem>
	</fcms:flexlist>
	
<fcms:partial partial="footer" />