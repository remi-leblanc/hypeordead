import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

	static targets = ['modal', 'btnOpen', 'btnClose'];

	initialize() {
		this.isOpen = false;
	}

	open() {
		this.modalTarget.classList.remove('hidden');
		document.body.classList.add('no-scroll');
		if(this.hasBtnCloseTarget){
			this.btnCloseTarget.classList.remove('hidden');
		}
		if(this.hasBtnOpenTarget){
			this.btnOpenTarget.classList.add('hidden');
		}
	}

	close() {
		this.modalTarget.classList.add('hidden');
		document.body.classList.remove('no-scroll');
		if(this.hasBtnCloseTarget){
			this.btnCloseTarget.classList.add('hidden');
		}
		if(this.hasBtnOpenTarget){
			this.btnOpenTarget.classList.remove('hidden');
		}
	}

	toggle() {
		if (!this.isOpen) {
			this.open();
		} else {
			this.close();
		}
		this.isOpen = !this.isOpen;
	}
}
