<?php
	function divide_svg($divideNum, $input) {
		$inputCopy = $input;
		if(preg_match_all("/"."[0-9\.]+"."/", $input, $matches, PREG_OFFSET_CAPTURE)) {
			krsort($matches[0]);
			foreach($matches[0] as $num) {
				$inputCopy = substr_replace($inputCopy, round(($num[0] / $divideNum), 3), $num[1], strlen($num[0]));
			}
			echo $inputCopy;
		}
	}
	
	$paths = [
		["company", 1, '<path d="M0 1h0.5v-1h-0.5v1zM0.3125 0.125h0.125v0.125h-0.125v-0.125zM0.3125 0.375h0.125v0.125h-0.125v-0.125zM0.3125 0.625h0.125v0.125h-0.125v-0.125zM0.0625 0.125h0.125v0.125h-0.125v-0.125zM0.0625 0.375h0.125v0.125h-0.125v-0.125zM0.0625 0.625h0.125v0.125h-0.125v-0.125zM0.5625 0.3125h0.4375v0.0625h-0.4375zM0.5625 1h0.125v-0.25h0.1875v0.25h0.125v-0.5625h-0.4375z"></path>'],
		["artist", 34, '<path d="M24 24.082v-1.649c2.203-1.241 4-4.337 4-7.432 0-4.971 0-9-6-9s-6 4.029-6 9c0 3.096 1.797 6.191 4 7.432v1.649c-6.784 0.555-12 3.888-12 7.918h28c0-4.030-5.216-7.364-12-7.918z"></path><path d="M10.225 24.854c1.728-1.13 3.877-1.989 6.243-2.513-0.47-0.556-0.897-1.176-1.265-1.844-0.95-1.726-1.453-3.627-1.453-5.497 0-2.689 0-5.228 0.956-7.305 0.928-2.016 2.598-3.265 4.976-3.734-0.529-2.39-1.936-3.961-5.682-3.961-6 0-6 4.029-6 9 0 3.096 1.797 6.191 4 7.432v1.649c-6.784 0.555-12 3.888-12 7.918h8.719c0.454-0.403 0.956-0.787 1.506-1.146z"></path>'],
		["user", 32, '<path stroke="null" fill="black" id="svg_1" d="m31.69646,11.18888c0,-0.418 -0.14098,-0.81101 -0.37694,-1.04858c-0.23597,-0.24249 -0.53919,-0.301 -0.81221,-0.15901l-6.70758,3.48516l-7.11947,-12.24146c-0.3378,-0.58361 -1.02272,-0.58361 -1.36052,0l-7.11947,12.24146l-6.70758,-3.48516c-0.27245,-0.14226 -0.57434,-0.08377 -0.81259,0.15901c-0.23653,0.24059 -0.37656,0.6303 -0.37656,1.04858l0,15.38855c0,4.94514 14.09284,5.06873 15.69864,5.06873c1.6058,0 15.69409,-0.12359 15.69409,-4.44173l0.00019,-16.01555z"/>'],
		["news", 32, '<path d="M28 8v-4h-28v22c0 1.105 0.895 2 2 2h27c1.657 0 3-1.343 3-3v-17h-4zM26 26h-24v-20h24v20zM4 10h20v2h-20zM16 14h8v2h-8zM16 18h8v2h-8zM16 22h6v2h-6zM4 14h10v10h-10z"></path>'],
		["release", 32, '<path d="M11.757 11.758c-2.343 2.343-2.343 6.142 0 8.485s6.142 2.343 8.485 0c2.344-2.344 2.344-6.143 0-8.485-2.343-2.344-6.141-2.344-8.485 0zM17.414 17.414c-0.781 0.781-2.047 0.781-2.828 0s-0.781-2.047 0-2.828 2.047-0.781 2.828 0 0.781 2.047 0 2.828zM27.313 4.687c-6.248-6.249-16.379-6.249-22.627 0-6.249 6.248-6.249 16.379 0 22.627 6.248 6.249 16.379 6.249 22.627 0 6.249-6.249 6.249-16.379 0-22.627zM21.656 21.657c-3.123 3.124-8.189 3.124-11.313 0-3.125-3.125-3.125-8.189 0-11.314s8.19-3.124 11.313 0c3.125 3.125 3.125 8.189 0 11.314zM22.363 9.636l3.536-4.949 1.414 1.414-4.95 3.535z"></path>'],
		["join", 32, '<path d="M12 23c0-4.726 2.996-8.765 7.189-10.319 0.509-1.142 0.811-2.411 0.811-3.681 0-4.971 0-9-6-9s-6 4.029-6 9c0 3.096 1.797 6.191 4 7.432v1.649c-6.784 0.555-12 3.888-12 7.918h12.416c-0.271-0.954-0.416-1.96-0.416-3z"></path><path d="M23 14c-4.971 0-9 4.029-9 9s4.029 9 9 9c4.971 0 9-4.029 9-9s-4.029-9-9-9zM28 24h-4v4h-2v-4h-4v-2h4v-4h2v4h4v2z"></path>'],
		["trash", 30, '<path d="M8 26c0 1.656 1.343 3 3 3h10c1.656 0 3-1.344 3-3l2-16h-20l2 16zM19 13h2v13h-2v-13zM15 13h2v13h-2v-13zM11 13h2v13h-2v-13zM25.5 6h-6.5c0 0-0.448-2-1-2h-4c-0.553 0-1 2-1 2h-6.5c-0.829 0-1.5 0.671-1.5 1.5s0 1.5 0 1.5h22c0 0 0-0.671 0-1.5s-0.672-1.5-1.5-1.5z"></path>'],
		["down-caret", 16, '<path d="M1,6.938C1,6.425,1.425,6,1.938,6h13.125C15.575,6,16,6.425,16,6.938c0,0.249-0.102,0.483-0.278,0.659l-6.563,6.563 c-0.175,0.175-0.411,0.278-0.659,0.278s-0.483-0.102-0.659-0.278L1.278,7.597C1.103,7.421,1,7.187,1,6.938z"></path>'],
		["up-caret", 16, '<path d="M16,13.5c0,0.513-0.425,0.938-0.938,0.938H1.938C1.425,14.438,1,14.013,1,13.5c0-0.249,0.102-0.483,0.278-0.659l6.563-6.563 C8.016,6.103,8.251,6,8.5,6c0.248,0,0.483,0.102,0.659,0.278l6.563,6.563C15.897,13.017,16,13.251,16,13.5z"></path>'],
		["down-up", 900, '<path d="M576 640v192h128l-192 192-192-192h128v-192zM448 384v-192h-128l192-192 192 192h-128v192z"></path>'],
		["help", 20, '<path d="M10 0.4c-5.302 0-9.6 4.298-9.6 9.6s4.298 9.6 9.6 9.6c5.301 0 9.6-4.298 9.6-9.601 0-5.301-4.299-9.599-9.6-9.599zM9.849 15.599h-0.051c-0.782-0.023-1.334-0.6-1.311-1.371 0.022-0.758 0.587-1.309 1.343-1.309l0.046 0.002c0.804 0.023 1.35 0.594 1.327 1.387-0.023 0.76-0.578 1.291-1.354 1.291zM13.14 9.068c-0.184 0.26-0.588 0.586-1.098 0.983l-0.562 0.387c-0.308 0.24-0.494 0.467-0.563 0.688-0.056 0.174-0.082 0.221-0.087 0.576v0.090h-2.145l0.006-0.182c0.027-0.744 0.045-1.184 0.354-1.547 0.485-0.568 1.555-1.258 1.6-1.287 0.154-0.115 0.283-0.246 0.379-0.387 0.225-0.311 0.324-0.555 0.324-0.793 0-0.334-0.098-0.643-0.293-0.916-0.188-0.266-0.545-0.398-1.061-0.398-0.512 0-0.863 0.162-1.072 0.496-0.216 0.341-0.325 0.7-0.325 1.067v0.092h-2.211l0.004-0.096c0.057-1.353 0.541-2.328 1.435-2.897 0.563-0.361 1.264-0.544 2.081-0.544 1.068 0 1.972 0.26 2.682 0.772 0.721 0.519 1.086 1.297 1.086 2.311-0.001 0.567-0.18 1.1-0.534 1.585z"></path>'],
		["vip", 30, '<path d="M16.033 30.004c0 0-11.037-6.833-11.037-12.339 0-3.053 1.745-6.071 5.042-6.538v-2.145c0-3.307 2.687-5.986 6-5.986 3.314 0 6 2.68 6 5.986v2.146c3.182 0.475 4.967 3.434 4.967 6.536-0.001 5.574-10.972 12.34-10.972 12.34zM14.475 23.947h3.062l-1.298-3.47c0.728-0.102 1.298-0.701 1.298-1.456 0-0.826-0.672-1.497-1.5-1.497s-1.5 0.671-1.5 1.497c0 0.718 0.518 1.29 1.194 1.435l-1.256 3.491zM20.037 8.982c0-2.204-1.791-3.991-4-3.991s-4 1.787-4 3.991v2.182c2.75 0.607 3.995 3.26 3.995 3.26s1.11-2.684 4.004-3.267v-2.175z"></path>'],
		["search", 32, '<path d="M30 26l-7.785-7.785c1.111-1.814 1.785-3.929 1.785-6.215 0-6.626-5.375-12-12-12s-12 5.374-12 12c0 6.625 5.374 12 12 12 2.286 0 4.4-0.674 6.215-1.781l7.785 7.781c0.547 0.547 1.453 0.543 2 0l2-2c0.547-0.547 0.547-1.453 0-2zM12 20c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"></path>'],
		["locked", 20, '<path d="M17 10h-1v-2c0-2.205-1.794-4-4-4s-4 1.795-4 4v2h-1c-1.103 0-2 0.896-2 2v7c0 1.104 0.897 2 2 2h10c1.103 0 2-0.896 2-2v-7c0-1.104-0.897-2-2-2zM12 18.299c-0.719 0-1.3-0.58-1.3-1.299s0.581-1.301 1.3-1.301 1.3 0.582 1.3 1.301-0.581 1.299-1.3 1.299zM14 11h-4v-3c0-1.104 0.897-2 2-2s2 0.896 2 2v3z"></path>'],
		["like", 28, '<path d="M4 21c0-0.547-0.453-1-1-1-0.562 0-1 0.453-1 1 0 0.562 0.438 1 1 1 0.547 0 1-0.438 1-1zM6.5 13v10c0 0.547-0.453 1-1 1h-4.5c-0.547 0-1-0.453-1-1v-10c0-0.547 0.453-1 1-1h4.5c0.547 0 1 0.453 1 1zM25 13c0 0.828-0.328 1.719-0.859 2.328 0.172 0.5 0.234 0.969 0.234 1.188 0.031 0.781-0.203 1.516-0.672 2.141 0.172 0.578 0.172 1.203 0 1.828-0.156 0.578-0.453 1.094-0.844 1.469 0.094 1.172-0.172 2.125-0.766 2.828-0.672 0.797-1.703 1.203-3.078 1.219h-2.016c-2.234 0-4.344-0.734-6.031-1.313-0.984-0.344-1.922-0.672-2.469-0.688-0.531-0.016-1-0.453-1-1v-10.016c0-0.516 0.438-0.953 0.953-1 0.578-0.047 2.078-1.906 2.766-2.812 0.562-0.719 1.094-1.391 1.578-1.875 0.609-0.609 0.781-1.547 0.969-2.453 0.172-0.922 0.359-1.891 1.031-2.547 0.187-0.187 0.438-0.297 0.703-0.297 3.5 0 3.5 2.797 3.5 4 0 1.281-0.453 2.188-0.875 3-0.172 0.344-0.328 0.5-0.453 1h4.328c1.625 0 3 1.375 3 3z"></path>'],
		["caret-right", 20, '<path d="M15 10l-9 5v-10l9 5z"></path>'],
		["arrow-right-circled", 28, '<path d="M18 14c0 0.125-0.047 0.266-0.141 0.359l-5 5c-0.094 0.094-0.234 0.141-0.359 0.141-0.266 0-0.5-0.234-0.5-0.5v-3h-5.5c-0.266 0-0.5-0.234-0.5-0.5v-3c0-0.266 0.234-0.5 0.5-0.5h5.5v-3c0-0.281 0.219-0.5 0.5-0.5 0.141 0 0.266 0.063 0.375 0.156l4.984 4.984c0.094 0.094 0.141 0.234 0.141 0.359zM20.5 14c0-4.688-3.813-8.5-8.5-8.5s-8.5 3.813-8.5 8.5 3.813 8.5 8.5 8.5 8.5-3.813 8.5-8.5zM24 14c0 6.625-5.375 12-12 12s-12-5.375-12-12 5.375-12 12-12 12 5.375 12 12z"></path>'],
		["loading", 30, '<path d="M32 16c-0.040-2.089-0.493-4.172-1.331-6.077-0.834-1.906-2.046-3.633-3.533-5.060-1.486-1.428-3.248-2.557-5.156-3.302-1.906-0.748-3.956-1.105-5.981-1.061-2.025 0.040-4.042 0.48-5.885 1.292-1.845 0.809-3.517 1.983-4.898 3.424s-2.474 3.147-3.193 4.994c-0.722 1.846-1.067 3.829-1.023 5.79 0.040 1.961 0.468 3.911 1.254 5.694 0.784 1.784 1.921 3.401 3.316 4.736 1.394 1.336 3.046 2.391 4.832 3.085 1.785 0.697 3.701 1.028 5.598 0.985 1.897-0.040 3.78-0.455 5.502-1.216 1.723-0.759 3.285-1.859 4.574-3.208 1.29-1.348 2.308-2.945 2.977-4.67 0.407-1.046 0.684-2.137 0.829-3.244 0.039 0.002 0.078 0.004 0.118 0.004 1.105 0 2-0.895 2-2 0-0.056-0.003-0.112-0.007-0.167h0.007zM28.822 21.311c-0.733 1.663-1.796 3.169-3.099 4.412s-2.844 2.225-4.508 2.868c-1.663 0.646-3.447 0.952-5.215 0.909-1.769-0.041-3.519-0.429-5.119-1.14-1.602-0.708-3.053-1.734-4.25-2.991s-2.141-2.743-2.76-4.346c-0.621-1.603-0.913-3.319-0.871-5.024 0.041-1.705 0.417-3.388 1.102-4.928 0.683-1.541 1.672-2.937 2.883-4.088s2.642-2.058 4.184-2.652c1.542-0.596 3.192-0.875 4.832-0.833 1.641 0.041 3.257 0.404 4.736 1.064 1.48 0.658 2.82 1.609 3.926 2.774s1.975 2.54 2.543 4.021c0.57 1.481 0.837 3.064 0.794 4.641h0.007c-0.005 0.055-0.007 0.11-0.007 0.167 0 1.032 0.781 1.88 1.784 1.988-0.195 1.088-0.517 2.151-0.962 3.156z"></path>'],
		["success", 34, '<path d="M16 0q-6.625 0-11.313 4.688t-4.688 11.313 4.688 11.313 11.313 4.688 11.313-4.688 4.688-11.313-4.688-11.313-11.313-4.688zM13.5 23.375l-7.313-7.375 2.813-2.813 4.5 4.563 9.625-9.625 2.813 2.813z"></path>'],
		["error", 34, '<path d="M31.25 28l-14.25-25.063q-0.313-0.563-0.969-0.563t-0.969 0.563l-14.25 25.063q-0.313 0.5 0 1.063t0.938 0.563h28.5q0.625 0 0.969-0.563t0.031-1.063zM17.938 26.375h-3.875v-3.188h3.875v3.188zM17.938 20.813h-3.875v-9.625h3.875v9.625z"></path>'],
		["exit", 32, '<path d="M24 20v-4h-10v-4h10v-4l6 6zM22 18v8h-10v6l-12-6v-26h22v10h-2v-8h-16l8 4v18h8v-6z"></path>'],
		["tag", 32, '<path d="M30.5 0h-12c-0.825 0-1.977 0.477-2.561 1.061l-14.879 14.879c-0.583 0.583-0.583 1.538 0 2.121l12.879 12.879c0.583 0.583 1.538 0.583 2.121 0l14.879-14.879c0.583-0.583 1.061-1.736 1.061-2.561v-12c0-0.825-0.675-1.5-1.5-1.5zM23 12c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"></path>'],
		["oldest", 32, '<path d="M4 28v-24h4v11l10-10v10l10-10v22l-10-10v10l-10-10v11z"></path>'],
		["newest", 32, '<path d="M28 4v24h-4v-11l-10 10v-10l-10 10v-22l10 10v-10l10 10v-11z"></path>'],
		["previous", 18, '<path d="M12.452 4.516c0.446 0.436 0.481 1.043 0 1.576l-3.747 3.908 3.747 3.908c0.481 0.533 0.446 1.141 0 1.574-0.445 0.436-1.197 0.408-1.615 0-0.418-0.406-4.502-4.695-4.502-4.695-0.223-0.217-0.335-0.502-0.335-0.787s0.112-0.57 0.335-0.789c0 0 4.084-4.287 4.502-4.695s1.17-0.436 1.615 0z"></path>'],
		["next", 18, '<path d="M9.163 4.516c0.418 0.408 4.502 4.695 4.502 4.695 0.223 0.219 0.335 0.504 0.335 0.789s-0.112 0.57-0.335 0.787c0 0-4.084 4.289-4.502 4.695-0.418 0.408-1.17 0.436-1.615 0-0.446-0.434-0.481-1.041 0-1.574l3.747-3.908-3.747-3.908c-0.481-0.533-0.446-1.141 0-1.576s1.197-0.409 1.615 0z"></path>'],
		["unchecked", 32, '<path d="M16 0c-8.837 0-16 7.163-16 16s7.163 16 16 16 16-7.163 16-16-7.163-16-16-16zM16 28c-6.627 0-12-5.373-12-12s5.373-12 12-12c6.627 0 12 5.373 12 12s-5.373 12-12 12z"></path>'],
		["checked", 32, '<path d="M16 0q-6.625 0-11.313 4.688t-4.688 11.313 4.688 11.313 11.313 4.688 11.313-4.688 4.688-11.313-4.688-11.313-11.313-4.688zM13.5 23.375l-7.313-7.375 2.813-2.813 4.5 4.563 9.625-9.625 2.813 2.813z"></path>'],
		["ticket", 32, '<path d="M18.286 8.071l5.643 5.643-10.214 10.214-5.643-5.643zM14.518 25.554l11.036-11.036c0.446-0.446 0.446-1.161 0-1.607l-6.464-6.464c-0.429-0.429-1.179-0.429-1.607 0l-11.036 11.036c-0.446 0.446-0.446 1.161 0 1.607l6.464 6.464c0.214 0.214 0.5 0.321 0.804 0.321s0.589-0.107 0.804-0.321zM30.393 14.179l-16.196 16.214c-0.893 0.875-2.357 0.875-3.232 0l-2.25-2.25c1.339-1.339 1.339-3.518 0-4.857s-3.518-1.339-4.857 0l-2.232-2.25c-0.893-0.875-0.893-2.339 0-3.232l16.196-16.179c0.875-0.893 2.339-0.893 3.232 0l2.232 2.232c-1.339 1.339-1.339 3.518 0 4.857s3.518 1.339 4.857 0l2.25 2.232c0.875 0.893 0.875 2.357 0 3.232z"></path>'],
		["section", 32, '<path d="M15.499 32c-1.543 0-2.847-0.45-3.878-1.338-1.038-0.894-1.565-1.939-1.565-3.104 0-0.567 0.206-1.055 0.613-1.452 0.414-0.404 0.931-0.617 1.495-0.617 0.563 0 1.069 0.2 1.463 0.579 0.39 0.374 0.587 0.869 0.587 1.472 0 0.354-0.058 0.744-0.172 1.161-0.113 0.412-0.137 0.623-0.137 0.728 0 0.115 0.029 0.252 0.243 0.399 0.399 0.276 0.877 0.409 1.465 0.409 0.706 0 1.335-0.246 1.924-0.751 0.581-0.498 0.863-1.010 0.863-1.564 0-0.617-0.165-1.151-0.504-1.633-0.573-0.805-1.652-1.749-3.207-2.802-2.496-1.67-4.158-3.118-5.081-4.422-0.715-1.021-1.077-2.122-1.077-3.272 0-1.158 0.379-2.311 1.128-3.426 0.641-0.955 1.588-1.816 2.82-2.567-0.659-0.709-1.153-1.377-1.472-1.99-0.402-0.774-0.606-1.574-0.606-2.379 0-1.494 0.591-2.786 1.756-3.84s2.621-1.59 4.323-1.59c1.565 0 2.881 0.44 3.912 1.308 1.041 0.878 1.569 1.903 1.569 3.048 0 0.583-0.218 1.105-0.649 1.552l-0.009 0.009c-0.249 0.248-0.707 0.543-1.47 0.543-0.598 0-1.123-0.196-1.517-0.567-0.392-0.369-0.592-0.81-0.592-1.311 0-0.217 0.053-0.544 0.167-1.031 0.056-0.23 0.083-0.442 0.083-0.633 0-0.323-0.116-0.571-0.366-0.78-0.259-0.217-0.628-0.322-1.129-0.322-0.777 0-1.415 0.236-1.95 0.722-0.537 0.488-0.798 1.065-0.798 1.766 0 0.63 0.143 1.149 0.424 1.543 0.535 0.748 1.462 1.556 2.757 2.401 2.63 1.706 4.466 3.271 5.454 4.651 0.73 1.035 1.099 2.136 1.099 3.275 0 1.144-0.371 2.297-1.104 3.427-0.631 0.974-1.585 1.852-2.84 2.616 0.693 0.733 1.182 1.376 1.486 1.954 0.378 0.718 0.569 1.502 0.569 2.329 0 1.552-0.591 2.871-1.758 3.919s-2.621 1.58-4.322 1.58zM13.95 11.136c-1.507 0.905-2.241 1.943-2.241 3.166 0 0.711 0.203 1.348 0.621 1.946 0.623 0.873 1.852 1.939 3.65 3.165 0.761 0.518 1.449 1.022 2.050 1.502 1.533-0.922 2.28-1.949 2.28-3.131 0-0.644-0.254-1.337-0.756-2.060-0.525-0.756-1.652-1.744-3.348-2.935-0.885-0.611-1.642-1.167-2.255-1.654z"></path>'],
		["star--empty", 28, '<path d="M17.766 15.687l4.781-4.641-6.594-0.969-2.953-5.969-2.953 5.969-6.594 0.969 4.781 4.641-1.141 6.578 5.906-3.109 5.891 3.109zM26 10.109c0 0.281-0.203 0.547-0.406 0.75l-5.672 5.531 1.344 7.812c0.016 0.109 0.016 0.203 0.016 0.313 0 0.422-0.187 0.781-0.641 0.781-0.219 0-0.438-0.078-0.625-0.187l-7.016-3.687-7.016 3.687c-0.203 0.109-0.406 0.187-0.625 0.187-0.453 0-0.656-0.375-0.656-0.781 0-0.109 0.016-0.203 0.031-0.313l1.344-7.812-5.688-5.531c-0.187-0.203-0.391-0.469-0.391-0.75 0-0.469 0.484-0.656 0.875-0.719l7.844-1.141 3.516-7.109c0.141-0.297 0.406-0.641 0.766-0.641s0.625 0.344 0.766 0.641l3.516 7.109 7.844 1.141c0.375 0.063 0.875 0.25 0.875 0.719z"></path>'],
		["star--half", 28, '<path d="M18.531 14.953l4.016-3.906-6.594-0.969-0.469-0.938-2.484-5.031v15.047l0.922 0.484 4.969 2.625-0.938-5.547-0.187-1.031zM25.594 10.859l-5.672 5.531 1.344 7.812c0.109 0.688-0.141 1.094-0.625 1.094-0.172 0-0.391-0.063-0.625-0.187l-7.016-3.687-7.016 3.687c-0.234 0.125-0.453 0.187-0.625 0.187-0.484 0-0.734-0.406-0.625-1.094l1.344-7.812-5.688-5.531c-0.672-0.672-0.453-1.328 0.484-1.469l7.844-1.141 3.516-7.109c0.203-0.422 0.484-0.641 0.766-0.641v0c0.281 0 0.547 0.219 0.766 0.641l3.516 7.109 7.844 1.141c0.938 0.141 1.156 0.797 0.469 1.469z"></path>'],
		["star--full", 28, '<path d="M26 10.109c0 0.281-0.203 0.547-0.406 0.75l-5.672 5.531 1.344 7.812c0.016 0.109 0.016 0.203 0.016 0.313 0 0.406-0.187 0.781-0.641 0.781-0.219 0-0.438-0.078-0.625-0.187l-7.016-3.687-7.016 3.687c-0.203 0.109-0.406 0.187-0.625 0.187-0.453 0-0.656-0.375-0.656-0.781 0-0.109 0.016-0.203 0.031-0.313l1.344-7.812-5.688-5.531c-0.187-0.203-0.391-0.469-0.391-0.75 0-0.469 0.484-0.656 0.875-0.719l7.844-1.141 3.516-7.109c0.141-0.297 0.406-0.641 0.766-0.641s0.625 0.344 0.766 0.641l3.516 7.109 7.844 1.141c0.375 0.063 0.875 0.25 0.875 0.719z"></path>'],
		["home", 32, '<path d="M32 19l-6-6v-9h-4v5l-6-6-16 16v1h4v10h10v-6h4v6h10v-10h4z"></path>'],
		["edit", 18, '<path d="M14.69 2.661c-1.894-1.379-3.242-1.349-3.754-1.266-0.144 0.023-0.265 0.106-0.35 0.223l-6.883 9.497c-0.277 0.382-0.437 0.836-0.462 1.307l-0.296 5.624c-0.021 0.405 0.382 0.698 0.76 0.553l5.256-2.010c0.443-0.17 0.828-0.465 1.106-0.849l6.88-9.494c0.089-0.123 0.125-0.273 0.1-0.423-0.084-0.526-0.487-1.802-2.357-3.162zM8.977 15.465l-2.043 0.789c-0.080 0.031-0.169 0.006-0.221-0.062-0.263-0.335-0.576-0.667-1.075-1.030-0.499-0.362-0.911-0.558-1.31-0.706-0.080-0.030-0.131-0.106-0.126-0.192l0.122-2.186 0.549-0.755c0 0 1.229-0.169 2.833 0.998 1.602 1.166 1.821 2.388 1.821 2.388l-0.55 0.756z"></path>'],
		["copy", 28, '<path d="M26.5 6c0.828 0 1.5 0.672 1.5 1.5v19c0 0.828-0.672 1.5-1.5 1.5h-15c-0.828 0-1.5-0.672-1.5-1.5v-4.5h-8.5c-0.828 0-1.5-0.672-1.5-1.5v-10.5c0-0.828 0.484-1.984 1.062-2.562l6.375-6.375c0.578-0.578 1.734-1.062 2.562-1.062h6.5c0.828 0 1.5 0.672 1.5 1.5v5.125c0.609-0.359 1.391-0.625 2-0.625h6.5zM18 9.328l-4.672 4.672h4.672v-4.672zM8 3.328l-4.672 4.672h4.672v-4.672zM11.062 13.438l4.937-4.937v-6.5h-6v6.5c0 0.828-0.672 1.5-1.5 1.5h-6.5v10h8v-4c0-0.828 0.484-1.984 1.062-2.562zM26 26v-18h-6v6.5c0 0.828-0.672 1.5-1.5 1.5h-6.5v10h14z"></path>'],
		["line", 900, '<path d="M572.953 745.082v-466.164c50.286-23.162 85.333-73.631 85.333-132.632 0-80.823-65.463-146.286-146.286-146.286s-146.286 65.463-146.286 146.286c0 58.94 35.108 109.47 85.333 132.632v466.225c-50.286 23.101-85.333 73.631-85.333 132.571 0 80.823 65.463 146.286 146.286 146.286s146.286-65.524 146.286-146.286c0-58.94-35.048-109.47-85.333-132.632zM512.001 61.867c46.568 0 84.358 37.73 84.358 84.419 0 46.568-37.79 84.419-84.358 84.419-46.629 0-84.358-37.851-84.358-84.419 0-46.689 37.73-84.419 84.358-84.419zM512.001 962.133c-46.689 0-84.419-37.851-84.419-84.419 0-46.689 37.73-84.419 84.419-84.419 46.568 0 84.358 37.73 84.358 84.419 0 46.568-37.79 84.419-84.358 84.419z"></path>'],
		["musician", 900, '<path d="M512 704c88.366 0 160-71.634 160-160v-384c0-88.366-71.634-160-160-160s-160 71.634-160 160v384c0 88.366 71.636 160 160 160zM736 448v96c0 123.71-100.29 224-224 224-123.712 0-224-100.29-224-224v-96h-64v96c0 148.238 112.004 270.3 256 286.22v129.78h-128v64h320v-64h-128v-129.78c143.994-15.92 256-137.982 256-286.22v-96h-64z"></path>'],
		["database", 34, '<path d="M16 0c-8.837 0-16 2.239-16 5v4c0 2.761 7.163 5 16 5s16-2.239 16-5v-4c0-2.761-7.163-5-16-5z"></path><path d="M16 17c-8.837 0-16-2.239-16-5v6c0 2.761 7.163 5 16 5s16-2.239 16-5v-6c0 2.761-7.163 5-16 5z"></path><path d="M16 26c-8.837 0-16-2.239-16-5v6c0 2.761 7.163 5 16 5s16-2.239 16-5v-6c0 2.761-7.163 5-16 5z"></path>'],
		['random', 20, '<path d="M15.093 6.694h0.92v2.862l3.987-4.024-3.988-4.025v2.387h-0.92c-3.694 0-5.776 2.738-7.614 5.152-1.652 2.172-3.080 4.049-5.386 4.049h-2.092v2.799h2.093c3.694 0 5.776-2.736 7.614-5.152 1.652-2.173 3.080-4.048 5.386-4.048zM5.41 8.458c0.158-0.203 0.316-0.412 0.477-0.623 0.39-0.514 0.804-1.055 1.252-1.596-1.322-1.234-2.915-2.144-5.046-2.144h-2.093v2.799h2.093c1.327 0 2.362 0.623 3.317 1.564zM16.012 13.294h-0.92c-1.407 0-2.487-0.701-3.491-1.738-0.1 0.131-0.201 0.264-0.303 0.397-0.441 0.58-0.915 1.201-1.439 1.818 1.356 1.324 3 2.324 5.232 2.324h0.92v2.398l3.989-4.025-3.988-4.025v2.851z"></path>'],
		['facebook', 32, '<path d="M30.235 0h-28.469c-0.975 0-1.765 0.791-1.765 1.765v28.469c0 0.976 0.791 1.765 1.765 1.765h15.325v-12.392h-4.172v-4.828h4.172v-3.567c0-4.132 2.525-6.38 6.212-6.38 1.767 0 3.285 0.129 3.728 0.188v4.32h-2.561c-2 0-2.389 0.961-2.389 2.361v3.081h4.779l-0.62 4.84h-4.159v12.376h8.153c0.977 0 1.767-0.789 1.767-1.765v-28.469c0-0.975-0.789-1.765-1.765-1.765z"></path>'],
		['youtube', 32, '<path d="M31.327 8.273c-0.386-1.353-1.431-2.398-2.756-2.777l-0.028-0.007c-2.493-0.668-12.528-0.668-12.528-0.668s-10.009-0.013-12.528 0.668c-1.353 0.386-2.398 1.431-2.777 2.756l-0.007 0.028c-0.443 2.281-0.696 4.903-0.696 7.585 0 0.054 0 0.109 0 0.163l-0-0.008c-0 0.037-0 0.082-0 0.126 0 2.682 0.253 5.304 0.737 7.845l-0.041-0.26c0.386 1.353 1.431 2.398 2.756 2.777l0.028 0.007c2.491 0.669 12.528 0.669 12.528 0.669s10.008 0 12.528-0.669c1.353-0.386 2.398-1.431 2.777-2.756l0.007-0.028c0.425-2.233 0.668-4.803 0.668-7.429 0-0.099-0-0.198-0.001-0.297l0 0.015c0.001-0.092 0.001-0.201 0.001-0.31 0-2.626-0.243-5.196-0.708-7.687l0.040 0.258zM12.812 20.801v-9.591l8.352 4.803z"></path>'],
		['twitter', 32, '<path d="M31.939 6.092c-1.18 0.519-2.44 0.872-3.767 1.033 1.352-0.815 2.392-2.099 2.884-3.631-1.268 0.74-2.673 1.279-4.169 1.579-1.195-1.279-2.897-2.079-4.788-2.079-3.623 0-6.56 2.937-6.56 6.556 0 0.52 0.060 1.020 0.169 1.499-5.453-0.257-10.287-2.876-13.521-6.835-0.569 0.963-0.888 2.081-0.888 3.3 0 2.28 1.16 4.284 2.917 5.461-1.076-0.035-2.088-0.331-2.971-0.821v0.081c0 3.18 2.257 5.832 5.261 6.436-0.551 0.148-1.132 0.228-1.728 0.228-0.419 0-0.82-0.040-1.221-0.115 0.841 2.604 3.26 4.503 6.139 4.556-2.24 1.759-5.079 2.807-8.136 2.807-0.52 0-1.039-0.031-1.56-0.089 2.919 1.859 6.357 2.945 10.076 2.945 12.072 0 18.665-9.995 18.665-18.648 0-0.279 0-0.56-0.020-0.84 1.281-0.919 2.4-2.080 3.28-3.397l-0.063-0.027z"></path>'],
		['patreon', 32, '<path d="M20.515 0.699c-6.352 0-11.52 5.168-11.52 11.52 0 6.333 5.168 11.484 11.52 11.484 6.333 0 11.485-5.152 11.485-11.484 0-6.352-5.152-11.52-11.485-11.52zM0.004 31.383h5.627v-30.684h-5.627z"></path>'],
		['discord', 32, '<path d="M26.963 0c1.875 0 3.387 1.516 3.476 3.3v28.7l-3.569-3.031-1.96-1.784-2.139-1.864 0.893 2.94h-18.717c-1.869 0-3.387-1.42-3.387-3.301v-21.653c0-1.784 1.52-3.303 3.393-3.303l22.009-0.004zM18.805 7.577h-0.040l-0.269 0.267c2.764 0.8 4.101 2.049 4.101 2.049-1.781-0.891-3.387-1.336-4.992-1.516-1.16-0.18-2.32-0.085-3.3 0h-0.267c-0.627 0-1.96 0.267-3.747 0.98-0.623 0.271-0.98 0.448-0.98 0.448s1.336-1.336 4.28-2.049l-0.18-0.18s-2.229-0.085-4.636 1.693c0 0-2.407 4.192-2.407 9.36 0 0 1.333 2.32 4.991 2.408 0 0 0.533-0.711 1.073-1.336-2.053-0.624-2.853-1.872-2.853-1.872s0.179 0.088 0.447 0.267h0.080c0.040 0 0.059 0.020 0.080 0.040v0.008c0.021 0.021 0.040 0.040 0.080 0.040 0.44 0.181 0.88 0.36 1.24 0.533 0.621 0.269 1.42 0.537 2.4 0.715 1.24 0.18 2.661 0.267 4.28 0 0.8-0.18 1.6-0.356 2.4-0.713 0.52-0.267 1.16-0.533 1.863-0.983 0 0-0.8 1.248-2.94 1.872 0.44 0.621 1.060 1.333 1.060 1.333 3.659-0.080 5.080-2.4 5.16-2.301 0-5.16-2.42-9.36-2.42-9.36-2.18-1.619-4.22-1.68-4.58-1.68l0.075-0.027zM19.029 13.461c0.937 0 1.693 0.8 1.693 1.78 0 0.987-0.76 1.787-1.693 1.787s-1.693-0.8-1.693-1.779c0.003-0.987 0.764-1.784 1.693-1.784zM12.972 13.461c0.933 0 1.688 0.8 1.688 1.78 0 0.987-0.76 1.787-1.693 1.787s-1.693-0.8-1.693-1.779c0-0.987 0.76-1.784 1.693-1.784z"></path>'],
		['register', 32, '<path d="M29 4h-9c0-2.209-1.791-4-4-4s-4 1.791-4 4h-9c-0.552 0-1 0.448-1 1v26c0 0.552 0.448 1 1 1h26c0.552 0 1-0.448 1-1v-26c0-0.552-0.448-1-1-1zM16 2c1.105 0 2 0.895 2 2s-0.895 2-2 2c-1.105 0-2-0.895-2-2s0.895-2 2-2zM28 30h-24v-24h4v3c0 0.552 0.448 1 1 1h14c0.552 0 1-0.448 1-1v-3h4v24z"></path><path d="M14 26.828l-6.414-7.414 1.828-1.828 4.586 3.586 8.586-7.586 1.829 1.828z"></path>'],
		['sign-in', 32, '<path d="M12 16h-10v-4h10v-4l6 6-6 6zM32 0v26l-12 6v-6h-12v-8h2v6h10v-18l8-4h-18v8h-2v-10z"></path>'],
		['plus', 20, '<path d="M11 9h4v2h-4v4h-2v-4h-4v-2h4v-4h2v4zM10 20c-5.523 0-10-4.477-10-10s4.477-10 10-10v0c5.523 0 10 4.477 10 10s-4.477 10-10 10v0zM10 18c4.418 0 8-3.582 8-8s-3.582-8-8-8v0c-4.418 0-8 3.582-8 8s3.582 8 8 8v0z"></path>'],
		['user-crown', 32, '<path stroke="null" fill="black" id="svg_1" d="m31.69646,11.18888c0,-0.418 -0.14098,-0.81101 -0.37694,-1.04858c-0.23597,-0.24249 -0.53919,-0.301 -0.81221,-0.15901l-6.70758,3.48516l-7.11947,-12.24146c-0.3378,-0.58361 -1.02272,-0.58361 -1.36052,0l-7.11947,12.24146l-6.70758,-3.48516c-0.27245,-0.14226 -0.57434,-0.08377 -0.81259,0.15901c-0.23653,0.24059 -0.37656,0.6303 -0.37656,1.04858l0,15.38855c0,4.94514 14.09284,5.06873 15.69864,5.06873c1.6058,0 15.69409,-0.12359 15.69409,-4.44173l0.00019,-16.01555z"/>'],
		['user-heart', 22, '<path d="M12 21.328l-1.453-1.313q-2.484-2.25-3.609-3.328t-2.508-2.672-1.898-2.883-0.516-2.648q0-2.297 1.57-3.891t3.914-1.594q2.719 0 4.5 2.109 1.781-2.109 4.5-2.109 2.344 0 3.914 1.594t1.57 3.891q0 1.828-1.219 3.797t-2.648 3.422-4.664 4.359z"></path>'],
		['user-flower', 28, '<path d="M22.51,9.697c-0.386,0-0.866,0.087-1.442,0.26c-1.636,0.606-2.741,1.603-3.317,2.986 c-0.097-0.172-0.338-0.345-0.721-0.52c1.537-0.864,2.211-2.249,2.02-4.154c-0.097-0.951-0.482-1.772-1.154-2.466 C16.835,4.679,15.537,4.115,14,4.115c-1.25,0-2.357,0.39-3.317,1.168C9.624,6.149,9.046,7.146,8.952,8.269 c-0.193,1.732,0.48,3.115,2.02,4.154c-0.194,0.26-0.433,0.434-0.721,0.52c-0.577-1.471-1.684-2.466-3.317-2.986 c-0.482-0.172-1.009-0.26-1.586-0.26c-1.059,0-2.02,0.305-2.885,0.909c-0.865,0.694-1.492,1.473-1.875,2.337 c-0.194,0.606-0.288,1.083-0.288,1.428c0,0.953,0.335,1.817,1.009,2.596c0.769,0.779,1.634,1.343,2.596,1.688 c0.48,0.174,0.96,0.26,1.442,0.26c1.154,0,2.164-0.302,3.029-0.909c0.095,0.259,0.191,0.519,0.289,0.779 c-1.636,0-2.981,0.649-4.039,1.947c-0.771,0.954-1.106,1.992-1.01,3.116c0.095,1.385,0.769,2.466,2.02,3.245 C6.5,27.698,7.51,28,8.664,28c1.73,0,3.124-0.649,4.183-1.947c0.769-0.692,1.104-1.558,1.01-2.596c0-0.432-0.097-0.909-0.289-1.428 c0.191,0,0.336,0,0.433,0c0.191,0.087,0.336,0.087,0.433,0c-0.194,0.519-0.289,0.996-0.289,1.428c0,1.039,0.289,1.862,0.866,2.466 C16.355,27.308,17.797,28,19.336,28c1.057,0,1.97-0.26,2.74-0.779c1.537-1.124,2.308-2.422,2.308-3.895 c0-0.951-0.338-1.817-1.01-2.596c-0.962-1.298-2.308-1.947-4.039-1.947c0.192-0.26,0.289-0.52,0.289-0.779 c0.96,0.606,1.922,0.909,2.885,0.909c0.577,0,1.104-0.085,1.586-0.26c1.057-0.345,1.922-0.909,2.596-1.688 c0.671-0.692,1.009-1.558,1.009-2.596c0-0.345-0.097-0.821-0.288-1.428c-0.385-0.951-1.01-1.73-1.875-2.337 C24.576,10.001,23.566,9.697,22.51,9.697L22.51,9.697z"></path>'],
		['user-star', 22, '<path d="M12 17.25l-6.188 3.75 1.641-7.031-5.438-4.734 7.172-0.609 2.813-6.609 2.813 6.609 7.172 0.609-5.438 4.734 1.641 7.031z"></path>'],
	];
	
	ob_start();
	?>
		<svg class="symbol__container" height="0" width="0">
			<defs>
				<?php
					foreach($paths as $path) {
						?>
							<clipPath id="symbol__<?php echo $path[0]; ?>" clipPathUnits="objectBoundingBox">
								<?php
									divide_svg($path[1], $path[2]);
								?>
							</clipPath>
						<?php
					}
				?>
			</defs>
		</svg>
		<style>
			.artist::before {
				clip-path: url(#symbol__artist);
				-webkit-clip-path: url(#symbol__artist);
				will-change: transform;
			}
			.musician::before {
				clip-path: url(#symbol__musician);
				-webkit-clip-path: url(#symbol__musician);
				will-change: transform;
			}
			.company::before {
				clip-path: url(#symbol__company);
				-webkit-clip-path: url(#symbol__company);
				will-change: transform;
			}
			.user::before, .text a[href^="/user/"]:not([href$="/user/"])::before {
				clip-path: url(#symbol__user);
				-webkit-clip-path: url(#symbol__user);
				will-change: transform;
			}
			.loading::before {
				clip-path: url(#symbol__loading);
				-webkit-clip-path: url(#symbol__loading);
				will-change: transform;
			}
			<?php
				foreach($paths as $path) {
					?>
						.symbol__<?php echo $path[0]; ?>::before {
							-moz-clip-path: url(#symbol__<?php echo $path[0]; ?>);
							-webkit-clip-path: url(#symbol__<?php echo $path[0]; ?>);
							clip-path: url(#symbol__<?php echo $path[0]; ?>);
							will-change: transform;
						}
					<?php
				}
			?>
			[class*="symbol__"]::before, .artist::before, .company::before, .loading::before, .user::before {
				visibility: visible;
			}
		</style>
	<?php
	
	$output = str_replace(["\t", "\r", "\n"], " ", ob_get_clean());
	$output = preg_replace('/'.'\s+'.'/', ' ', $output);
	echo $output;
	unset($paths, $output);
?>