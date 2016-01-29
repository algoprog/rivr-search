$(document).ready(function(){
	$('#q').autocomplete({
		delay: 300,
		source: function(request, response) {
			$.ajax({
				url: 'suggest.json',
				dataType: 'json',
				data: {
					q: request.term
				},
				success: function(data) {
					response(data.slice(0,4));
				}
			});
		},
		minLength: 1,
		select: function(event, ui) {
			$('#q').val(ui.item.query);
			$('#q').blur();
			$('.ui-autocomplete').hide();
			$('#container').fadeTo('slow',0.3);
			$('#loading').fadeIn();
			window.location = 'search?q='+encodeURIComponent(ui.item.query);
			return false;
		},
		focus: function(event, ui) {
			$('#q').val(ui.item.query);
			return false;
		},
		change: function(event, ui) {
			$('#q').val(ui.item.query);
			return false;
		}
	}).autocomplete('instance')._renderItem = function(ul, item) {
		return $('<li>')
		.append('<a class="ac"><span class="ac_query">'+item.query+'</span> <span class="ac_type">/'+item.type+'</span></a>')
		.appendTo(ul);
	};

	var query = decodeURIComponent(QueryString.q);
	var words = query.split('+');
	$.each(words, function(index,item){
		$('.result .title').highlight(item);
	});
	
	$('.tag').click(function(){
		$('.tag').removeClass('tag_selected');
		$(this).addClass('tag_selected');
		var type = $(this).html();
		if(type!='ALL'){
			$('.row').hide();
			$('.'+type).show();
		}else{
			$('.row').show();
			$('.subresult').show();
		}
	});
	
	$('#sbt').click(function(){
		$('#search').submit();
	});
	
	$(window).resize(function(){
		$('.ui-autocomplete').hide();
	});
	
	$('#search').submit(function(e){
		$('.ui-autocomplete').hide();
		$('#container').fadeTo('slow',0.3);
		$('#loading').fadeIn();
		e.preventDefault();
		window.location = 'search?q='+encodeURIComponent($('#q').val());
	});
	
});

var QueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split('&');
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split('=');
        // If first entry with this name
    if (typeof query_string[pair[0]] === 'undefined') {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === 'string') {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  } 
    return query_string;
}();

jQuery.fn.highlight = function(str){
    var regex = new RegExp(str, 'gi');
    return this.each(function(){
        $(this).contents().filter(function(){
            return this.nodeType == 3 && regex.test(this.nodeValue);
        }).replaceWith(function(){
            return(this.nodeValue || '').replace(regex, function(match){
                return '<b>'+match+'</b>';
            });
        });
    });
};