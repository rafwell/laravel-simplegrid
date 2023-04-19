<!DOCTYPE html>
	<head>
        <style>
            {!!$inlineCss!!}
        </style>
		<meta charset="utf-8" />		
	</head>
	<body>
		<table class="table table-bordered table-striped table-hover table-condensed grid">
            <thead>
                <tr>
                    @foreach ($headers as $v)	
                        <th>
                            {!!$v!!}
                        </th>			
                    @endforeach
                </tr>
            </thead>			
            <tbody>        
                @foreach ($rows as $row)			
                    <tr>
                        @foreach ($row as $v)
                            <td>
                                {!!$v!!}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
	</body>
</html>