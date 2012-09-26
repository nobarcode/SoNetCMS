/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	
	config.uiColor = '#aeaeae';
	config.resize_enabled = false;
	config.toolbar = 'MyToolbar';
	config.contentsCss = ['/assets/core/resources/css/main/rte.css', '/assets/core/resources/css/main/custom.css'];
	config.serverPreviewURL = '/previewDocument.php';
	config.templates_files = ['/assets/core/resources/templates/document_templates.js'];
	config.disableNativeSpellChecker = false;
	config.smiley_path='/assets/core/resources/images/emoticons/';
	config.smiley_images=['smiley.gif','teeth.gif','giggle.gif','laugh.gif','greed.gif','wink.gif','suprise.gif','surprise.gif','tongue.gif','blank.gif','worried.gif','frown.gif','cry.gif','mad.gif','toothless.gif','smoke.gif','sick.gif','satan.gif','alien.gif','vampire.gif','dead.gif','flame.gif','heart.gif','star.gif'];
	config.smiley_descriptions=[':)',';)',':D',':giggle:',':lol:','$$-)',':o',':O',':p',':|',':/',':(',':((',':@',':-B',':-Q',':sick:',':devil:',':alien:',':vampire:',':dead:',':flame:',':heart:',':star:'];
	config.toolbar_MyToolbar =
	[
    ['AjaxSave','NewPage','SonetPreview','-','SonetTemplateChooser'],
    ['Print', 'SpellChecker', 'Scayt'],
    ['SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord'],
    ['Undo','Redo','-','Find','Replace'],
    ['Source','-','Maximize', 'ShowBlocks'],
    ['Component','AuthContent','GroupContent','RCComponent','Toggler','Smartlink','DocumentAttribute'],
    '/',
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['BidiLtr', 'BidiRtl'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
    ['Iframe'],
    '/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor','-','RemoveFormat'],
    ['Link','Unlink','Anchor'],
    ['Image','Table','Flash'],
    ['HorizontalRule','Smiley','SpecialChar','PageBreak']
    ];
	
	config.extraPlugins = 'sonet_ajaxsave,sonet_preview,sonet_template_chooser,iframe,sonet_component,sonet_rc_component,sonet_auth_content,sonet_group_content,sonet_toggler,sonet_smartlink,sonet_document_attribute';
};