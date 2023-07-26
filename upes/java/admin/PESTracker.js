/**
 *
 */
import PESPriorityBox from '../modules/boxes/PESPriorityBox.js';
import stageValueChangeBox from '../modules/boxes/stageValueChangeBox.js';
import PESStatusBox from '../modules/boxes/PESStatusBox.js';
import chaserBox from '../modules/boxes/chaserBox.js';
import initiatePES from '../modules/boxes/initiatePESRequestBox.js';
import confirmPES from '../modules/boxes/confirmPESEmailBox.js';
import amendPES from '../modules/boxes/amendPESStatusBox.js';
import cancelPES from '../modules/boxes/cancelPESRequestBox.js';
import commentBox from '../modules/boxes/commentBox.js';
import extractTrackerBox from '../modules/boxes/extractTrackerBox.js';
import togglePESTrackerStatusDetailsBox from '../modules/boxes/togglePesTrackerStatusDetailsBox.js';

$.expr[":"].contains = $.expr.createPseudo(function (arg) {
    return function (elem) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});

class PESTracker {

    table;

    constructor() {
        console.log('+++ Function +++ PESTracker.constructor');

        this.populatePesTracker();
        this.listenForBtnRecordSelection(); // PAGE filtering keep where it is now
        this.listenForFilterPriority(); // PAGE filtering keep where it is now
        this.listenForFilterProcess(); // PAGE filtering keep where it is now
        this.listenForKeyUpPesTrackerTableSearch();

        // console.log($('button[name=pesRecordFilter]:checked'));
        // console.log($('button[name=pesRecordFilter]:checked').val());
        // console.log($('button[name=pesRecordFilter]:checked').data('pesrecords'));

        // console.log($('.btnRecordSelection'));
        // console.log($('.btnRecordSelection:checked'));

        console.log('--- Function --- PESTracker.constructor');
    }

    searchTable() {
        var filter = $('#pesTrackerTableSearch').val();
        var table = this.table;

        if (filter.length > 3) {
            table.search(filter).draw();
        } else {
            table.search('').draw();
        }
    }

    populatePesTracker() {
        if ($('#pesTrackerTable').length != 0) {
            this.table = $('#pesTrackerTable').DataTable({
                processing: true,
                serverSide: true,
                scrollColapse: false,
                searchDelay: 1000,
                ajax: {
                    url: 'ajax/populatePesTrackerTable.php',
                    dataType: 'json',
                    type: 'POST',
                    data: function (d) {
                        d.records = $('.btnRecordSelection.active').data('pesrecords');
                    },
                    dataSrc: function (json) {
                        // console.log('dataSrc');
                        // console.log(json);
                        // console.log($('#pesTrackerTable_processing').is(":visible"));

                        //Make your callback here.
                        if (json.error.length != 0) {
                            $('#errorMessageBody').html(json.error);
                            $('#errorMessageModal').modal('show');
                        }
                        // console.log(json.data);
                        return json.data;
                    },
                    beforeSend: function (jqXHR, settings) {
                        // console.log('before send');
                        // console.log($('.dataTables_processing'));
                        // console.log($('#pesTrackerTable_processing').is(":visible"));

                        $.each(xhrPool, function (idx, jqXHR) {
                            jqXHR.abort();  // basically, cancel any existing request, so this one is the only one running
                            xhrPool.splice(idx, 1);
                        });
                        xhrPool.push(jqXHR);
                    },
                },
                language: {
                    searchPlaceholder: "",
                    emptyTable: "No records found",
                    processing: "Processing<i class='fas fa-spinner fa-spin '></i>"
                },
                autoWidth: true,
                responsive: false,
                // dom: 'Blfrtip',
                dom: 'Blrtip',
                paging: true,
                pagingType: 'full_numbers',
                pageLength: 50,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                buttons: [
                    // 'csvHtml5',
                    // 'excelHtml5',
                    // 'print'
                ],
                columns:
                    [{
                        "searchable": true, data: "PERSON_DETAILS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "ACCOUNT_DETAILS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "CONTRACT_DETAILS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "REQUESTOR", render: { _: "display", sort: "sort" }
                    }, {
                        data: "CONSENT", render: { _: "display", sort: "sort" }
                    }, {
                        data: "RIGHT_TO_WORK", render: { _: "display", sort: "sort" }
                    }, {
                        data: "PROOF_OF_ID", render: { _: "display", sort: "sort" }
                    }, {
                        data: "PROOF_OF_RESIDENCY", render: { _: "display", sort: "sort" }
                    }, {
                        data: "CREDIT_CHECK", render: { _: "display", sort: "sort" }
                    }, {
                        data: "FINANCIAL_SANCTIONS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "CRIMINAL_RECORDS_CHECK", render: { _: "display", sort: "sort" }
                    }, {
                        data: "PROOF_OF_ACTIVITY", render: { _: "display", sort: "sort" }
                    }, {
                        data: "QUALIFICATIONS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "CIFAS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "DIRECTORS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "MEDIA", render: { _: "display", sort: "sort" }
                    }, {
                        data: "MEMBERSHIP", render: { _: "display", sort: "sort" }
                    }, {
                        data: "NI_EVIDENCE", render: { _: "display", sort: "sort" }
                    }, {
                        data: "PROCESS_STATUS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "PES_STATUS", render: { _: "display", sort: "sort" }
                    }, {
                        data: "COMMENT", render: { _: "display", sort: "sort" }
                    }],
            });
        } else {
            alert('unable to initialize DataTable');
        }
    }

    listenForBtnRecordSelection() {
        var $this = this;
        $(document).on('click', '.btnRecordSelection', function () {
            $('.btnRecordSelection').removeClass('active');
            $(this).addClass('active');
            $this.table.ajax.reload();
        });
    }

    listenForFilterPriority() {
        $(document).on('click', '.btnSelectPriority', function () {
            var priority = $(this).data('pespriority');
            if (priority != 0) {
                $('tr').hide();
                $(".priorityDiv:contains('" + priority + "')").parents('tr').show();
                $('th').parent('tr').show();
            } else {
                $('tr').show();
            }
        });
    }

    listenForFilterProcess() {
        $(document).on('click', '.btnSelectProcess', function () {
            var pesprocess = $(this).data('pesprocess');
            $('tr').hide();
            $(".pesProcessStatusDisplay:contains('" + pesprocess + "')").parents('tr').show();
            $('th').parent('tr').show();
        });
    }

    listenForKeyUpPesTrackerTableSearch() {
        var $this = this;
        $(document).on('keyup', '#pesTrackerTableSearch', function (e) {
            $this.searchTable();
        });
    }
}

const PesTracker = new PESTracker();

const PesPriorityBox = new PESPriorityBox();
const StageValueChangeBox = new stageValueChangeBox();
const PesStatusBox = new PESStatusBox();
const ChaserBox = new chaserBox();
const InitiatePes = new initiatePES();
const ConfirmPes = new confirmPES();
const AmendPes = new amendPES();
const CancelPes = new cancelPES(PesTracker);
const CommentBox = new commentBox();
const ExtractTrackerBox = new extractTrackerBox();
const TogglePesTrackerStatusDetailsBox = new togglePESTrackerStatusDetailsBox();

export { PesTracker as default };