--
-- v10.3.0
--

alter table soutenance_adresse
    add constraint soutenance_adresse_pk
        primary key (id);

alter table soutenance_adresse
    add constraint soutenance_adresse_proposition_fk
        foreign key (proposition_id) references soutenance_proposition
            on delete restrict;

alter table soutenance_adresse
    add constraint soutenance_adresse_hc_fk
        foreign key (histo_createur_id) references utilisateur
            on delete restrict;

alter table soutenance_adresse
    add constraint soutenance_adresse_hd_fk
        foreign key (histo_destructeur_id) references utilisateur
            on delete restrict;

alter table soutenance_adresse
    add constraint soutenance_adresse_hm_fk
        foreign key (histo_modificateur_id) references utilisateur
            on delete restrict;

create unique index soutenance_adresse_uniq
    on soutenance_adresse (proposition_id)
    where (histo_destruction IS NULL);

create unique index soutenance_adresse_uniq_histo
    on soutenance_adresse (proposition_id, histo_destruction)
    where (histo_destruction IS NOT NULL);

alter table soutenance_avis
    add constraint soutenance_avis_pk
        primary key (id);

create unique index soutenance_avis_uniq
    on soutenance_avis (proposition_id, membre_id)
    where (histo_destruction IS NULL);
-- Si ça ne passe pas, recherchez les doublons ainsi et historisez (avec histo_destructeur_id et histo_destruction) le dernier :
-- select proposition_id, membre_id, count(*) from soutenance_avis where histo_destruction is null group by proposition_id, membre_id having count(*) > 1;

create unique index soutenance_avis_uniq_histo
    on soutenance_avis (proposition_id, membre_id, histo_destruction)
    where (histo_destruction IS NOT NULL);

create unique index soutenance_etat_uniq
    on soutenance_etat (code);
