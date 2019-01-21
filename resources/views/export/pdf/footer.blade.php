<!DOCTYPE html>
	<head>
		<link href="{!!asset('assets/relatorios/estilos.css')!!}" rel="stylesheet" />	
		<meta charset="utf-8" />
		<script>
	        function substitutePdfVariables() {

	            function getParameterByName(name) {
	                var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
	                return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	            }

	            function substitute(name) {
	                var value = getParameterByName(name);
	                var elements = document.getElementsByClassName(name);

	                for (var i = 0; elements && i < elements.length; i++) {
	                    elements[i].textContent = value;
	                }
	            }

	            ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection']
	                .forEach(function(param) {
	                    substitute(param);
	                });
	        }
	    </script>
	</head>
	<body onload="substitutePdfVariables()">
		<div class="text-center">
			@lang('Simplegrid::grid.Page') <span class="page"></span>/<span class="topage"></span>
		</div>
	</body>
</html>