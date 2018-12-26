<div class="grid-container grid" id="grid{{$id}}">	
	<span></span>
	@if ($advancedSearch && $advancedSearchOpened === true)
		<div class="search advanced-search {{isset($searchedValue) && $searchedValue<>'' ? 'searched' : ''}}">
			<form action="{{$url}}" method="get">
				@foreach($fieldsRequest as $field=>$valor)
					@if ($field<>'search')
						<input type="hidden" name="{{$field}}" value="{{$valor}}">
					@endif
				@endforeach
				<fieldset>
					<legend>@lang('Simplegrid::grid.Advanced Search')</legend>
					@include('Simplegrid::advancedSearch', ['fields'=>$advancedSearchFields])
					<button class="btn-submit-advanced-search btn btn-default" type="submit" title="@lang('Simplegrid::grid.Search')">
						<span class="glyphicon glyphicon-search"> </span> @lang('Simplegrid::grid.Search')
					</button>
					@if($allowSearch)
						<a href="{{$urlSimpleSearch}}" class="btn btn-default" title="@lang('Simplegrid::grid.Simple Search')"><span class="glyphicon glyphicon-zoom-out"></span></a>
					@endif
					@if ($totalRows>0)
						<span class="total-info pull-right">
							{{trans_choice('Simplegrid::grid.Page :current_page of :total_pages. Total of :total_rows row.', $totalRows, [
								'current_page'=>$currentPage, 
								'total_pages'=>$totalPages, 
								'total_rows'=>$totalRows
							])}}
						</span>
					@endif
				</fieldset>
			</form>
		</div>
	@elseif($allowSearch)
		<div class="search simple-search {{isset($searchedValue) && $searchedValue<>'' ? 'searched' : ''}}">		
			<form action="{{$url}}" method="get">			
				@foreach($fieldsRequest as $field=>$valor)
					@if ($field<>'search' && !is_array($valor) && !is_object($valor))
						<input type="hidden" name="{{$field}}" value="{{$valor}}">
					@endif
				@endforeach
		      	<input type="text" name="search" class="form-control input-search" placeholder="@lang('Simplegrid::grid.Search by...')" value="{{$searchedValue}}">
		        <button class="btn-search btn btn-default" type="submit" title="@lang('Simplegrid::grid.Search')"><span class="glyphicon glyphicon-search"></span></button>		      	
		      	@if (isset($searchedValue) && $searchedValue<>'')				      		
			       	<button class="btn-clear-search btn btn-default" type="button" title="@lang('Simplegrid::grid.Clear search')"><span class="glyphicon glyphicon-remove"></span></button>
		      	@endif
		      	@if ($advancedSearch && $advancedSearchOpened === false)
					<a href="{{$urlAdvancedSearch}}" class="btn-advanced-search btn btn-default" title="@lang('Simplegrid::grid.Advanced Search')"><span class="glyphicon glyphicon-zoom-in"></span></a>
		      	@endif
		    </form>
			@if ($totalRows>0)
				<span class="total-info">
					{{trans_choice('Simplegrid::grid.Page :current_page of :total_pages. Total of :total_rows row.', $totalRows, [
						'current_page'=>$currentPage, 
						'total_pages'=>$totalPages, 
						'total_rows'=>$totalRows
					])}}
				</span>
			@endif
		</div>
	@endif
	
	<div class="row">
		<div class="col-md-8">
			@if($bulkActions)
			<div class="bulk-action">
				<select name="grid_bulk_action" class="grid_bulk_action" data-token="{{ csrf_token() }}" data-confirm-msg="@lang('Simplegrid::grid.Do you really want to apply this action to the selected items?')" data-alert-msg="@lang('Simplegrid::grid.Select at least one item to apply the action!')">
					<option value="">@lang('Simplegrid::grid.Apply to selected')</option>
					@foreach($bulkActions as $action)
					<option 
						value="{{$action['url']}}"
						data-confirm-msg="{{$action['confirm']}}"
						data-method="{{$action['method']}}"
						@foreach($action['attrs'] as $attr=>$value)
							{!!$attr!!}="{!!$value!!}"
						@endforeach
					>
						{!!$action['title']!!}
					</option>
					@endforeach
				</select>		
			</div>
			@endif	
		</div>
		<div class="col-md-4">
			<div class="showing-rows-info pull-right">
				<span>@lang('Simplegrid::grid.Showing') </span>
				<select name="rows-per-page" data-url="{{$urlRowsPerPage}}">
					@foreach($rowsPerPage as $nr)
					<option value="{!!$nr!!}" {!!$nr==$currentRowsPerPage ? 'selected' : '' !!}>{!!$nr!!}</option>
					@endforeach
				</select>
				<span>@lang('Simplegrid::grid.rows per page.')</span>
			</div>
		</div>
	</div>	
	<div class="table-responsive">
		<table class="table table-bordered table-striped table-hover table-condensed grid">
			<thead>
				<tr>
					@if($checkbox['show'])
						<th>
							<input type="checkbox" class="select-all">
						</th>
					@endif					
					@foreach ($fields as $k=>$v)	
						@if($v['show'])
						<th class="{!!$k.' '.str_replace('.', ' ', $v['alias'])!!}">
							<div class="arrows">
								<a href="{{$urlOrder}}&order={!!$k!!}&direction=asc" title="@lang('Simplegrid::grid.Order ascending')" class="arrow-up"></a>
								<a href="{{$urlOrder}}&order={!!$k!!}&direction=desc" title="@lang('Simplegrid::grid.Order descending')" class="arrow-down"></a>
							</div>
							<span>{{$v['label']}}</span>
						</th>	
						@endif				
					@endforeach
					@if (isset($actions))
						<th class="actions">@lang('Simplegrid::grid.Actions')</th>
					@endif
				</tr>
			</thead>			
			<tbody>
				@if (isset($rows) && count($rows)>0)
					@foreach ($rows as $row)			
						<tr>
							@if($checkbox['show'])
								<td>
									<input class="grid-checkbox" type="checkbox" name="grid_checkbox_{!!$checkbox['field']!!}" value="{!!$row[$checkbox['field']]!!}"/>
								</td>
							@endif
							@foreach ($fields as $k=>$v)	
								@if($v['show'])															
								<td class="field {!!str_replace('.', ' ', $v['alias'])!!}">									
									{!!$row[$v['alias_after_query_executed']]!!}
								</td>
								@endif
							@endforeach
							@if (isset($actions))
								<td class="actions">
									@foreach ($row['gridActions'] as $action)	
										@if($action['method']=='GET')								
											<a href="{!!$action['url']!!}" title="{{$action['title']}}" class="btn btn-xs action btn-default" target="{{$action['target']}}">
												@if (isset($action['icon']))
													<span class="{{$action['icon']}}"></span>
												@endif
												@if ($action['onlyIcon']===false)
													{{$action['title']}}
												@endif
												{!!$action['append']!!}
											</a>
										@elseif($action['method']=='BUTTON')
											<button 
											 type="button" title="{{$action['title']}}" 
											 class="btn btn-xs action btn-default" 
											 data-csrf="{{csrf_token()}}"
											 @foreach($action['attrs'] as $attr=>$value)
											 	{!!$attr!!}="{!!$value!!}"
											 @endforeach
											>
												@if (isset($action['icon']))
													<span class="{{$action['icon']}}"></span>
												@endif
												@if ($action['onlyIcon']===false)
													{{$action['title']}}
												@endif
												{!!$action['append']!!}
											</button>
										@else
											<form action="{!!$action['url']!!}" method="POST" {!! ($action['confirm']!==false ? 'onsubmit="if(!confirm(\''.addslashes(htmlentities($action['confirm'])).'\')){event.preventDefault; return false;}; "' : '' ); !!} >
												{{csrf_field()}}
												<input type="hidden" name="_method" value="{!!$action['method']!!}">
												<button type="submit" title="{{$action['title']}}" class="btn btn-xs action btn-default">
													@if (isset($action['icon']))
														<span class="{{$action['icon']}}"></span>
													@endif
													@if ($action['onlyIcon']===false)
														{{$action['title']}}
													@endif
													{!!$action['append']!!}
												</button>
											</form>
										@endif
										{!!$action['next']!!}
									@endforeach
								</td>
							@endif
						</tr>
					@endforeach
				@else
					<tr>
						<td colspan="{!!isset($actions) ? count($fields)+($checkbox['show'] ? 1 : 0)+1 : count($fields)+($checkbox['show'] ? 1 : 0) !!}" class="no-results-found">
							<span>@lang('Simplegrid::grid.No results found.')</span>
						</td>
					</tr>
				@endif
			</tbody>
		</table>
	</div>
	@if (isset($rows) && count($rows)>0)
	<div class="row">
		<div class="col-md-{!! $totalPages>1 ? '7' : '12' !!}">	
			@if($allowExport)
			<div class="input-group">				
				<select name="export" class="form-control">
					<option value="">@lang('Simplegrid::grid.Select an option to export')</option>
					<option value="xls">XLS</option>
					<option value="csv">CSV</option>
				</select>
				<a href="#" data-href="{{$urlExport}}" target="_blank" class="input-group-addon btn-export" title="@lang('Simplegrid::grid.Export')" data-alert-msg="@lang('Simplegrid::grid.Select a format for export!')">
					<span class="glyphicon glyphicon-download"></span> @lang('Simplegrid::grid.Export')
				</a>
			</div>		
			@endif
		</div>
		<div class="col-md-5">
			@if ($totalPages>1)
				@include('Simplegrid::pagination-types.'.$simpleGridConfig['paginationType'])
		    @endif	
		</div>
	</div>
	@endif
</div>