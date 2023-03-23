create table horodatage_horodatage
(
    id          serial
                constraint horodatage_horodatage_pk primary key,
    date        timestamp     not null,
    user_id     integer       not null
                constraint horodatage_horodatage_utilisateur_id_fk
                references utilisateur,
    type        varchar(1024) not null,
    complement  varchar(1024)
);

create table soutenance_horodatage
(
    proposition_id integer not null
        constraint soutenance_horodatage_soutenance_proposition_id_fk
            references soutenance_proposition on delete cascade,
    horodatage_id  integer not null
        constraint soutenance_horodatage_horodatage_horodatage_id_fk
        references horodatage_horodatage on delete cascade,
    constraint soutenance_horodatage_pk primary key (proposition_id, horodatage_id)
);


