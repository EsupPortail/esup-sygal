Migration vers Bootstrap 4
==========================

- passage à jquery 3.x ?

Remplacements
-------------

- .dl-horizontal => .row + grid column classes (or mixins) on its <dt> and <dd> children.
- .navbar-inverse, .navbar-header disparaissent
- .navbar-fixed-top => .fixed-top
- .navbar-toggle => .navbar-toggler
- nouvelles classes : .nav-link, .nav-item
- .input-group-addon => input-group-prepend ou input-group-append
- .col-xs-* => .col-*
- .panel => .card + .card-body (panel-primary supprimé, alternatiove à trouver https://getbootstrap.com/docs/4.0/components/card/#card-styles)
- glyphicon glyphicon-* => icon icon-* (nouveau dans unicaen/app)
- .table-condensed => .table-sm
- control-label => .col-form-label.
- .input-lg and .input-sm => .form-control-lg and .form-control-sm, respectively.
- .help-block => .form-text (https://getbootstrap.com/docs/4.0/migration/#forms-1)
- .form-horizontal supprimé : trouver une parade (https://getbootstrap.com/docs/4.0/migration/#forms-1)
- .has-error, .has-warning, and .has-success disparaissent : style à récupérer
- .btn-default => .btn-secondary
- .btn-xs => .btn-sm
- navbar : navbar-expand-md ajouté pour gestion du collapse, + navbar-dark bg-dark. Quid de .navbar-inverse ?
- .breadcrumb-item, is now required on the descendants of .breadcrumbs


NB
--

- "Added a new sm grid tier below 768px for more granular control. We now have xs, sm, md, lg, and xl. This also means 
every tier has been bumped up one level (so .col-md-6 in v3 is now .col-lg-6 in v4)."
(https://getbootstrap.com/docs/4.0/migration/#grid-system)

- màj bootstrap-confirmation : fait pour B4, mais pas de version pour B5 !
- màj bootstrap-datetimepicker nécessaire ? Non, unicaen/app fournit la dernière version 4.17.47 et elle semble pouvoir fonctionner avec B5.
- màj bootstrap-select ? Non, fonctionne avec B4 (ex: filtres de thèses).
- màj bootstrap-multiselect ? non, ça semble fonctionner (ex: filtre textuel de thèses)

- popover : option `sanitize: true` requise sinon pas de <table> possible das un popover ! 
- .page-header disparaît
- <blockquote> plus stylé : utiliser .blockquote and the .blockquote-reverse 
- nouveau : .table-inverse, .thead-default and .thead-inverse.
- disparaissent : $().button(string) and $().button('reset') 
- envisager de remplacer <div.navbar> par <nav>




SyGAL RAF
---------

- remplacer : $().button(string) and $().button('reset') https://getbootstrap.com/docs/4.0/migration/#buttons