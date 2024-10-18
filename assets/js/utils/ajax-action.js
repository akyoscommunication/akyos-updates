import $ from 'jquery';
import {Notyf} from "notyf";

export class AjaxAction {
	constructor() {
		this._action = document.querySelectorAll('*[data-wp-ajax]');

		if (this._action.length) {
			this.init();
		}
	}

	init() {
		let that = this;
		$('*[data-wp-ajax]').on('submit', function (e) {
			if (e.target.getAttribute('data-wp-ajax') === 'true') {
				e.preventDefault();

				that.loader(e.target);

				let block_action = $(this).parent().parent().attr('data-action');
				let formAction = $(this).attr('action');

				let url = new URL(window.location.href);
				let params = new URLSearchParams(url.search);
				let page = params.get('page');

				let data = {page: page};

				$(this).find('input').each(function () {
					data[$(this).attr('name')] = $(this).val();
				});

				that.initAjax(formAction, block_action, data);
			}
		});
	}

	initAjax(formAction, block_action, data) {
		let that = this;
		let current_block = document.querySelector(`*[data-action="${block_action}"]`);

		fetch(formAction, {
			method: 'POST', headers: {
				'Content-Type': 'application/x-www-form-urlencoded', 'Cache-Control': 'no-cache',
			}, body: new URLSearchParams(data),
		})
			.then(response => {
				if (response.status === 500) {

					that.alert({
						message: current_block.querySelector('*[data-message-error]').getAttribute('data-message-error'),
						type: 'error'
					})

					that.loader(current_block, false);
					that.reset();
					that.init();

				} else {
					fetch(window.location.href, {
						method: 'GET', headers: {
							'Content-Type': 'application/x-www-form-urlencoded', 'Cache-Control': 'no-cache',
						},
					})
						.then(response => response.text())
						.then(response => {
							const parser = new DOMParser();
							const html = parser.parseFromString(response, 'text/html');

							let block = html.querySelector(`*[data-action="${block_action}"]`);
							current_block.innerHTML = block.innerHTML;

							that.alert({
								message: current_block.querySelector('*[data-message-success]').getAttribute('data-message-success'),
								type: 'success'
							})

							that.reset();
							that.init();
						})
				}
			})

	}

	loader(target, isLoading = true) {
		let btn = target.querySelector('button[type="submit"]');

		btn.appendChild(document.createElement('span')).innerHTML = '<svg version="1.1" id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"\n' +
			'  viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve">\n' +
			'    <path fill="#fff" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50">\n' +
			'      <animateTransform \n' +
			'         attributeName="transform" \n' +
			'         attributeType="XML" \n' +
			'         type="rotate"\n' +
			'         dur="1s" \n' +
			'         from="0 50 50"\n' +
			'         to="360 50 50" \n' +
			'         repeatCount="indefinite" />\n' +
			'  </path>\n' +
			'</svg>';

		if (isLoading) {
			btn.classList.add('btn--loading');
		} else {
			btn.classList.remove('btn--loading');

		}
	}

	alert({message, type = 'success'}) {
		let notyf = new Notyf();

		notyf.open({
			type: type,
			message: message,
			duration: 10000,
			position: {x: 'center', y: 'top'},
			dismissible: true,
			ripple: false,
		});
	}

	reset() {
		$('*[data-wp-ajax]').off('submit');
	}
}
