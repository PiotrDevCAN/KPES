<?php


use itdq\FormClass;
use itdq\Trace;
use upes\AccountTable;
use upes\AllTables;
use upes\AccountRecord;
use upes\PesLevelTable;
use upes\ContractTable;
use itdq\Loader;
use itdq\JavaScript;

Trace::pageOpening($_SERVER['PHP_SELF']);

?>
<div class='container-fluid	'>

<div class='col-sm-8 col-sm-offset-2'>
<h2>PES Status Change Log</h2>
<hr>
<table id='pesStatusChangeTable' class='table table-responsive table-striped' >
<thead>
<tr><th>CNUM</th><th>Email</th><th>Account</th><th >Status</th><th>Date</th><th>Updater</th><th >Updated</th></tr>
</thead>
<tbody></tbody>
<tfoot>
<tr><th>CNUM</th><th>Email</th><th>Account</th><th >Status</th><th>Date</th><th>Updater</th><th >Updated</th></tr>
</tfoot>
</table>
</div>
</div>

<script>

var pesStatusChangeTable;

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



$(document).ready(function(){

    // Setup - add a text input to each footer cell
    $('#pesStatusChangeTable tfoot th').each( function () {
        var title = $(this).text();
        var titleCondensed = title.replace(' ','');
        $(this).html( '<input type="text" id="footer'+ titleCondensed + '" placeholder="Search '+title+'" size="5" />' );
    } );



	
	pesStatusChangeTable = $('#pesStatusChangeTable').DataTable({
    	ajax: {
            url: 'ajax/populatePesStatusChangeTable.php',
        }	,
    	autoWidth: true,
    	processing: true,
    	responsive: true,
    	dom: 'Blfrtip',
        buttons: [
            'colvis',
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
        }),
        $.extend( true, {}, buttonCommon, {
            extend: 'print',
            exportOptions: {
                orthogonal: 'sort',
                stripHtml: true,
                stripNewLines:false
            }
        })
        ],
       columns:  [{ data: "CNUM"
                  },{
                    data: "EMAIL_ADDRESS", 
                  },{
                    data: "ACCOUNT", 
                  },{
                    data: "PES_STATUS", 
                  },{
                    data: "PES_CLEARED_DATE", 
                  },{
                    data: "UPDATER"
                  },{
                    data: "UPDATED"
                  }] ,
	});

    // Apply the search
    pesStatusChangeTable.columns().every( function () {
        var that = this;
        $( 'input', this.footer() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );

	

});
</script>

<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);
?>
