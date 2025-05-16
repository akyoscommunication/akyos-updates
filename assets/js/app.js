import {Sidebar} from "./utils/sidebar";
import {AjaxAction} from "./utils/ajax-action";
import {Recaptcha} from "./utils/recaptcha";
import {SvgAnimation} from "./utils/svg-animation";
window.onload = function () {
	new Sidebar();
	new AjaxAction();
	new Recaptcha();
	new SvgAnimation();
}
