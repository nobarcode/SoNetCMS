/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	
	config.uiColor = '#aeaeae';
	config.resize_enabled = false;
	config.toolbar = 'CustomHeader';
	config.contentsCss = ['/assets/core/resources/css/main/rte.css', '/assets/core/resources/css/main/custom.css'];
	config.disableNativeSpellChecker = false;
	config.smiley_path='/assets/core/resources/images/emoticons/';
	config.smiley_images=['smiley.gif','teeth.gif','giggle.gif','laugh.gif','greed.gif','wink.gif','suprise.gif','surprise.gif','tongue.gif','blank.gif','worried.gif','frown.gif','cry.gif','mad.gif','toothless.gif','smoke.gif','sick.gif','satan.gif','alien.gif','vampire.gif','dead.gif','flame.gif','heart.gif','star.gif'];
	config.smiley_descriptions=[':)',';)',':D',':giggle:',':lol:','$$-)',':o',':O',':p',':|',':/',':(',':((',':@',':-B',':-Q',':sick:',':devil:',':alien:',':vampire:',':dead:',':flame:',':heart:',':star:'];
	config.toolbar_CustomHeader =
	[
    ['NewPage'],
    ['Undo','Redo','-','Find','Replace'],
    ['Source','-','Maximize', 'ShowBlocks'],
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['BidiLtr', 'BidiRtl'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
    '/',
    ['DocumentAttribute'],
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor','-','RemoveFormat'],
    ['Link','Unlink','Anchor'],
    ['Image','Table','Flash'],
    ['HorizontalRule','Smiley','SpecialChar']
    ];
 	
 	config.extraPlugins = 'sonet_document_attribute'; 	
};
