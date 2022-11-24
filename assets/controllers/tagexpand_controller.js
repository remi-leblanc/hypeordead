import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

	static targets = ['search', 'option', 'checkbox'];

	connect(){
		this.options = {};
		this.optionTargets.forEach(element => {
			this.options[element.getAttribute('tag-value')] = element;
		});

		this.checkboxes = {};
		this.checkboxTargets.forEach(element => {
			this.checkboxes[element.getAttribute('tag-value')] = element;
		});
	}

	select(e) {
		let tagVal = e.target.getAttribute('tag-value');
		if(e.target.checked) {
			this.options[tagVal].classList.add('order-first');
		} else {
			this.options[tagVal].classList.remove('order-first');
		}
		
		
	}

	search() {
		for(const checkbox of this.checkboxTargets){
			let tagVal = checkbox.getAttribute('tag-value');
			if(tagVal.toLowerCase().indexOf(this.searchTarget.value.toLowerCase()) > -1){
				this.options[tagVal].classList.remove('hidden');
			} else {
				if(!checkbox.checked){
					this.options[tagVal].classList.add('hidden');
				}
			}
		}
	}
}
