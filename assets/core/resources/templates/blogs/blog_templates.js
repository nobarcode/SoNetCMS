// Register a template definition set named "default".
CKEDITOR.addTemplates( 'default',
{
	// The name of the subfolder that contains the preview images of the templates.
	imagesPath : '/assets/core/resources/templates/blogs/images/',
 
	// Template definitions.
	templates :
		[
			{
				title: 'Standard Blog Layout',
				image: 'standard_blog.gif',
				description: 'A standard single column blog layout.',
				html:
					'<p>' +
					'	Body of blog goes here.</p>'
			},
			
			{
				title: 'Two-column Blog Layout',
				image: 'two_column_blog.gif',
				description: 'A two-column blog layout.',
				html:
					'<div style="width:310px; float:left; margin-right:20px;">' +
					'	<p>' +
					'		Left Column: Body of blog goes here.</p>' +
					'</div>' +
					'<div style="width:310px; float:right;">' +
					'	<p>' +
					'		Right Column: Body of blog goes here.</p>' +
					'</div>'
			},
			
			{
				title: 'Review Blog Layout',
				image: 'review_blog.gif',
				description: 'A single column blog layout that displays the rating at the bottom.',
				html:
					'<p>' +
					'	Body of blog goes here.</p>' +
					'<p>' +
					'	[attribute type="ratingGraphic"][attribute type="ratingText"]</p>'
			}
		]
});