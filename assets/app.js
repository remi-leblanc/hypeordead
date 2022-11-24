/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

function getCookie(name) {
	const value = `; ${document.cookie}`;
	const parts = value.split(`; ${name}=`);
	if (parts.length === 2) return parts.pop().split(';').shift();
}

(function() {

	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('consent', 'default', {
		'ad_storage': 'denied',
		'analytics_storage': 'denied'
	});
	gtag('config', 'G-S5LXZR9PFT');
	
	const checkCookies = setInterval(() => {
		if(getCookie('hodCookieConsentKey')){
			if(getCookie('hodCookieConsentAnalytics') == 1){
				gtag('consent', 'update', {
					'ad_storage': 'granted',
					'analytics_storage': 'granted'
				});
			}
			if(getCookie('hodCookieConsentAds') == 1){
				(adsbygoogle=window.adsbygoogle||[]).requestNonPersonalizedAds=0;
				(adsbygoogle=window.adsbygoogle||[]).pauseAdRequests=0;
			}
			clearInterval(checkCookies);
		}
	}, 500);
})();
