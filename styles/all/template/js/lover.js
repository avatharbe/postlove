(function($) {  // Avoid conflicts with other libraries

"use strict";

phpbb.addAjaxCallback('toggle_love', function(data) {
	var $icon = $('#likeimg_' + data.toggle_post);
	var $count = $('#like_' + data.toggle_post);
	var $button = $icon.closest('.postlove-button');

	if (data.toggle_action === 'add')
	{
		$icon.removeClass('like').addClass('liked');
		$count.text(parseInt($count.text()) + 1);
	}
	else
	{
		$icon.removeClass('liked').addClass('like');
		$count.text(parseInt($count.text()) - 1);
	}

	// Update title/tooltip attributes
	if (data.toggle_title)
	{
		$icon.attr('title', data.toggle_title);
		$button.attr('title', data.toggle_title);
		$button.find('.sr-only').text(data.toggle_title);
	}
	if (typeof data.toggle_likers !== 'undefined')
	{
		$count.attr('title', data.toggle_likers);
	}
});

})(jQuery); // Avoid conflicts with other libraries
