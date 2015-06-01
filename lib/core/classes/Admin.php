<?php
	
	namespace Advitum\Frontcms;
	
	class Admin
	{
		public static function request() {
			if(Router::$page !== null && Router::$user !== null && isset($_POST['element']) && is_array($_POST['element'])) {
				DB::delete('elements', sprintf("WHERE `page_id` = %d", Router::$page->id));
				foreach($_POST['element'] as $key => $value) {
					if(!empty($value)) {
						DB::insert('elements', array(
							'page_id' => Router::$page->id,
							'name' => $key,
							'content' => $value
						));
					}
				}
				DB::update('pages', array(
					'modified = NOW()'
				), sprintf("WHERE `id` = %d", Router::$page->id));
				Session::setMessage(Language::string('Your changes were saved.'), 'success');
				Router::redirect(Router::here());
			}
			
			self::render();
		}
		
		private static function render() {
			?><!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="UTF-8">
	<title><?php echo Language::string('Administration'); ?></title>
	
	<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL; ?>css/admin.css" />
</head>

<body class="admin">
	<div id="adminBar">
		<div class="buttons left">
			<button type="button" id="toggleEdit" class="button" title="<?php echo Language::string('Edit'); ?>"><i class="fa fa-pencil"></i></button>
			<button type="button" id="save" class="button" title="<?php echo Language::string('Save'); ?>"><i class="fa fa-floppy-o"></i></button>
			<button type="button" id="abort" class="button" title="<?php echo Language::string('Abort'); ?>"><i class="fa fa-times"></i></button>
		</div>
		<?php echo Session::getMessage(); ?>
		<div class="buttons right">
			<button type="button" id="changePassword" class="button" title="<?php echo Language::string('Change password'); ?>"><i class="fa fa-key"></i></button>
			<button type="button" id="togglePageTree" class="button" title="<?php echo Language::string('Show page tree'); ?>"><i class="fa fa-sitemap"></i></button>
			<a class="button" href="<?php echo ROOT_URL; ?>logout" title="<?php echo Language::string('Logout'); ?>"><i class="fa fa-sign-out"></i></a>
		</div>
	</div>
	
	<div id="pageTree">
		<div class="buttons">
			<button type="button" id="add" class="button" title="<?php echo Language::string('Add page'); ?>"><i class="fa fa-plus"></i></button>
			<button type="button" id="toggleShowDeleted" class="button" title="<?php echo Language::string('Show deleted pages'); ?>"><i class="fa fa-trash-o"></i></button>
		</div>
		<?php echo Router::navigation(array(
			'active' => false,
			'home' => true,
			'hidden' => true,
			'all' => true,
			'deleted' => true
		)); ?>
	</div>
	
	<div id="main">
		<iframe id="page" src="<?php echo Router::here() . '?fcms_content'; ?>"></iframe>
	</div>
	
	<script type="text/javascript">
		var rootUrl = "<?php echo ROOT_URL; ?>";
		var adminUrl = "<?php echo ADMIN_URL; ?>";
		var language = "<?php echo LANGUAGE; ?>";
	</script>
	<?php if(is_file(ADMIN_PATH . 'js/languages/' . LANGUAGE . '.js')) { ?>
	<script id="language" type="text/javascript" src="<?php echo ADMIN_URL; ?>js/languages/<?php echo LANGUAGE; ?>.js"></script>
	<?php } ?>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/languagestring.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/localstorage.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/jquery.lightbox.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/box.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/contextmenu.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/jquery.iframe-transport.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/jquery.fileupload.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/mediabrowser.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/pagebrowser.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/repeatable.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL; ?>js/admin.js"></script>
</body>

</html><?php
		}
	}
	
?>