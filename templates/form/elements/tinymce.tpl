<textarea id="<@ $element.field @>" name="<@ $element.field @>" class="rapidev_form_element_textarea rapidev_form_element_textarea_<@ $form.id @> rapidev_form_element_<@ $element.field @> tinymce"><@ $element.value @></textarea>
<@ js @>

	function <@ $element.field @>_save() {
		$('#<@ $form.id @>').val("1");
		$('#rapidev_form').submit();
	}

	$(function(){
	
		$('#<@ $element.field @>').tinymce({
		
			//NS 18.01.2010
			extended_valid_elements : "iframe[src|width|height|name|align|frameborder|scrolling|marginheight|marginwidth]",
		
			// TinyBrowser
			file_browser_callback : "tinyBrowser",
		
			// Location of TinyMCE script
			script_url : '/js/tiny_mce/tiny_mce.js',
			width : '800',
			height : '600',
			
			theme_advanced_resize_horizontal : false,
			theme_advanced_resize_vertical : false,

			// General options
			theme : "advanced",
			plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

			// Theme options
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,

			// Example content CSS (should be your site CSS)
			content_css : "/css/content.css",

			save_enablewhendirty : false,
			save_onsavecallback : "<@ $element.field @>_save",

			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js"


		});
		
	});

<@ /js @>
