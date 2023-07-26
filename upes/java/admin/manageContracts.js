/**
 *
 */

class manageContracts {

	contractTable;

	constructor() {
		console.log('+++ Function +++ manageContracts.constructor');

		this.populateDataTable();
		this.listenForContractsFormSubmit();
		this.listenForDeleteContract();
		this.listenForEditContractName();
		this.listenForConfirmContractDelete();
		this.listenForKeyUpContract();

		$('#ACCOUNT_ID').select2({
			placeholder: 'Select Account',
			width: '100%'
		});

		console.log('--- Function --- manageContracts.constructor');
	}

	populateDataTable() {
		this.contractTable = $('#contractTable').DataTable({
			ajax: {
				url: 'ajax/populateContractsTable.php',
			},
			autoWidth: true,
			processing: true,
			responsive: true,
			dom: 'Blfrtip',
			buttons: [
				'csvHtml5',
				'excelHtml5',
				'print'
			],
			columns:
				[{
					data: "ACTION"
				}, {
					data: "CONTRACT_ID"
				}, {
					data: "ACCOUNT"
				}, {
					data: "CONTRACT"
				}]
		});
	}

	listenForContractsFormSubmit() {
		var $this = this;
		$(document).on('submit', '#contractsForm', function (e) {
			console.log(e);
			e.preventDefault();

			var submitBtn = $(e.target).find('input[name="Submit"]').addClass('spinning');
			var url = 'ajax/saveContractRecord.php';

			var disabledFields = $(':disabled');
			$(disabledFields).removeAttr('disabled');
			var formData = $("#contractsForm").serialize();
			$(disabledFields).attr('disabled', true);

			$.ajax({
				type: 'post',
				url: url,
				data: formData,
				context: document.body,
				success: function (response) {
					var responseObj = JSON.parse(response);
					if (responseObj.success) {
						$(submitBtn).removeClass('spinning').attr('disabled', false);
						$('#contractsForm').trigger("reset");
					} else {
						$(submitBtn).removeClass('spinning').attr('disabled', false);
						$('#contractsForm').trigger("reset");
						$('#errorMessageBody').html(responseObj.Messages);
						$('#errorMessageBody').addClass('bg-danger');
						$('#errorMessageModal').modal('show');
					}
					$('#CONTRACT').css("background-color", "white");
					$this.contractTable.ajax.reload();
				},
				always: function () {
					console.log('--- saved resource request ---');
				}
			});
		});
	}

	listenForDeleteContract() {
		$(document).on('click', '.deleteContract', function (e) {
			console.log(e);
			$('#confirmDeleteContractName').html($(e.target).data('contract'));
			$('#confirmDeleteContractId').val($(e.target).data('contractid'));
			$('#modalDeleteContractConfirm').modal('show');
		});
	}

	listenForEditContractName() {
		$(document).on('click', '.editContractName', function (e) {
			var button = $(e.target).parent('button').addClass('spinning');
			$('#ACCOUNT_ID').val($(e.target).data('accountid')).trigger('change');
			$('#CONTRACT').val($(e.target).data('contract'));
			$('#CONTRACT_ID').val($(e.target).data('contractid'));
			$('#mode').val('edit');
			$(button).removeClass('spinning');
		});
	}

	listenForConfirmContractDelete() {
		var $this = this;
		$(document).on('click', '.confirmContractDelete', function (e) {
			var contractid = $('#confirmDeleteContractId').val();
			console.log(contractid);

			var submitBtn = $(e.target).find('input[name="Submit"]').addClass('spinning');
			var url = 'ajax/deleteContractRecord.php';

			$.ajax({
				type: 'post',
				url: url,
				data: {
					contractid: contractid
				},
				context: document.body,
				success: function (response) {
					var responseObj = JSON.parse(response);
					if (!responseObj.success) {
						$('#contractsForm').trigger("reset");
						$('#errorMessageBody').html(responseObj.Messages);
						$('#errorMessageBody').addClass('bg-danger');
						$('#errorMessageModal').modal('show');
					}
					$this.contractTable.ajax.reload();
				},
				always: function () {
					console.log('--- saved resource request ---');
				}
			});
		});
	}

	listenForKeyUpContract() {
		$(document).on('keyup', '#CONTRACT', function (e) {
			var newContract = $(this).val().trim().toLowerCase();
			var allreadyExists = ($.inArray(newContract, contracts) >= 0);
			if (allreadyExists) { // comes back with Position in array(true) or false is it's NOT in the array.
				$('#Submit').attr('disabled', true);
				$(this).css("background-color", "LightPink");
				alert('Contract already defined');
			} else {
				$(this).css("background-color", "LightGreen");
				$('#Submit').attr('disabled', false);
			}
		});
	}
}

const ManageContracts = new manageContracts();

export { ManageContracts as default };