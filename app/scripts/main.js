$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value.trim() || '');
        } else {
            o[this.name] = this.value.trim() || '';
        }
    });
    return o;
};

$.fn.isValid = function(){
  return this[0].checkValidity()
}

if (!String.prototype.trim) {
  (function() {
    // Make sure we trim BOM and NBSP
    var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
    String.prototype.trim = function() {
      return this.replace(rtrim, '');
    };
  })();
}

function olength(o){
  var count = 0;
  var i;

  for (i in o) {
    if (a.hasOwnProperty(i)) {
      count++;
    }
  }
  return count;
}

(function($) {
	$(function() {
	  ht = $(window).height() - 28;

  	$('.cancelButton').click(function(){
 			var rel = $(this).parent().parent().attr('data-rel').split('|');
			var id = rel[1];
			var pageID = rel[0];
	  	$.post(SITE_PATH+'editSave', {divID: id, pageID: pageID, edit: -1}, function(){
	  		location.reload(true);
  	  });
    });

  	$('.approveButton').click(function(){
 			var rel = $(this).parent().parent().attr('data-rel').split('|');
			var id = rel[1];
			var pageID = rel[0];
	  	$.post(SITE_PATH+'editSave', {divID: id, pageID: pageID, edit: 2}, function(){
	  		location.reload(true);
  	  });
    });

  	$('.editButton').click(function(){
  		var theEdit;
  		if (tinymce.editors.length){
        theEdit = tinyMCE.activeEditor.getContent()
  	  	//make a post call to the edit controller to save the data.
  	  	var id = $('#saveID').val();
  	  	var page = $('#savePage').val();
  	  	$.post(SITE_PATH+'editSave', {divID: id, pageID: page, contents: theEdit, edit: 1}, function(){
  	  		location.reload(true);
  	  	});
  		}else{
  		  $(this).parent().css('z-index', 101);
  		  $('.cancelButton').hide();
  		  $('.approveButton').hide();

  			var rel = $(this).parent().parent().attr('data-rel').split('|');
  			var id = $(this).parent().parent().attr('id');
        console.log(rel);
  			$('#saveID').val(rel[1]);
  			$('#savePage').val(rel[0]);

  			var wt = $('#'+id).width()+40;

        tinymce.init({
          content_css : "/css/main.css,/css/edit.css",
          selector: '#editor_'+rel[1],theme: "modern",width: '100%',height: 300,
          plugins: [
               "advlist autolink link image lists charmap print preview hr anchor pagebreak",
               "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
               "table contextmenu directionality emoticons paste textcolor responsivefilemanager",
               "code"
         ],
         toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
         toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code | fontselect fontsizeselect",
         image_advtab: true ,
         external_filemanager_path:"/filemanager/",
         filemanager_title:"Responsive Filemanager" ,
         external_plugins: { "filemanager" : "/filemanager/plugin.min.js"}
       });
  		}
  	});
	});
})(jQuery);
