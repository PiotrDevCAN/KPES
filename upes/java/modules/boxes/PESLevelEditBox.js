/**
 *
 */

import changePesLevels from '../../modules/changePesLevels.js';
import box from "./box.js";

class PESLevelEditBox extends box {

    constructor(parent) {
        console.log('+++ Function +++ PESLevelEditBox.constructor');

        super(parent);
        this.listenForEditPesLevelFormSubmit();
        this.listenForEditPesLevel();
        this.listenForEditPesLevelModalShown();

        console.log('--- Function --- PESLevelEditBox.constructor');
    }

    listenForEditPesLevelFormSubmit() {
        var $this = this;
        $(document).on('submit', '#editPesLevelForm', function (e) {
            e.preventDefault();

            var submitBtn = $('#savePesLevel').addClass('spinning').attr('disabled', true);;
            var url = 'ajax/savePesLevelChanges.php';

            var disabledFields = $(':disabled');
            $(disabledFields).removeAttr('disabled');
            var formData = $("#editPesLevelForm").serialize();
            $(disabledFields).attr('disabled', true);

            $.ajax({
                type: 'post',
                url: url,
                data: formData,
                context: document.body,
                success: function (response) {
                    var responseObj = JSON.parse(response);

                    console.log(responseObj.success);
                    if (responseObj.success) {
                        $('.spinning').removeClass('spinning').attr('disabled', false);
                        $('#editPesLevelForm').trigger("reset");
                        $('#modalEditPesLevel').modal('hide');
                        $this.table.ajax.reload();
                    } else {
                        $('.spinning').removeClass('spinning').attr('disabled', false);
                        $('#editPesLevelForm').trigger("reset");
                        $('#modalEditPesLevel').modal('hide');
                        $('#errorMessageBody').html(responseObj.Messages);
                        $('#errorMessageBody').addClass('bg-danger');
                        $('#errorMessageModal').modal('show');
                    }
                },
                always: function () {
                    console.log('--- saved resource request ---');
                }
            });
        });
    }

    listenForEditPesLevel() {
        $(document).on('click', '.editPesLevel', function (e) {
            $(this).addClass('spinning').attr('disabled', true);
            $('#plEMAIL_ADDRESS').val($(this).data('plemailaddress'));
            $('#plUPES_REF').val($(this).data('plupesref'));
            $('#plACCOUNT').val($(this).data('placcount'));
            $('#plACCOUNT_ID').val($(this).data('placcountid'));
            $('#plCOUNTRY').val($(this).data('plcountry'));
            $('#plFULL_NAME').val($(this).data('plfullname'));
            $('#plPES_LEVEL').val($(this).data('plpeslevelref'));
            $('#plCONTRACT').val($(this).data('plcontract'));
            $('#plCONTRACT_ID').val($(this).data('plcontractid'));
            $('#plPES_REQUESTOR').val($(this).data('plrequestor'));
            $('#plPES_CLEARED_DATE').val($(this).data('plcleareddate'));
            $('#plPES_RECHECK_DATE').val($(this).data('plrecheckdate'));
            $('#modalEditPesLevel').modal('show');
        });
    }

    listenForEditPesLevelModalShown() {
        $(document).on('shown.bs.modal', '#modalEditPesLevel', function (e) {

            try {
                $('#plPES_CLEARED_DATE').datepicker('destroy');
            }
            catch (error) {
                console.log(error);
            }

            try {
                $('#plPES_RECHECK_DATE').datepicker('destroy');
            }
            catch (error) {
                console.log(error);
            }

            $('#plPES_CLEARED_DATE').datepicker({
                dateFormat: 'dd-mm-yy',
                altField: '#plPES_CLEARED_DATE_db2',
                altFormat: 'yy-mm-dd',
            });

            $('#plPES_RECHECK_DATE').datepicker({
                dateFormat: 'dd-mm-yy',
                altField: '#plPES_RECHECK_DATE_db2',
                altFormat: 'yy-mm-dd',
            });

            $('#plCOUNTRY_OF_RESIDENCE').select2({
                placeholder: 'Select Country of Residence',
                width: '100%',
                data: countryOfResidence,
                dataType: 'json'
            });

            $('#PES_LEVEL').select2({
                width: '100%'
            });

            var country = $('#plCOUNTRY').val();
            $('#plCOUNTRY_OF_RESIDENCE').val(country).trigger('change');

            var accountId = $('#plACCOUNT_ID').val();
            var pesLevel = $('#plPES_LEVEL').val();
            $("#PES_LEVEL").select2("destroy");
            $("#PES_LEVEL").html("<option><option>");
            changePesLevels(pesLevelByAccount[accountId]);

            console.log(pesLevel);
            $('#PES_LEVEL').val(pesLevel).trigger('change');
            $('.spinning').removeClass('spinning').attr('disabled', false);
        });
    }
}

export { PESLevelEditBox as default };