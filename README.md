iProspect Roots Wordpress Template!
==================================================

Projet wordpress vanille basé sur [Roots](http://roots.io/)

## Todo 
- Test ACF on homepage
- Facebook Fields
- Espace pour Googe Tag Manager Container
- Change Modernizr.load for yepnope (Modernizr.load will be deprecated in the near future)
..* Est-ce qu'on utilise yepnope au lieu de concatener?
- Faire un Grunt dans le child theme pour concatener les scripts du child et du parent (?)
- Write procedure to create child theme and accomplish common actions
..* adding a template
..* adding a stylesheet
..* adding a script
..* choosing a royal slider theme
- Script pour le transfert de Base de donnée (might not be needed)
- Intégrer GSCE (Google Search) (À voir avec Dave, ce n'est pas GSCE qui est sur mdanderson en ce moment)

## Prérequis
- Solution vanille réutilisable
- 100% Responsive
- Suivant la checklist QA

## Included Librairies 
- Font-Awesome
- Twitter Bootstrap 3
- Snap.js
- GASP TweenMax
- jQuery
- Modernizr
- Royal Slider

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

## Motivation
- Avoir un squelette Wordpress que les développeur peuvent utiliser pour commencer le développement d'un blog rapidement, sans avoir à faire trop de setup, comme downloader des plugins ou configurer le thème. 

## Workflow
1. Go to the iProspect Roots Template [Bitbucket project page](https://bitbucket.org/iprospect_ca/iprospect-roots-wordpress-template)
2. **Fork** the project into a new bitbucket repository, which will become your projects repository. If your are unfamiliar with Git forks, please read this short [article](https://help.github.com/articles/fork-a-repo).
3. **Clone** your new project into your local environment
4. In MAMP, **create a new virtual host** pointing to your project. Make sure to include your username somewhere in the domain name.
5. **Create a new database** for your project and import the lastest SQL Dump (DUMP folder)
6. In the **wp_options** table, change the **siteurl** and **home** values to your local URL 
7. **Modify wp-config.php** to include your DB information under the proper switch space. If your username is not in the switch, add it. 
8. You should now be able to see your local site
9. In your local repository, run `git add remote upstream https://bitbucket.org/iprospect_ca/iprospect-roots-wordpress-template.git`