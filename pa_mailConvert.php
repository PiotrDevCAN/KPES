<?php
use itdq\Trace;
use itdq\FormClass;

Trace::pageOpening($_SERVER['PHP_SELF']);
?>

<style>

div.editable {
    width: 10   0%;
    height: 100px;
    border: 1px solid #ccc;
    padding: 5px;
    resize:vertical;
    overflow:auto;
}

</style>




<div class="container">
<div class='row'>
<div class='col-sm-6'>
<h1>Addresses to Convert</h1>
	<form id='contactsForm' class="form-horizontal" method='post'>
        <hr>
    	<div class="form-group required" >
        	<label for=CONTACTS class='col-sm-3 control-label ceta-label-left' data-toggle='tooltip' data-placement='top' title='Contacts'>Contacts</label>
        	<div class='col-sm-9'>
	        	<div id='CONTACTS' contenteditable="true" class='editable'></div>
				<!-- <textarea id='CONTACTS' name='CONTACTS' class='form-control' ></textarea> -->
            </div>
        </div>
        <hr>
        <div class='col-sm-offset-3 col-sm-9'>
  		<input type='hidden' id='emailsToSave'  />
  		<input type='hidden' id='emailsSaved'  />
        <?php
        $allButtons = array();
        $form = new FormClass();
        $submitButton = $form->formButton('submit','Convert','convertMail',null,'Convert');
        $resetButton  = $form->formButton('reset','Reset','resetContacts  Form',null,'Reset','btn-warning');
   		$allButtons[] = $submitButton;
   		$allButtons[] = $resetButton;
   		$form->formBlueButtons($allButtons);
  		?>
  		</div>        
	</form>
	</div>
	</div>
<div class='row'>
<div class='col-sm-6'>
<hr>
<h1>Addresses</h1>
<table id='Addresses' class='table table-stripped table-responsive'>
<thead>
<tr><th>Email</th><th>Notes Id</th></tr>
</thead>
<tfoot>
<tr><th>Email</th><th>Notes Id</th></tr>
</tfoot>
</table>
</div>

</div>
</div>
<script type="text/javascript">
<!--

//-->

var Addresses = 'global';

var buttonCommon = {
        exportOptions: {
            format: {
                body: function ( data, row, column, node ) {
                 //   return data ?  data.replace( /<br\s*\/?>/ig, "\n") : data ;
                 return data ? data.replace( /<br\s*\/?>/ig, "\n").replace(/(&nbsp;|<([^>]+)>)/ig, "") : data ;
                 //    data.replace( /[$,.]/g, '' ) : data.replace(/(&nbsp;|<([^>]+)>)/ig, "");

                }
            }
        }
    };


function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function validateNotesId(notesid) {
    var re = /^([a-z0-9 ]+\/[a-z]*(\/ibm|\/contr\/ibm))$/;
    return re.test(String(notesid).toLowerCase());
}

$(document).ready(function(){
	Addresses = $('#Addresses').DataTable({
        order: [[ 0, "asc" ],[1,"asc"]],
        processing: true,
        responsive: true,
        dom: 'Blfrtip',
        buttons: [
            $.extend( true, {}, buttonCommon, {
                extend: 'excelHtml5',
                exportOptions: {
                    orthogonal: 'sort',
                    stripHtml: true,
                    stripNewLines:false
                },
                 customize: function( xlsx ) {
                     var sheet = xlsx.xl.worksheets['sheet1.xml'];

                 }
        }),
        $.extend( true, {}, buttonCommon, {
            extend: 'csvHtml5',
            exportOptions: {
                orthogonal: 'sort',
                stripHtml: true,
                stripNewLines:false
            }
        })
        ],
    });
});

$(document).on('click','#convertMail', function(e){
	e.preventDefault();
	var validatedEmail = [];
    var emailAddresses = $('#CONTACTS').html();

    var emailAddressesH = $('#CONTACTS').html();
    var emailAddressesT = $('#CONTACTS').text();
    var emailArray = emailAddresses.replace(/<\/div><div>/gm,'\n').replace(/\"/gm,' ').replace(/<[^>]*>?/gm,'').split(/[,;\n]/g);
	emailArray = emailArray.filter(Boolean);
	console.log(emailArray);

    var arrayLength = emailArray.length;
    $('#emailsToSave').val(arrayLength);
    $('#emailsSaved').val(0);
    for (var i = 0; i < arrayLength; i++) {
        console.log('loop starts');
        console.log(emailArray[i].trim());
        //Do something
        console.log(validateNotesId(emailArray[i].trim()));
        if(validateNotesId(emailArray[i].trim())){
            validatedEmail.push(emailArray[i].trim());
    	} else {
			var email = emailArray[i].replace(/[\(\)]/g,'').trim();
			console.log(email);
    		var regex = new RegExp(email);
    		$('#CONTACTS').html($('#CONTACTS').html().replace(regex,"<span style='color:red;text-decoration:line-through;'>" + email + "</span>"));
        }
	}
	console.log(validatedEmail);
  	$.ajax({
		url: "ajax/convertMail.php",
		type: 'POST',
		data : { emailaddresses: validatedEmail },
		success: function(result){
	  		resultObj = JSON.parse(result);
	  		var table   = $('#Addresses').DataTable();
	  		table.clear();


	  		for(var notesid in resultObj.converted){
		  		var email = resultObj.converted[notesid]
		  		table.row.add( [
		             email,
		             notesid,
		         ] ).draw( false );
	  		}
        }
	});
});
</script>
<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);