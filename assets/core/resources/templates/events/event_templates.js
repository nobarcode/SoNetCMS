// Register a template definition set named "default".
CKEDITOR.addTemplates( 'default',
{
	// The name of the subfolder that contains the preview images of the templates.
	imagesPath : '/assets/core/resources/templates/events/images/',
 
	// Template definitions.
	templates :
		[
			{
				title: 'Standard Event Layout',
				image: 'standard_event.gif',
				description: 'A standard single column event layout.',
				html:
					'<p>' +
					'	Body of blog goes here.</p>'
			},
			
			{
				title: 'Alternate Event Layout',
				image: 'alternate_event.gif',
				description: 'An alternative event layout that features two columns.',
				html:
					'<div style="width:310px; float:left; margin-right:20px;">' +
					'	<p>' +
					'		Left Column: Body of event goes here.</p>' +
					'</div>' +
					'<div style="width:310px; float:right;">' +
					'	<p>' +
					'		Right Column: Body of event goes here.</p>' +
					'</div>'
					
			}
		]
});