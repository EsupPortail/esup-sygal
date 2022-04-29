Module UnicaenAvis
==================


Installation
------------

### PHP

```bash
composer require unicaen/avis
```

### Base de données

Cf. [Script SQL de création des objets](./data/sql/schema.sql)


Intégration
-----------

### Base de données

- Schéma

```sql
/*
drop table if exists rapport_activite_avis ;
*/

create sequence rapport_activite_avis_id_seq ;

create table rapport_activite_avis
(
    id bigint not null constraint rapport_activite_avis__pkey primary key default nextval('rapport_activite_avis_id_seq'),
    rapport_activite_id bigint not null constraint rapport_activite_avis__rapport__fk references rapport_activite,
    avis_id bigint not null constraint rapport_activite_avis__avis__fk references unicaen_avis
    ,
    histo_createur_id bigint not null,
    histo_modificateur_id bigint not null,
    histo_destructeur_id bigint,
    histo_creation timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modification timestamp default ('now'::text)::timestamp without time zone not null,
    histo_destruction timestamp
);

create index rapport_activite_avis__idx on rapport_activite_avis (id);
create index rapport_activite_avis__rapport_activite__idx on rapport_activite_avis (rapport_activite_id);
create index rapport_activite_avis__avis__idx on rapport_activite_avis (avis_id);

comment on table rapport_activite_avis is 'Avis à propos des rapports d''activité';
comment on column rapport_activite_avis.rapport_activite_id is 'Identifiant du rapport d''activité sur lequel porte cet avis';
comment on column rapport_activite_avis.avis_id is 'Identifiant de l''avis';
```

- Données

```sql



```
