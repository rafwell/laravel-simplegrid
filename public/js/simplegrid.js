$(document).ready(function(){ 
    $('.grid-container .select-page').change(function(){
        window.location.href = $(this).attr('data-url')+'&page='+$(this).val();
    });
    $('.grid-container .btn-clear-search').click(function(){  
        var $form = $(this).closest('form');
        $form.find('[name=search]').val('');
        $form.submit();
    }); 

    $('.grid input[type="checkbox"].select-all').click(function(){
        if($(this).is(':checked')){
            $(this).closest('table').find('tbody input[type="checkbox"].grid-checkbox').prop('checked', true);
        }else{
            $(this).closest('table').find('tbody input[type="checkbox"].grid-checkbox').prop('checked', false);
        }
    });

    $('.grid input[type="checkbox"].grid-checkbox').click(function(){
        if($(this).is(':checked') && $(this).closest('table').find('tbody input[type="checkbox"].grid-checkbox').not(':checked').length===0){
            $(this).closest('table').find('input[type="checkbox"].select-all').prop('checked', true);
        }else{
            $(this).closest('table').find('input[type="checkbox"].select-all').prop('checked', false);
        }
    });

    $('.grid-container .bulk-action select').change(function(){
        if($(this).val()){
            if($(this).closest('.grid-container').find('table tbody input[type="checkbox"].grid-checkbox:checked').length>0){
                if(confirm($(this).attr('data-confirm-msg'))){
                    var $form = $('<form></form>').attr('id', 'grid-form-bulk-actions').attr('action', $(this).val()).attr('method', 'post');
                    $form.append('<input type="hidden" name="_token" value="'+$(this).attr('data-token')+'" />');
                    $form.append('<input type="hidden" name="acao" value="'+$(this).find('option:selected').text()+'" />');
                    $(this).closest('.grid-container').find('table tbody input[type="checkbox"].grid-checkbox:checked').each(function(){
                        $form.append('<input type="hidden" name="'+$(this).attr('name').substr('14')+'[]" value="'+$(this).val()+'" />');
                    });
                    $('body').append($form);
                    $('#grid-form-bulk-actions').submit();
                }else{
                    $(this).find('option:selected').prop('selected', false);
                    $(this).find('option').first().prop('selected', true);
                    return false;
                }
            }else{
                alert($(this).attr('data-alert-msg'));
                $(this).find('option:selected').prop('selected', false);
                $(this).find('option').first().prop('selected', true);
                return false;
            }
        }
    });

    $('.grid-container .advanced-search .datetimepicker').each(function(){        
        $(this).datetimepicker({
            format: $(this).closest('.field').attr('data-format-input')
        });   
    });

    $('.grid-container .advanced-search .field .btn-remove').click(function(){
        $(this).closest('.field').not('.double').find('input, select').val('');
        $(this).closest('.from').find('input, select').val('');
        $(this).closest('.to').find('input, select').val('');
        $(this).closest('form').submit();
    });

    $('.grid-container .showing-rows-info select').change(function(){
        window.location = $(this).attr('data-url')+'&rows-per-page='+$(this).val();
    });

    $('.grid-container select[name=export]').change(function(){
        if($(this).val()){
            $(this).closest('.grid-container').find('.btn-export').attr('href', $(this).closest('.grid-container').find('.btn-export').attr('data-href')+'&export='+$(this).val() );
        }else{
            $(this).closest('.grid-container').find('.btn-export').attr('href', '#');         
        }
    });

    $('.grid-container .btn-export').click(function(e){
        if($(this).attr('href')=='#'){
            e.preventDefault();
            alert($(this).attr('data-alert-msg'));
            return false;
        }
    });

    /*$('.grid-container .advanced-search form').submit(function(e){
        erros = '';

        if(erros){
            e.preventDefault();
            alert(erros);
        }
    });*/
});