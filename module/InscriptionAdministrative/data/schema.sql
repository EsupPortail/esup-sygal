--
-- Flux Inscriptions Administratives
--

alter table source add synchro_insert_enabled boolean default true not null;
alter table source add synchro_update_enabled boolean default true not null;
alter table source add synchro_undelete_enabled boolean default true not null;
alter table source add synchro_delete_enabled boolean default true not null;
comment on column source.synchro_insert_enabled is 'Indique si dans le cadre d''une synchro l''opération ''insert'' est autorisée.';
comment on column source.synchro_update_enabled is 'Indique si dans le cadre d''une synchro l''opération ''update'' est autorisée.';
comment on column source.synchro_undelete_enabled is 'Indique si dans le cadre d''une synchro l''opération ''undelete'' est autorisée.';
comment on column source.synchro_delete_enabled is 'Indique si dans le cadre d''une synchro l''opération ''delete'' est autorisée.';

create sequence if not exists source_id_seq;

alter table tmp_doctorant add column code_apprenant_in_source varchar(128);
alter table doctorant add column code_apprenant_in_source varchar(128);

--drop view src_inscription_administrative cascade;
--drop table if exists tmp_inscription_administrative;
create table tmp_inscription_administrative
(
    id bigserial,
    insert_date timestamp(0) default ('now'::text)::timestamp without time zone,

    source_id bigint not null,
    source_code varchar(128) not null, -- Id Pegase inscription (merge request en cours)

    doctorant_id varchar(64) not null,
    ecole_doct_id varchar(32) not null,

    date_inscription date not null,
    date_annulation date,
    principale bool not null,
    statut_inscription varchar(32) not null,
    chemin varchar(128) not null,
    code_structure_etablissement_du_chemin varchar(64) not null,
    formation varchar(64),
    cesure varchar(32),
    mobilite varchar(32),
    origine varchar(32),
    --regime_inscription_code varchar(32), -- utile ?
    regime_inscription_libelle varchar(64),
    no_candidat varchar(32),

    periode_code varchar(32) not null,
    periode_libelle varchar(32) not null,
    periode_annee_universitaire smallint not null,

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0),
    histo_modificateur_id bigint,
    histo_destruction timestamp(0),
    histo_destructeur_id bigint
);

create index tmp_inscription_administrative_source_code_index
    on tmp_inscription_administrative (source_code);
create index tmp_inscription_administrative_source_id_index
    on tmp_inscription_administrative (source_id);
create unique index tmp_inscription_administrative_unique_index
    on tmp_inscription_administrative (source_id, source_code);
alter table tmp_inscription_administrative
    add constraint tmp_inscription_administrative_source_id_fk
        foreign key (source_id) references source;

--drop table inscription_administrative;
create table inscription_administrative
(
    id bigserial constraint inscription_administrative_pkey primary key,
    source_id bigint not null constraint inscription_administrative_source_fk references source,
    source_code varchar(128) not null,

    doctorant_id bigint not null constraint inscription_administrative_doctorant_fk references doctorant,
    ecole_doct_id bigint not null constraint inscription_administrative_ecole_doct_fk references ecole_doct,

    date_inscription date not null,
    date_annulation date,
    principale boolean not null,
    statut_inscription varchar(32) not null,
    chemin varchar(128) not null,
    code_structure_etablissement_du_chemin varchar(64) not null,
    formation varchar(64),
    cesure varchar(32),
    mobilite varchar(32),
    origine varchar(32),
    --regime_inscription_code varchar(32), -- utile ?
    regime_inscription_libelle varchar(64),
    no_candidat varchar(32),

    periode_code varchar(32) not null,
    periode_libelle varchar(32) not null,
    periode_annee_universitaire smallint not null,

    histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_createur_id bigint not null,
    histo_modification timestamp(0) without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp(0) without time zone,
    histo_destructeur_id bigint
);

create index inscription_administrative_source_code_index
    on inscription_administrative (source_code);
create index inscription_administrative_source_id_index
    on inscription_administrative (source_id);
create unique index inscription_administrative_unique_index
    on inscription_administrative (source_id, source_code);
alter table inscription_administrative
    add constraint inscription_administrative_source_id_fk
        foreign key (source_id) references source;

create index inscription_administrative_doctorant_id_idx
    on inscription_administrative (doctorant_id);
create index inscription_administrative_ecole_doct_id_idx
    on inscription_administrative (ecole_doct_id);
create index inscription_administrative_hcfk_idx
    on inscription_administrative (histo_createur_id);
create index inscription_administrative_hdfk_idx
    on inscription_administrative (histo_destructeur_id);
create index inscription_administrative_hmfk_idx
    on inscription_administrative (histo_modificateur_id);

--drop view src_inscription_administrative;
create or replace view src_inscription_administrative as
SELECT null::bigint as id,
       tmp.source_code,
       src.id as source_id,
       d.id as doctorant_id,
       ed.id as ecole_doct_id,
       tmp.no_candidat,
       tmp.date_inscription,
       tmp.date_annulation,
       tmp.cesure,
       tmp.chemin,
       tmp.code_structure_etablissement_du_chemin,
       tmp.formation,
       tmp.mobilite,
       tmp.origine,
       tmp.principale,
       tmp.regime_inscription_libelle,
       tmp.statut_inscription,
       tmp.periode_code,
       tmp.periode_libelle,
       tmp.periode_annee_universitaire
FROM tmp_inscription_administrative tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN doctorant d ON d.source_code = tmp.doctorant_id
         LEFT JOIN ecole_doct ed ON ed.source_code = tmp.ecole_doct_id -- left join obligatoire sinon perte possible d'une IA
;

-- alter table tmp_doctorant alter column ine set not null;


drop view if exists v_diff_variable;
drop view if exists src_variable;
create or replace view src_variable
            (id, source_code, source_id, etablissement_id, code, description, valeur, date_deb_validite,
             date_fin_validite) as
SELECT NULL::bigint  AS id,
       tmp.source_code,
       src.id      AS source_id,
       e.id        AS etablissement_id,
       tmp.cod_vap AS code,
       tmp.lib_vap AS description,
       tmp.par_vap AS valeur,
       tmp.date_deb_validite,
       tmp.date_fin_validite
FROM tmp_variable tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN etablissement e ON e.structure_id = src.etablissement_id
         JOIN structure s ON s.id = e.structure_id;



--
-- Création des sources de test correspondant à des instances pégase.
--
-- NB : le 'code' doit matcher avec le 'instancePegase' du contrat d'API.
-- NB : si la séquence 'source_id_seq' vient d'être créée, il peut être nécessaire de relancer la commande jusqu'à ce qu'elle passe.
insert into source (id, code, libelle, importable, synchro_delete_enabled, etablissement_id)
select nextval('source_id_seq'), 'PEGASE_INSA', 'Instance Pégase INSA', true, false/*delete interdit*/, 5 union
select nextval('source_id_seq'), 'PEGASE_UCN', 'Instance Pégase UCN', true, false/*delete interdit*/, 2;

update source
set synchro_delete_enabled = false
where code in ('PEGASE_INSA',
               'PEGASE_UCN');


--
-- Création des EDs
-- ----------------
-- NB : Pour que la réception du flux inscription soit possible (i.e. la jointure avec l'ED se fasse), il faut que l'ED
-- spécifiée dans les données existe dans la base SYGAL, donc pas le choix pour l'instant il faut dupliquer manuellement
-- les ED autant de fois qu'il y a de sources Pégase déclarées.
-- Solution plus pérenne : obtenir les ED de Pégase (soit dans le même flux, soit dans un autre flux 'ecole-doctorale').
--
create table tmp_ed as
with data(code, libelle) as (
    select '41', 'École doctorale Droit' union all
    select '42', 'École doctorale Entreprise, économie, société' union all
    select '62', 'École doctorale Sciences de la vie et de la santé' union all
    select '65', 'École doctorale Sciences de la Vie, Santé, Agronomie, Environnement' union all
    select '70', 'École doctorale Sciences pour l''ingénieur' union all
    select '78', 'École doctorale Sociétés, langages, temps, connaissances' union all
    select '84', 'École doctorale Sciences et technologies de l''information et de la communication' union all
    select '88', 'École doctorale Droit et science politique' union all
    select '112', 'École doctorale Archéologie' union all
    select '113', 'École doctorale Histoire' union all
    select '159', 'École doctorale Esthétique, sciences et technologies des arts' union all
    select '162', 'École doctorale Mécanique, énergétique, génie civil, acoustique' union all
    select '166', 'École doctorale Information, structures et systèmes' union all
    select '168', 'École doctorale Sciences chimiques et biologiques pour la santé' union all
    select '171', 'École doctorale Toulouse sciences économiques' union all
    select '178', 'École doctorale Sciences fondamentales' union all
    select '217', 'École doctorale Mathématiques, sciences et technologies de l''information, informatique' union all
    select '227', 'École doctorale Sciences de la nature et de l''homme et de l''Ecologie' union all
    select '234', 'École doctorale de Sciences Po' union all
    select '242', 'École doctorale Économie - Gestion - Normandie' union all
    select '251', 'École doctorale Sciences de l''environnement' union all
    select '265', 'École doctorale Langues, littératures et sociétés du monde' union all
    select '269', 'École doctorale Mathématiques, sciences de l''information et de l''ingénieur' union all
    select '355', 'École doctorale Espaces, cultures, sociétés' union all
    select '396', 'École doctorale Économie, organisations, société' union all
    select '402', 'École doctorale Sciences de la vie et de la santé' union all
    select '433', 'École doctorale Concepts et langages' union all
    select '455', 'École doctorale Économie, Gestion, Information et Communication' union all
    select '460', 'École doctorale Sciences juridiques' union all
    select '468', 'École doctorale Mécanique, énergétique, génie civil, procédés' union all
    select '479', 'École doctorale Sciences juridiques et politiques' union all
    select '483', 'École doctorale Sciences sociales de l''université de Lyon (Histoire, géographie, aménagement, urbanisme, archéologie, science politique, sociologie, anthropologie)' union all
    select '485', 'École doctorale Éducation, Psychologie, Information et Communication' union all
    select '513', 'École doctorale Droit et sciences politiques, économiques et de gestion' union all
    select '531', 'École doctorale Sciences, ingénierie et environnement' union all
    select '543', 'École doctorale Sciences de la Décision, des Organisations, de la Société et de l’Echange' union all
    select '548', 'École doctorale Mer et Sciences' union all
    select '558', 'École doctorale Normandie-humanités nouvelles' union all
    select '559', 'École doctorale Management Panthéon-Sorbonne' union all
    select '561', 'École doctorale Hématologie, Oncogenèse et Biothérapies' union all
    select '566', 'École doctorale Sciences du Sport, de la Motricité et du Mouvement Humain' union all
    select '567', 'École doctorale Sciences du Végétal : du gène à l''écosystème' union all
    select '574', 'École doctorale de Mathématiques Hadamard' union all
    select '579', 'École doctorale Sciences mécaniques et energétiques, matériaux et géosciences' union all
    select '582', 'École doctorale Cancérologie, Biologie, Médecine, Santé' union all
    select '585', 'École doctorale Sciences, Technologie, Santé' union all
    select '600', 'École doctorale Écologie, Géosciences, Agronomie et Alimentation' union all
    select '602', 'École doctorale Sciences de l''Ingénierie et des Systèmes' union all
    select '604', 'École doctorale Sociétés, Temps, Territoires' union all
    select '605', 'École doctorale Biologie Santé' union all
    select '616', 'École doctorale humanités nouvelles et Langues' union all
    select '619', 'École doctorale Sciences Fondamentales et Santé' union all
    select '620', 'École doctorale Sciences du Numérique et de l''Ingénieur' union all
    select '635', 'École doctorale Polytechnique Hauts-de-France' union all
    select '636', 'École doctorale Dynamique des environnements dans l''espace Caraïbes-Amériques' union all
    select '648', 'École doctorale Sciences pour l’ingénieur et le Numérique' union all
    select '650', 'École doctorale Humains en société' union all
    select '654', 'École doctorale Littératures, Sciences Humaines et Sociales' union all
    select '6', 'École doctorale Droit privé' union all
    select '22', 'École doctorale Mondes antiques et médiévaux' union all
    select '47', 'École doctorale Physique de Grenoble' union all
    select '50', 'École doctorale Langues, littératures et sciences humaines' union all
    select '58', 'École doctorale Langues, littératures, cultures, civilisations' union all
    select '98', 'École doctorale Droit-Normandie' union all
    select '122', 'École doctorale Europe latine - Amérique latine' union all
    select '131', 'École doctorale Langue, littérature, image, civilisations et sciences humaines (domaines francophone et anglophone et d''Asie Orientale)' union all
    select '138', 'École doctorale Lettres, langues, spectacles' union all
    select '146', 'École doctorale Santé - Galilée' union all
    select '160', 'École doctorale Électronique, électrotechnique, automatique' union all
    select '188', 'École doctorale Histoire moderne et contemporaine' union all
    select '205', 'École doctorale Interdisciplinaire Sciences-Santé' union all
    select '206', 'École doctorale de Chimie de l''université de Lyon' union all
    select '211', 'École doctorale Sciences exactes et leurs applications' union all
    select '222', 'École doctorale Sciences chimiques' union all
    select '270', 'École doctorale Théologie et sciences religieuses' union all
    select '286', 'École doctorale de l''Ecole des Hautes Etudes en Sciences Sociales' union all
    select '327', 'École doctorale Temps, espaces, sociétés, cultures' union all
    select '328', 'École doctorale Arts, lettres, langues, philosophie et communication' union all
    select '340', 'École doctorale Biologie moléculaire, intégrative et cellulaire' union all
    select '354', 'École doctorale Langues, lettres et arts' union all
    select '372', 'École doctorale Sciences économiques et de gestion d''Aix-Marseille' union all
    select '377', 'École doctorale Environnement et société' union all
    select '391', 'École doctorale Sciences mécaniques, acoustique et électronique et robotique de Paris' union all
    select '394', 'École doctorale Physiologie, Physiopathologie et Thérapeutique' union all
    select '406', 'École doctorale Chimie moléculaire de Paris centre' union all
    select '411', 'École doctorale humanités nouvelles nouvelles : Fernand Braudel' union all
    select '417', 'École doctorale Sciences et Ingénierie' union all
    select '446', 'École doctorale Biologie - Santé de Lille' union all
    select '450', 'École doctorale Recherches en psychanalyse et psychopathologie' union all
    select '461', 'École doctorale Droit et science politique' union all
    select '463', 'École doctorale Sciences du mouvement humain' union all
    select '465', 'École doctorale Économie Panthéon Sorbonne' union all
    select '474', 'École doctorale Frontières de l''Innovation en Recherche et Education' union all
    select '476', 'École doctorale Neurosciences et cognition' union all
    select '488', 'École doctorale Sciences, ingénierie, santé' union all
    select '508', 'École doctorale Normande de chimie' union all
    select '537', 'École doctorale Culture et patrimoine' union all
    select '545', 'École doctorale Sociétés, politique et santé publique' union all
    select '549', 'École doctorale Santé, Sciences Biologiques et Chimie du Vivant' union all
    select '563', 'École doctorale Médicament - Toxicologie - Chimie - Imageries' union all
    select '575', 'École doctorale Electrical, Optical, Bio-Physics and Engineering / Physique et ingénierie : electrons, photons, sciences du vivant' union all
    select '583', 'École doctorale Risques et Société' union all
    select '586', 'École doctorale Sciences Humaines et Sociales' union all
    select '597', 'École doctorale Sciences économiques et sciences de Gestion - Bretagne' union all
    select '598', 'École doctorale Sciences de La Mer et du Littoral' union all
    select '599', 'École doctorale Droit et Science Politique - Bretagne' union all
    select '601', 'École doctorale Mathématiques, Télécommunications, Informatique, Signal, Systèmes et Électronique' union all
    select '618', 'École doctorale EUCLIDE' union all
    select '626', 'École doctorale de l’Institut Polytechnique de Paris' union all
    select '633', 'École doctorale Cultures, Sociétés, Territoires' union all
    select '641', 'École doctorale Mathématiques et Sciences et Technologies du numérique, de l’Information et de la Communication' union all
    select '646', 'École doctorale Education, Langages, Interactions, Cognition, Clinique, Expertise' union all
    select '649', 'École doctorale Rosalind Franklin – Energie, Environnement, Biosanté' union all
    select '8', 'École doctorale Histoire du droit, philosophie du droit et sociologie du droit' union all
    select '20', 'École doctorale Civilisations, cultures, littératures et sociétés' union all
    select '40', 'École doctorale Sciences chimiques' union all
    select '67', 'École doctorale Sciences juridiques et politiques' union all
    select '85', 'École doctorale Sciences de la vie et de la santé' union all
    select '101', 'École doctorale de Sciences juridiques' union all
    select '127', 'École doctorale Astronomie et astrophysique d''Ile de France' union all
    select '130', 'École doctorale Informatique, télécommunications et électronique de Paris' union all
    select '154', 'École doctorale Sciences de la vie et de la santé' union all
    select '218', 'École doctorale Chimie et sciences du vivant' union all
    select '220', 'École doctorale Électronique, électrotechnique, automatique, traitement du signal' union all
    select '224', 'École doctorale Cognition, langage, interaction' union all
    select '231', 'École doctorale Économie et gestion' union all
    select '245', 'École doctorale Sciences économiques, juridiques, politiques et de gestion' union all
    select '250', 'École doctorale Sciences chimiques' union all
    select '262', 'École doctorale Sciences juridiques, politiques, économiques et de gestion' union all
    select '267', 'École doctorale Arts et médias' union all
    select '284', 'École doctorale Droit et Science Politique' union all
    select '323', 'École doctorale Génie électrique, électronique et télécommunications' union all
    select '326', 'École doctorale Comportement, langage, éducation, socialisation, cognition' union all
    select '341', 'École doctorale Évolution, écosystèmes, microbiologie, modélisation' union all
    select '352', 'École doctorale Physique et sciences de la matière' union all
    select '353', 'École doctorale Sciences pour l''ingénieur : mécanique, physique, micro et nanoélectronique' union all
    select '393', 'École doctorale Pierre Louis de Santé Publique à Paris : Épidémiologie et Sciences de l''information biomédicale' union all
    select '395', 'École doctorale Espaces, Temps, Cultures' union all
    select '413', 'École doctorale Sciences de la terre et de l''Environnement' union all
    select '414', 'École doctorale Sciences de la vie et de la santé' union all
    select '459', 'École doctorale Sciences chimiques Balard' union all
    select '482', 'École doctorale Sciences de la matière' union all
    select '492', 'École doctorale Droit' union all
    select '493', 'École doctorale Erasme' union all
    select '510', 'École doctorale Ingénierie - Matériaux, mécanique, environnement, énergétique, procédés, production' union all
    select '530', 'École doctorale Organisations, marchés, institutions' union all
    select '532', 'École doctorale Mathématiques et sciences et technologies de l''information et de la communication' union all
    select '540', 'École doctorale Lettres, Arts, Sciences humaines et sociales' union all
    select '541', 'École doctorale de sciences humaines et sociales' union all
    select '544', 'École doctorale Inter-Med : Espaces, temps, cultures' union all
    select '546', 'École doctorale Abbé Grégoire' union all
    select '551', 'École doctorale Mathématiques, Informatique, Physique Théorique et Ingénierie des Systèmes' union all
    select '552', 'École doctorale Énergie - Matériaux - Sciences de la Terre et de l''Univers' union all
    select '554', 'École doctorale Environnements - Santé' union all
    select '555', 'École doctorale Sciences Humaines et sociales' union all
    select '556', 'École doctorale Homme, Sociétés, Risques, Territoire' union all
    select '565', 'École doctorale Droit de la Sorbonne' union all
    select '568', 'École doctorale Signalisations et Réseaux Intégratifs en Biologie' union all
    select '569', 'École doctorale Innovation thérapeutique : du fondamental à l''appliqué' union all
    select '572', 'École doctorale Ondes et matière' union all
    select '590', 'École doctorale Mathématiques, Information, Ingénierie des Systèmes' union all
    select '592', 'École doctorale Lettres, Communication, Langues, Arts' union all
    select '593', 'École doctorale Droit, Gestion, sciences Économiques et Politique' union all
    select '596', 'École doctorale Matière, Molécules, Matériaux et Géosciences' union all
    select '606', 'École doctorale Chimie - Mécanique - Matériaux - Physique' union all
    select '607', 'École doctorale Sciences et Ingénierie des Ressources Naturelles' union all
    select '608', 'École doctorale Sciences et Ingénierie des Molécules, des Produits, des Procédés et de l''Énergie' union all
    select '621', 'École doctorale Ingénierie des Systèmes, Matériaux, Mécanique, Énergétique' union all
    select '625', 'École doctorale Mondes Anglophones, Germanophones, Indiens, Iraniens et Études Européennes' union all
    select '627', 'École doctorale Éducation, Didactiques, Cognition' union all
    select '630', 'École doctorale Droit, Economie et Management' union all
    select '632', 'École doctorale Science de l’ingénierie et des systèmes' union all
    select '634', 'École doctorale Sciences, Ingénierie, Environnement' union all
    select '637', 'École doctorale Sciences de la vie et de la santé' union all
    select '640', 'École doctorale Sciences économiques et sciences de gestion - Pays de la Loire' union all
    select '651', 'École doctorale Mathématiques, Informatique, Matériaux, Mécanique, Énergétique' union all
    select '652', 'École doctorale Biologie, Chimie, Santé' union all
    select '653', 'École doctorale Sciences et Ingénierie' union all
    select '7', 'École doctorale Georges Vedel - Droit public interne, science administrative et science politique' union all
    select '19', 'École doctorale Littératures françaises et comparée' union all
    select '34', 'École doctorale Matériaux de Lyon' union all
    select '39', 'École doctorale Mathématiques et Informatique' union all
    select '52', 'École doctorale Physique et astrophysique de Lyon' union all
    select '71', 'École doctorale Sciences pour l''ingénieur' union all
    select '73', 'École doctorale Sciences économiques, sociales, de l''aménagement et du management' union all
    select '74', 'École doctorale Sciences juridiques, politique et de gestion' union all
    select '77', 'École doctorale Informatique, automatique, électronique, électrotechnique, mathématiques de Lorraine' union all
    select '120', 'École doctorale Littérature française et comparée' union all
    select '124', 'École doctorale Histoire de l''art et archéologie' union all
    select '139', 'École doctorale Connaissance, langage, modélisation' union all
    select '151', 'École doctorale Biologie, santé, biotechnologies' union all
    select '173', 'École doctorale Sciences de l''univers, de l''environnement et de l''espace' union all
    select '182', 'École doctorale Physique et chimie-physique' union all
    select '184', 'École doctorale Mathématiques et informatique de Marseille' union all
    select '216', 'École doctorale Ingénierie pour la santé, la cognition, l''environnement' union all
    select '221', 'École doctorale Augustin Cournot' union all
    select '261', 'École doctorale Cognition, comportements, conduites humaines' union all
    select '275', 'École doctorale Sciences de gestion' union all
    select '279', 'École doctorale Arts plastiques et sciences de l''art' union all
    select '280', 'École doctorale Philosophie' union all
    select '300', 'École doctorale Sciences Économiques' union all
    select '305', 'École doctorale Énergie et environnement' union all
    select '309', 'École doctorale Systèmes' union all
    select '386', 'École doctorale Sciences mathématiques de Paris centre' union all
    select '398', 'École doctorale Géosciences, ressources naturelles et environnement' union all
    select '401', 'École doctorale Sciences sociales' union all
    select '432', 'École doctorale Sciences des métiers de l''ingénieur' union all
    select '441', 'École doctorale Histoire de l''art' union all
    select '454', 'École doctorale Sciences de l''homme, du politique et du territoire' union all
    select '467', 'École doctorale Aéronautique, astronautique' union all
    select '484', 'École doctorale Lettres, langues, linguistique, arts' union all
    select '486', 'École doctorale Sciences économiques et de gestion' union all
    select '487', 'École doctorale Philosophie' union all
    select '497', 'École doctorale Normande de Biologie Intégrative, Santé, Environnement' union all
    select '520', 'École doctorale des humanités nouvelles' union all
    select '528', 'École doctorale Ville, transports et territoires' union all
    select '542', 'École doctorale de sciences' union all
    select '553', 'École doctorale Carnot - Pasteur' union all
    select '562', 'École doctorale Bio Sorbonne Paris Cité' union all
    select '564', 'École doctorale Physique en Ile de France' union all
    select '571', 'École doctorale Sciences Chimiques : Molécules, Matériaux, Instrumentation et Biosystemes' union all
    select '573', 'École doctorale Interfaces : Approches Interdisciplinaires, Fondements, Applications et Innovation' union all
    select '576', 'École doctorale Particules, Hadrons, Énergie, Noyau, Instrumentation, Imagerie, Cosmos et Simulation' union all
    select '587', 'École doctorale Diversites, santé et développement en Amazonie' union all
    select '591', 'École doctorale Physique, Sciences de l''Ingénieur, Materiaux, Énergie' union all
    select '594', 'École doctorale Sociétés, Espaces, Pratiques, Temps' union all
    select '595', 'École doctorale Arts, Lettres, Langues - Bretagne' union all
    select '617', 'École doctorale Sciences de la Société : Territoires, Économie, Droit' union all
    select '622', 'École doctorale Sciences du langage' union all
    select '623', 'École doctorale Savoir, Sciences, Éducation' union all
    select '628', 'École doctorale Arts humanités nouvelles et Sciences sociales' union all
    select '638', 'École doctorale Science de la Matière, des Molécules et Matériaux' union all
    select '639', 'École doctorale Droit et Science politique - Pays de la Loire' union all
    select '642', 'École doctorale Végétal, Animal, Aliment, Mer, Environnement' union all
    select '645', 'École doctorale Espaces, Sociétés, Civilisations' union all
    select '647', 'École doctorale Sciences pour l’Ingénieur' union all
    select '9', 'École doctorale Droit international, droit européen, relations internationales et droit comparé' union all
    select '31', 'École doctorale Pratiques et théories du sens' union all
    select '37', 'École doctorale Sciences physiques pour l''ingénieur et microtechniques' union all
    select '60', 'École doctorale Territoires, temps, sociétés et développement' union all
    select '79', 'École doctorale Sciences juridiques, politiques, économiques et de gestion' union all
    select '86', 'École doctorale Sociétés, humanités nouvelles, arts et lettres' union all
    select '104', 'École doctorale Sciences de la matière, du rayonnement et de l''environnement' union all
    select '105', 'École doctorale Terre, univers, environnement' union all
    select '119', 'École doctorale Science politique' union all
    select '129', 'École doctorale Sciences de l''environnement d''Ile de France' union all
    select '141', 'École doctorale Droit et science politique' union all
    select '158', 'École doctorale Cerveau, cognition, comportement' union all
    select '209', 'École doctorale Sciences physiques et de L''ingénieur' union all
    select '266', 'École doctorale Biologie, santé, environnement' union all
    select '304', 'École doctorale Sciences et environnements' union all
    select '356', 'École doctorale Cognition, langage, éducation' union all
    select '361', 'École doctorale Sciences pour l''ingénieur' union all
    select '364', 'École doctorale Sciences fondamentales et appliquées' union all
    select '370', 'École doctorale Lettres, sciences humaines et sociales' union all
    select '388', 'École doctorale Chimie physique et chimie analytique de Paris centre' union all
    select '397', 'École doctorale Physique et chimie des matériaux' union all
    select '405', 'École doctorale Économie, management,mathématiques, physique et sciences informatiques' union all
    select '434', 'École doctorale Géographie de Paris' union all
    select '458', 'École doctorale Sciences écologiques, vétérinaires, agronomiques et bioingénieries' union all
    select '469', 'École doctorale du Pacifique' union all
    select '472', 'École doctorale de l''Ecole Pratique des Hautes Etudes' union all
    select '473', 'École doctorale Sciences de l''homme et de la société' union all
    select '475', 'École doctorale Mathématiques, informatique, télécommunications de Toulouse' union all
    select '478', 'École doctorale Sciences de Gestion' union all
    select '480', 'École doctorale Montaigne-humanités nouvelles' union all
    select '481', 'École doctorale Sciences sociales et humanités' union all
    select '509', 'École doctorale Sociétés méditerranéennes et sciences humaines' union all
    select '512', 'École doctorale Informatique et mathématiques de Lyon' union all
    select '515', 'École doctorale Complexité du vivant' union all
    select '519', 'École doctorale Sciences humaines et sociales - Perspectives européennes' union all
    select '529', 'École doctorale Cultures et sociétés' union all
    select '536', 'École doctorale Agrosciences et Sciences' union all
    select '560', 'École doctorale Sciences de la terre et de l''environnement et Physique de l''univers, Paris' union all
    select '570', 'École doctorale Santé Publique' union all
    select '577', 'École doctorale Structure et dynamique des systèmes vivants' union all
    select '580', 'École doctorale Sciences et technologies de l''information et de la communication' union all
    select '581', 'École doctorale Agriculture, alimentation, biologie, environnement, santé' union all
    select '584', 'École doctorale Biodiversité, Agriculture, Alimentation, Environnement, Terre, Eau' union all
    select '603', 'École doctorale Education, Cognition, Langages, Interactions, Santé' union all
    select '612', 'École doctorale humanités nouvelles' union all
    select '624', 'École doctorale Sciences des Sociétés' union all
    select '629', 'École doctorale Sciences sociales et humanités nouvelles' union all
    select '631', 'École doctorale Mathématiques, sciences du numérique et de leurs interactions' union all
    select '643', 'École doctorale Arts, Lettres, Langues' union all
    select '644', 'École doctorale Mathématiques & Sciences et Technologies de l’Information et de la Communication en Bretagne Océane' union all
    select '655', 'École doctorale Gouvernance des Institutions et des Organisations'
)
select * from data;

insert into tmp_structure (id, sigle,libelle,type_structure_id,source_id,source_code,code,histo_createur_id)
with data(source_code) as (
    select 'PEGASE_INSA' union all
    select 'PEGASE_UCN'
)
select nextval('tmp_structure_id_seq'), 'ED'||d.code, d.libelle, 'ecole-doctorale', src.id, src.code||'::'||d.code, d.code, 1
from data, tmp_ed d, source src
where src.code = data.source_code
on conflict DO NOTHING ;

insert into structure (id, sigle,libelle,type_structure_id,source_id,source_code,code,histo_createur_id)
with data(source_code) as (
    select 'PEGASE_INSA' union all
    select 'PEGASE_UCN'
)
select nextval('structure_id_seq'), 'ED'||d.code, d.libelle, ts.id, src.id, src.code||'::'||d.code, d.code, 1
from data, tmp_ed d, type_structure ts, source src
where ts.code = 'ecole-doctorale' and src.code = data.source_code
on conflict DO NOTHING ;

insert into tmp_ecole_doct (id, source_code, source_id, structure_id,histo_createur_id)
with data(source_code) as (
    select 'PEGASE_INSA' union all
    select 'PEGASE_UCN'
)
select nextval('tmp_ecole_doct_id_seq'), src.code||'::'||d.code, src.id, src.code||'::'||d.code, 1
from data, tmp_ed d, source src
where src.code = data.source_code
on conflict DO NOTHING ;

insert into ecole_doct (id, source_code, source_id, structure_id,histo_createur_id)
with data(source_code) as (
    select 'PEGASE_INSA' union all
    select 'PEGASE_UCN'
)
select nextval('ecole_doct_id_seq'), src.code||'::'||d.code, src.id, s.id, 1
from data, tmp_ed d, structure s, source src
where s.source_code = src.code||'::'||d.code and src.code = data.source_code
on conflict DO NOTHING ;
