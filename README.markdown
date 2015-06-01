FrontCMS v0.1
=============

You code, your clients manage
-----------------------------

The idea behind FrontCMS is to give you the freedom to layout a webpage anyway you like while still being able to give your client an easy way to edit the content.

There is no backend, thus the name FrontCMS. The content is completely manageable through the frontend in a true what-you-see-is-what-you-get manner. No steep learning curve for your clients!

You code the website, marking editable areas directly in the code, and FrontCMS will display the fitting editing interface for your client.

If you want to keep an eye on this project, feel free to *star* or *watch* it.

Happy coding!


Who uses FrontCMS?
------------------

This are some websites that I know of that use FrontCMS. I will try to keep this list updated.

- [taupadel.de](http://taupadel.de/) (german)


Installation
------------

 1. Download the [newest release of FrontCMS](https://github.com/advitum/FrontCMS/releases) from GitHub into the document root of your website.
 2. Configure your website in the file *config.php*.
   - DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD and DATABASE_NAME constants are required to connect to your MySQL database.
   - LANGUAGE constant is optional and can be used to change the language of FrontCMS. Currently, only en_us and de_de are supported.
   - ROOT_URL constant is optional and can be used if you install FrontCMS in a subdirectory of your website. All URLs will automatically be changed for you.
   - PAGE_OPTIONS constant is an array of additional page options, which can be changed per page (like keywords). Supported types are *text*, *textarea* and *image*. You can access these options in your layouts later.
 3. Log into your backend at yourdomain.com/login as *admin*, using password *admin*. Change that default password **now** by clicking on the key in the top right corner!


Coding
------

Now you are all set to build your layouts. You basically build one layout for each different page layout you will need.

	<- Header ----------->		<- Header ----------->
	<- Big image -------->		<- Image -> <- Text ->
	<- Text -> <- Text -->		<- Footer ----------->
	<- Footer ----------->

For example, this pages are build differently, so you will need two layouts. The editor can later select one of the layouts for each site.

Each editable element will be represented by an editable "slot", which can be of the type *plain*, *rich*, *image* or *plugin*.

Just take a look at the demo layout in the file *layouts/default.tpl*.

	<fcms:partial partial="header" />
		
		<h1><fcms:edit type="plain" name="heading" /></h1>
		<fcms:edit type="image" name="lead" width="1024" height="200" crop />
		<fcms:edit type="rich" name="content" />
		<fcms:edit type="plugin" plugin="boxes" name="plugin" />
		
	<fcms:partial partial="footer" />

Here you have every possible element to use in your layouts. The first and last line import *partials*, which are useful for reocurring content (like the header and footer). They are stored in the folder *layouts/partials* and use the same syntax as layouts.

The next four lines define four editable slots. Each slot has a name, by which it is saved in the database. If you use the same name more than once in a layout, the slots are automagically numbered.

###Type *plain* and *rich*

The third line makes the Headline editable, the fifth line makes the content editable. These tags will be replaced with an input interface when logged in and with the entered content when not logged in. The editor can simply edit the content right there in the frontend.

Plain slots are usually just one line of content without formatting, like headlines. Rich slots are textareas with formatting options.

###Type *image*

The fourth line defines an image slot. Here the editor will be able to upload an image or choose one from the media library. If you specify the dimensions and add the *crop* attribute, the image will automatically be resized and cropped.

###Type *pluguin*

The sixth line includes a plugin slot. Plugins are used for everything else. Plugins are stored inside the folder *lib/plugins*. There is a demo plugin which shows the basic functions of a plugin.

###Other tags

In the two partials, there are some more tags you can (or should) use.

`<fcms:title />` inserts the current page title.

`{ROOT_URL}` will be replaced by the *ROOT_URL*, which is "/" by default. Use this to make sure your urls do not break when moving FrontCMS to another folder.

You cann access the defined page options using `{PAGE_OPTION.key}`, where "key" is the array key you defined for the option in *config.php*. So to access your keywords, use `{PAGE_OPTION.keywords}`.

`<fcms:head />` will include header information your plugins might define, like additional css files. Similarly, `<fcms:foot />` will do the same in the footer.

`<fcms:body></fcms:body>` should be used instead of the regular body tag, so FrontCMS can add attributes to your body. By default, the name of the used layout will be added as a class to the body tag.

`<fcms:navigation />` will include the navigation. There are some options, which you can add as attributes:

Attribute | Description | Default
--------- | ----------- | -------
home      | whether to include the home page in the navigation. | true
active    | whether to show all sub navigations (false) or only the ones of active items (true). | true
all       | whether to show pages not included in the menu. | false
start     | the depth at which the menu is supposed to start. | 1
end       | the depth at which the menu is supposed to end. | -1
list      | whether to render the items as an unordered list. Note: if the depth of the navigation is greater than one, the items will be rendered as list either way. | false


Editing
-------

After your layouts are done, you can start editing. The editing interface should be fairly intuitive, just try it.


Plugins
-------

If you want to use more complex functionality, like a contact form, you need to create a small plugin. Take a look at the demo plugin at *lib/plugins/Boxes*.

There is currently no more detailed reference for the plugin interface. Just take a look at the source code or contact me at info@advitum.de!