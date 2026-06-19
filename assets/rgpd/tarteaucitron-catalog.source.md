Source URL: https://tarteaucitron.io/installation-gratuite-open-source/
Title: Installation gratuite (open source) – tarteaucitron.io

Mise à jour : modifiez le tag pour https://cdntag.tarteaucitron.io/load.js sur vos sites ⚡️ Performances et résilience aux pannes×

Aller au contenu 

tarteaucitron.io 

Menu 

Menu 

* Dashboard
* Tarifs
* Version gratuite
* Ressources  
   * Aide  
   * Articles  
   * Changelog  
   * Stats
* Contact
* Français  
   * Français  
   * English

# Installation gratuite (open source)

#### Étape 1 : Téléchargement de la librairie

La première étape consiste à intégrer sur votre site les fichiers nécessaires au bon fonctionnement de tarteaucitron.

Téléchargez les fichiers (Github ou NPM) et placez le dossier `/tarteaucitron/` à la racine de votre site. 

#### Étape 2 : Initialisation du script

Seconde étape, initialiser la librairie tarteaucitron avec vos options. Placez le code ci-dessous juste après la balise d’ouverture `<head>` :

```
        <script src="/tarteaucitron/tarteaucitron.min.js"></script>

        <script type="text/javascript">
        tarteaucitron.init({
    	  "privacyUrl": "", /* Url de la politique de confidentialité */
          "bodyPosition": "top", /* top place le bandeau de consentement au début du code html, mieux pour l'accessibilité */

    	  "hashtag": "#tarteaucitron", /* Hashtag qui permet d'ouvrir le panneau de contrôle  */
    	  "cookieName": "tarteaucitron", /* Nom du cookie (uniquement lettres et chiffres) */
    
    	  "orientation": "middle", /* Position de la bannière (top - bottom - popup - banner) */
       
          "groupServices": true, /* Grouper les services par catégorie */
          "showDetailsOnClick": true, /* Cliquer pour ouvrir la description */
          "serviceDefaultState": "wait", /* Statut par défaut (true - wait - false) */
                           
    	  "showAlertSmall": false, /* Afficher la petite bannière en bas à droite */
    	  "cookieslist": false, /* Afficher la liste des cookies via une mini bannière */
           "cookieslistEmbed": false, /* Afficher la liste des cookies dans le panneau de contrôle */
                           
          "closePopup": true, /* Afficher un X pour fermer la bannière */

          "showIcon": true, /* Afficher un cookie pour ouvrir le panneau */
          //"iconSrc": "", /* Optionnel: URL ou image en base64 */
          "iconPosition": "BottomRight", /* Position de l'icons: (BottomRight - BottomLeft - TopRight - TopLeft) */

    	  "adblocker": false, /* Afficher un message si un Adblocker est détecté */
                           
          "DenyAllCta" : true, /* Afficher le bouton Tout refuser */
          "AcceptAllCta" : true, /* Afficher le bouton Tout accepter */
          "highPrivacy": true, /* Attendre le consentement */
          "alwaysNeedConsent": false, /* Demander le consentement même pour les services "Privacy by design" */
                           
    	  "handleBrowserDNTRequest": false, /* Refuser tout par défaut si Do Not Track est activé sur le navigateur */

    	  "removeCredit": false, /* Retirer le lien de crédit vers tarteaucitron.io */
    	  "moreInfoLink": true, /* Afficher le lien En savoir plus */

          "useExternalCss": false, /* Mode expert : désactiver le chargement des fichiers .css tarteaucitron */
          "useExternalJs": false, /* Mode expert : désactiver le chargement des fichiers .js tarteaucitron */

    	  //"cookieDomain": ".my-multisite-domaine.fr", /* Optionnel: domaine principal pour partager le consentement avec des sous domaines */
                          
          "readmoreLink": "", /* Changer le lien En savoir plus par défaut */

          "mandatory": true, /* Afficher un message pour l'utilisation de cookies obligatoires */
          "mandatoryCta": false, /* Afficher un bouton pour les cookies obligatoires (déconseillé) */
    
          //"customCloserId": "", /* Optionnel a11y: ID personnalisé pour ouvrir le panel */
          
          "googleConsentMode": true, /* Activer le Google Consent Mode v2 pour Google ads & GA4 */
          "bingConsentMode": true, /* Activer le Bing Consent Mode pour Clarity & Bing Ads */
          "pianoConsentMode": true, /* Activer le Consent Mode pour Piano Analytics */
          "pianoConsentModeEssential": false, /* Activer par défaut le mode Essential de Piano */
          "softConsentMode": false, /* Soft consent mode (le consentement est requis pour charger les tags) */

          "dataLayer": false, /* Envoyer un événement dans dataLayer avec le statut des services */
          "serverSide": false, /* Server side seulement, les tags ne sont pas chargé côté client */
          
          "partnersList": true /* Afficher le détail du nombre de partenaires sur la bandeau */
        });
        </script>
```

#### Étape 3 : Ajout des services

Maintenant que la librairie est en place, vous pouvez remplacer vos marqueurs actuels par la version tarteaucitron en fonction des services que vous utilisez.

##### API

ABTasty

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.abtastyID = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('abtasty'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//try.abtasty.com/**id**.js"></script>`

Arc.io

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.arcId = '**arcId**'; (tarteaucitron.job = tarteaucitron.job || []).push('arcio'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async src="https://arc.io/widget.min.js#**arcId**"></script>`

Dynatrace

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.dynatraceJSPath = '**path**'; (tarteaucitron.job = tarteaucitron.job || []).push('dynatrace'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.dynatraceConfig = '**config**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="**path**" data-dtconfig="**config**"></script>`

Genial.ly

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('genially'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_genially" data-geniallyid="**geniallyid**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<div style="position: relative; padding-bottom: 109.00%; padding-top: 0; height: 0;"><iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="https://view.genial.ly/**geniallyid**" width="**width**" height="**height**" frameborder="0" scrolling="yes" allowfullscreen="allowfullscreen"></iframe></div>`

Geoportail maps Embed

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('geoportail'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="geoportail" data-url="**url**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" sandbox="allow-forms allow-scripts allow-same-origin" src="**url**" allowfullscreen></iframe>`

Google Fonts

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.googleFonts = '**families**'; (tarteaucitron.job = tarteaucitron.job || []).push('googlefonts'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.24/webfont.js"></script> <script> WebFont.load({ google: { families: ['**families**'] } }); </script>`

Google jsapi

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('jsapi'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//www.google.com/jsapi"></script>`

Google Maps

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.googlemapsKey = '**API KEY**'; (tarteaucitron.job = tarteaucitron.job || []).push('googlemaps'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="googlemaps-canvas" data-zoom="**zoom**" data-latitude="**latitude**" data-longitude="**longitude**" style="width: **width**px; height: **height**px;"></div><script>tarteaucitron.user.mapscallback = '**callback_function**';tarteaucitron.user.googlemapsLibraries = '**LIBRARIES**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://maps.googleapis.com/maps/api/js?key=**API KEY**" type="text/javascript"></script> <script type="text/javascript"> function initialize() { var mapOptions = { center: { lat: **latitude**, lng: **longitude**}, zoom: **zoom** }; var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions); } google.maps.event.addDomListener(window, 'load', initialize); </script>`

Google Maps (search query)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('googlemapssearch'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="googlemapssearch" data-search="**SEARCHWORDS**" data-api-key="**YOUR_GOOGLE_MAP_API_KEY**" data-width="**WIDTH**" data-height="**HEIGHT**" ></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**WIDTH**" height="**HEIGHT**" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=**YOUR_API_KEY**&q=**SEARCHWORDS**" allowfullscreen> </iframe>`

Google Tag Manager

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.googletagmanagerId = '**GTM-XXXX**'; (tarteaucitron.job = tarteaucitron.job || []).push('googletagmanager'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src= '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','**GTM-XXXX**');</script>`

Google Tag Manager (multiple)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.multiplegoogletagmanagerId = ['**GTM-XXXX**']; (tarteaucitron.job = tarteaucitron.job || []).push('multiplegoogletagmanager'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src= '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','**GTM-XXXX**');</script>`

HelloAsso

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('helloasso'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_helloasso" data-url="**url**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe id="haWidget" allowtransparency="true" scrolling="auto" src="**url**" style="width:**width**;height:**height**;border:none;" onload="window.scroll(0, this.offsetTop)"></iframe>`

M6 Météo

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('m6meteo'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_m6meteo" data-id="**id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<div id="cont_**id**"> <div id="spa_xxx"><a id="a_xxx" href="" target="_top" style="color:#333;text-decoration:none;">Météo</a> ©<a target="_top" href="https://www.meteocity.com">M6météo</a></div> <script type="text/javascript" src="https://www.meteocity.com/widget/js/xxx"></script> </div>`

Marketo munchkin

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.marketomunchkinkey = '**marketomunchkinkey**'; (tarteaucitron.job = tarteaucitron.job || []).push('marketomunchkin'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> var didInit = false; function initMunchkin() { if(didInit === false) { didInit = true; Munchkin.init('**marketomunchkinkey**'); } } var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//munchkin.marketo.net/munchkin.js'; s.onreadystatechange = function() { if (this.readyState == 'complete' || this.readyState == 'loaded') { initMunchkin(); } }; s.onload = initMunchkin; document.getElementsByTagName('head')[0].appendChild(s);</script>`

Matomo Tag Manager

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.matomotmUrl = '**matomomtUrl**'; (tarteaucitron.job = tarteaucitron.job || []).push('matomotm'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> var _mtm = window._mtm = window._mtm || []; _mtm.push({'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start'}); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.async=true; g.src='**matomomtUrl**'; s.parentNode.insertBefore(g,s); </script>`

MTcaptcha

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.mtcaptchaSitekey = '**SiteKey**'; (tarteaucitron.job = tarteaucitron.job || []).push('mtcaptcha'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> var mtcaptchaConfig = { "sitekey": "**SiteKey**" }; (function(){var mt_service = document.createElement('script');mt_service.async = true;mt_service.src = 'https://service.mtcaptcha.com/mtcv1/client/mtcaptcha.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_service); var mt_service2 = document.createElement('script');mt_service2.async = true;mt_service2.src = 'https://service2.mtcaptcha.com/mtcv1/client/mtcaptcha2.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_service2);}) (); </script>`

MyFeelBack (Skeepers)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.myfeelbackId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('myfeelback'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type='text/javascript'> window._Mfb_useCookie = true; // Review the commented lines. Insert real values for the placeholders window._Mfb_ud = { _context : { lang : undefined, // You can force the language of the survey. privacyMode : false, _page : { url : location.pathname, storageDuration : 30 // You can add page's properties here ie : pageProperties : { property1 : 'value', property2 : 'value'} } } }; (function() { var mfb = document.createElement('script'); mfb.type = 'text/javascript'; mfb.charset = 'UTF-8'; mfb.async = true; mfb.id = 'MFBActor'; mfb.src = 'https://actorssl-5637.kxcdn.com/actor/**ID**/action'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(mfb, s); })(); </script>`

Météo France

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('meteofrance'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_meteofrance" data-insee="**insee**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe id="widget_autocomplete_preview" width="**width**" height="**height**" frameborder="0" src="https://meteofrance.com/widget/prevision/**insee**"></iframe>`

OneSignal

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.onesignalAppId = '**onesignalAppId**'; (tarteaucitron.job = tarteaucitron.job || []).push('onesignal'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" type="text/javascript"></script> <script> var OneSignal = window.OneSignal || []; OneSignal.push(function() { OneSignal.init({ appId: "**onesignalAppId**", }); }); </script>`

Openstreetmap Embed

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('openstreetmap'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="openstreetmap" data-url="**url**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="**url**" style="border: 1px solid black"></iframe>`

Pingdom

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.pingdomId = '**pingdomId**'; (tarteaucitron.job = tarteaucitron.job || []).push('pingdom'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> var _prum = [['id', '**pingdomId**'], ['mark', 'firstbyte', (new Date()).getTime()]]; (function() { var s = document.getElementsByTagName('script')[0] , p = document.createElement('script'); p.async = 'async'; p.src = '//rum-static.pingdom.net/prum.min.js'; s.parentNode.insertBefore(p, s); })(); </script>`

reCAPTCHA

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.recaptchaapi = '**XXXXX**'; (tarteaucitron.job = tarteaucitron.job || []).push('recaptcha'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="g-recaptcha" data-sitekey="**sitekey**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src='https://www.google.com/recaptcha/api.js?render=**XXXXX**'></script>`

Seamlessaccess

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.seamlessaccessInitiator = '**LOGIN_INITIATOR_URL**'; (tarteaucitron.job = tarteaucitron.job || []).push('seamlessaccess'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://service.seamlessaccess.org/thiss.js"></script> <script> window.onload = function() { // Render the Seamless Access button thiss.DiscoveryComponent.render({ loginInitiatorURL: '**LOGIN_INITIATOR_URL**', }, '#putMyLoginButtonHere'); }; </script>`

Stonly

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.stonlyId = '**stonlyId**'; (tarteaucitron.job = tarteaucitron.job || []).push('stonly'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<!-- stonly-widget - copy into the head --> <script>var STONLY_WID = "**stonlyId**";</script> <script> !function(s,t,o,n,l,y,w,g){s.StonlyWidget||((w=s.StonlyWidget=function(){ w._api?w._api.apply(w,arguments):w.queue.push(arguments)}).queue=[],(y=t.createElement(o)).async=!0, (g=new XMLHttpRequest).open("GET",n+"version?v="+Date.now(),!0),g.onreadystatechange=function(){ 4===g.readyState&&(y.src=n+"stonly-widget.js?v="+(200===g.status?g.responseText:Date.now()), (l=t.getElementsByTagName(o)[0]).parentNode.insertBefore(y,l))},g.send()) }(window,document,"script","https://stonly.com/js/widget/v2/"); </script> <!-- stonly-widget - copy into the head -->`

TagCommander

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.tagcommanderid = '**tagcommanderid**'; (tarteaucitron.job = tarteaucitron.job || []).push('tagcommander'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://cdn.tagcommander.com/**tagcommanderid**.js"></script>`

Timeline JS

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('timelinejs'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="timelinejs-canvas" data-spreadsheet_id="**spreadsheet_id**" data-width="**width**" data-height="**height**" lang="**lang_2_letter**" data-font="**font (Bevan-PotanoSans | Georgia-Helvetica | Arvo-PTSans)**" data-map="**map (toner | osm)**" data-start_at_end="**start_at_end (false | true)**" data-hash_bookmark="**hash_bookmark (false | true)**" data-start_at_slide="**start_at_slide (0 | ...)**" data-start_zoom="**start_zoom (0 | ... | 5)**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="//cdn.knightlab.com/libs/timeline/latest/embed/index.html?source=**spreadsheet_id**&font=**font**&maptype=**map**&lang=**lang_2_letter**&start_at_end=**start_at_end**&hash_bookmark=**hash_bookmark**&start_at_slide=**start_at_slide**&start_zoom_adjust=**start_zoom_adjust**&height=**height**" width="**width**" height="**height**" frameborder="0"></iframe>`

Typekit (adobe)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.typekitId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('typekit'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//use.typekit.net/**id**.js"></script> <script type="text/javascript">try{Typekit.load();}catch(e){}</script>`

X (formerly Twitter) Widgets API

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('twitterwidgetsapi'); </script>`

##### Autre

Active Campaign 2023

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.activecampaignAccount = '**Account**'; (tarteaucitron.job = tarteaucitron.job || []).push('activecampaignvgo'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function(e,t,o,n,p,r,i){e.visitorGlobalObjectAlias=n;e[e.visitorGlobalObjectAlias]=e[e.visitorGlobalObjectAlias]||function(){(e[e.visitorGlobalObjectAlias].q=e[e.visitorGlobalObjectAlias].q||[]).push(arguments)};e[e.visitorGlobalObjectAlias].l=(new Date).getTime();r=t.createElement("script");r.src=o;r.async=true;i=t.getElementsByTagName("script")[0];i.parentNode.insertBefore(r,i)})(window,document,"https://diffuser-cdn.app-us1.com/diffuser/diffuser.js","vgo"); vgo('setAccount', '**Account**'); vgo('setTrackByDefault', true); vgo('process'); </script>`

Calendly

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('calendly'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="calendly-inline-widget" data-url="https://calendly.com/**URL**/30min" style="min-width:320px;height:1030px;"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src=https://assets.calendly.com/assets/external/widget.js async></script>`

Collect Chat

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.collectchatId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('collectchat'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(w, d) { w.CollectId = "**ID**"; var h = d.head || d.getElementsByTagName("head")[0]; var s = d.createElement("script"); s.setAttribute("type", "text/javascript"); s.async=true; s.setAttribute("src", https://collectcdn.com/launcher.js); h.appendChild(s); })(window, document);</script>`

Crisp Chat

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.crispID = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('crisp'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID=**ID**;(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>`

Facil'ITI

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.facilitiID = '**facilitiID**'; (tarteaucitron.job = tarteaucitron.job || []).push('faciliti'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript">(function(w, d, s, f) { w[f] = w[f] || {conf: function () { (w[f].data = w[f].data || []).push(arguments);}}; var l = d.createElement(s), e = d.getElementsByTagName(s)[0]; l.async = 1; l.src = 'https://ws.facil-iti.com/tag/faciliti-tag.min.js'; e.parentNode.insertBefore(l, e); }(window, document, 'script', 'FACIL_ITI')); FACIL_ITI.conf('userId', '**facilitiID**');</script>`

Fillout

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('fillout'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_fillout" data-fillout-id="**ID**" data-fillout-embed-type="standard" data-fillout-inherit-parameters data-fillout-dynamic-resize data-fillout-domain="**DOMAIN**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://server.fillout.com/embed/v1/"></script>`

Gallica

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gallica'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="gallica_player" data-style="**style**" data-src="**src**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<div style="display: block; "><iframe style="**style**" src="**src**"></iframe></div>`

Google Agenda

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gagenda'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="gagenda_embed" data-width="**width**" data-height="**height**" data-data="**data**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://www.google.com/calendar/embed?**data**"></iframe>`

Google Docs

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gdocs'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="gdocs_embed" data-width="**width**" data-height="**height**" id="**id**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://docs.google.com/document/d/e/**id**/pub?embedded=true"></iframe>`

Google Forms

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gforms'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="gforms_embed" data-width="**width**" data-height="**height**" id="**id**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://docs.google.com/forms/d/e/**id**/viewform?embedded=true"></iframe>`

Google Maps (no api)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('maps_noapi'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="googlemaps_embed" data-width="**width**" data-height="**height**" id="**id**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://google.com/maps/embed?pb=**id**"></iframe>`

Google Optimize

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.goptimize = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('goptimize'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://www.googleoptimize.com/optimize.js?id=**ID**"></script>`

Google Sheets

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gsheets'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="gsheets_embed" data-width="**width**" data-height="**height**" id="**id**" data-headers="**headers**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://docs.google.com/spreadsheets/d/e/**id**/pubhtml?widget=true&amp;headers=**headers**"></iframe>`

Google Signin

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('googlesignin'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://accounts.google.com/gsi/client" async defer></script>`

Google Slides

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gslides'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="gslides_embed" data-width="**width**" data-height="**height**" id="**id**" data-autostart="**autostart (true | false)**" data-loop="**loop (true | false)**" data-delay="**delay**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://docs.google.com/presentation/d/e/**id**/embed?start=**autostart (true | false)**&loop=**loop (true | false)**&delayms=**delay**"></iframe>`

hCaptcha

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('hcaptcha'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="h-captcha" data-sitekey="**siteKey**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://hcaptcha.com/1/api.js"></script>`

Posthog

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.posthogApiKey = '**apiKey**'; (tarteaucitron.job = tarteaucitron.job || []).push('posthog'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.posthogHost = '**host**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.async=!0,p.src=s.api_host+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="capture identify alias people.set people.set_once set_config register register_once unregister opt_out_capturing has_opted_out_capturing opt_in_capturing reset isFeatureEnabled onFeatureFlags".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]); posthog.init('**apiKey**', {api_host: '**host**'}) </script>`

Robo Fabrica Chatbot

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.robofabricaUuid = '**uuid**'; (tarteaucitron.job = tarteaucitron.job || []).push('robofabrica'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<button id="inceptive-cw-launch" type="button" name="button"></button>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://app.robofabrica.tech/widget/script" type="text/javascript" id="inceptive-cw-script" unique-url="**uuid**" label="start" launch-btn-id="inceptive-cw-launch" chat-server-url="https://app.robofabrica.tech:443"></script> <button id="inceptive-cw-launch" type="button" name="button" ></button>`

sendinblue

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.sendinblueKey = '**Key**'; (tarteaucitron.job = tarteaucitron.job || []).push('sendinblue'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function() { window.sib = { equeue: [], client_key: "**Key**" }; window.sendinblue = {}; for (var j = ['track', 'identify', 'trackLink', 'page'], i = 0; i < j.length; i++) { (function(k) { window.sendinblue[k] = function() { var arg = Array.prototype.slice.call(arguments); (window.sib[k] || function() { var t = {}; t[k] = arg; window.sib.equeue.push(t); })(arg[0], arg[1], arg[2], arg[3]); }; })(j[i]); } var n = document.createElement("script"), i = document.getElementsByTagName("script")[0]; n.type = "text/javascript", n.id = "sendinblue-js", n.async = !0, n.src = "https://sibautomation.com/sa.js?key=" + window.sib.client_key, i.parentNode.insertBefore(n, i), window.sendinblue.page(); })(); </script>`

tolk.ai

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.tolkaiBot = '**bot**'; (tarteaucitron.job = tarteaucitron.job || []).push('tolkai'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> var tcfbot = "**bot**"; var TcfWbchtParams = { behaviour: 'default' }; var display = 'iframe'; var script = document.createElement('script'); script.type = 'text/javascript'; script.src = 'https://script.tolk.ai/iframe-latest.js'; document.body.appendChild(script); </script>`

Trustpilot

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('trustpilot'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="trustpilot-widget" data-locale="**dataLocale**" data-template-id="**templateId**" data-businessunit-id="**businessunitId**" data-style-height="**height**" data-style-width="**width**" data-theme="**theme (light | dark)**" data-stars="**stars**" data-review-languages="**reviewLocale**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.sync.bootstrap.min.js" async></script>`

Ubib Chatbot

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.ubibId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('ubib'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.ubibHash = '**hash**';</script><div id="libchat_**hash**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://**id**.libanswers.com/load_chat.php?hash=**hash**"></script>`

##### Commentaires

Disqus

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.disqusShortname = '**disqus_shortname**'; (tarteaucitron.job = tarteaucitron.job || []).push('disqus'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div id="disqus_thread"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var disqus_shortname = '**disqus_shortname**'; /* * * DON'T EDIT BELOW THIS LINE * * */ (function() { var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true; dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js'; (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq); })(); </script>`

Facebook (commentaire)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('facebookcomment'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_comment\_light-inline-t2_  
`<div class="fb-comments" data-numposts="5" data-colorscheme="light" data-href="CURRENT_URI"></div>`_comment\_dark-inline-t2_  
`<div class="fb-comments" data-numposts="5" data-colorscheme="dark" data-href="CURRENT_URI"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.0"; fjs.parentNode.insertBefore(js, fjs); } (document, 'script', 'facebook-jssdk')); </script>`

##### Google Consent Mode v2

Advertising

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gcmadstorage'); </script>`

Analytics

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gcmanalyticsstorage'); </script>`

Functionality

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gcmfunctionality'); </script>`

Personalization

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gcmpersonalization'); </script>`

Personalized Advertising

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gcmadsuserdata'); </script>`

Security

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gcmsecurity'); </script>`

##### Mesure d'audience

ActiSTAT

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.actistatId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('actistat'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async defer data-website-id="**id**" src="https://actistat.fr/umami.js"></script>`

Adobe - Analysis Workspace

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.adobeworkspaceId1 = '**id1**'; (tarteaucitron.job = tarteaucitron.job || []).push('adobeworkspace'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.adobeworkspaceId2 = '**id2**';tarteaucitron.user.adobeworkspaceId3 = '**id3**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://assets.adobedtm.com/**id1**/**id2**/launch-**id3**.min.js" async></script>`

Adobe Analytics

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.adobeanalyticskey = '**adobeanalyticskey**'; (tarteaucitron.job = tarteaucitron.job || []).push('adobeanalytics'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//assets.adobedtm.com/launch-**adobeanalyticskey**.min.js" async></script>`

Amplitude

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.amplitude = '**API_KEY**'; (tarteaucitron.job = tarteaucitron.job || []).push('amplitude'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function(e,t){var n=e.amplitude||{_q:[],_iq:{}};var r=t.createElement("script") ;r.type="text/javascript" ;r.integrity="sha384-vYYnQ3LPdp/RkQjoKBTGSq0X5F73gXU3G2QopHaIfna0Ct1JRWzwrmEz115NzOta" ;r.crossOrigin="anonymous";r.async=true ;r.src="https://cdn.amplitude.com/libs/amplitude-5.8.0-min.gz.js" ;r.onload=function(){if(!e.amplitude.runQueuedFunctions){ console.log("[Amplitude] Error: could not load SDK")}} ;var i=t.getElementsByTagName("script")[0];i.parentNode.insertBefore(r,i) ;function s(e,t){e.prototype[t]=function(){ this._q.push([t].concat(Array.prototype.slice.call(arguments,0)));return this}} var o=function(){this._q=[];return this} ;var a=["add","append","clearAll","prepend","set","setOnce","unset"] ;for(var u=0;u<a.length;u++){s(o,a[u])}n.Identify=o;var c=function(){this._q=[] ;return this} ;var l=["setProductId","setQuantity","setPrice","setRevenueType","setEventProperties"] ;for(var p=0;p<l.length;p++){s(c,l[p])}n.Revenue=c ;var d=["init","logEvent","logRevenue","setUserId","setUserProperties","setOptOut","setVersionName","setDomain","setDeviceId", "enableTracking", "setGlobalUserProperties","identify","clearUserProperties","setGroup","logRevenueV2","regenerateDeviceId","groupIdentify","onInit","logEventWithTimestamp","logEventWithGroups","setSessionId","resetSessionId"] ;function v(e){function t(t){e[t]=function(){ e._q.push([t].concat(Array.prototype.slice.call(arguments,0)))}} for(var n=0;n<d.length;n++){t(d[n])}}v(n);n.getInstance=function(e){ e=(!e||e.length===0?"$default_instance":e).toLowerCase() ;if(!n._iq.hasOwnProperty(e)){n._iq[e]={_q:[]};v(n._iq[e])}return n._iq[e]} ;e.amplitude=n})(window,document); amplitude.getInstance().init("**API_KEY**"); </script>`

AT Internet (privacy by design)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.atLibUrl = '**SMARTTAG_JS_LINK**'; tarteaucitron.user.atMore = function () { /* ~~add here your optionnal ATInternet.Tracker.Tag configuration~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('atinternet'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.atinternetSendData = **sendData (true | false)**;tarteaucitron.user.atNoFallback = **noFallback (false | true)**;</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="**SMARTTAG_JS_LINK**"></script>`

Clarity

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.clarity = '**clarity_id**'; (tarteaucitron.job = tarteaucitron.job || []).push('clarity'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function(c,l,a,r,i,t,y){ c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)}; t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i; y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y); })(window, document, "clarity", "script", "**clarity_id**"); </script>`

Clicky

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.clickyId = **YOUR-ID**; tarteaucitron.user.clickyMore = function () { /* ~~add here your optionnal clicky function~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('clicky'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var clicky_site_ids = clicky_site_ids || []; clicky_site_ids.push(**YOUR-ID**); (function() { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//static.getclicky.com/js'; ( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild( s ); })(); </script>`

Compteur.fr

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.compteurID = '**compteurID**'; (tarteaucitron.job = tarteaucitron.job || []).push('compteur'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<span id="wts**compteurID**"></span> <script> var wts7 = {}; var wts=document.createElement('script');wts.async=true; wts.src='https://server2.compteur.fr/log7.js';document.head.appendChild(wts); wts.onload = function(){ wtslog7(**compteurID**,1); }; </script>`

ContentSquare

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.contentsquareID = '**YOUR_TAG_ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('contentsquare'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function() { var mt = document.createElement("script"); mt.type = "text/javascript"; mt.async = true; mt.src = "//t.contentsquare.net/uxa/**YOUR_TAG_ID**.js"; document.getElementsByTagName("head")[0].appendChild(mt); })(); </script>`

Crazy Egg

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.crazyeggId = '**account_id**'; (tarteaucitron.job = tarteaucitron.job || []).push('crazyegg'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> setTimeout(function(){var a=document.createElement("script"); var b=document.getElementsByTagName("script")[0]; a.src=document.location.protocol+"//dnn506yrbagrg.cloudfront.net/pages/scripts/**account_id**.js?"+Math.floor(new Date().getTime()/3600000); a.async=true;a.type="text/javascript";b.parentNode.insertBefore(a,b)}, 1); </script>`

eTracker

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.etracker = '**data-secure-code**'; (tarteaucitron.job = tarteaucitron.job || []).push('etracker'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script id="_etLoader" type="text/javascript" charset="UTF-8" data-secure-code="**data-secure-code**" src="//static.etracker.com/code/e.js"></script>`

Eulerian (not officially supported)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.eulerianHost = '**host**'; (tarteaucitron.job = tarteaucitron.job || []).push('eulerian'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function(e,a){var i=e.length,y=5381,k='script',s=window,v=document,o=v.createElement(k);for(;i;){i-=1;y=(y*33)^e.charCodeAt(i)}y='_EA_'+(y>>>=0);(function(e,a,s,y){s[a]=s[a]||function(){(s[y]=s[y]||[]).push(arguments);s[y].eah=e;};}(e,a,s,y));i=new Date/1E7|0;o.ea=y;y=i%26;o.async=1;o.src='//'+e+'/'+String.fromCharCode(97+y,122-y,65+y)+(i%1E3)+'.js?2';s=v.getElementsByTagName(k)[0];s.parentNode.insertBefore(o,s);}) ('**host**','EA_push'); EA_push(); </script>`

Firebase

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.firebaseApiKey = '**APIKEY**'; (tarteaucitron.job = tarteaucitron.job || []).push('firebase'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.firebaseAuthDomain = '**AUTHDOMAIN**';tarteaucitron.user.firebaseDatabaseUrl = '**DATABASEURL**';tarteaucitron.user.firebaseProjectId = '**PROJECTID**';tarteaucitron.user.firebaseStorageBucket = '**STORAGEBUCKET**';tarteaucitron.user.firebaseAppId = '**APPID**';tarteaucitron.user.firebaseMeasurementId = '**MEASUREMENTID**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://www.gstatic.com/firebasejs/8.6.2/firebase-app.js"></script> <script src="https://www.gstatic.com/firebasejs/8.6.2/firebase-analytics.js"></script> <script> var firebaseConfig = { apiKey: "**APIKEY**", authDomain: "**AUTHDOMAIN**", databaseURL: "**DATABASEURL**", projectId: "**PROJECTID**", storageBucket: "**STORAGEBUCKET**", appId: "**APPID**", measurementId: "**MEASUREMENTID**", }; firebase.initializeApp(firebaseConfig); firebase.analytics(); </script>`

Force24

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.force24trackingId = '**trackingId**'; (tarteaucitron.job = tarteaucitron.job || []).push('force24'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.force24clientId = '**clientId**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function (f, o, r, c, e, _2, _4) { f.Force24Object = e, f[e] = f[e] || function () { f[e].q = f[e].q || [], f[e].q.push(arguments) }, f[e].l = 1 * new Date, _2 = o.createElement(r), _4 = o.getElementsByTagName(r)[0], _2.async = !0, _2.src = c, _4.parentNode.insertBefore(_2, _4) })(window, document, "script", "https://static.websites.data-crypt.com/scripts/activity/v3/inject-v3.min.js", "f24"); f24('config', 'set_tracking_id', '**trackingId**'); f24('config', 'set_client_id', '**clientId**'); </script>`

FreshSales (CRM)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.freshsalescrmId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('freshsalescrm'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="//eu.fw-cdn.com/**ID**.js" chat="false"></script>`

Get+

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.getplusId = '**ACCOUNT_ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('getplus'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var webleads_site_ids = webleads_site_ids || []; webleads_site_ids.push(**ACCOUNT_ID**); (function() { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = ( document.location.protocol == 'https:' ? 'https://stats.webleads-tracker.com/js' : 'http://stats.webleads-tracker.com/js' ); ( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild( s ); })(); </script>`

GetQuanty

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.getguanty = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('getquanty'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async defer src="https://get.smart-data-systems.com/gq?siteid=**id**&consent=1"></script>`

Google Analytics (ga.js)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.gajsUa = '**UA-XXXXXXXX-X**'; tarteaucitron.user.gajsMore = function () { /* ~~add here your optionnal _ga.push()~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('gajs'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> var _gaq = _gaq || []; _gaq.push(['_setAccount', '**UA-XXXXXXX-X**']); _gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })();~~// your optionnal _ga.push()~~</script>`

Google Analytics (GA4)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.gtagUa = '**G-XXXXXXXXX**';~~// tarteaucitron.user.gtagCrossdomain = ['example.com', 'example2.com'];~~ tarteaucitron.user.gtagMore = function () { /* ~~add here your optionnal gtag()~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('gtag'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async src="https://www.googletagmanager.com/gtag/js?id=**G-XXXXXXXXX**"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '**G-XXXXXXXXX**');~~// your optionnal gtag()~~</script>`

Google Analytics (gtag.js) \[for multiple UA\]

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.multiplegtagUa = **['UA-XXXXXXXX-X', 'UA-XXXXXXXX-X', 'UA-XXXXXXXX-X']**; (tarteaucitron.job = tarteaucitron.job || []).push('multiplegtag'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async src="https://www.googletagmanager.com/gtag/js?id=**UA-XXXXXXXX-X**"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '**UA-XXXXXXXX-X**');~~// your optionnal gtag()~~</script>`

Google Analytics (universal)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.analyticsUa = '**UA-XXXXXXXX-X**'; tarteaucitron.user.analyticsMore = function () { /* ~~optionnal ga.push()~~ */ }; tarteaucitron.user.analyticsUaCreate = { /* ~~optionnal create configuration~~ */ }; tarteaucitron.user.analyticsAnonymizeIp = true; tarteaucitron.user.analyticsPageView = { /* ~~optionnal pageview configuration~~ */ }; tarteaucitron.user.analyticsMore = function () { /* ~~optionnal ga.push()~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('analytics'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','//www.google-analytics.com/analytics.js','ga'); ga('create', '**UA-XXXXXXXX-X**', 'auto'); ga('send', 'pageview');~~// your optionnal ga.push()~~</script>`

Hotjar

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('hotjar'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script type="text/javascript">tarteaucitron.user.hotjarId = **hotjarId**;tarteaucitron.user.HotjarSv = **HotjarSv**;</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(h,o,t,j,a,r){ h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)}; h._hjSettings={hjid:**hotjarId**,hjsv:**HotjarSv**}; a=o.getElementsByTagName('head')[0]; r=o.createElement('script');r.async=1; r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv; a.appendChild(r); })(window,document,'//static.hotjar.com/c/hotjar-','.js?sv='); </script>`

Hubspot

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.hubspotId = '**API_KEY**'; (tarteaucitron.job = tarteaucitron.job || []).push('hubspot'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.hubspotBusinessUnitId = '**UNIT_ID**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/**API_KEY**.js?businessUnitId=**UNIT_ID**"></script>`

Kameleoon

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.kameleoon = '**kameleoon_id**'; (tarteaucitron.job = tarteaucitron.job || []).push('kameleoon'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://**kameleoon_id**.kameleoon.eu/kameleoon.js"></script>`

Koban

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.kobanurl = '**KOBAN_URL**'; (tarteaucitron.job = tarteaucitron.job || []).push('koban'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.kobanapi = '**KOBAN_API**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function (i, s, o, g, r, a, m) { i['KobanObject'] = r; i[r] = i[r] || function () { (i[r].q = i[r].q || []).push(arguments) }, i[r].l = 1 * new Date(); a = s.createElement(o), m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m) }) (window, document, 'script', '**KOBAN_URL**', 'kb'); kb('reg', '**KOBAN_API**'); </script>`

Kompass

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.kompassId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('kompass'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async defer src="https://fr.kompass.com/leads/script.js?id=**ID**"></script>`

Leadinfo

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.leadinfoId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('leadinfo'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(l,e,a,d,i,n,f,o){if(!l[i]){l.GlobalLeadinfoNamespace=l.GlobalLeadinfoNamespace||[]; l.GlobalLeadinfoNamespace.push(i);l[i]=function(){(l[i].q=l[i].q||[]).push(arguments)};l[i].t=l[i].t||n; l[i].q=l[i].q||[];o=e.createElement(a);f=e.getElementsByTagName(a)[0];o.async=1;o.src=d;f.parentNode.insertBefore(o,f);} }(window,document,"script","https://cdn.leadinfo.net/ping.js","leadinfo","**ID**")); </script>`

Matomo (privacy by design)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.matomoId = **SITE_ID**; (tarteaucitron.job = tarteaucitron.job || []).push('matomo'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.matomoHost = '**YOUR_MATOMO_URL**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _paq = _paq || []; _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); (function() { var u="**YOUR_MATOMO_URL**"; _paq.push(['setTrackerUrl', u+'piwik.php']); _paq.push(['setSiteId', **SITE_ID**]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s); })(); </script>`

Matomo Cloud (heatmap)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('matomoheatmap'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> window._paq = window._paq || []; _paq.push(['HeatmapSessionRecording::enable']); </script>`

Matomo Cloud (privacy by design)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.matomoId = **SITE_ID**; (tarteaucitron.job = tarteaucitron.job || []).push('matomocloud'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.matomoHost = '**YOUR_MATOMO_URL**';tarteaucitron.user.matomoCustomJSPath = '**JS_PATH**';tarteaucitron.user.matomoDontTrackPageView = **dontTrackPageView (false | true)**;tarteaucitron.user.matomoFullTracking = **fullTracking (true | false)**;</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _paq = _paq || []; _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); (function() { var u="**YOUR_MATOMO_URL**"; _paq.push(['setTrackerUrl', u+'matomo.php']); _paq.push(['setSiteId', **SITE_ID**]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.async=true; g.defer=true; g.src='**JS_PATH**'; s.parentNode.insertBefore(g,s); })(); </script>`

Mautic

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.mauticurl = '**mautic_url**'; (tarteaucitron.job = tarteaucitron.job || []).push('mautic'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n; w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t), m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m) })(window,document,'script','**mautic_url**','mt'); mt('send', 'pageview'); </script>`

MicroAnalytics

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.microanalyticsID = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('microanalytics'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script data-host="https://microanalytics.io/" data-dnt="false" src="https://microanalytics.io/js/script.js" id="**ID**" async defer></script>`

Microsoft Campaign Analytics

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('microsoftcampaignanalytics'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script type="text/javascript">tarteaucitron.user.microsoftcampaignanalyticsUUID = '**UUID**';tarteaucitron.user.microsoftcampaignanalyticsdomaineId = '**domainId**';tarteaucitron.user.microsoftcampaignanalyticsactionId = '**actionId**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> if (!window.mstag) mstag = {loadTag : function(){},time : (new Date()).getTime()};</script> <script id="mstag_tops" type="text/javascript" src="https://flex.atdmt.com/mstag/site/**UUID**/mstag.js"></script> <script class="conversionmsn" type="text/javascript"> mstag.loadTag("analytics", {dedup:"1",domainId:"**domainId**",type:"1",actionid:"**actionId**"})</script>`

Mixpanel

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('mixpanel'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function (f, b) { if (!b.__SV) { var e, g, i, h; window.mixpanel = b; b._i = []; b.init = function (e, f, c) { function g(a, d) { var b = d.split("."); 2 == b.length && ((a = a[b[0]]), (d = b[1])); a[d] = function () { a.push([d].concat(Array.prototype.slice.call(arguments, 0))); }; } var a = b; "undefined" !== typeof c ? (a = b[c] = []) : (c = "mixpanel"); a.people = a.people || []; a.toString = function (a) { var d = "mixpanel"; "mixpanel" !== c && (d += "." + c); a || (d += " (stub)"); return d; }; a.people.toString = function () { return a.toString(1) + ".people (stub)"; }; i = "disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking start_batch_senders people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split( " "); for (h = 0; h < i.length; h++) g(a, i[h]); var j = "set set_once union unset remove delete".split(" "); a.get_group = function () { function b(c) { d[c] = function () { call2_args = arguments; call2 = [c].concat(Array.prototype.slice.call(call2_args, 0)); a.push([e, call2]); }; } for ( var d = {}, e = ["get_group"].concat( Array.prototype.slice.call(arguments, 0)), c = 0; c < j.length; c++) b(j[c]); return d; }; b._i.push([e, f, c]); }; b.__SV = 1.2; e = f.createElement("script"); e.type = "text/javascript"; e.async = !0; e.src = "undefined" !== typeof MIXPANEL_CUSTOM_LIB_URL ? MIXPANEL_CUSTOM_LIB_URL : "file:" === f.location.protocol && "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//) ? "https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js" : "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js"; g = f.getElementsByTagName("script")[0]; g.parentNode.insertBefore(e, g); } })(document, window.mixpanel || []); </script>`

Open Web Analytics

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.openwebanalyticsHost = '**HOST**'; (tarteaucitron.job = tarteaucitron.job || []).push('openwebanalytics'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.openwebanalyticsId = '**SITE_ID**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<!-- Start Open Web Analytics Tracker --> <script type='text/javascript'> //<![CDATA[ var owa_baseUrl = '**HOST**'; var owa_cmds = owa_cmds || []; owa_cmds.push(['setDebug', true]); owa_cmds.push(['setSiteId', '**SITE_ID**']); owa_cmds.push(['trackPageView']); owa_cmds.push(['trackClicks']); (function() { var _owa = document.createElement('script'); _owa.type = 'text/javascript'; _owa.async = true; owa_baseUrl = ('https:' == document.location.protocol ? window.owa_baseSecUrl || owa_baseUrl.replace(/http:/, 'https:') : owa_baseUrl ); _owa.src = owa_baseUrl + 'modules/base/js/owa.tracker-combined-min.js'; var _owa_s = document.getElementsByTagName('script')[0]; _owa_s.parentNode.insertBefore(_owa, _owa_s); }()); //]]> </script> <!-- End Open Web Analytics Code -->`

Pardot

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.piAId = '**piAId**'; (tarteaucitron.job = tarteaucitron.job || []).push('pardot'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.piCId = '**piCId**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> piAId = "**piAId**"; piCId = "**piCId**"; piHostname = "pi.pardot.com"; (function() { function async_load(){ var s = document.createElement('script'); s.type = 'text/javascript'; s.src = ('https:' == document.location.protocol ? 'https://pi' : 'http://cdn') + '.pardot.com/pd.js'; var c = document.getElementsByTagName('script')[0]; c.parentNode.insertBefore(s, c); } if(window.attachEvent) { window.attachEvent('onload', async_load); } else { window.addEventListener('load', async_load, false); } })(); </script>`

Piano Analytics

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.pianoSite = **siteId**; (tarteaucitron.job = tarteaucitron.job || []).push('pianoanalytics'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.pianoCollectDomain = '**collectDomain**';tarteaucitron.user.pianoSendData = **sendData (true | false)**;</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://tag.aticdn.net/piano-analytics.js"></script> <script type="text/javascript"> pa.setConfigurations({site:**siteId**, collectDomain:'**collectDomain**' });</script><script type="text/javascript"> pa.sendEvent('page.display', {'page' : 'page name', 'page_chapter1' : 'level 1', 'page_chapter2' : 'level 2', 'page_chapter3' : 'level 3' }); </script>`

Piwik Pro

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.piwikProId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('piwikpro'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.piwikProContainer = '**container**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript">(function(window, document, dataLayerName, id) {window[dataLayerName]=window[dataLayerName]||[],window[dataLayerName].push({start:(new Date).getTime(),event:"stg.start"});var scripts=document.getElementsByTagName('script')[0],tags=document.createElement('script');function stgCreateCookie(a,b,c){var d="";if(c){var e=new Date;e.setTime(e.getTime()+24*c*60*60*1e3),d="; expires="+e.toUTCString()}document.cookie=a+"="+b+d+"; path=/"}var isStgDebug=(window.location.href.match("stg_debug")||document.cookie.match("stg_debug"))&&!window.location.href.match("stg_disable_debug");stgCreateCookie("stg_debug",isStgDebug?1:"",isStgDebug?14:-1);var qP=[];dataLayerName!=="dataLayer"&&qP.push("data_layer_name="+dataLayerName),isStgDebug&&qP.push("stg_debug");var qPString=qP.length>0?("?"+qP.join("&")):"";tags.async=!0,tags.src=https://**container**.containers.piwik.pro/+id+".js"+qPString,scripts.parentNode.insertBefore(tags,scripts);!function(a,n,i){a[n]=a[n]||{};for(var c=0;c<i.length;c++)!function(i){a[n][i]=a[n][i]||{},a[n][i].api=a[n][i].api||function(){var a=[].slice.call(arguments,0);"string"==typeof a[0]&&window[dataLayerName].push({event:n+"."+i+":"+a[0],parameters:[].slice.call(arguments,1)})}}(i[c])}(window,"ppms",["tm","cm"]);})(window, document, 'dataLayer', '**ID**'); </script>`

Plausible

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.plausibleDomain = '**plausibleDomain**'; (tarteaucitron.job = tarteaucitron.job || []).push('plausible'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script defer data-domain="**plausibleDomain**" src="https://plausible.io/js/script.js"></script>`

Plezi

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.pleziTenant = '**pleziTenant**'; (tarteaucitron.job = tarteaucitron.job || []).push('plezi'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.pleziTw = '**pleziTw**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type='text/javascript' async src='https://app.plezi.co/scripts/ossleads_analytics.js?tenant='**pleziTenant**'&tw='**pleziTw**''></script>`

SharpSpring

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.ssAccount = '**ACCOUNT**'; (tarteaucitron.job = tarteaucitron.job || []).push('sharpspring'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.ssId = '**ID**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _ss = _ss || []; _ss.push(['_setDomain', 'https://**ID**.marketingautomation.services/net']); _ss.push(['_setAccount', '**ACCOUNT**']); _ss.push(['_trackPageView']); window._pa = window._pa || {}; (function() { var ss = document.createElement('script'); ss.type = 'text/javascript'; ss.async = true; ss.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + '**ID**.marketingautomation.services/client/ss.js?ver=2.4.0'; var scr = document.getElementsByTagName('script')[0]; scr.parentNode.insertBefore(ss, scr); })(); </script>`

Shinystat

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.shinystatUser = '**user**'; (tarteaucitron.job = tarteaucitron.job || []).push('shinystat'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="//codice.shinystat.com/cgi-bin/getcod.cgi?USER=**user**"></script>`

Simple Analytics (privacy by design)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('simpleanalytics'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async defer src="https://scripts.simpleanalyticscdn.com/latest.js"></script> <noscript><img src="https://queue.simpleanalyticscdn.com/noscript.gif" alt="" /></noscript>`

Snapchat

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.snapchatId = '**snapchatId**'; tarteaucitron.user.snapchatMore = function () { /* ~~add here your optionnal snapchat function~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('snapchat'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.snapchatEmail = '**snapchatEmail**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type='text/javascript'> (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function() {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)}; a.queue=[];var s='script';r=t.createElement(s);r.async=!0; r.src=n;var u=t.getElementsByTagName(s)[0]; u.parentNode.insertBefore(r,u);})(window,document, 'https://sc-static.net/scevent.min.js'); snaptr('init', '**snapchatId**', { 'user_email': '**snapchatEmail**' }); snaptr('track', 'PAGE_VIEW'); </script>`

StatCounter

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('statcounter'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="statcounter-canvas"></div><script type="text/javascript">var sc_project = **sc_project**, sc_invisible = **sc_invisible (0 | 1)**, sc_security = "**sc_security**", sc_text = **sc_text (0 | 2 | 3 | 4 | 5)**;</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var scJsHost = (("https:" == document.location.protocol) ? "https://secure." : "http://www."); document.write("<sc"+"ript type='text/javascript' src='" + scJsHost+ "statcounter.com/counter/counter.js'></"+"script>"); </script>`

TeamBrain

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.teambrainUrl = '**URL**'; (tarteaucitron.job = tarteaucitron.job || []).push('teambrain'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.teambrainProxyUrl = '**PROXY**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script id="tb-ext-app" data-proxy-url="**PROXY**" src="**URL**" ></script>`

Tiktok

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.tiktokId = '**tiktokId**'; tarteaucitron.user.tiktokMore = function () { /* ~~add here your optionnal tiktok function~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('tiktok'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function() { var ta = document.createElement('script'); ta.type = 'text/javascript'; ta.async = true; ta.src = 'https://analytics.tiktok.com/i18n/pixel/sdk.js?sdkid=**tiktokId**'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ta, s); })(); </script>`

User.com

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.userId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('usercom'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.userApiKey = '**apiKey**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> window.civchat = { apiKey: '**apiKey**', }; </script> <script data-cfasync="false" type="text/javascript" src="https://**ID**.user.com/widget.js"> </script>`

UserPilot

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.userpilotToken = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('userpilot'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>window.userpilotSettings = {token: "**ID**"}; </script><script src = "https://js.userpilot.io/sdk/latest.js"></script>`

Verizon Dot Tag

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.verizondottagProjectId = '**verizon_project_id**'; (tarteaucitron.job = tarteaucitron.job || []).push('verizondottag'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.verizondottagPixelId = '**verizon_pixel_id**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript">(function(w,d,t,r,u){w[u]=w[u]||[];w[u].push({properties:{pixelId:"**verizon_pixel_id**"}, projectId:"**verizon_project_id**"});var s=d.createElement(t);s.src=r;s.async=true;s.onload=s.onreadystatechange=function(){var y,rs=this.readyState,c=w[u];if(rs&&rs!="complete"&&rs!="loaded"){return}try{y=YAHOO.ywa.I13N.fireBeacon;w[u]=[];w[u].push=function(p){y([p])};y(c)}catch(e){}};var scr=d.getElementsByTagName(t)[0],par=scr.parentNode;par.insertBefore(s,scr)})(window,document,"script","https://s.yimg.com/wi/ytc.js","dotq"); </script>`

Visiblee

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.visibleeclientid = '**CLIENTID**'; (tarteaucitron.job = tarteaucitron.job || []).push('visiblee'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.visibleedomain = '**DOMAIN_COM**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="//www.link-page.info/tracking_**CLIENTID**.js"></script>`

VisualRevenue

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.visualrevenueId = **ID**; (tarteaucitron.job = tarteaucitron.job || []).push('visualrevenue'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _vrq = _vrq || []; _vrq.push(['id', **ID**]); _vrq.push(['automate', true]); _vrq.push(['track', function(){}]); (function(d, a){ var s = d.createElement(a), x = d.getElementsByTagName(a)[0]; s.async = true; s.src = 'http://a.visualrevenue.com/vrs.js'; x.parentNode.insertBefore(s, x); })(document, 'script'); </script>`

Webmecanik

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.webmecanikurl = '**webmecanikurl**'; (tarteaucitron.job = tarteaucitron.job || []).push('webmecanik'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(w, d, t, u, n, a, m) { w['MauticTrackingObject'] = n; w[n] = w[n] || function() { (w[n].q = w[n].q || []).push(arguments) }, a = d.createElement(t), m = d.getElementsByTagName(t)[0]; a.async = 1; a.src = u; m.parentNode.insertBefore(a, m) })(window, document, 'script', '**webmecanikurl**', 'mt'); mt('send', 'pageview'); </script>`

Weborama

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('weborama'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://cstatic.weborama.fr/js/advertiserv2/adperf_conversion.js"></script>`

Woopra

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.woopraDomain = '**woopraDomain**'; (tarteaucitron.job = tarteaucitron.job || []).push('woopra'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(){ var t,i,e,n=window,o=document,a=arguments,s="script",r=["config","track","identify","visit","push","call","trackForm","trackClick"],c=function(){var t,i=this;for(i._e=[],t=0;r.length>t;t++)(function(t){i[t]=function(){return i._e.push([t].concat(Array.prototype.slice.call(arguments,0))),i}})(r[t])};for(n._w=n._w||{},t=0;a.length>t;t++)n._w[a[t]]=n[a[t]]=n[a[t]]||new c;i=o.createElement(s),i.async=1,i.src="//static.woopra.com/js/w.js",e=o.getElementsByTagName(s)[0],e.parentNode.insertBefore(i,e) })("woopra"); woopra.config({ domain: **woopraDomain** }); woopra.track(); </script>`

Wysistat

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('wysistat'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script type="text/javascript">tarteaucitron.user.wysistat = {"cli": "**nom**", "frm": "**frame**", "prm": "**prm**", "ce": "**compteurExtranet**", "page": "**page**", "roi": "**roi**", "prof": "**profiling**", "cpt": "**compte**"};</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<!--STATTAG--> <script type="text/javascript">var valeur=0;</script> <script type="text/javascript" src="https://www.wysistat.com/statistique.js";></script> <script type="text/javascript">if (valeur==1){stat("**nom**","**frame**","**prm**","**compteurExtranet**","**page**","**roi**","**profiling**","**compte**");}</script> <noscript><a href="[https://www.wysistat.com"]http://www.wysistat.com"; title="mesure audience internet">mesure d'audience</a></noscript> <!--/STATTAG-->`

Wysistat (privacy by design)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.wysistatNom = '**nom**'; (tarteaucitron.job = tarteaucitron.job || []).push('wysistathightrack'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _wsq = _wsq || []; _wsq.push(['_setNom', '**nom**']); _wsq.push(['_wysistat']); (function(){ var ws = document.createElement('script'); ws.type = 'text/javascript'; ws.async = true; ws.src = ('https:' == document.location.protocol ? 'https://www/' : 'http://www/') + '.wysistat.com/ws.jsa'; var s = document.getElementsByTagName('script')[0]||document.getElementsByTagName('body')[0]; s.parentNode.insertBefore(ws, s); })(); </script>`

Xiti

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.xitiId = '**YOUR-ID**'; tarteaucitron.user.xitiMore = function () { /* ~~add here your optionnal xiti function~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('xiti'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> Xt_param = 's=**YOUR-ID**&p='; try {Xt_r = top.document.referrer;} catch(e) {Xt_r = document.referrer; } Xt_h = new Date(); Xt_i = '<img width="39" height="25" border="0" alt="" '; Xt_i += 'src="http://logv3.xiti.com/hit.xiti?'+Xt_param; Xt_i += '&hl='+Xt_h.getHours()+'x'+Xt_h.getMinutes()+'x'+Xt_h.getSeconds(); if(parseFloat(navigator.appVersion)>=4) {Xt_s=screen;Xt_i+='&r='+Xt_s.width+'x'+Xt_s.height+'x'+Xt_s.pixelDepth+'x'+Xt_s.colorDepth;} document.write(Xt_i+'&ref='+Xt_r.replace(/[<>"]/g, '').replace(/&/g, '$')+'" title="Internet Audience">'); </script>`

Yandex Metrica

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.yandexmetrica = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('metrica'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" > (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)}; m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)}) (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym"); ym(**id**, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); </script> <noscript><div><img src="https://mc.yandex.ru/watch/**id**" style="position:absolute; left:-9999px;" alt="" /></div></noscript>`

Zoho PageSense

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.zohoPageSenseProjectId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('zohopagesense'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.zohoPageSenseScriptHash = '**hash**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://cdn-eu.pagesense.io/js/**id**/**hash**.js"></script>`

##### Publicité

Active Campaign

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.actid = '**actid**'; (tarteaucitron.job = tarteaucitron.job || []).push('activecampaign'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var trackcmp_email = ''; var trackcmp = document.createElement("script"); trackcmp.async = true; trackcmp.type = 'text/javascript'; trackcmp.src = '//trackcmp.net/visit?actid=**actid**&e='+encodeURIComponent(trackcmp_email)+'&r='+encodeURIComponent(document.referrer)+'&u='+encodeURIComponent(window.location.href); var trackcmp_s = document.getElementsByTagName("script"); if (trackcmp_s.length) { trackcmp_s[0].parentNode.appendChild(trackcmp); } else { var trackcmp_h = document.getElementsByTagName("head"); trackcmp_h.length && trackcmp_h[0].appendChild(trackcmp); } </script>`

Ad Up Technology (ads)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('aduptech_ads'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_static-ads_  
`<div class="aduptech_ads" data-placementKey="**PLACEMENT_KEY**"></div>`_response-ads_  
`<div class="aduptech_ads" style="width:**WIDTH**;height:**HEIGHT**;" data-responsive="1" data-placementKey="**PLACEMENT_KEY**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`// static ads (synchronous) <script type="text/javascript" src="https://s.d.adup-tech.com/ads/display.js?p=PLACEMENT_KEY"> </script> // static ads (asynchronous) <div id="adup1"></div> <script type="text/javascript"> window.uAd_init = function() { window.uAd.embed("adup1", { placementKey: "PLACEMENT_KEY", responsive: false }); }; if (typeof window.uAd === "object") window.uAd_init(); else (function(d, t) { var g = d.createElement(t), s = d.getElementsByTagName(t)[0]; g.src = "https://s.d.adup-tech.com/jsapi"; g.async = true; s.parentNode.insertBefore(g, s); }(document, "script")); </script> // responsive ads (synchronous) <div style="width:WIDTH;height:HEIGHT;"> <script type="text/javascript" src="https://s.d.adup-tech.com/ads/responsive.js?p=PLACEMENT_KEY"> </script> </div> // responsive ads (asynchronous) <div id="adup1" style="width:WIDTH;height:HEIGHT;"></div> <script type="text/javascript"> window.uAd_init = function() { window.uAd.embed("adup1", { placementKey: "PLACEMENT_KEY", responsive: true }); }; if (typeof window.uAd === "object") window.uAd_init(); else (function(d, t) { var g = d.createElement(t), s = d.getElementsByTagName(t)[0]; g.src = "https://s.d.adup-tech.com/jsapi"; g.async = true; s.parentNode.insertBefore(g, s); }(document, "script")); </script>`

Ad Up Technology (conversion)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('aduptech_conversion'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="aduptech_conversion" data-advertiserId="**ADVERTISER_ID**" data-conversionCode="**CONVERSION_CODE**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<img src="https://d.adup-tech.com/campaign/conversion/**ADVERTISER_ID**?t=**CONVERSION_CODE**" width="1px" height="1px" border="0px" alt="" />`

Ad Up Technology (retargeting)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('aduptech_retargeting'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="aduptech_retargeting" data-account="**ACCOUNT_ID**" product='["**PRODUCT_ID1**", "**PRODUCT_ID2**"]' data-track="productList" ></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://s.d.adup-tech.com/services/retargeting.js" async="true"></script> <script type="text/javascript"> window.AdUpRetargeting = function(api) { api.setAccount(**ACCOUNT_ID**) .setProduct(["**PRODUCT_ID1**", "**PRODUCT_ID2**"]) .trackProductList(); }; </script>`

Adform

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.adformpm = **adformpm**; (tarteaucitron.job = tarteaucitron.job || []).push('adform'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.adformpagename = '**adformpagename**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _adftrack = { pm: **adformpm**, divider: encodeURIComponent('|'), pagename: encodeURIComponent('**adformpagename**') }; (function () { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//track.adform.net/serving/scripts/trackpoint/async/'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })(); </script> <noscript> <p style="margin:0;padding:0;border:0;"> <img src="//track.adform.net/Serving/TrackPoint/?pm=**adformpm**&ADFPageName=WebsiteName|SectionName|SubSection|PageName&ADFdivider=|" width="1" height="1" alt="" /> </p> </noscript>`

Affilae

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.affilae = '**PID**'; (tarteaucitron.job = tarteaucitron.job || []).push('affilae'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _ae = { "pid":"**PID**", }; (function() { var element = document.createElement('script'); element.type = 'text/javascript'; element.async = true; element.src = '//static.affilae.com/ae-v3.5.js'; var scr = document.getElementsByTagName('script')[0]; scr.parentNode.insertBefore(element, scr); })(); </script>`

Amazon

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('amazon'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="amazon_product" data-amazonid="**xxxxx-xx**" data-productid="**product_id**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe style="width:120px;height:240px;" marginwidth="0" marginheight="0" scrolling="no" frameborder="0" src="//ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=FR&source=ss&ref=ss_til&ad_type=product_link&tracking_id=**amazon_id**&marketplace=amazon&region=FR&placement=**product_id**&show_border=true&link_opens_in_new_window=true"></iframe>`

antvoice

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.antvoiceId = '**antvoiceId**'; (tarteaucitron.job = tarteaucitron.job || []).push('antvoice'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> window.avDataLayer = window.avDataLayer || []; window.avtag = window.avtag || function(_cmd,_p){window.avDataLayer.push({cmd:_cmd,p:_p});} avtag('init', {id:'**antvoiceId**'}); </script><script async src="https://static.avads.net/avtag.min.js"></script>`

Bing Ads Universal Event Tracking

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.bingadsID = '**bingadsID**'; (tarteaucitron.job = tarteaucitron.job || []).push('bingads'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"**bingadsID**"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>`

CJ.com

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.cjUserId = '**TAG_ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('cjcom'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<!-- BEGIN CJ TRACKING CODE - DO NOT MODIFY --> <script type="text/javascript"> (function(a,b,c,d){ a='//www.mczbf.com/tags/**TAG_ID**/tag.js'; b=document;c='script';d=b.createElement(c);d.src=a; d.type='text/java'+c;d.async=true; d.id='cjapitag'; a=b.getElementsByTagName(c)[0];a.parentNode.insertBefore(d,a) })(); </script> <!-- END CJ TRACKING CODE -->`

Click Dimensions

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.clickdimensionsAccountKey = '**accountKey**'; (tarteaucitron.job = tarteaucitron.job || []).push('clickdimensions'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.clickdimensionsDomain = '**domain**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://analytics-eu.clickdimensions.com/ts.js" > </script> <script type="text/javascript"> var cdAnalytics = new clickdimensions.Analytics('analytics-eu.clickdimensions.com'); cdAnalytics.setAccountKey('**accountKey**'); cdAnalytics.setDomain('**domain**'); cdAnalytics.setScore(typeof(cdScore) == "undefined" ? 0 : (cdScore == 0 ? null : cdScore)); </script>`

Clicmanager

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('clicmanager'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="clicmanager-canvas" data-c="**c**" data-s="**s**" data-t="**t**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="http://ads.clicmanager.fr/exe.php?c=**c**&s=**s**&t=**t**&q="></script>`

Criteo

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('criteo'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="criteo-canvas" data-zoneid="**zoneid**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> document.MAX_ct0 =''; var m3_u = (location.protocol=='https:'?'https://cas.criteo.com/delivery/ajs.php?':'http://cas.criteo.com/delivery/ajs.php?'); var m3_r = Math.floor(Math.random()*99999999999); document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u); document.write ("zoneid=**zoneid**");document.write("&amp;nodis=1"); document.write ('&amp;cb=' + m3_r); if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used); document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : '')); document.write ("&amp;loc=" + escape(window.location)); if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer)); if (document.context) document.write ("&context=" + escape(document.context)); if ((typeof(document.MAX_ct0) != 'undefined') && (document.MAX_ct0.substring(0,4) == 'http')) { document.write ("&amp;ct0=" + escape(document.MAX_ct0)); } if (document.mmm_fo) document.write ("&amp;mmm_fo=1"); document.write ("'></scr"+"ipt>"); </script>`

Criteo OneTag

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.criteoonetagAccount = **account**; (tarteaucitron.job = tarteaucitron.job || []).push('criteoonetag'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script> <script type="text/javascript"> window.criteo_q = window.criteo_q || []; window.criteo_q.push( { event: "setAccount", account: **account**}, ); </script>`

Dating Affiliation

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('datingaffiliation'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="datingaffiliation-canvas" data-comfrom="**data-comfrom**" data-r="**data-r**" data-p="**data-p**" data-cf0="**data-cf0**" data-langue="**data-langue**" data-forwardAffiliate="**data-forwardAffiliate**" data-cf2="**data-cf2**" data-cfsa2="**data-cfsa2**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="http://www.tools-affil2.com/rotaban/ban.php?comfrom=**data-comfrom**&r=**data-r**&p=**data-p**&cf0=**data-cf0**&langue=**data-langue**&forward_affiliate=**data-forwardAffiliate**&cf2=**data-cf2**&cfsa2=**data-cfsa2**" width="**width**" height="**height**" frameborder="0" marginheight="0" marginwidth="0" scrolling="no"></iframe>`

Dating Affiliation (popup)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('datingaffiliationpopup'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="datingaffiliationpopup-canvas" data-comfrom="**comfrom**" data-promo="**promo**" data-productid="**productid**" data-submitconfig="**submitconfig**" data-ur="**ur**" data-brand="**brand**" lang="**lang**" data-cf0="**cf0**" data-cf2="**cf2**" data-subid1="**subid1**" data-cfsa2="**cfsa2**" data-subid2="**subid2**" data-nicheid="**nicheid**" data-degreid="**degreid**" data-bt="**bt**" data-vis="**vis**" data-hid="**hid**" data-snd="**snd**" data-aabd="**aabd**" data-aabs="**aabs**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="http://www.promotools.biz/da/popunder/script.php?comfrom=**comfrom**&promo=**promo**&productid=**productid**&submitconfig=**submitconfig**&ur=**ur**&brand=**brand**&lang=**lang**&cf0=**cf0**&cf2=**cf2**&subid1=**subid1**&cfsa2=**cfsa2**&subid2=**subid2**&nicheid=**nicheid**&degreid=**degreid**&bt=**bt**&vis=**vis**&hid=**hid**&snd=**snd**&aabd=**aabd**&aabs=**aabs**"></script>`

DoubleClick

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('doubleclick'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="doubleclick_container" data-id1="**data-id1**" data-id2="**data-id2**" data-type="**data-type**" data-cat="**data-cat**" data-item="**data-item**" data-quantity="**data-quantity**" data-price="**data-price**" data-postage="**data-postage**" data-seller="**data-seller**" data-gdpr="**data-gdpr**" data-gdpr-consent="**data-gdpr-consent**" data-ord="**data-ord**" data-num="**data-num**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://**data-id1**.fls.doubleclick.net/activityi;src=**data-id2**;type=**data-type**;cat=**data-cat**;item=**data-item**;quantity=**data-quantity**;price=**data-price**;postage=**data-postage**;seller=**data-seller**;gdpr=**data-gdpr**;gdpr_consent=**data-gdpr-consent**;ord=**data-ord**;num=**data-num**;?" width="1" height="1" frameborder="0" style="display:none"></iframe>`

Equativ

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.equativId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('equativ'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="application/javascript" src="https://ced.sascdn.com/tag/**ID**/smart.js" async></script>`

Eskimi

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.eskimiInit = '**init**'; (tarteaucitron.job = tarteaucitron.job || []).push('eskimi'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> !function(f,e,t,u,n,s,p) {if(f.esk)return;n=f.esk=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f.___esk)f.___esk=n;n.push=n;n.loaded=!0;n.queue=[];s=e.createElement(t);s.async=!0;s.src=u;p=e.getElementsByTagName(t)[0];p.parentNode.insertBefore(s,p)}(window,document,'script', '[https://dsp-media.eskimi.com/assets/js/e/gtr.min.js?_=0.0.0.4'](https://dsp-media.eskimi.com/assets/js/e/gtr.min.js?_=0.0.0.4%27)); esk('init', '**init**'); </script>`

Golden Bees

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.goldenbeesId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('goldenbees'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript">var gbJsScript = document.createElement("script");gbJsScript.src = "https://cdn.goldenbees.fr/proxy?url=http%3A%2F%2Fstatic.goldenbees.fr%2Fcdn%2Fjs%2Fgtag%2Fgoldentag-min.js&attachment=0";document.head.appendChild(gbJsScript);gbJsScript.addEventListener("load", function() {var gbTag=GbTagBuilder.build("**ID**");gbTag.fire();})</script>`

Google Ads

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.googleadsId = '**AW-XXXXXXXXX**'; (tarteaucitron.job = tarteaucitron.job || []).push('googleads'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async src="https://www.googletagmanager.com/gtag/js?id=**AW-XXXXXXXXX**"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '**AW-XXXXXXXXX**'); </script>`

Google Adsense

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('adsense'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<ins class="adsbygoogle" style="display:inline-block;width:**width**px;height:**height**px" data-ad-client="**ca_pub_xxxxxxxxxxxxxxx**" data-ad-slot="**ad_slot**"></ins><script type="text/javascript">(adsbygoogle = window.adsbygoogle || []).push({});</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>`

Google Adsense Automatic

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.adsensecapub = '**caPub**'; (tarteaucitron.job = tarteaucitron.job || []).push('adsenseauto'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=**caPub**" crossorigin="anonymous"></script>`

Google Adsense Search

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('adsensesearch'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div id="afscontainer1"></div> <script type="text/javascript" charset="utf-8"> (function(g,o){g[o]=g[o]||function(){(g[o]['q']=g[o]['q']||[]).push(arguments)},g[o]['t']=1*new Date})(window,'_googCsa'); var pageOptions = { 'pubId' : 'test client ID', // Enter your own client-ID here 'query' : 'flowers', // User query for this page 'styleId': '7824176615' // Enter your own style ID here }; var adblock1 = { 'container' : 'afscontainer1', 'width' : 700 }; var adblock2 = { 'container' : 'afscontainer2', 'width' : 700 }; _googCsa('ads', pageOptions, adblock1, adblock2); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async="async" src="https://www.google.com/adsense/search/ads.js"></script>`

Google Adsense Search (form)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('adsensesearchform'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<form action="**url_destination**" id="cse-search-box" target="**target (_self | _blank)**"><div><input type="hidden" name="cx" value="**partner-pub-XXXXXXXXXXXX:XXXXXX**" /><input type="hidden" name="ie" value="UTF-8" /><input type="text" name="q" size="25" /><input type="submit" name="sa" value="Search" /></div></form>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=cse-search-box&amp;lang=en"></script>`

Google Adsense Search (result)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.adsensesearchresultCx = '**partner-pub-XXXXXXXXXXXXX:XXXXXXX**'; (tarteaucitron.job = tarteaucitron.job || []).push('adsensesearchresult'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<gcse:searchresults-only id="gcse_searchresults"></gcse:searchresults-only>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function() { var cx = '**partner-pub-XXXXXXXXXXXXX:XXXXXXX**'; var gcse = document.createElement('script'); gcse.type = 'text/javascript'; gcse.async = true; gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + '//www.google.com/cse/cse.js?cx=' + cx; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(gcse, s); })(); </script>`

Google Adwords (conversion)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('googleadwordsconversion'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script type="text/javascript">tarteaucitron.user.adwordsconversionId = '**id**';tarteaucitron.user.adwordsconversionLabel = '**label**';tarteaucitron.user.adwordsconversionLanguage = '**language**';tarteaucitron.user.adwordsconversionFormat = '**format**';tarteaucitron.user.adwordsconversionColor = '**color**';tarteaucitron.user.adwordsconversionValue = '**value**';tarteaucitron.user.adwordsconversionCurrency = '**currency**';tarteaucitron.user.adwordsconversionCustom1 = '**custom1**';tarteaucitron.user.adwordsconversionCustom2 = '**custom2**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var google_conversion_id = "**id**"; var google_conversion_label = "**label**"; var google_conversion_language = "**language**"; var google_conversion_format = "**format**"; var google_conversion_color = "**color**"; var google_conversion_value = **value**; var google_conversion_currency = "**currency**"; var google_remarketing_only = false; </script> <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>`

Google Adwords (remarketing)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.adwordsremarketingId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('googleadwordsremarketing'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var google_conversion_id = "**id**"; var google_custom_params = window.google_tag_params; var google_remarketing_only = true; </script> <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>`

Google Partners Badge

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('googlepartners'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="g-partnersbadge" data-agency-id="**id**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://apis.google.com/js/platform.js" async defer></script>`

Klaviyo

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.klaviyoCompanyId = '**CompanyId**'; (tarteaucitron.job = tarteaucitron.job || []).push('klaviyo'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async type="text/javascript" src="//static.klaviyo.com/onsite/js/klaviyo.js?company_id=**CompanyId**"></script>`

Kwanko

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('kwanko'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_kwanko" data-mclic="**mclic**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<img src="https://action.metaffiliation.com/trk.php?mclic=**mclic**" width="1" height="1" border="0" />`

Lead Forensics

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.leadforensicsId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('leadforensics'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://secure.team8save.com/js/sc/**id**.js"></script>`

Linkedin Insight

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.linkedininsighttag = '**linkedin_partner_id**'; (tarteaucitron.job = tarteaucitron.job || []).push('linkedininsighttag'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"><!--//--><![CDATA[// ><!-- _linkedin_partner_id = "**linkedin_partner_id**"; window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || []; window._linkedin_data_partner_ids.push(_linkedin_partner_id); //--><!]]> </script><script type="text/javascript"><!--//--><![CDATA[// ><!-- (function(){var s = document.getElementsByTagName("script")[0]; var b = document.createElement("script"); b.type = "text/javascript";b.async = true; b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js"; s.parentNode.insertBefore(b, s);})(); //--><!]]> </script><noscript><img height="1" width="1" style="display:none;" alt="" src="https://dc.ads.linkedin.com/collect/?pid=311233&amp;fmt=gif" /> </noscript>`

MadMetrics

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.madmetricsHostname = '**hostname**'; (tarteaucitron.job = tarteaucitron.job || []).push('madmetrics'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.madmetricsClientId = **clientId**;tarteaucitron.user.madmetricsSiteId = **siteId**;tarteaucitron.user.madmetricsDirectId = **directId**;tarteaucitron.user.madmetricsReferalId = **referalId**;tarteaucitron.user.madmetricsLlmId = **llmId**;</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src=https://static.madmetrics.com/ktck_seo_acd_pv-min.js></script> <script> (function() { var clientId = **clientId**, siteId = **siteId**, directId = **directId**, referalId = **referalId**, llmId = **llmId**; _kTck = new KaTracker( clientId, siteId, directId, referalId, llmId ); _kTck.setBridge('https://**hostname**/k_redirect_md.php'); _kTck.track(); })(); </script>`

Outbrain

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('outbrain'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="OUTBRAIN" data-src="**PERMALINK**" data-widget-id="**ID**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://widgets.outbrain.com/outbrain.js"></script>`

Outbrain Amplify

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.outbrainamplifyId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('outbrainamplify'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> !function(_window, _document) { var OB_ADV_ID = '**id**'; if (_window.obApi) { var toArray = function(object) { return Object.prototype.toString.call(object) === '[object Array]' ? object : [object]; }; _window.obApi.marketerId = toArray(_window.obApi.marketerId).concat(toArray(OB_ADV_ID)); return; } var api = _window.obApi = function() { api.dispatch ? api.dispatch.apply(api, arguments) : api.queue.push(arguments); }; api.version = '1.1'; api.loaded = true; api.marketerId = OB_ADV_ID; api.queue = []; var tag = _document.createElement('script'); tag.async = true; tag.src = 'https://amplify.outbrain.com/cp/obtp.js'; tag.type = 'text/javascript'; var script = _document.getElementsByTagName('script')[0]; script.parentNode.insertBefore(tag, script); }(window, document); obApi('track', 'PAGE_VIEW'); </script>`

Pinterest Pixel

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.pinterestpixelId = '**pixelid**'; (tarteaucitron.job = tarteaucitron.job || []).push('pinterestpixel'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>!function(e){if(!window.pintrk){window.pintrk=function(){window.pintrk.queue.push( Array.prototype.slice.call(arguments))};var n=window.pintrk;n.queue=[],n.version="3.0";var t=document.createElement("script");t.async=!0,t.src=e;var r=document.getElementsByTagName("script")[0];r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js"); pintrk('load', '**pixelid**'); pintrk('page');</script>`

Piximedia

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.piximediaName = '**NAME**'; (tarteaucitron.job = tarteaucitron.job || []).push('piximedia'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.piximediaTag = '**TAG**';tarteaucitron.user.piximediaType = '**type (ACTIVITY | LEAD | CONVERSION)**';tarteaucitron.user.piximediaId = '**ID**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://ad.piximedia.com/tools/activity/?**NAME**||**TAG**|**type (ACTIVITY | LEAD | CONVERSION)**|**ID**|||||"></script>`

Prelinker

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('prelinker'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="prelinker-canvas" data-siteId="**siteId**" data-bannerId="**bannerId**" data-defaultLanguage="**defaultLanguage**" data-tracker="**tracker**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//promo.easy-dating.org/banner/index?site_id=**siteId**&banner_id=**bannerId**&default_language=**defaultLanguage**&tr4ck=**tracker**"></script>`

Pubdirecte

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('pubdirecte'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="pubdirecte-canvas" data-pid="**id**" data-ref="**ref**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="http://www.pubdirecte.com/script/banniere.php?id=**id**&ref=**ref**"></script>`

Reddit

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.redditInit = '**INIT**'; (tarteaucitron.job = tarteaucitron.job || []).push('reddit'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.redditAAID = '**AAID**';tarteaucitron.user.redditExternalId = '**EXTERNAL_ID**';tarteaucitron.user.redditIDFA = '**IDFA**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> !function(w,d){if(!w.rdt){var p=w.rdt=function(){p.sendEvent?p.sendEvent.apply(p,arguments):p.callQueue.push(arguments)};p.callQueue=[];var t=d.createElement("script");t.src="https://www.redditstatic.com/ads/pixel.js",t.async=!0;var s=d.getElementsByTagName("script")[0];s.parentNode.insertBefore(t,s)}}(window,document);rdt('init','**INIT**', {"aaid":"**AAID**","externalId":"**EXTERNAL_ID**","idfa":"**IDFA**"});rdt('track', 'PageVisit'); </script>`

ShareASale

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('shareasale'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="shareasale-canvas" data-amount="**amount**" data-tracking="**tracking**" data-transtype="**transtype**" data-persale="**persale**" data-perlead="**perlead**" data-perhit="**perhit**" data-merchantID="**merchantID**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<img src="https://shareasale.com/sale.cfm?amount=**amount**&tracking=**tracking**&transtype=**transtype**&persale=**persale**&perlead=**perlead**&perhit=**perhit**&merchantID=**merchantID**" width=1 height=1>`

Skaze

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.skazeIdentifier = '**SITE_IDENTIFIER**'; tarteaucitron.user.skazeMore = function () { /* ~~add here your optionnal skaze.pushEvent~~ */ }; (tarteaucitron.job = tarteaucitron.job || []).push('skaze'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async="async" src="//events.sk.ht/**SITE_IDENTIFIER**/lib.js"></script> <script> var skaze = skaze || {}; skaze.cmd = skaze.cmd || []; skaze.cmd.push(function() { skaze.init({ siteIdentifier : "XXX" }); skaze.pushEvent({ name : "YYY", properties : { xxx: "yyy", } }); }); </script>`

Teads

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.teadsBuyerPixelId = **BUYER_PIXEL_ID**; (tarteaucitron.job = tarteaucitron.job || []).push('teads'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://p.teads.tv/teads-fellow.js" async="true"></script> <script> window.teads_e = window.teads_e || []; window.teads_buyer_pixel_id = **BUYER_PIXEL_ID**; </script>`

TheTradeDesk

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.thetradedeskAdvertiserId = '**ADVERTISER_ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('thetradedesk'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.thetradedeskUpixelId = '**UPIXEL_ID**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://js.adsrvr.org/up_loader.1.1.0.js" type="text/javascript"></script> <script type="text/javascript"> ttd_dom_ready( function() { if (typeof TTDUniversalPixelApi === 'function') { var universalPixelApi = new TTDUniversalPixelApi(); universalPixelApi.init("**ADVERTISER_ID**", ["**UPIXEL_ID**"], "https://insight.adsrvr.org/track/up"); } }); </script>`

Twenga

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('twenga'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script type="text/javascript">tarteaucitron.user.twengaId = **id**;tarteaucitron.user.twengaLocale = '**locale**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//tracker.twenga.**locale**/st/tracker_**id**.js"></script>`

vShop

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('vshop'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="vcashW" style="width: **width**px; height: **height**px;" data-key="**key**" data-tracking="**zone**" data-category="**category**" data-keyword="**keyword**" data-layout="**layout (small | medium | big)**" data-theme="**theme (shadow | circle)**" data-linkColor="**link_color**" data-textColor="**text_color**" data-backgroundColor="**background_color**" data-borderColor="**border_color**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="http://vshop.fr/js/w.js"></script>`

X (formerly Twitter) Universal Website Tag

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.twitteruwtId = '**twitter_uwt_Id**'; (tarteaucitron.job = tarteaucitron.job || []).push('twitteruwt'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script><!--//--><![CDATA[// ><!-- !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments); },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js', a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script'); // Insert Twitter Pixel ID and Standard Event data below twq('init','**twitter_uwt_Id**'); twq('track','PageView'); //--><!]]> </script>`

Xandr (Conversion)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('xandrconversion'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div id="**uniqId**" data-xandrconversionId="**xandrconversionId**" data-xandrconversionSeg="**xandrconversionSeg**" data-xandrconversionOrderId="**xandrconversionOrderId**" data-xandrconversionValue="**xandrconversionValue**" data-xandrconversionRedir="**xandrconversionRedir**" data-xandrconversionOther="**xandrconversionOther**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<img src="//ib.adnxs.com/px?t=2&id=**xandrconversionId**&seg=**xandrconversionSeg**&order_id=**xandrconversionOrderId**&value=**xandrconversionValue**&redir=**xandrconversionRedir**&other=**xandrconversionOther**" width="1" height="1" />`

Xandr (Segment)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('xandrsegment'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div id="**uniqId**" data-xandrsegmentAdd="**xandrsegmentAdd**" data-xandrsegmentAddCode="**xandrsegmentAddCode**" data-xandrsegmentRemove="**xandrsegmentRemove**" data-xandrsegmentRemoveCode="**xandrsegmentRemoveCode**" data-xandrsegmentMember="**xandrsegmentMember**" data-xandrsegmentRedir="**xandrsegmentRedir**" data-xandrsegmentValue="**xandrsegmentValue**" data-xandrsegmentOther="**xandrsegmentOther**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<img src="//ib.adnxs.com/seg?t=2&add=**xandrsegmentAdd**&add_code=**xandrsegmentAddCode**&remove=**xandrsegmentRemove**&remove_code=**xandrsegmentRemoveCode**&member=**xandrsegmentMember**&redir=**xandrsegmentRedir**&value=**xandrsegmentValue**&other=**xandrsegmentOther**" width="1" height="1" />`

Xandr (Universal)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.xandrId = '**xandrId**'; (tarteaucitron.job = tarteaucitron.job || []).push('xandr'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>!function(e,i){if(!e.pixie){var n=e.pixie=function(e,i,a){n.actionQueue.push({action:e,actionValue:i,params:a})};n.actionQueue=[];var a=i.createElement("script");a.async=!0,a.src="//acdn.adnxs.com/dmp/up/pixie.js";var t=i.getElementsByTagName("head")[0];t.insertBefore(a,t.firstChild)}}(window,document);pixie('init', '**xandrId**');pixie('event', 'PageView');</script>`

##### Réseaux sociaux

AddThis

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.addthisPubId = '**YOUR-PUB-ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('addthis'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="addthis_inline_share_toolbox"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=**YOUR-PUB-ID**"></script>`

AddToAny (feed)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.addtoanyfeedUri = '**feed_uri**'; (tarteaucitron.job = tarteaucitron.job || []).push('addtoanyfeed'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_subscribe-long_  
`<a class="a2a_dd" href="#" onclick="window.open(tarteaucitron.user.addtoanyfeedSubscribeLink)"><img src="//static.addtoany.com/buttons/subscribe_171_16.gif" data-width="171" data-height="16" data-border="0" alt="Subscribe"/></a>`_subscribe-small_  
`<a class="a2a_dd" href="#" onclick="window.open(tarteaucitron.user.addtoanyfeedSubscribeLink)"><img src="//static.addtoany.com/buttons/subscribe_120_16.gif" data-width="120" data-height="16" data-border="0" alt="Subscribe"/></a>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var a2a_config = a2a_config || {}; a2a_config.linkurl = "**feed_uri**"; </script> <script type="text/javascript" src="//static.addtoany.com/menu/feed.js"></script>`

AddToAny (share)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('addtoanyshare'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_share-long_  
`<a class="a2a_dd" href="https://www.addtoany.com/share_save"><img src="//static.addtoany.com/buttons/share_save_171_16.png" data-width="171" data-height="16" data-border="0" alt="Share"/></a>`_share-small_  
`<a class="a2a_dd" href="https://www.addtoany.com/share_save"><img src="//static.addtoany.com/buttons/share_save_120_16.png" data-width="120" data-height="16" data-border="0" alt="Share"/></a>`_share-link_  
`<span class="tac_addtoanyshare"></span><div class="a2a_kit a2a_default_style"><a class="a2a_dd" href="https://www.addtoany.com/share_save">Share</a><span class="a2a_divider"></span><a class="a2a_button_facebook"></a><a class="a2a_button_twitter"></a><a class="a2a_button_google_plus"></a></div>`_share-button_  
`<span class="tac_addtoanyshare"></span><div class="a2a_kit a2a_kit_size_32 a2a_default_style"><a class="a2a_dd" href="https://www.addtoany.com/share_save"></a><a class="a2a_button_facebook"></a><a class="a2a_button_twitter"></a><a class="a2a_button_google_plus"></a></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="//static.addtoany.com/menu/page.js"></script>`

Discord (Server Widget)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('discord'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="discord_widget" data-width="**width**" data-height="**height**" data-guildID="**guildID**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://discord.com/widget?id=**guildID**"></iframe>`

eKomi

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.ekomiCertId = '**CERT-ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('ekomi'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function(){ eKomiIntegrationConfig = new Array( {certId:'**CERT-ID**'} ); if(typeof eKomiIntegrationConfig != "undefined"){for(var eKomiIntegrationLoop=0;eKomiIntegrationLoop<eKomiIntegrationConfig.length;eKomiIntegrationLoop++){ var eKomiIntegrationContainer = document.createElement('script'); eKomiIntegrationContainer.type = 'text/javascript'; eKomiIntegrationContainer.defer = true; eKomiIntegrationContainer.src = (document.location.protocol=='https:'?'https:':'http:') +"//connect.ekomi.de/integration_1410173009/" + eKomiIntegrationConfig[eKomiIntegrationLoop].certId + ".js"; document.getElementsByTagName("head")[0].appendChild(eKomiIntegrationContainer); }}else{if('console' in window){ console.error('connectEkomiIntegration - Cannot read eKomiIntegrationConfig'); }} })(); </script>`

Facebook

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('facebook'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_like\_share-inline-t2_  
`<div class="fb-like" data-layout="standard" data-action="like" data-share="true"></div>`_like-inline-t2_  
`<div class="fb-like" data-layout="standard" data-action="like" data-share="false"></div>`_like\_share-bubble-t2_  
`<div class="fb-like" data-layout="box_count" data-action="like" data-share="true"></div>`_like-bubble-t2_  
`<div class="fb-like" data-layout="box_count" data-action="like" data-share="false"></div>`_like\_share-horizontal-t2_  
`<div class="fb-like" data-layout="button_count" data-action="like" data-share="true"></div>`_like-horizontal-t2_  
`<div class="fb-like" data-layout="button_count" data-action="like" data-share="false"></div>`_like\_share-none-t2_  
`<div class="fb-like" data-layout="button" data-action="like" data-share="true"></div>`_like-none-t2_  
`<div class="fb-like" data-layout="button" data-action="like" data-share="false"></div>`_recommend\_share-inline-t2_  
`<div class="fb-like" data-layout="standard" data-action="recommend" data-share="true"></div>`_recommend-inline-t2_  
`<div class="fb-like" data-layout="standard" data-action="recommend" data-share="false"></div>`_recommend\_share-bubble-t2_  
`<div class="fb-like" data-layout="box_count" data-action="recommend" data-share="true"></div>`_recommend-bubble-t2_  
`<div class="fb-like" data-layout="box_count" data-action="recommend" data-share="false"></div>`_recommend\_share-horizontal-t2_  
`<div class="fb-like" data-layout="button_count" data-action="recommend" data-share="true"></div>`_recommend-horizontal-t2_  
`<div class="fb-like" data-layout="button_count" data-action="recommend" data-share="false"></div>`_recommend\_share-none-t2_  
`<div class="fb-like" data-layout="button" data-action="recommend" data-share="true"></div>`_recommend-none-t2_  
`<div class="fb-like" data-layout="button" data-action="recommend" data-share="false"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.0"; fjs.parentNode.insertBefore(js, fjs); } (document, 'script', 'facebook-jssdk')); </script>`

Facebook (Customer Chat)

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.facebookChatID = **ID**; (tarteaucitron.job = tarteaucitron.job || []).push('facebookcustomerchat'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="fb-customerchat"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js'; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk')); </script>`

Facebook (like box)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('facebooklikebox'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_likebox-light_  
`<div class="fb-like-box" data-href="**page_url**" data-width="**width**" data-height="**height**" data-colorscheme="light" data-show-faces="**faces (true | false)**" data-header="**header (true | false)**" data-stream="**posts (false | true)**" data-show-border="**border (true | false)**"></div>`_likebox-dark_  
`<div class="fb-like-box" style="background:#141823" data-href="**page_url**" data-width="**width**" data-height="**height**" data-colorscheme="dark" data-show-faces="**faces (true | false)**" data-header="**header (true | false)**" data-stream="**posts (false | true)**" data-show-border="**border (true | false)**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.0"; fjs.parentNode.insertBefore(js, fjs); } (document, 'script', 'facebook-jssdk')); </script>`

Facebook (post)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('facebookpost'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_facebookpost" data-appId="**appId**" data-url="**url**" data-show-text="**show_text (true|false)**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe allowtransparency="true" scrolling="auto" src="https://www.facebook.com/plugins/post.php?href=**url**&amp;width=**width**&amp;show_text=**show_text**&amp;appId=**appId**&amp;height=**height**"></iframe>`

Facebook Pixel

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.facebookpixelId = '**YOUR-ID**'; tarteaucitron.user.facebookpixelMore = function () { /* add here your optionnal facebook pixel function */ }; (tarteaucitron.job = tarteaucitron.job || []).push('facebookpixel'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> !function(f,b,e,v,n,t,s) { if(f.fbq) return; n=f.fbq=function(){ n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments); }; if(!f._fbq) f._fbq=n; n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[]; t=b.createElement(e); t.async=!0; t.src=v; s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s); } (window, document,'script', 'https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '**YOUR-ID**'); fbq('track', 'PageView'); </script>`

Google+

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gplus'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_plusone-inline-t0_  
`<div class="g-plusone" data-size="small" data-annotation="inline" data-width="300"></div>`_plusone-inline-t1_  
`<div class="g-plusone" data-size="medium" data-annotation="inline" data-width="300"></div>`_plusone-inline-t2_  
`<div class="g-plusone" data-annotation="inline" data-width="300"></div>`_plusone-inline-t3_  
`<div class="g-plusone" data-size="tall" data-annotation="inline" data-width="300"></div>`_plusone-horizontal-t0_  
`<div class="g-plusone" data-size="small"></div>`_plusone-horizontal-t1_  
`<div class="g-plusone" data-size="medium"></div>`_plusone-horizontal-t2_  
`<div class="g-plusone"></div>`_plusone-bubble-t3_  
`<div class="g-plusone" data-size="tall"></div>`_plusone-none-t0_  
`<div class="g-plusone" data-size="small" data-annotation="none"></div>`_plusone-none-t1_  
`<div class="g-plusone" data-size="medium" data-annotation="none"></div>`_plusone-none-t2_  
`<div class="g-plusone" data-annotation="none"></div>`_plusone-none-t3_  
`<div class="g-plusone" data-size="tall" data-annotation="none"></div>`_share-inline-t0_  
`<div class="g-plus" data-action="share" data-height="15"></div>`_share-inline-t1_  
`<div class="g-plus" data-action="share"></div>`_share-inline-t3_  
`<div class="g-plus" data-action="share" data-height="24"></div>`_share-horizontal-t0_  
`<div class="g-plus" data-action="share" data-annotation="bubble" data-height="15"></div>`_share-horizontal-t1_  
`<div class="g-plus" data-action="share" data-annotation="bubble"></div>`_share-horizontal-t3_  
`<div class="g-plus" data-action="share" data-annotation="bubble" data-height="24"></div>`_share-bubble-t2_  
`<div class="g-plus" data-action="share" data-annotation="vertical-bubble" data-height="60"></div>`_share-none-t0_  
`<div class="g-plus" data-action="share" data-annotation="none" data-height="15"></div>`_share-none-t1_  
`<div class="g-plus" data-action="share" data-annotation="none"></div>`_share-none-t3_  
`<div class="g-plus" data-action="share" data-annotation="none" data-height="24"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://apis.google.com/js/platform.js" async defer> {lang: 'fr'} </script>`

Google+ (badge)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('gplusbadge'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_page-portrait-light_  
`<div class="g-page" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="publisher" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`_page-portrait-dark_  
`<div class="g-page" data-theme="dark" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="publisher" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`_page-landscape-light_  
`<div class="g-page" data-layout="landscape" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="publisher" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`_page-landscape-dark_  
`<div class="g-page" data-layout="landscape" data-theme="dark" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="publisher" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`_profil-portrait-light_  
`<div class="g-person" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="author" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`_profil-portrait-dark_  
`<div class="g-person" data-theme="dark" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="author" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`_profil-landscape-light_  
`<div class="g-person" data-layout="landscape" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="author" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`_profil-landscape-dark_  
`<div class="g-person" data-layout="landscape" data-theme="dark" data-width="**width**" data-href="//plus.google.com/u/0/**page_id**" data-rel="author" data-showtagline="**description (true | false)**" data-showcoverphoto="**cover (true | false)**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://apis.google.com/js/platform.js" async defer> {lang: 'fr'} </script>`

Instagram

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('instagram'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="instagram_post" data-postID="**postID**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="//www.instagram.com/**postID**/embed" width="**width**" height="**height**" frameborder="0"></iframe>`

Linkedin

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('linkedin'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_share-bubble-t2_  
`<span class="tacLinkedin"></span><script type="IN/Share" data-counter="top"></script>`_share-horizontal-t2_  
`<span class="tacLinkedin"></span><script type="IN/Share" data-counter="right"></script>`_share-none-t2_  
`<span class="tacLinkedin"></span><script type="IN/Share"></script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US </script>`

Pinterest

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('pinterest'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_pin\_rectangular\_white-none-t0_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-color="white"></a>`_pin\_rectangular\_gray-none-t0_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-color="gray"></a>`_pin\_rectangular\_red-none-t0_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-color="red"></a>`_pin\_rectangular\_white-none-t3_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-color="white" data-pin-height="28"></a>`_pin\_rectangular\_gray-none-t3_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-color="gray" data-pin-height="28"></a>`_pin\_rectangular\_red-none-t3_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-color="red" data-pin-height="28"></a>`_pin\_circular-none-t0_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-shape="round"></a>`_pin\_circular-none-t3_  
`<span class="tacPinterest"></span><a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" data-pin-shape="round" data-pin-height="32"></a>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark"> <img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" /> </a> <script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script>`

Shareaholic

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.shareaholicSiteId = '**site_id**'; (tarteaucitron.job = tarteaucitron.job || []).push('shareaholic'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class='shareaholic-canvas' data-app='share_buttons' data-app-id='**app_id**'></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function() { var shr = document.createElement('script'); shr.setAttribute('data-cfasync', 'false'); shr.src = '//dsms0mj1bbhn4.cloudfront.net/assets/pub/shareaholic.js'; shr.type = 'text/javascript'; shr.async = 'true'; shr.onload = shr.onreadystatechange = function() { var rs = this.readyState; if (rs && rs != 'complete' && rs != 'loaded') return; var site_id = '**site_id**'; try { Shareaholic.init(site_id); } catch (e) {} }; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(shr, s); })(); </script>`

ShareThis

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.sharethisPublisher = '**publisher**'; (tarteaucitron.job = tarteaucitron.job || []).push('sharethis'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<span class="tacSharethis"></span>**services_list_spans**`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var switchTo5x=true; </script> <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script> <script type="text/javascript">stLight.options({publisher: "**publisher**", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>`

ShareThis Sticky

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.sharethisStickyProperty = '**property**'; (tarteaucitron.job = tarteaucitron.job || []).push('sharethissticky'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=**property**&product=sticky-share-buttons' async='async'></script>`

X (formerly Twitter)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('twitter'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_tweet-bubble-t2_  
`<span class="tacTwitter"></span><a href="https://twitter.com/share" class="twitter-share-button" data-via="**twitter_username**" data-count="vertical" data-dnt="true"></a>`_tweet-horizontal-t2_  
`<span class="tacTwitter"></span><a href="https://twitter.com/share" class="twitter-share-button" data-via="**twitter_username**" data-count="horizontal" data-dnt="true"></a>`_tweet-none-t2_  
`<span class="tacTwitter"></span><a href="https://twitter.com/share" class="twitter-share-button" data-via="**twitter_username**" data-count="none" data-dnt="true"></a>`_tweet-bubble-t3_  
`<span class="tacTwitter"></span><a data-size="large" href="https://twitter.com/share" class="twitter-share-button" data-via="**twitter_username**" data-count="vertical" data-dnt="true"></a>`_tweet-horizontal-t3_  
`<span class="tacTwitter"></span><a data-size="large" href="https://twitter.com/share" class="twitter-share-button" data-via="**twitter_username**" data-count="horizontal" data-dnt="true"></a>`_tweet-none-t3_  
`<span class="tacTwitter"></span><a data-size="large" href="https://twitter.com/share" class="twitter-share-button" data-via="**twitter_username**" data-count="none" data-dnt="true"></a>`_follow-horizontal-t2_  
`<span class="tacTwitter"></span><a href="https://twitter.com/**twitter_username**" class="twitter-follow-button" data-show-count="horizontal" data-dnt="true"></a>`_follow-none-t2_  
`<span class="tacTwitter"></span><a href="https://twitter.com/**twitter_username**" class="twitter-follow-button" data-show-count="false" data-dnt="true"></a>`_follow-horizontal-t3_  
`<span class="tacTwitter"></span><a href="https://twitter.com/**twitter_username**" class="twitter-follow-button" data-show-count="horizontal" data-size="large" data-dnt="true"></a>`_follow-none-t3_  
`<span class="tacTwitter"></span><a href="https://twitter.com/**twitter_username**" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true"></a>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s); js.id=id; js.src=p+'://platform.twitter.com/widgets.js'; fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>`

X (formerly Twitter) cards

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('twitterembed'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="twitterembed-canvas" data-tweetid="**tweet_id**" data-width="**width**" data-theme="**theme (light | dark)**" data-cards="**cards (show | hidden)**" data-conversation="**conversation (show | none)**" data-align="**align (left | center | right)**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s); js.id=id; js.src=p+'://platform.twitter.com/widgets.js'; fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>`

X (formerly Twitter) timelines

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('twittertimeline'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<span class="tacTwitterTimelines"></span><a class="twitter-timeline" href="**twitter_url**" data-tweet-limit="**tweet-limit**" data-dnt="**dnt (true | false)**" data-width="**width**" data-height="**height**" data-theme="**theme (dark | light)**" data-link-color="**hex link-color**"></a>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s); js.id=id; js.src=p+'://platform.twitter.com/widgets.js'; fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>`

##### Support

Brevo Conversations

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.brevoConversationsId = '**conversationsId**'; (tarteaucitron.job = tarteaucitron.job || []).push('brevochat'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> (function(d, w, c) { w.BrevoConversationsID = '**conversationsId**'; w[c] = w[c] || function() { (w[c].q = w[c].q || []).push(arguments); }; var s = d.createElement('script'); s.async = true; s.src = 'https://conversations-widget.brevo.com/brevo-conversations.js'; if (d.head) d.head.appendChild(s); })(document, window, 'BrevoConversations'); </script>`

Dialog Insight

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.dialogInsightId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('dialoginsight'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src=https://t.ofsys.com/js/Journey/1/**ID**/DI.Journey-min.js></script>`

Elfsight

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('elfsight'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="elfsight-app-**elfsightKey**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://apps.elfsight.com/p/platform.js" defer></script>`

Intercom

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.intercomKey = '**intercomKey**'; (tarteaucitron.job = tarteaucitron.job || []).push('intercomChat'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function () { var w = window; var ic = w.Intercom; if (typeof ic === 'function') { ic('reattach_activator'); ic('update', w.intercomSettings); } else { var d = document; var i = function () { i.c(arguments); }; i.q = []; i.c = function (args) { i.q.push(args); }; w.Intercom = i; var l = function () { var s = d.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = 'https://widget.intercom.io/widget/**intercomKey**'; var x = d.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); }; if (w.attachEvent) { w.attachEvent('onload', l); } else { w.addEventListener('load', l, false); } } })();</script>`

Marker.io

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.markerioProjectId = '**PROJECT_ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('markerio'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> window.markerConfig = { project: '**PROJECT_ID**', source: 'snippet' }; !function(e,r,a){if(!e.__Marker){e.__Marker={};var t=[],n={__cs:t};["show","hide","isVisible","capture","cancelCapture","unload","reload","isExtensionInstalled","setReporter","setCustomData","on","off"].forEach(function(e){n[e]=function(){var r=Array.prototype.slice.call(arguments);r.unshift(e),t.push(r)}}),e.Marker=n;var s=r.createElement("script");s.async=1,s.src="https://edge.marker.io/latest/shim.js";var i=r.getElementsByTagName("script")[0];i.parentNode.insertBefore(s,i)}}(window,document); </script>`

Pipedrive

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.pipedriveCompany = **companyId**; (tarteaucitron.job = tarteaucitron.job || []).push('pipedrive'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.pipedriveCompany = '**playbookUuid**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script> window.pipedriveLeadboosterConfig = {base: 'leadbooster-chat.pipedrive.com',companyId: **companyId**,playbookUuid: '**playbookUuid**',version: 2};(function () {var w = window;if (w.LeadBooster) {console.warn('LeadBooster already exists');} else {w.LeadBooster = {q: [],on: function (n, h) {this.q.push({ t: 'o', n: n, h: h });},trigger: function (n) {this.q.push({ t: 't', n: n });},};}})();</script><script src="https://leadbooster-chat.pipedrive.com/assets/loader.js" async> <iframe role="complementary" scrolling="no" id="LeadboosterContainer" title="Chatbot" class="proactiveChat" style="height:140px !important"></iframe>`

PureChat

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.purechatId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('purechat'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" data-cfasync="false">(function () { var done = false;var script = document.createElement('script');script.async = true;script.type = 'text/javascript';script.src = 'https://app.purechat.com/VisitorWidget/WidgetScript';document.getElementsByTagName('HEAD').item(0).appendChild(script);script.onreadystatechange = script.onload = function (e) {if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {var w = new PCWidget({ c: '**ID**', f: true });done = true;}};})();</script>`

Screeb

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.screebId = '**id**'; (tarteaucitron.job = tarteaucitron.job || []).push('screeb'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.screebDontInit = **dontInit (false | true)**;</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function (s,c,r,ee,b) { s['ScreebObject']=r;s[r]=s[r]||function(){var d=arguments;return new Promise(function(a,b){(s[r].q=s[r].q||[]).push({v:1,args:d,ok:a,ko:b})})}; b=c.createElement('script');b.type='text/javascript'; b.id=r;b.src=ee;b.async=1;c.getElementsByTagName("head")[0].appendChild(b); }(window,document,'$screeb','https://t.screeb.app/tag.js')); $screeb('init', '**id**'); </script>`

Smartsupp

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.smartsuppKey = '**smartsuppKey**'; (tarteaucitron.job = tarteaucitron.job || []).push('smartsupp'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var _smartsupp = _smartsupp || {}; _smartsupp.key = '**smartsuppKey**'; window.smartsupp||(function(d) { var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[]; s=d.getElementsByTagName('script')[0];c=d.createElement('script'); c.type='text/javascript';c.charset='utf-8';c.async=true; c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s); })(document); </script>`

Studizz Chatbot

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.studizzToken = '**token**'; (tarteaucitron.job = tarteaucitron.job || []).push('studizz'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script href="https://webchat.studizz.fr/webchat.js?token=**token**"></script>`

Tawk.to chat

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.tawktoId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('tawkto'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<script>tarteaucitron.user.tawktoWidgetId = '**WidgetID**';</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript">var Tawk_API=Tawk_API||{}; var Tawk_LoadStart=new Date(); (function(){ var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0]; s1.async=true; s1.src='https://embed.tawk.to/**ID**/**WidgetID**'; s1.charset='UTF-8'; s1.setAttribute('crossorigin','*'); s0.parentNode.insertBefore(s1,s0); })();</script>`

Tolk.ai Genii

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.tolkaiGeniiProject = '**PROJECTID**'; (tarteaucitron.job = tarteaucitron.job || []).push('tolkaigenii'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="module" project-id="**PROJECTID**" template="widget" src="https://genii-script.tolk.ai/lightchat.js" id="lightchat-bot"> </script>`

Userlike

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.userlikeKey = '**userlikekey**'; (tarteaucitron.job = tarteaucitron.job || []).push('userlike'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript" src="https://userlike-cdn-widgets.s3-eu-west-1.amazonaws.com/**userlikekey**"></script>`

UserVoice

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.userVoiceApi = '**YOUR_API_KEY**'; (tarteaucitron.job = tarteaucitron.job || []).push('uservoice'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

_contact-none-t2_  
`<script type="text/javascript">UserVoice=window.UserVoice||[];UserVoice.push(['addTrigger', {mode: 'contact', trigger_position: '**position**', trigger_color: '**color**', trigger_background_color: '**background-color**', accent_color: '**accent_color**'}]);</script>`_smartvote-none-t2_  
`<script type="text/javascript">UserVoice=window.UserVoice||[];UserVoice.push(['addTrigger', {mode: 'smartvote', trigger_position: '**position**', trigger_color: '**color**', trigger_background_color: '**background-color**', accent_color: '**accent_color**'}]);</script>`_satisfaction-none-t2_  
`<script type="text/javascript">UserVoice=window.UserVoice||[];UserVoice.push(['addTrigger', {mode: 'satisfaction', trigger_position: '**position**', trigger_color: '**color**', trigger_background_color: '**background-color**', accent_color: '**accent_color**'}]);</script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> (function(){ var uv=document.createElement('script'); uv.type='text/javascript'; uv.async=true; uv.src='//widget.uservoice.com/**YOUR_API_KEY**.js'; var s=document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(uv,s) })(); </script>`

Weply

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.weplyId = '**ID**'; (tarteaucitron.job = tarteaucitron.job || []).push('weply'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script src="https://app.weply.chat/widget/**ID**" async></script>`

Zoho SalesIQ

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.zohoWidgetCode = '**WIDGET_CODE**'; (tarteaucitron.job = tarteaucitron.job || []).push('zoho'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> var $zoho=$zoho || {};$zoho.salesiq = $zoho.salesiq || {widgetcode:"**WIDGET_CODE**", values:{},ready:function(){}};var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;s.src="https://salesiq.zoho.eu/widget";t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);d.write("<div id='zsiqwidget'></div>"); </script>`

Zopim

###### ➕ Code à **ajouter** dans le code source du site

`<script> tarteaucitron.user.zopimID = '**zopim_id**'; (tarteaucitron.job = tarteaucitron.job || []).push('zopim'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script type="text/javascript"> window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s= d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set. _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute('charset','utf-8'); $.src='//v2.zopim.com/?**zopim_id**';z.t=+new Date;$. type='text/javascript';e.parentNode.insertBefore($,e)})(document,'script'); </script>`

##### Vidéo

Acast

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('acast'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="acast_embed" data-height="**height**" data-width="**width**" data-id1="**id1**" data-id2="**id2**" data-seek="**seek**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe height="**height**" width="**width**" src="https://embed.acast.com/**id1**/**id2**?seek=**seek**></iframe>`

Arte.tv

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('artetv'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="artetv_player" data-json="**video_json**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe style="transition-duration: 0; transition-property: no; margin: 0 auto; position: relative; display: block; background-color: #000000;" src="https://www.arte.tv/player/v5/index.php?json_url=**video_json**" width="**width**" height="**height**" frameborder="0" scrolling="no" allowfullscreen="allowfullscreen"></iframe>`

Ausha

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('ausha'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="ausha_player" data-height="**data-height**" data-podcast-id="**data-podcast-id**" data-player-id="**data-player-id**" data-playlist="**data-playlist**" data-color="**data-color**" data-useshowid="**useshowid (0 | 1)**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe frameborder="0" loading="lazy" id="ausha-Zhaw" height="**data-height**" style="border: none; width:100%; height:220px" src="https://player.ausha.co/index.html?podcastId=**data-podcast-id**&v=3&playerId=**data-player-id**"></iframe><script src="https://player.ausha.co/ausha-player.js"></script>`

Bandcamp

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('bandcamp'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="bandcamp_player" data-albumID="**album_id**" size="**size (small | large)**" data-artwork="**artwork (small | none)**" data-transparent="**transparent (true | false)**" data-minimal="**minimal (true | false)**" data-tracklist="**tracklist (true | false)**" data-linkcol="**linkcol**" data-package="**package**" data-bgcol="**bgcol**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://bandcamp.com/EmbeddedPlayer/album=**album_id**/" width="**width**" height="**height**" frameborder="0" scrolling="yes" allowfullscreen="allowfullscreen"></iframe>`

Calaméo

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('calameo'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="calameo-canvas" data-id="**bkcode**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="//v.calameo.com/?bkcode=**bkcode**" width="**width**" height="**height**" frameborder="0" scrolling="no" allowtransparency allowfullscreen style="margin:0 auto;"></iframe>`

Calaméo Library

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('calameolibrary'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="calameolibrary-canvas" data-id="**id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="//v.calameo.com/library/?type=subscription&id=**id**" width="**width**" height="**height**" frameborder="0" scrolling="no" allowtransparency allowfullscreen style="margin:0 auto;"></iframe>`

Canal-U.tv

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('canalu'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="canalu_player" data-videoTitle="**videoTitle**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://www.canal-u.tv/video/embed_code_plugin.1/**videoTitle**" frameborder="0" allowfullscreen></iframe>`

Dailymotion

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('dailymotion'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="dailymotion_player" data-videoID="**video_id**" data-width="**width**" data-height="**height**" data-showinfo="**showinfo (1 | 0)**" data-autoplay="**autoplay (0 | 1)**" data-embedType="**embedType (video | playlist)**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="//www.dailymotion.com/embed/video/**video_id**" frameborder="0" allowfullscreen></iframe>`

Deezer

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('deezer'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="deezer_player" data-deezerID="**deezerID**" data-theme="**theme (auto | dark | light)**" data-embedType="**type (album | track | playlist)**" data-radius="**radius (true | false)**" data-tracklist="**tracklist (true | false)**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="//widget.deezer.com/widget/**theme (auto | dark | light)**/**type (album | track | playlist)**/**deezerID**" width="**width**" height="**height**" frameborder="0" allowfullscreen></iframe>`

France Culture

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('fculture'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="fculture_embed" id="**id**" data-height="**height**" data-width="**width**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://www.franceculture.fr/player/export-reecouter?content=**id**" height="**height**" width="**width**"></iframe>`

Internet Archive

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('archive'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="archive_player" data-videoID="**videoID**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://archive.org/embed/**videoID**" width="**width**" height="**height**" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen></iframe>`

Issuu

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('issuu'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="issuu_player" data-issuuID="**your_issuu_id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe style="width:**width**; height:**height**;" src="//e.issuu.com/embed.html#**your_issue_id**" frameborder="0" allowfullscreen></iframe>`

Mixcloud

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('mixcloud'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="mixcloud_embed" data-height="**height**" data-width="**width**" data-hidecover="**hidecover (0 | 1)**" data-mini="**mini (0 | 1)**" data-light="**light (0 | 1)**" id="**id**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe height="**height**" width="**width**" src="https://www.mixcloud.com/widget/iframe/?hide_cover=**hidecover (0 | 1)**&mini=**mini (0 | 1)**&light=**light (0 | 1)**&feed=**id**"></iframe>`

PlayPlay

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('playplay'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_playplay" data-id="**id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://playplay.com/app/embed-video/**id**"></iframe>`

podCloud

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('podcloud'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_podcloud" data-url="**url**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe allowtransparency="true" scrolling="auto" src="**url**" style="width:**width**;height:**height**;border:none;"></iframe>`

Prezi

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('prezi'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="prezi-canvas" data-id="**slide_id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://prezi.com/embed/**slide_id**/?bgcolor=ffffff&amp;lock_to_path=1&amp;autoplay=0&amp;autohide_ctrls=0" allowfullscreen="" mozallowfullscreen="" webkitallowfullscreen="" frameborder="0" height="**height**" width="**width**"></iframe>`

SlideShare

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('slideshare'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="slideshare-canvas" data-id="**slide_id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="//www.slideshare.net/slideshow/embed_code/**slide_id**" width="**width**" height="**height**" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC; border-width:1px; margin-bottom:5px; max-width: 100%;" allowfullscreen></iframe>`

SoundCloud

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('soundcloud'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="soundcloud_player" data-playable-id="**track_or_playlist_id**" data-playable-type="**type (playlists|sets)**" data-height="**iframe_height**" data-color="**accent_color**" data-auto-play="**autoplay (true|false)**" data-hide-related="**hide related (true|false)**" data-show-comments="**show comment (true|false)**" data-show-user="**show user (true|false)**" data-show-reposts="**show repost (true|false)**" data-show-teaser="**show teaser (true|false)**" data-visual="**visual (true|false)**" data-artwork="**artwork (true|false)**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="100%" scrolling="no" frameborder="no" **autoplay (true|false)** src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/**type (playlists|sets)**/**track_or_playlist_id**&hide_related=**hide related (true|false)**&color=**accent_color**&auto_play=**autoplay (true|false)**&show_comments=**show comment (true|false)**&hide_related=**hide related (true|false)**&show_user=**show user (true|false)**&show_reposts=**show repost (true|false)**&show_teaser=**show teaser (true|false)**&visual=**visual (true|false)**&artwork=**artwork (true|false)**"></iframe>`

Spotify

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('spotify'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="spotify_player" data-spotifyID="**spotifyID**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="//open.spotify.com/embed/**spotifyID**" width="**width**" height="**height**" frameborder="0" allowfullscreen></iframe>`

Tiktok Video

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('tiktokvideo'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<blockquote class="tiktok-embed" data-video-id="**videoId**" style="max-width: 605px;min-width: 325px;" ><section></section></blockquote>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script async src="https://www.tiktok.com/embed.js"></script>`

Twitch

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('twitch'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="twitch_player" data-videoID="**video_id**" data-width="**width**" data-height="**height**" data-parent="**parent**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="https://player.twitch.tv/?video=**video_id**&parent=**parent**" frameborder="0" allowfullscreen></iframe>`

Videas

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('videas'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="tac_videas" data-id="**id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="100%" height="100%" src="https://app.videas.fr/embed/**id**/" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen="true"></iframe>`

Vimeo

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('vimeo'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="vimeo_player" data-videoID="**video_id**" data-width="**width**" data-height="**height**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="//player.vimeo.com/video/**video_id**" frameborder="0" allowfullscreen></iframe>`

WebTV Normandie Université

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('webtvnu'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="webtvnu_player" data-videoID="**videoID**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe src="https://webtv.normandie-univ.fr/permalink/**videoID**/iframe/" frameborder="0" allowfullscreen></iframe>`

Youtube

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('youtube'); </script>`  
  
###### ➕ Code à **ajouter (il est peut-être déjà présent)** dans le code source du site **à l'endroit où il doit s'afficher**

`<div class="youtube_player" data-videoID="**video_id**" data-width="**width**" data-height="**height**" data-theme="**theme (dark | light)**" data-rel="**rel (1 | 0)**" data-controls="**controls (1 | 0)**" data-showinfo="**showinfo (1 | 0)**" data-autoplay="**autoplay (0 | 1)**" data-mute="**mute (0 | 1)**" srcdoc="**srcdoc**" data-loop="**loop (0 | 1)**" data-loading="**loading (0 | 1)**" data-start="**start**" data-end="**end**"></div>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<iframe width="**width**" height="**height**" src="//www.youtube.com/embed/**video_id**" frameborder="0" allowfullscreen></iframe>`

Youtube (Js API)

###### ➕ Code à **ajouter** dans le code source du site

`<script> (tarteaucitron.job = tarteaucitron.job || []).push('youtubeapi'); </script>`  
  
###### 🗑️ Code à **supprimer** du code source du site

`<script>(function(){ var s = document.createElement("script"); s.src = "https://www.youtube.com/player_api"; var before = document.getElementsByTagName("script")[0]; before.parentNode.insertBefore(s, before); })();</script>`

Merci aux sponsors ❤️

🇫🇷 Amauri

## Mentions légales

* DPA
* Confidentialité
* CGV
* Mentions
* Statut