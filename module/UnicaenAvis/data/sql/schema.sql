
-----------------------------------------------------------------------------------------------------------------
--                                             UnicaenAvis
-----------------------------------------------------------------------------------------------------------------


drop table if exists unicaen_avis_complem cascade ;
drop table if exists unicaen_avis cascade ;
drop table if exists unicaen_avis_type_valeur_complem cascade ;
drop table if exists unicaen_avis_type_valeur cascade ;
drop table if exists unicaen_avis_valeur cascade ;
drop table if exists unicaen_avis_type cascade ;
drop sequence if exists unicaen_avis_type_id_seq ;
drop sequence if exists unicaen_avis_valeur_id_seq ;
drop sequence if exists unicaen_avis_type_valeur_id_seq ;
drop sequence if exists unicaen_avis_type_valeur_complem_id_seq ;
drop sequence if exists unicaen_avis_id_seq ;
drop sequence if exists unicaen_avis_complem_id_seq ;


--
-- Types d'avis
--
create sequence unicaen_avis_type_id_seq ;

create table unicaen_avis_type
(
    id bigint not null constraint unicaen_avis_type__pkey primary key default nextval('unicaen_avis_type_id_seq'),
    code varchar(64) not null,
    libelle varchar(128) not null,
    description varchar(128),
    ordre smallint default 0 not null
);

create index unicaen_avis_type__idx on unicaen_avis_type (id);
create unique index unicaen_avis_type__un on unicaen_avis_type (code);

comment on table unicaen_avis_type is 'Types d''avis existants';
comment on column unicaen_avis_type.code is 'Code littéral unique de ce type d''avis';
comment on column unicaen_avis_type.libelle is 'Libellé de ce type d''avis';
comment on column unicaen_avis_type.description is 'Description éventuelle de ce type d''avis';
comment on column unicaen_avis_type.ordre is 'Entier permettant d''ordonner les types d''avis';

--
-- Valeurs d'avis possibles.
--
create sequence unicaen_avis_valeur_id_seq ;

create table unicaen_avis_valeur
(
    id bigint not null constraint unicaen_avis_valeur__pk primary key default nextval('unicaen_avis_valeur_id_seq'),
    code varchar(64) not null,
    valeur varchar(128) not null,
    valeur_bool boolean,
    tags varchar(64),
    ordre serial not null,
    description varchar(128)
);

create index unicaen_avis_valeur__idx on unicaen_avis_valeur (id);
create unique index unicaen_avis_valeur__un on unicaen_avis_valeur (code);

comment on table unicaen_avis_valeur is 'Valeurs d''avis possibles';
comment on column unicaen_avis_valeur.code is 'Code littéral unique de cette valeur';
comment on column unicaen_avis_valeur.valeur is 'Valeur (ex : Favorable, Défavorable, etc.)';
comment on column unicaen_avis_valeur.valeur_bool is 'Éventuelle valeur booléenne équivalente';
comment on column unicaen_avis_valeur.tags is 'Éventuels tags associés à cette valeur (ex : classe CSS "success")';
comment on column unicaen_avis_valeur.ordre is 'Entier permettant d''ordonner les valeurs';
comment on column unicaen_avis_valeur.description is 'Description éventuelle de cette valeur';

--
-- Valeurs d'avis autorisées par type d'avis.
--
create sequence unicaen_avis_type_valeur_id_seq ;

create table unicaen_avis_type_valeur
(
    id bigint not null constraint unicaen_avis_type_valeur__pk primary key default nextval('unicaen_avis_type_valeur_id_seq'),
    avis_type_id bigint not null constraint unicaen_avis_type_valeur__unicaen_avis_type__fk references unicaen_avis_type,
    avis_valeur_id bigint not null constraint unicaen_avis_type_valeur__unicaen_avis_valeur__fk references unicaen_avis_valeur
);

create index unicaen_avis_type_valeur__idx on unicaen_avis_type_valeur (id);
create index unicaen_avis_type_valeur__unicaen_avis_type__idx on unicaen_avis_type_valeur (avis_type_id);
create index unicaen_avis_type_valeur__unicaen_avis_valeur__idx on unicaen_avis_type_valeur (avis_valeur_id);

comment on table unicaen_avis_type_valeur is 'Valeurs d''avis autorisées par type d''avis';
comment on column unicaen_avis_type_valeur.avis_type_id is 'Identifiant du type d''avis concerné';
comment on column unicaen_avis_type_valeur.avis_valeur_id is 'Identifiant de la valeur d''avis autorisée';

--
-- Compléments possibles selon le type d'avis et la valeur de l'avis.
--
create sequence unicaen_avis_type_valeur_complem_id_seq ;

create table unicaen_avis_type_valeur_complem
(
    id bigint not null constraint unicaen_avis_type_valeur_complem__pk primary key default nextval('unicaen_avis_type_valeur_complem_id_seq'),
    avis_type_valeur_id bigint not null constraint unicaen_avis_type_valeur_complem__unicaen_avis_type_valeur__fk references unicaen_avis_type_valeur,
    parent_id bigint constraint unicaen_avis_type_valeur_complem__parent__fk references unicaen_avis_type_valeur_complem,
    code varchar(128) not null,
    libelle varchar(128) not null,
    type varchar(64) not null,
    ordre serial not null,
    obligatoire boolean not null default false,
    obligatoire_un_au_moins boolean not null default false
);

create index unicaen_avis_type_valeur_complem__idx on unicaen_avis_type_valeur_complem (id);
create index unicaen_avis_type_valeur_complem__unicaen_avis_type_valeur__idx on unicaen_avis_type_valeur (id);
create unique index unicaen_avis_type_valeur_complem__un on unicaen_avis_type_valeur_complem (code);

comment on table unicaen_avis_type_valeur_complem is 'Compléments possibles selon le type d''avis et la valeur de l''avis';
comment on column unicaen_avis_type_valeur_complem.avis_type_valeur_id is 'Identifiant du type+valeur d''avis permettant ce complément';
comment on column unicaen_avis_type_valeur_complem.parent_id is 'Identifiant du complément parent éventuel de type checkbox uniquement (pour affichage et required conditionnel)';
comment on column unicaen_avis_valeur.code is 'Code littéral unique de ce complément';
comment on column unicaen_avis_type_valeur_complem.libelle is 'Libellé du complément';
comment on column unicaen_avis_type_valeur_complem.type is 'Type de valeur attendue pour ce complément (textarea, checkbox, checkbox+textarea, select, etc.)';
comment on column unicaen_avis_type_valeur_complem.obligatoire is 'Témoin indiquant si une valeur est requise pour ce complément';
comment on column unicaen_avis_type_valeur_complem.obligatoire_un_au_moins is 'Témoin indiquant si une valeur est requise pour l''un au moins des compléments ayant ce même témoin à `true`';

--
-- Avis
--
create sequence unicaen_avis_id_seq ;

create table unicaen_avis
(
    id bigint not null constraint unicaen_avis__pkey primary key default nextval('unicaen_avis_id_seq'),
    avis_type_id bigint not null constraint unicaen_avis__unicaen_avis_type__fk references unicaen_avis_type,
    avis_valeur_id bigint not null constraint unicaen_avis__unicaen_avis_valeur__fk references unicaen_avis_valeur
);

create index unicaen_avis__idx on unicaen_avis (id);
create index unicaen_avis__unicaen_avis_type__idx on unicaen_avis (avis_type_id);
create index unicaen_avis__unicaen_avis_valeur__idx on unicaen_avis (avis_valeur_id);

comment on table unicaen_avis is 'Avis';
comment on column unicaen_avis.avis_type_id is 'Identifiant du type de cet avis';
comment on column unicaen_avis.avis_valeur_id is 'Identifiant de la valeur de cet vis';

--
-- Compléments apportés aux avis
--
create sequence unicaen_avis_complem_id_seq ;

create table unicaen_avis_complem
(
    id bigint not null constraint unicaen_avis_complem__pk primary key default nextval('unicaen_avis_complem_id_seq'),
    avis_id bigint not null constraint unicaen_avis_complem__unicaen_avis__fk references unicaen_avis,
    avis_type_complem_id bigint not null constraint unicaen_avis_complem__unicaen_avis_type_valeur_complem__fk references unicaen_avis_type_valeur_complem,
    valeur text
);

create index unicaen_avis_complem__idx on unicaen_avis_complem (id);
create index unicaen_avis_complem__unicaen_avis_type_valeur_complem__idx on unicaen_avis_complem (avis_type_complem_id);

comment on table unicaen_avis_complem is 'Compléments apportés aux avis';
comment on column unicaen_avis_complem.avis_id is 'Identifiant de l''avis concerné';
comment on column unicaen_avis_complem.avis_type_complem_id is 'Identifiant du complement attendu';
comment on column unicaen_avis_complem.valeur is 'Valeur du complement apportée';
