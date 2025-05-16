import { DotLottie } from '@lottiefiles/dotlottie-web';

export class SvgAnimation {
    constructor() {
        const dotLottie = new DotLottie({
            autoplay: true,
            loop: true,
            canvas: document.querySelector('#dotlottie-canvas'),
            src: "/app/plugins/akyos-updates/assets/icons/robot.lottie",
        });
    }
   
}
