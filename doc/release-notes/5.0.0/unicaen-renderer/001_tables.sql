-- TABLE DES MACROS

create table unicaen_renderer_macro
(
    id serial not null constraint unicaen_document_macro_pk primary key,
    code varchar(256) not null,
    description text,
    variable_name varchar(256) not null,
    methode_name varchar(256) not null
);
create unique index unicaen_document_macro_code_uindex on unicaen_renderer_macro (code);
create unique index unicaen_document_macro_id_uindex on unicaen_renderer_macro (id);

-- TABLE DES TEMPLATES

create table unicaen_renderer_template
(
    id serial not null constraint unicaen_document_template_pk primary key,
    code varchar(256) not null,
    description text,
    document_type varchar(256) not null,
    document_sujet text not null,
    document_corps text not null,
    document_css text
);
create unique index unicaen_document_template_code_uindex on unicaen_renderer_template (code);
create unique index unicaen_document_template_id_uindex on unicaen_renderer_template (id);

-- TABLE DES RENDU
create table unicaen_renderer_rendu
(
    id serial not null constraint unicaen_document_rendu_pk primary key,
    template_id int default null
        constraint unicaen_document_rendu_template_id_fk
            references unicaen_renderer_template
            on delete set null,
    date_generation timestamp not null,
    sujet text not null,
    corps text not null
);
create unique index unicaen_document_rendu_id_uindex on unicaen_renderer_template (id);


