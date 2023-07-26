/**
 *
 */

class manageAccounts {

	accountTable;

	constructor() {
		console.log('+++ Function +++ manageAccounts.constructor');

		this.populateDataTable();
		this.listenForAccountsFormSubmit();
		this.listenForDeleteAccount();
		this.listenForEditAccountName();
		this.listenForConfirmAccountDelete();
		this.listenForKeyUpAccount();
		this.listenForKeyUpAndChangeTaskId();

		$('#ACCOUNT_TYPE').select2({
			placeholder: 'Select Account Type',
			width: '100%'
		});

		console.log('--- Function --- manageAccounts.constructor');
	}

	populateDataTable() {
		this.accountTable = $('#accountTable').DataTable({
			ajax: {
				url: 'ajax/populateAccountsTable.php',
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
					data: "ACCOUNT_ID"
				}, {
					data: "ACCOUNT"
				}, {
					data: "ACCOUNT_TYPE"
				}, {
					data: "TASKID"
				}]
		});
	}

	listenForAccountsFormSubmit() {
		var $this = this;
		$(document).on('submit', '#accountsForm', function (e) {
			console.log(e);
			e.preventDefault();

			var submitBtn = $(e.target).find('input[name="Submit"]').addClass('spinning');
			var url = 'ajax/saveAccountRecord.php';

			var disabledFields = $(':disabled');
			$(disabledFields).removeAttr('disabled');
			var formData = $("#accountsForm").serialize();
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
						$('#accountsForm').trigger("reset");
					} else {
						$(submitBtn).removeClass('spinning').attr('disabled', false);
						$('#accountsForm').trigger("reset");
						$('.modal-body').html(responseObj.Messages);
						$('.modal-body').addClass('bg-danger');
						$('#errorMessageModal').modal('show');
					}
					$('#ACCOUNT').css("background-color", "white");
					$this.accountTable.ajax.reload();
				},
				always: function () {
					console.log('--- saved resource request ---');
				}
			});
		});
	}

	listenForDeleteAccount() {
		$(document).on('click', '.deleteAccount', function (e) {
			console.log(e);
			var account = $(e.target).data('account');
			var accountId = $(e.target).data('accountid');
			$('#confirmDeleteAccountName').html($(e.target).data('account'));
			$('#confirmDeleteAccountId').val(accountId);
			$('#modalDeleteAccountConfirm').modal('show');
		});
	}

	listenForEditAccountName() {
		$(document).on('click', '.editAccountName', function (e) {
			var button = $(e.target).parent('button').addClass('spinning');
			var account = $(e.target).data('account');
			$('#ACCOUNT').val($(e.target).data('account'));
			$('#ACCOUNT_ID').val($(e.target).data('accountid'));
			$('#ACCOUNT_TYPE').val($(e.target).data('accounttype')).trigger('change');
			$('#TASKID').val($(e.target).data('taskid'));
			$('#mode').val('edit');
			$(button).removeClass('spinning');
		});
	}

	listenForConfirmAccountDelete() {
		var $this = this;
		$(document).on('click', '.confirmAccountDelete', function (e) {
			var accountid = $('#confirmDeleteAccountId').val();
			console.log(accountid);

			var submitBtn = $(e.target).find('input[name="Submit"]').addClass('spinning');
			var url = 'ajax/deleteAccountRecord.php';

			$.ajax({
				type: 'post',
				url: url,
				data: {
					accountid: accountid
				},
				context: document.body,
				success: function (response) {
					var responseObj = JSON.parse(response);
					if (!responseObj.success) {
						$('#accountsForm').trigger("reset");
						$('#errorMessageBody').html(responseObj.Messages);
						$('#errorMessageBody').addClass('bg-danger');
						$('#errorMessageModal').modal('show');
					}
					$this.accountTable.ajax.reload();
				},
				always: function () {
					console.log('--- saved resource request ---');
				}
			});
		});
	}

	listenForKeyUpAccount() {
		$(document).on('keyup', '#ACCOUNT', function (e) {
			var newAccount = $(this).val().trim().toLowerCase();
			var allreadyExists = ($.inArray(newAccount, accounts) >= 0);
			if (allreadyExists) { // comes back with Position in array(true) or false is it's NOT in the array.
				$('#Submit').attr('disabled', true);
				$(this).css("background-color", "LightPink");
				alert('Account already defined');
			} else {
				$(this).css("background-color", "LightGreen");
				$('#Submit').attr('disabled', false);
			}
		});
	}
	
	listenForKeyUpAndChangeTaskId() {
		$(document).on('keyup change', '#TASKID', function (e) {
			var emailAddress = $('#TASKID').val();
			var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2, 4}$/;

			console.log(emailAddress);

			if (emailAddress == '') {
				$('#TASKID').css('background-color', 'inherit');
			} else {
				if (emailReg.test(emailAddress)) {
					$('#TASKID').css('background-color', 'LightGreen');
				} else {
					$('#TASKID').css('background-color', 'LightPink');
				};
			}
		});
	}
}

const ManageAccounts = new manageAccounts();

export { ManageAccounts as default };