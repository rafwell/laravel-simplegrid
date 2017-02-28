$(document).ready(function(){   
    $('.grid-container .select-pagina').change(function(){
        window.location.href = $(this).attr('data-url')+'&pagina='+$(this).val();
    });
    $('.grid-container .btn-limpar-pesquisa').click(function(){  
        var $form = $(this).closest('form');
        $form.find('[name=pesquisar]').val('');        
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

    $('.grid-container .bulk-actions select').change(function(){
        if($(this).val()){
            if($(this).closest('.grid-container').find('table tbody input[type="checkbox"].grid-checkbox:checked').length>0){
                if(confirm('Deseja realmente aplicar esta ação aos itens selecionados?')){
                    var $form = $('<form></form>').attr('id', 'grid-form-acao-em-massa').attr('action', $(this).val()).attr('method', 'post');
                    $form.append('<input type="hidden" name="_token" value="'+$(this).attr('data-token')+'" />');
                    $form.append('<input type="hidden" name="acao" value="'+$(this).find('option:selected').text()+'" />');
                    $(this).closest('.grid-container').find('table tbody input[type="checkbox"].grid-checkbox:checked').each(function(){
                        $form.append('<input type="hidden" name="'+$(this).attr('name').substr('14')+'[]" value="'+$(this).val()+'" />');
                    });
                    $('body').append($form);
                    $('#grid-form-acao-em-massa').submit();
                }else{
                    $(this).find('option:selected').prop('selected', false);
                    $(this).find('option').first().prop('selected', true);
                    return false;
                }
            }else{
                alert('Selecione ao menos um item para aplicar a ação!');
                $(this).find('option:selected').prop('selected', false);
                $(this).find('option').first().prop('selected', true);
                return false;
            }
        }
    });

    $('.grid-container .pesquisa-avancada .datetimepicker.date').datetimepicker({
        format: 'DD/MM/YYYY'        
    });   

    $('.grid-container .pesquisa-avancada .datetimepicker.datetime').datetimepicker();  

    $('.grid-container .pesquisa-avancada .integer input').keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .        
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: Ctrl+C
            (e.keyCode == 67 && e.ctrlKey === true) ||
             // Allow: Ctrl+X
            (e.keyCode == 88 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    $('.grid-container .pesquisa-avancada .numeric input').keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and ,
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 188]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: Ctrl+C
            (e.keyCode == 67 && e.ctrlKey === true) ||
             // Allow: Ctrl+X
            (e.keyCode == 88 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                if($(this).val().indexOf(',')>=0 && e.keyCode==188) e.preventDefault();
                else return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }        
    });

    $('.grid-container .pesquisa-avancada .money input').priceFormat({
        prefix: 'R$ ',
        centsSeparator: ',',
        thousandsSeparator: '.',
        clearPrefix: true,
        allowNegative:true,
        clearOnEmpty: true        
    }); 

    $('.grid-container .pesquisa-avancada .campo .remover').click(function(){
        $(this).closest('.campo').not('.duplo').find('input, select').val('');
        $(this).closest('.de').find('input, select').val('');
        $(this).closest('.ate').find('input, select').val('');
        $(this).closest('form').submit();
    });

    $('.grid-container .exibindo-registros-info select').change(function(){
        window.location = $(this).attr('data-url')+'&registros-por-pagina='+$(this).val();
    });

    $('.grid-container select[name=exportar]').change(function(){
        if($(this).val()){
            $(this).closest('.grid-container').find('.btn-exportar').attr('href', $(this).closest('.grid-container').find('.btn-exportar').attr('data-href')+'&exportar='+$(this).val() );
        }else{
            $(this).closest('.grid-container').find('.btn-exportar').attr('href', '#');         
        }
    });

    $('.grid-container .btn-exportar').click(function(e){
        if($(this).attr('href')=='#'){
            e.preventDefault();
            alert('Selecione um formato para a exportação!');
            return false;
        }
    });

    $('.grid-container .pesquisa-avancada form').submit(function(e){
        erros = '';
        $(this).find('.integer input').each(function(){
            if($(this).val().search(/[^0-9]/)>=0){
                erros+='Informe um número inteiro válido no campo "'+$(this).closest('.campo').find('label').text()+'".\n';
            }            
        });

        $(this).find('.numeric input').each(function(){
            if($(this).val().search(/[^0-9\.,]/)>=0){
                erros+='Informe um número válido no campo "'+$(this).closest('.campo').find('label').text()+'".\n';
            }
        });

        if(erros){
            e.preventDefault();
            alert(erros);
        }
    });
});