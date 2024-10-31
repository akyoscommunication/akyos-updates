import {Sidebar} from "./utils/sidebar";
import {AjaxAction} from "./utils/ajax-action";
import {Recaptcha} from "./utils/recaptcha";

window.onload = function () {
	new Sidebar();
	new AjaxAction();
	new Recaptcha();
}
