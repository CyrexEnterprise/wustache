/**
 *	Wustache Admin
 *	Adding some style in Wordpress
 *
 *	These functions handle the "edit post" Template box.
 */
 
jQuery(window).load(function()
{
	var metabox = jQuery('#wustache-edit-post');

	// Template
	var template = metabox.find('#template').val ();
	if (!template) template = 0;
	
	metabox.find('.templates select')
		.val(template)
		.chosen({disable_search_threshold: 8})
		.on('change', function(e, param){ metabox.find('#template').val (param.selected); });
	
	// Retrieve and store id's
	parseImageIds = function (store)
	{
		var ids = metabox.find('.images li').map(function(){ return jQuery(this).data('id')}).get();
		
		if (store) metabox.find('#attachments').val (ids.join (','));
		
		return ids;
	}
	
	// Add Selection
	metabox.find('.add-image').on('click', function(event)
	{
		event.preventDefault();
		
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		
		// Create the media frame.
		var file_frame = wp.media.frame = wp.media({
			title: "Template Images",
			button: {text: "Select",},
			library : { type : 'image'},
			multiple: true
		});
		
		file_frame.on('open', function() {
			var selection = file_frame.state().get('selection');
			var ids = parseImageIds ();
			
			ids.forEach(function(id) {
				attachment = wp.media.attachment(id);
				attachment.fetch();
				selection.add( attachment ? [ attachment ] : [] );
			});
		});
		
		// When an image is selected, run a callback.
		file_frame.on('select', function(s) {
		
			var ids = [];
			
			file_frame.state().get('selection').each(function(image)
			{	
				ids.push(image.attributes.id);
				
				if(!metabox.find('.images li[data-id='+ image.attributes.id +']').size()) addAttachment (image.attributes);
			});
			
			// remove delected ones
			var removables = metabox.find('.images li').not('[data-id=' + ids.join("],[data-id=") + ']');
			if (removables.size()) 
			{
				metabox.find('#ex-attachments').val (removables.map(function(){ return jQuery(this).data('id')}).get().join (','));
				removables.remove();
			}
			
			// Update input field
			parseImageIds (true);
		
		});
		
		// Finally, open the modal
		file_frame.open();
	});
	
	// Add Draggable
	metabox.find( ".images" ).sortable({revert: "invalid", axis: "y", stop: parseImageIds});
	metabox.find( "ul, li" ).disableSelection();
	
	// Update input field
	parseImageIds (true);
	
	// Add Attachment
	addAttachment = function (attr)
	{
		var li = jQuery("<li style='background-image: url("+ attr.url +");'><div>"+ attr.title +"</div></li>").appendTo(metabox.find('.images')).attr('data-id', attr.id);
		
		li.prepend("<span class='pull-right'><i class='ion-trash-a remove-attachment'></i><i class='ion-edit edit-attachment'></i></span>");
		li.find( ".remove-attachment" ).on('click', removeAttachment);
		li.find( ".edit-attachment" ).on('click', editAttachment);
	}
	
	// Remove/Edit functions
	removeAttachment = function () 
	{
		jQuery(this).parents('li').remove ();
		
		// Update input field
		parseImageIds (true);
	}
	
	editAttachment = function () 
	{
		var id = jQuery(this).parents('li').data('id');
		
		window.open ("post.php?post=" + id + "&action=edit&image-editor", "Edit Image");
	}
	
	// Add remove/edit listeners
	metabox.find( ".remove-attachment" ).on('click', removeAttachment);
	metabox.find( ".edit-attachment" ).on('click', editAttachment);
});