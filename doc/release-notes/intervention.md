# Version 2.X.X

## 1. Sur le serveur d'application
  
- Placez-vous dans le répertoire de l'application puis lancez la commande suivante 
pour installer la nouvelle version :

```bash
git fetch --tags && git checkout --force 2.X.X && bash ./install.sh
```

- Selon le moteur PHP que vous avez installé, rechargez le service, exemple :
  - php7.3-fpm         : `service php7.3-fpm reload`
  - apache2-mod-php7.3 : `service apache2 reload`

## 2. Dans la base de données

```sql
create table SOUTENANCE_INTERVENTION
(
    ID NUMBER not null,
    THESE_ID NUMBER not null
        constraint SINTERVENTION_THESE_ID_FK
            references THESE,
    TYPE_INTERVENTION NUMBER not null,
    HISTO_CREATION DATE not null,
    HISTO_CREATEUR_ID NUMBER not null
        constraint SINTERVENTION_USERC_ID_FK
            references UTILISATEUR,
    HISTO_MODIFICATION DATE,
    HISTO_MODIFICATEUR_ID NUMBER
        constraint SINTERVENTION_USERM_ID_FK
            references UTILISATEUR,
    HISTO_DESTRUCTION DATE,
    HISTO_DESTRUCTEUR_ID NUMBER
        constraint SINTERVENTION_USERD_ID_FK
            references UTILISATEUR
)
/

create unique index SINTERVENTION_ID_UINDEX
    on SOUTENANCE_INTERVENTION (ID)
/

alter table SOUTENANCE_INTERVENTION
    add constraint SOUTENANCE_INTERVENTION_PK
        primary key (ID)
/

create sequence SOUTENANCE_INTERVENTION_ID_seq;

insert into CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'soutenance_intervention', 'Intervention sur les soutenances', 102);
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'intervention_index', 'Afficher la liste des interventions', 10);
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 4);  --MDD
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 6);  --Admin
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'intervention_afficher', 'Afficher une intervention', 20);
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 4);  --MDD
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 6);  --Admin
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 9);  --Directeur
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 10); --CoDirecteur
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 7);  --Doctorant
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'intervention_modifier', 'Déclarer/Modifier une intervention', 30);
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 4); --MDD
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 6); --Admin
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 9); --Directeur

insert into CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'soutenance_justificatif', 'Justificatifs associés à la soutenance', 102);
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'justificatif_index ', 'Index des justificatifs', 10);
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 6); --Admin
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'justificatif_ajouter', 'Ajouter un justificatif la liste des interventions', 20);
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 6);  --Admin
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 9);  --Directeur
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 10); --CoDirecteur
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 7);  --Doctorant
insert into PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'justificatif_retirer', 'Retirer un justificatif la liste des interventions', 30);
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 6);  --Admin
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 9);  --Directeur
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 10); --CoDirecteur
insert into PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID) VALUES (PRIVILEGE_ID_SEQ.currval, 7);  --Doctorant

-- TODO reappliquer les profil
```