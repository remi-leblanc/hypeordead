import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

	static targets = ['video'];

	toggleAudio() {
		this.videoTarget.muted = !this.videoTarget.muted;
	}
}
