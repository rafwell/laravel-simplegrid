<div class="fields">
<?php
foreach($fields as $field=>$opts){		
	switch ($opts['type']){
		case 'text': ?>
			<div class="field {!!str_replace('.', '_', $field)!!} {!!isset($searchedValue[$field]) && $searchedValue[$field]!=='' ? 'searched' : ''!!}">
				<label>{!!$opts['label']!!} <span class="btn-remove"><span class="fa fa-times"></span></span> </label>
				<input type="text" name="search[][{!!$field!!}]" value="{{isset($searchedValue[$field]) ? $searchedValue[$field] : ''}}" class="form-control" placeholder="{{isset($opts['placeholder']) ? $opts['placeholder'] : ''}}"/>
			</div>
		<?php
		break;
		case 'select': ?>
			<div class="field {!!str_replace('.', '_', $field)!!} {!!isset($searchedValue[$field]) && $searchedValue[$field]!=='' ? 'searched' : ''!!}">
				<label>{!!$opts['label']!!} <span class="btn-remove"><span class="fa fa-times"></span></label>
				<select 
					name="search[][{!!$field!!}]" 
					class="form-control" 
					@if($opts['multiple'])
					multiple
					data-value="{{isset($searchedValue[$field]) ? json_encode($searchedValue[$field]) : ''}}" 
					@else
					data-value="{{isset($searchedValue[$field]) && $searchedValue[$field]!=='' ? $searchedValue[$field] : ''}}" 
					@endif
					@foreach($opts['attrs'] as $k=>$v)
						@if(is_int($k))
							{{$v}}
						@else
							{{$k}}="{{$v}}"
						@endif
					@endforeach
				>
					<option value="">{{isset($opts['placeholder']) ? $opts['placeholder'] : ''}}</option>					
					@foreach($opts['options'] as $value=>$label)					
					<option value="{{$value}}" 
						@if($opts['multiple'])
						{!!isset($searchedValue[$field]) && in_array($value, $searchedValue[$field]) ? 'selected' : ''!!}
						@else
						{!!isset($searchedValue[$field]) && $searchedValue[$field]!=='' && $searchedValue[$field]==$value ? 'selected' : ''!!}
						@endif
						>
						{{$label}}
					</option>
					@endforeach
				</select>
			</div>
		<?php
		break;
		case 'date':
		case 'datetime': ?>
			<div class="field double {!!str_replace('.', '_', $field)!!}" data-format-input="{{$simpleGridConfig['advancedSearch']['formats'][$opts['type']]['input'][0]}}">
				<div class="from {!!isset($searchedValue[$field.'_from']) && $searchedValue[$field.'_from']!=='' ? 'searched' : ''!!}">
					<label>{!!$opts['label']!!} <span class="btn-remove"><span class="fa fa-times"></span></label>
					<div class="input input-group {!!$opts['type']!!} datetimepicker">
						<span class="input-group-addon">@lang('Simplegrid::grid.from:')</span>
						<input type="text" name="search[][{!!$field!!}][from]" class="form-control" value="{{isset($searchedValue[$field.'_from']) ? $searchedValue[$field.'_from'] : ''}}">
						<span class="input-group-addon">
	                        <span class="fa fa-calendar"></span>
	                    </span>
					</div>			
				</div>				
				<div class="to {!!isset($searchedValue[$field.'_to']) && $searchedValue[$field.'_to']!=='' ? 'searched' : ''!!}">
					<label><span class="btn-remove"><span class="fa fa-times"></span></label>
					<div class="input input-group {!!$opts['type']!!} datetimepicker">
						<span class="input-group-addon">@lang('Simplegrid::grid.to:')</span>
						<input type="text" name="search[][{!!$field!!}][to]" class="form-control" value="{{isset($searchedValue[$field.'_to']) ? $searchedValue[$field.'_to'] : ''}}">
						<span class="input-group-addon">
	                        <span class="fa fa-calendar"></span>
	                    </span>
					</div>			
				</div>		
			</div>
		<?php
		break;
		case 'integer': ?>
			<div class="field double {!!str_replace('.', '_', $field)!!}">
				<div class="from {!!isset($searchedValue[$field.'_from']) && $searchedValue[$field.'_from']!=='' ? 'searched' : ''!!}">
					<label>{!!$opts['label']!!} <span class="btn-remove"><span class="fa fa-times"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('Simplegrid::grid.from:')</span>
						<input type="number" step="1" name="search[][{!!$field!!}][from]" class="form-control" value="{{isset($searchedValue[$field.'_from']) ? $searchedValue[$field.'_from'] : ''}}">
					</div>			
				</div>				
				<div class="to {!!isset($searchedValue[$field.'_to']) && $searchedValue[$field.'_to']!=='' ? 'searched' : ''!!}">
					<label><span class="btn-remove"><span class="fa fa-times"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('Simplegrid::grid.to:')</span>
						<input type="number" step="1" name="search[][{!!$field!!}][to]" class="form-control" value="{{isset($searchedValue[$field.'_to']) ? $searchedValue[$field.'_to'] : ''}}">
					</div>			
				</div>		
			</div>
		<?php
		break;
		case 'decimal': ?>
			<div class="field double {!!str_replace('.', '_', $field)!!}">
				<div class="from {!!isset($searchedValue[$field.'_from']) && $searchedValue[$field.'_from']!=='' ? 'searched' : ''!!}">
					<label>{!!$opts['label']!!} <span class="btn-remove"><span class="fa fa-times"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('Simplegrid::grid.from:')</span>
						<input type="number" step="any" name="search[][{!!$field!!}][from]" class="form-control" value="{{isset($searchedValue[$field.'_from']) ? $searchedValue[$field.'_from'] : ''}}">
					</div>			
				</div>				
				<div class="to {!!isset($searchedValue[$field.'_to']) && $searchedValue[$field.'_to']!=='' ? 'searched' : ''!!}">
					<label><span class="btn-remove"><span class="fa fa-times"></span></label>
					<div class="input input-group {!!$opts['type']!!}">
						<span class="input-group-addon">@lang('Simplegrid::grid.to:')</span>
						<input type="number" step="any" name="search[][{!!$field!!}][to]" class="form-control" value="{{isset($searchedValue[$field.'_to']) ? $searchedValue[$field.'_to'] : ''}}">
					</div>			
				</div>		
			</div>
		<?php
		break;
	}
} ?>
</div>