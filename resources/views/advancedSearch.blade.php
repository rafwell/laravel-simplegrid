<div class="fields">
<?php
foreach($fields as $field=>$opts){
	if(!is_array($opts)) $opts = ['type'=>$opts];
	if(!isset($opts['label'])) $opts['label'] = ucwords(str_replace('-', ' ', str_slug($field)));
	switch ($opts['type']){
		case 'text': ?>
			<div class="field {!!$field!!} {!!isset($searchedValue[$field]) && $searchedValue[$field]!=='' ? 'searched' : ''!!}">
				<label>{!!$opts['label']!!} <span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></span> </label>
				<input type="text" name="search[][{!!$field!!}]" value="{{isset($searchedValue[$field]) ? $searchedValue[$field] : ''}}" class="form-control" placeholder="{{isset($opts['placeholder']) ? $opts['placeholder'] : ''}}"/>
			</div>
		<?php
		break;
		case 'select': ?>
			<div class="field {!!$field!!} {!!isset($searchedValue[$field]) && $searchedValue[$field]!=='' ? 'searched' : ''!!}">
				<label>{!!$opts['label']!!} <span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></label>
				<select name="search[][{!!$field!!}]" class="form-control">
					<option value="">{{isset($opts['placeholder']) ? $opts['placeholder'] : ''}}</option>					
					@foreach($opts['options'] as $value=>$label)					
					<option value="{{$value}}" {!!isset($searchedValue[$field]) && $searchedValue[$field]!=='' && $searchedValue[$field]==$value ? 'selected' : ''!!}>{{$label}}</option>
					@endforeach
				</select>
			</div>
		<?php
		break;
		case 'date':
		case 'datetime': ?>
			<div class="field double {!!$field!!}" data-format-input="{{$simpleGridConfig['advancedSearch']['formats'][$opts['type']]['input'][0]}}">
				<div class="from {!!isset($searchedValue[$field.'_from']) && $searchedValue[$field.'_from']!=='' ? 'searched' : ''!!}">
					<label>{!!$opts['label']!!} <span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></label>
					<div class="input input-group {!!$opts['type']!!} datetimepicker">
						<span class="input-group-addon">@lang('from:')</span>
						<input type="text" name="search[][{!!$field!!}][from]" class="form-control" value="{{isset($searchedValue[$field.'_from']) ? $searchedValue[$field.'_from'] : ''}}">
						<span class="input-group-addon">
	                        <span class="glyphicon glyphicon-calendar"></span>
	                    </span>
					</div>			
				</div>				
				<div class="to {!!isset($searchedValue[$field.'_to']) && $searchedValue[$field.'_to']!=='' ? 'searched' : ''!!}">
					<label><span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></label>
					<div class="input input-group {!!$opts['type']!!} datetimepicker">
						<span class="input-group-addon">@lang('to:')</span>
						<input type="text" name="search[][{!!$field!!}][to]" class="form-control" value="{{isset($searchedValue[$field.'_to']) ? $searchedValue[$field.'_to'] : ''}}">
						<span class="input-group-addon">
	                        <span class="glyphicon glyphicon-calendar"></span>
	                    </span>
					</div>			
				</div>		
			</div>
		<?php
		break;
		case 'integer': ?>
			<div class="field double {!!$field!!}">
				<div class="from {!!isset($searchedValue[$field.'_from']) && $searchedValue[$field.'_from']!=='' ? 'searched' : ''!!}">
					<label>{!!$opts['label']!!} <span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('from:')</span>
						<input type="number" step="1" name="search[][{!!$field!!}][from]" class="form-control" value="{{isset($searchedValue[$field.'_from']) ? $searchedValue[$field.'_from'] : ''}}">
					</div>			
				</div>				
				<div class="to {!!isset($searchedValue[$field.'_to']) && $searchedValue[$field.'_to']!=='' ? 'searched' : ''!!}">
					<label><span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('to:')</span>
						<input type="number" step="1" name="search[][{!!$field!!}][to]" class="form-control" value="{{isset($searchedValue[$field.'_to']) ? $searchedValue[$field.'_to'] : ''}}">
					</div>			
				</div>		
			</div>
		<?php
		case 'decimal': ?>
			<div class="field double {!!$field!!}">
				<div class="from {!!isset($searchedValue[$field.'_from']) && $searchedValue[$field.'_from']!=='' ? 'searched' : ''!!}">
					<label>{!!$opts['label']!!} <span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('from:')</span>
						<input type="number" step="any" name="search[][{!!$field!!}][from]" class="form-control" value="{{isset($searchedValue[$field.'_from']) ? $searchedValue[$field.'_from'] : ''}}">
					</div>			
				</div>				
				<div class="to {!!isset($searchedValue[$field.'_to']) && $searchedValue[$field.'_to']!=='' ? 'searched' : ''!!}">
					<label><span class="btn-remove"><span class="glyphicon glyphicon-remove"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('to:')</span>
						<input type="number" step="any" name="search[][{!!$field!!}][to]" class="form-control" value="{{isset($searchedValue[$field.'_to']) ? $searchedValue[$field.'_to'] : ''}}">
					</div>			
				</div>		
			</div>
		<?php
		break;
	}
} ?>
</div>