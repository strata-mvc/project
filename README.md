# iProspect Roots Wordpress Template!

Projet wordpress vanille basé sur [Roots](http://roots.io/)

## Bugs

- Snap.js slides layout when RoyalSlider with touched

## Todo 
- Test ACF on homepage
- Facebook Fields
- Carousel Page d'Accueil en ACF (prendre code dans work hard play heart)
- Php Mobile Detect avec classque sur le body
- Fixer le css du split-view en francais
- Espace pour Google Tag Manager Container (remplace le GA dans le bas de scripts.php?)
- Change Modernizr.load for yepnope (Modernizr.load will be deprecated in the near future)
	* Est-ce qu'on utilise yepnope au lieu de concatener?
- Faire un Grunt dans le child theme pour concatener les scripts du child et du parent (?)
- Write procedure to create child theme and accomplish common actions
	- adding a template
	- adding a stylesheet
	- adding a script
	- choosing a royal slider theme
- Script pour le transfert de Base de donnée (might not be needed)
- Intégrer GSCE (Google Search) (À voir avec Dave, ce n'est pas GSCE qui est sur mdanderson en ce moment)
- Mettre wocoommerce et tester les différents templates de page.

## Prerequisites
- A clean Wordpress install packed with useful plugins and librairies
- 100% Responsive
- Meets the requirements of the Frontend and QA checklists
- Excellent score on PageSpeed 

## Install 

- **Fork the project** into a new Bitbucket repository. If your are unfamiliar with Git forks, please read this short [article](https://help.github.com/articles/fork-a-repo).
- **Clone your new project** and setup your local environment
- **Import the latest SQL Dump** from the DUMP folder.
- In the **wp_options** table, change the `siteurl` and `home` values to your local URL 
- **Modify wp-config.php** to include your DB information under the proper switch space. If your username is not in the switch, add it. e.g.:

```php
if( stristr( $_SERVER['SERVER_NAME'], "dlamarre" ) ) {
 	# LOCAL (Dave Lamarre) 
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
} else if ( stristr( $_SERVER['SERVER_NAME'], "yourlocalswitch" ) ) {
 	// Insert your config here
} 

```
- In your local repository, run `git add remote upstream https://bitbucket.org/iprospect_ca/iprospect-roots-wordpress-template.git`

## Getting started

If you are unfamiliar with the [Roots starter theme](http://roots.io/starter-theme/)



## Included Librairies 
- Font-Awesome
- Twitter Bootstrap 3
- Snap.js
- GASP TweenMax
- jQuery
- Modernizr
- Royal Slider
- Spin.js
- Masonry + ImagesLoaded

## Bundled Plugins
- AddThis
- Advanced Custom Fields
- Disable Comments
- Simple Page Ordering
- WordPress SEO
- WPML
- Gravity Forms
- Honeypot

## Node
- NPM
- Grunt
