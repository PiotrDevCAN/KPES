/*
 *
 *
 *
 */

class initiatePESRequestBox {

	constructor() {
		console.log('+++ Function +++ initiatePESRequestBox.constructor');

		this.listenForInitiatePes();

		console.log('--- Function --- initiatePESRequestBox.constructor');
	}

	listenForInitiatePes() {
		$(document).on('click', '.btnPesInitiate', function (e) {
			$('#errorMessageBody').html('This functionality has been disabled.');
			$('#errorMessageModal').modal('show');
		});
	}
}

export { initiatePESRequestBox as default };