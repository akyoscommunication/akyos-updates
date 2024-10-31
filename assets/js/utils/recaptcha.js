export class Recaptcha {
	constructor() {
		this._recaptcha = document.querySelector('.grecaptcha-badge');

		if (!this._recaptcha) {
			return;
		}

		this.init();
	}

	init() {
		this._container = document.querySelector('#aky_recaptcha_preview');

		this._container.appendChild(this._recaptcha);
	}
}
