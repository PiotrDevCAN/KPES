<?php

set_time_limit(0);
ob_start();
?>

<style>

#drop-area {
	border: 2px dashed #ccc;
	border-radius: 20px;
	width: 480px;
	font-family: sans-serif;
	margin: 100px auto;
	padding: 20px;
}

#progess-area {
	border: 2px #ccc;
	border-radius: 20px;
	width: 480px;
	font-family: sans-serif;
	margin: 100px auto;
	padding: 20px;
	background-color: #eee;
}

#drop-area.highlight {
	border-color: purple;
}
p {
	margin-top: 0;
}
.my-form {
	margin-bottom: 10px;
}
.button {
	display: inline-block;
	padding: 10px;
	background: #ccc;
	cursor: pointer;
	border-radius: 5px;
	border: 1px solid #ccc;
}
.button:hover {
	background: #ddd;
}
#fileElem {
	display: none;
}

</style>

<div class='container'>
<div class='row'>
<div class='col-sm-offset-2 col-sm-8'>
<h2>Employees Data Upload</h2>

<div id='fileType'>
	<button id='fileEmpData' type="button" class="btn btn-primary filetype" value='EMP_DATA'>Upload Employees Data</button>
</div>

<div id="drop-area">
	<form class="my-form">
		<p>To upload the Employees Data CSV files, simply drag and drop them onto the dashed region</p>
    	<input type="file" id="fileElem" multiple accept="image/*" onchange="handleFiles(this.files)">
  	</form>
</div>
</div>
</div>

<div class='row'>
<div class='col-sm-offset-2 col-sm-8' id="progress-area" >
</div>

</div>
</div>

<script>

$(document).on('click','.filetype',function(){
	console.log('click filetype');
	var filetype = $('.filetype > checked').val();
	$('.filetype').attr('disabled',true);
	$('#drop-area').show();
	$('#progress-area').show();
	$('#fileElem').data('filetype',filetype);
});

let dropArea = document.getElementById('drop-area');
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
	dropArea.addEventListener(eventName, preventDefaults, false)
})

function preventDefaults (e) {
	e.preventDefault()
	e.stopPropagation()
}

['dragenter', 'dragover'].forEach(eventName => {
	  dropArea.addEventListener(eventName, highlight, false)
	})

	;['dragleave', 'drop'].forEach(eventName => {
	  dropArea.addEventListener(eventName, unhighlight, false)
	})

	function highlight(e) {
	  dropArea.classList.add('highlight')
	}

	function unhighlight(e) {
	  dropArea.classList.remove('highlight')
	}

	dropArea.addEventListener('drop', handleDrop, false)

	function handleDrop(e) {
	  let dt = e.dataTransfer
	  let files = dt.files

	  handleFiles(files)
	}

	function handleFiles(files) {
		([...files]).forEach(uploadFile)
	}

	function uploadFile(file) {
		var url = 'ajax/upload.php'
		var xhr = new XMLHttpRequest()
		var formData = new FormData()
		xhr.open('POST', url, true)

		xhr.addEventListener('readystatechange', function(e) {
		if (xhr.readyState == 4 && xhr.status == 200) {
			// Done. Inform the user
			var responseText = xhr.responseText;
			var progress = $('#progress-area').html();
			$('#progress-area').html(responseText + "<br/>" + progress);
			$('#readIntoDB2').attr('disabled',false);
			$('#readIntoDB2').data('filename',file.name);
		}
		else if (xhr.readyState == 4 && xhr.status != 200) {
			// Error. Inform the user
			var responseText = xhr.responseText;
			responseText += "<br/>Error has occured, inform support";
			$('#progress-area').html(responseText);

			console.log(xhr);
		}
		})

		formData.append('file', file)
		xhr.send(formData)
	}

</script>