jQuery(document).ready(function($) {
	if(!$('body').hasClass('widgets_access')){
		smcfiSetupList($);
		$('.smcfi-edit-item').addClass('smcfi-toggled-off');
		smcfiSetupHandlers($);
	}
	
	$(document).ajaxSuccess(function() {
		smcfiSetupList($);
		$('.smcfi-edit-item').addClass('smcfi-toggled-off');
	});
});

function smcfiSetupList($){
	$( ".country-flags-info" ).sortable({
		items: '.list-item',
		opacity: 0.6,
		cursor: 'n-resize',
		axis: 'y',
		handle: '.smcfi-moving-handle',
		placeholder: 'sortable-placeholder',
		start: function (event, ui) {
			ui.placeholder.height(ui.helper.height());
		},
		update: function() {
			smcfi_updateOrder($(this));
		}
	});
	
	$( ".country-flags-info .smcfi-moving-handle" ).disableSelection();
}


// All Event handlers
function smcfiSetupHandlers($){
	$("body").on('change.countryisocode',function(event) { 
		var id = $(event.target).attr('id');
		var txtId = '#' + id.substring(0, id.length - 1) + '_name' + id.substring(id.length - 1);
		$(txtId).val($('#' + id + ' :selected').text().substring(5));
	});

	$("body").on('click.smcfi','.smcfi-delete',function() { 
		$(this).parent().parent().fadeOut(500,function(){
			var smcfi = $(this).parents(".widget-content");
			$(this).remove();
			smcfi.find('.order').val(smcfi.find('.country-flags-info').sortable('toArray'));
			var num = smcfi.find(".country-flags-info .list-item").length;
			var amount = smcfi.find(".amount");
			amount.val(num);
		});
	});
	
	$("body").on('click.smcfi','.smcfi-add',function() { 
		var smcfi = $(this).parent().parent();
		var num = smcfi.find('.country-flags-info .list-item').length + 1;
		
		smcfi.find('.amount').val(num);
		
		var item = smcfi.find('.country-flags-info .list-item:last-child').clone();
		var item_id = item.attr('id');
		item.attr('id',smcfi_increment_last_num(item_id));

		$('.smcfi-toggled-off',item).removeClass('smcfi-toggled-off');
		$('.number',item).html(num);
		$('.item-title',item).html('');
		
		$('label',item).each(function() {
			var for_val = $(this).attr('for');
			$(this).attr('for',smcfi_increment_last_num(for_val));
		});
		
		$('input',item).each(function() {
			var id_val = $(this).attr('id');
			var name_val = $(this).attr('name');
			$(this).attr('id',smcfi_increment_last_num(id_val));
			$(this).attr('name',smcfi_increment_last_num(name_val));
			if($(':checked',this)){
			   $(this).removeAttr('checked');
			}
			$(this).val('');
		});
		
		$('select',item).each(function() {
			var id_val = $(this).attr('id');
			var name_val = $(this).attr('name');
			$(this).attr('id',smcfi_increment_last_num(id_val));
			$(this).attr('name',smcfi_increment_last_num(name_val));
			$(this).val(' ');
		});
		
		smcfi.find('.country-flags-info').append(item);
		smcfi.find('.order').val(smcfi.find('.country-flags-info').sortable('toArray'));
	});
	
	$('body').on('click.smcfi','.smcfi-moving-handle', function() {
		$(this).parent().find('.smcfi-edit-item').slideToggle(200);
	} );
}

function smcfi_increment_last_num(v) {
    return v.replace(/[0-9]+(?!.*[0-9])/, function(match) {
        return parseInt(match, 10)+1;
    });
}

function smcfi_updateOrder(self){
	var smcfi = self.parents(".widget-content");
	smcfi.find('.order').val(smcfi.find('.country-flags-info').sortable('toArray'));
}