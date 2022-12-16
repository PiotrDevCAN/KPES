/*
 *
 *
 *
 */

class togglePesTrackerStatusDetailsBox {

	constructor() {
		console.log('+++ Function +++ togglePesTrackerStatusDetailsBox.constructor');

		this.listenForbtnTogglePesTrackerStatusDetails();

		console.log('--- Function --- togglePesTrackerStatusDetailsBox.constructor');
	}

	listenForbtnTogglePesTrackerStatusDetails() {
		$(document).on(
			"click",
			".btnTogglePesTrackerStatusDetails",
			function (e) {
				$(this).parent().children(".pesProcessStatusDisplay").toggle();
			}
		);
	}
}

export { togglePesTrackerStatusDetailsBox as default };