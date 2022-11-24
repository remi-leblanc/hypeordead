import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

	static targets = ['search', 'option', 'selected', 'form', 'group'];

	connect(){
		this.groups = [];
		this.groupTargets.forEach((group, groupId) => {
			this.groups[groupId] = {
				'group': group,
				'selected': [],
				'options': [],
				'search': null
			};

			this.selectedTargets.forEach(element => {
				if(group.contains(element)){
					this.groups[groupId]['selected'][element.getAttribute('tag-value')] = element;
				}
			});

			this.optionTargets.forEach(element => {
				if(group.contains(element)){
					this.groups[groupId]['options'][element.getAttribute('tag-value')] = element;
				}
			});

			this.searchTargets.forEach(element => {
				if(group.contains(element)){
					this.groups[groupId]['search'] = element;
				}
			});
		});

		this.formOptions = {};
		for(const option of this.formTarget.options){
			this.formOptions[option.value] = option
		}
	}

	select(e) {
		e.target.classList.add('hidden');
		let toAdd = e.target.getAttribute('tag-value');

		for(const group of this.groups){
			if(group['selected'][toAdd] === undefined){
				continue;
			}
			group['selected'][toAdd].classList.remove('hidden');
		}

		this.formOptions[toAdd].selected = true;
		this.formTarget.dispatchEvent(new Event('change'));
	}

	remove(e) {
		e.target.classList.add('hidden');
		let toRemove = e.target.getAttribute('tag-value');

		for(const group of this.groups){
			if(group['options'][toRemove] === undefined){
				continue;
			}
			group['options'][toRemove].classList.remove('hidden');
		}

		this.formOptions[toRemove].selected = false;
		this.formTarget.dispatchEvent(new Event('change'));
	}

	search(e) {
		for(const group of this.groups){
			if(!group['group'].contains(e.target)){
				continue;
			}
			for(const option of group['options']){
				if(option === undefined){
					continue;
				}
				let toSearch = option.getAttribute('tag-value');
				if(option.textContent.toLowerCase().indexOf(group['search'].value.toLowerCase()) > -1 && !this.formOptions[toSearch].selected){
					option.classList.remove('hidden');
				} else {
					option.classList.add('hidden');
				}
			}
		}
	}
}
