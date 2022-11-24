import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

	static targets = ['output', 'loader'];

	connect() {
		this.timeout = null;
	}

	send(event) {
		event.preventDefault();
		if (this.timeout !== null) {
			clearTimeout(this.timeout);
		}
		this.timeout = setTimeout(() => {
			let form = event.target.tagName == 'FORM' ? event.target : event.target.form
			if(this.hasOutputTarget){
				this.outputTarget.classList.add('hidden');
			}
			if(this.hasLoaderTarget){
				this.loaderTarget.classList.remove('hidden');
			}
			let formData = new FormData(form);
			if(event.submitter){
				formData.set(event.submitter.name, "");
			}
			fetch(form.action, {
				method: "POST",
				body: formData
			}).then((response) => {
				return response.text();
			}).then((html) => {
				if(this.hasOutputTarget){
					this.outputTarget.innerHTML = html;
					this.outputTarget.classList.remove('hidden');
				}
				if(this.hasLoaderTarget){
					this.loaderTarget.classList.add('hidden');
				}
			}).catch((error)=> {
				console.error(error);
			})
		}, event.type == 'input' ? 250 : 0);
	}
}
