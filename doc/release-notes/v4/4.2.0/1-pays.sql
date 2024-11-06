--
-- Pays
--

--drop table if exists pays; drop sequence if exists pays_id_seq;
create table pays (
    id bigint primary key ,
    code_iso varchar(3) not null unique,
    code_iso_alpha3 varchar(3) not null unique,
    code_iso_alpha2 varchar(2) not null unique,
    libelle varchar(128) not null,
    libelle_iso varchar(128) not null,
    libelle_nationalite varchar(64),
    code_pays_apogee varchar(3)
);

comment on table pays is 'Liste des pays selon la norme internationale de codification des pays ISO 3166-1';

create sequence if not exists pays_id_seq;

alter table pays add histo_creation        timestamp default current_timestamp not null;
alter table pays add histo_createur_id     bigint not null;
alter table pays add histo_modification    timestamp;
alter table pays add histo_modificateur_id bigint;
alter table pays add histo_destruction     timestamp;
alter table pays add histo_destructeur_id  bigint;
alter table pays add constraint pays_hcfk foreign key (histo_createur_id) references utilisateur (id);
alter table pays add constraint pays_hdfk foreign key (histo_destructeur_id) references utilisateur (id);
alter table pays add constraint pays_hmfk foreign key (histo_modificateur_id) references utilisateur (id);

alter table pays add source_id   bigint not null;
alter table pays add source_code varchar(64);
alter table pays add constraint pays_source_fk foreign key (source_id) references source (id);

with tmp(code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso) as (
    select '004', 'AFG', 'AF', 'Afghanistan', 'AFGHANISTAN' union all
    select '710', 'ZAF', 'ZA', 'Afrique du Sud', 'AFRIQUE DU SUD' union all
    select '248', 'ALA', 'AX', 'Îles Åland', 'ÅLAND, ÎLES' union all
    select '008', 'ALB', 'AL', 'Albanie', 'ALBANIE' union all
    select '012', 'DZA', 'DZ', 'Algérie', 'ALGÉRIE' union all
    select '276', 'DEU', 'DE', 'Allemagne', 'ALLEMAGNE' union all
    select '020', 'AND', 'AD', 'Andorre', 'ANDORRE' union all
    select '024', 'AGO', 'AO', 'Angola', 'ANGOLA' union all
    select '660', 'AIA', 'AI', 'Anguilla', 'ANGUILLA' union all
    select '010', 'ATA', 'AQ', 'Antarctique', 'ANTARCTIQUE' union all
    select '028', 'ATG', 'AG', 'Antigua-et-Barbuda', 'ANTIGUA-ET-BARBUDA' union all
    select '682', 'SAU', 'SA', 'Arabie saoudite', 'ARABIE SAOUDITE' union all
    select '032', 'ARG', 'AR', 'Argentine', 'ARGENTINE' union all
    select '051', 'ARM', 'AM', 'Arménie', 'ARMÉNIE' union all
    select '533', 'ABW', 'AW', 'Aruba', 'ARUBA' union all
    select '036', 'AUS', 'AU', 'Australie', 'AUSTRALIE' union all
    select '040', 'AUT', 'AT', 'Autriche', 'AUTRICHE' union all
    select '031', 'AZE', 'AZ', 'Azerbaïdjan', 'AZERBAÏDJAN' union all
    select '044', 'BHS', 'BS', 'Bahamas', 'BAHAMAS' union all
    select '048', 'BHR', 'BH', 'Bahreïn', 'BAHREÏN' union all
    select '050', 'BGD', 'BD', 'Bangladesh', 'BANGLADESH' union all
    select '052', 'BRB', 'BB', 'Barbade', 'BARBADE' union all
    select '112', 'BLR', 'BY', 'Biélorussie', 'BÉLARUS' union all
    select '056', 'BEL', 'BE', 'Belgique', 'BELGIQUE' union all
    select '084', 'BLZ', 'BZ', 'Belize', 'BELIZE' union all
    select '204', 'BEN', 'BJ', 'Bénin', 'BÉNIN' union all
    select '060', 'BMU', 'BM', 'Bermudes', 'BERMUDES' union all
    select '064', 'BTN', 'BT', 'Bhoutan', 'BHOUTAN' union all
    select '068', 'BOL', 'BO', 'Bolivie', 'BOLIVIE, ÉTAT PLURINATIONAL DE' union all
    select '535', 'BES', 'BQ', 'Pays-Bas caribéens', 'BONAIRE, SAINT-EUSTACHE ET SABA' union all
    select '070', 'BIH', 'BA', 'Bosnie-Herzégovine', 'BOSNIE-HERZÉGOVINE' union all
    select '072', 'BWA', 'BW', 'Botswana', 'BOTSWANA' union all
    select '074', 'BVT', 'BV', 'Île Bouvet', 'BOUVET, ÎLE' union all
    select '076', 'BRA', 'BR', 'Brésil', 'BRÉSIL' union all
    select '096', 'BRN', 'BN', 'Brunei', 'BRUNÉI DARUSSALAM' union all
    select '100', 'BGR', 'BG', 'Bulgarie', 'BULGARIE' union all
    select '854', 'BFA', 'BF', 'Burkina Faso', 'BURKINA FASO' union all
    select '108', 'BDI', 'BI', 'Burundi', 'BURUNDI' union all
    select '136', 'CYM', 'KY', 'Îles Caïmans', 'CAÏMANES, ÎLES' union all
    select '116', 'KHM', 'KH', 'Cambodge', 'CAMBODGE' union all
    select '120', 'CMR', 'CM', 'Cameroun', 'CAMEROUN' union all
    select '124', 'CAN', 'CA', 'Canada', 'CANADA' union all
    select '132', 'CPV', 'CV', 'Cap-Vert', 'CABO VERDE' union all
    select '140', 'CAF', 'CF', 'République centrafricaine', 'CENTRAFRICAINE, RÉPUBLIQUE' union all
    select '152', 'CHL', 'CL', 'Chili', 'CHILI' union all
    select '156', 'CHN', 'CN', 'Chine', 'CHINE' union all
    select '162', 'CXR', 'CX', 'Île Christmas', 'CHRISTMAS, ÎLE' union all
    select '196', 'CYP', 'CY', 'Chypre', 'CHYPRE' union all
    select '166', 'CCK', 'CC', 'Îles Cocos', 'COCOS (KEELING), ÎLES' union all
    select '170', 'COL', 'CO', 'Colombie', 'COLOMBIE' union all
    select '174', 'COM', 'KM', 'Comores', 'COMORES' union all
    select '178', 'COG', 'CG', 'République du Congo', 'CONGO' union all
    select '180', 'COD', 'CD', 'République démocratique du Congo', 'CONGO, RÉPUBLIQUE DÉMOCRATIQUE DU' union all
    select '184', 'COK', 'CK', 'Îles Cook', 'COOK, ÎLES' union all
    select '410', 'KOR', 'KR', 'Corée du Sud', 'CORÉE, RÉPUBLIQUE DE' union all
    select '408', 'PRK', 'KP', 'Corée du Nord', 'CORÉE, RÉPUBLIQUE POPULAIRE DÉMOCRATIQUE DE' union all
    select '188', 'CRI', 'CR', 'Costa Rica', 'COSTA RICA' union all
    select '384', 'CIV', 'CI', 'Côte d''Ivoire', 'CÔTE D''IVOIRE' union all
    select '191', 'HRV', 'HR', 'Croatie', 'CROATIE' union all
    select '192', 'CUB', 'CU', 'Cuba', 'CUBA' union all
    select '531', 'CUW', 'CW', 'Curaçao', 'CURAÇAO' union all
    select '208', 'DNK', 'DK', 'Danemark', 'DANEMARK' union all
    select '262', 'DJI', 'DJ', 'Djibouti', 'DJIBOUTI' union all
    select '214', 'DOM', 'DO', 'République dominicaine', 'DOMINICAINE, RÉPUBLIQUE' union all
    select '212', 'DMA', 'DM', 'Dominique', 'DOMINIQUE' union all
    select '818', 'EGY', 'EG', 'Égypte', 'ÉGYPTE' union all
    select '222', 'SLV', 'SV', 'Salvador', 'EL SALVADOR' union all
    select '784', 'ARE', 'AE', 'Émirats arabes unis', 'ÉMIRATS ARABES UNIS' union all
    select '218', 'ECU', 'EC', 'Équateur', 'ÉQUATEUR' union all
    select '232', 'ERI', 'ER', 'Érythrée', 'ÉRYTHRÉE' union all
    select '724', 'ESP', 'ES', 'Espagne', 'ESPAGNE' union all
    select '233', 'EST', 'EE', 'Estonie', 'ESTONIE' union all
    select '840', 'USA', 'US', 'États-Unis', 'ÉTATS-UNIS' union all
    select '231', 'ETH', 'ET', 'Éthiopie', 'ÉTHIOPIE' union all
    select '238', 'FLK', 'FK', 'Malouines', 'FALKLAND, ÎLES (MALVINAS)' union all
    select '234', 'FRO', 'FO', 'Îles Féroé', 'FÉROÉ, ÎLES' union all
    select '242', 'FJI', 'FJ', 'Fidji', 'FIDJI' union all
    select '246', 'FIN', 'FI', 'Finlande', 'FINLANDE' union all
    select '250', 'FRA', 'FR', 'France', 'FRANCE' union all
    select '266', 'GAB', 'GA', 'Gabon', 'GABON' union all
    select '270', 'GMB', 'GM', 'Gambie', 'GAMBIE' union all
    select '268', 'GEO', 'GE', 'Géorgie', 'GÉORGIE' union all
    select '239', 'SGS', 'GS', 'Géorgie du Sud-et-les îles Sandwich du Sud', 'GÉORGIE DU SUD ET LES ÎLES SANDWICH DU SUD' union all
    select '288', 'GHA', 'GH', 'Ghana', 'GHANA' union all
    select '292', 'GIB', 'GI', 'Gibraltar', 'GIBRALTAR' union all
    select '300', 'GRC', 'GR', 'Grèce', 'GRÈCE' union all
    select '308', 'GRD', 'GD', 'Grenade', 'GRENADE' union all
    select '304', 'GRL', 'GL', 'Groenland', 'GROENLAND' union all
    select '312', 'GLP', 'GP', 'Guadeloupe', 'GUADELOUPE' union all
    select '316', 'GUM', 'GU', 'Guam', 'GUAM' union all
    select '320', 'GTM', 'GT', 'Guatemala', 'GUATEMALA' union all
    select '831', 'GGY', 'GG', 'Guernesey', 'GUERNESEY' union all
    select '324', 'GIN', 'GN', 'Guinée', 'GUINÉE' union all
    select '624', 'GNB', 'GW', 'Guinée-Bissau', 'GUINÉE-BISSAU' union all
    select '226', 'GNQ', 'GQ', 'Guinée équatoriale', 'GUINÉE ÉQUATORIALE' union all
    select '328', 'GUY', 'GY', 'Guyana', 'GUYANA' union all
    select '254', 'GUF', 'GF', 'Guyane', 'GUYANE FRANÇAISE' union all
    select '332', 'HTI', 'HT', 'Haïti', 'HAÏTI' union all
    select '334', 'HMD', 'HM', 'Îles Heard-et-MacDonald', 'HEARD ET MACDONALD, ÎLES' union all
    select '340', 'HND', 'HN', 'Honduras', 'HONDURAS' union all
    select '344', 'HKG', 'HK', 'Hong Kong', 'HONG KONG' union all
    select '348', 'HUN', 'HU', 'Hongrie', 'HONGRIE' union all
    select '833', 'IMN', 'IM', 'Île de Man', 'ÎLE DE MAN' union all
    select '581', 'UMI', 'UM', 'Îles mineures éloignées des États-Unis', 'ÎLES MINEURES ÉLOIGNÉES DES ÉTATS-UNIS' union all
    select '092', 'VGB', 'VG', 'Îles Vierges britanniques', 'ÎLES VIERGES BRITANNIQUES' union all
    select '850', 'VIR', 'VI', 'Îles Vierges des États-Unis', 'ÎLES VIERGES DES ÉTATS-UNIS' union all
    select '356', 'IND', 'IN', 'Inde', 'INDE' union all
    select '360', 'IDN', 'ID', 'Indonésie', 'INDONÉSIE' union all
    select '364', 'IRN', 'IR', 'Iran', 'IRAN, RÉPUBLIQUE ISLAMIQUE D''' union all
    select '368', 'IRQ', 'IQ', 'Irak', 'IRAQ' union all
    select '372', 'IRL', 'IE', 'Irlande', 'IRLANDE' union all
    select '352', 'ISL', 'IS', 'Islande', 'ISLANDE' union all
    select '376', 'ISR', 'IL', 'Israël', 'ISRAËL' union all
    select '380', 'ITA', 'IT', 'Italie', 'ITALIE' union all
    select '388', 'JAM', 'JM', 'Jamaïque', 'JAMAÏQUE' union all
    select '392', 'JPN', 'JP', 'Japon', 'JAPON' union all
    select '832', 'JEY', 'JE', 'Jersey', 'JERSEY' union all
    select '400', 'JOR', 'JO', 'Jordanie', 'JORDANIE' union all
    select '398', 'KAZ', 'KZ', 'Kazakhstan', 'KAZAKHSTAN' union all
    select '404', 'KEN', 'KE', 'Kenya', 'KENYA' union all
    select '417', 'KGZ', 'KG', 'Kirghizistan', 'KIRGHIZISTAN' union all
    select '296', 'KIR', 'KI', 'Kiribati', 'KIRIBATI' union all
    select '414', 'KWT', 'KW', 'Koweït', 'KOWEÏT' union all
    select '418', 'LAO', 'LA', 'Laos', 'LAO, RÉPUBLIQUE DÉMOCRATIQUE POPULAIRE' union all
    select '426', 'LSO', 'LS', 'Lesotho', 'LESOTHO' union all
    select '428', 'LVA', 'LV', 'Lettonie', 'LETTONIE' union all
    select '422', 'LBN', 'LB', 'Liban', 'LIBAN' union all
    select '430', 'LBR', 'LR', 'Liberia', 'LIBÉRIA' union all
    select '434', 'LBY', 'LY', 'Libye', 'LIBYE' union all
    select '438', 'LIE', 'LI', 'Liechtenstein', 'LIECHTENSTEIN' union all
    select '440', 'LTU', 'LT', 'Lituanie', 'LITUANIE' union all
    select '442', 'LUX', 'LU', 'Luxembourg', 'LUXEMBOURG' union all
    select '446', 'MAC', 'MO', 'Macao', 'MACAO' union all
    select '807', 'MKD', 'MK', 'Macédoine du Nord', 'RÉPUBLIQUE DE MACÉDOINE' union all
    select '450', 'MDG', 'MG', 'Madagascar', 'MADAGASCAR' union all
    select '458', 'MYS', 'MY', 'Malaisie', 'MALAISIE' union all
    select '454', 'MWI', 'MW', 'Malawi', 'MALAWI' union all
    select '462', 'MDV', 'MV', 'Maldives', 'MALDIVES' union all
    select '466', 'MLI', 'ML', 'Mali', 'MALI' union all
    select '470', 'MLT', 'MT', 'Malte', 'MALTE' union all
    select '580', 'MNP', 'MP', 'Îles Mariannes du Nord', 'MARIANNES DU NORD, ÎLES' union all
    select '504', 'MAR', 'MA', 'Maroc', 'MAROC' union all
    select '584', 'MHL', 'MH', 'Îles Marshall', 'MARSHALL, ÎLES' union all
    select '474', 'MTQ', 'MQ', 'Martinique', 'MARTINIQUE' union all
    select '480', 'MUS', 'MU', 'Maurice', 'MAURICE' union all
    select '478', 'MRT', 'MR', 'Mauritanie', 'MAURITANIE' union all
    select '175', 'MYT', 'YT', 'Mayotte', 'MAYOTTE' union all
    select '484', 'MEX', 'MX', 'Mexique', 'MEXIQUE' union all
    select '583', 'FSM', 'FM', 'États fédérés de Micronésie', 'MICRONÉSIE, ÉTATS FÉDÉRÉS DE' union all
    select '498', 'MDA', 'MD', 'Moldavie', 'MOLDAVIE' union all
    select '492', 'MCO', 'MC', 'Monaco', 'MONACO' union all
    select '496', 'MNG', 'MN', 'Mongolie', 'MONGOLIE' union all
    select '499', 'MNE', 'ME', 'Monténégro', 'MONTÉNÉGRO' union all
    select '500', 'MSR', 'MS', 'Montserrat', 'MONTSERRAT' union all
    select '508', 'MOZ', 'MZ', 'Mozambique', 'MOZAMBIQUE' union all
    select '104', 'MMR', 'MM', 'Birmanie', 'MYANMAR' union all
    select '516', 'NAM', 'NA', 'Namibie', 'NAMIBIE' union all
    select '520', 'NRU', 'NR', 'Nauru', 'NAURU' union all
    select '524', 'NPL', 'NP', 'Népal', 'NÉPAL' union all
    select '558', 'NIC', 'NI', 'Nicaragua', 'NICARAGUA' union all
    select '562', 'NER', 'NE', 'Niger', 'NIGER' union all
    select '566', 'NGA', 'NG', 'Nigeria', 'NIGÉRIA' union all
    select '570', 'NIU', 'NU', 'Niue', 'NIUÉ' union all
    select '574', 'NFK', 'NF', 'Île Norfolk', 'NORFOLK, ÎLE' union all
    select '578', 'NOR', 'NO', 'Norvège', 'NORVÈGE' union all
    select '540', 'NCL', 'NC', 'Nouvelle-Calédonie', 'NOUVELLE-CALÉDONIE' union all
    select '554', 'NZL', 'NZ', 'Nouvelle-Zélande', 'NOUVELLE-ZÉLANDE' union all
    select '086', 'IOT', 'IO', 'Territoire britannique de l''océan Indien', 'OCÉAN INDIEN, TERRITOIRE BRITANNIQUE DE L''' union all
    select '512', 'OMN', 'OM', 'Oman', 'OMAN' union all
    select '800', 'UGA', 'UG', 'Ouganda', 'OUGANDA' union all
    select '860', 'UZB', 'UZ', 'Ouzbékistan', 'OUZBÉKISTAN' union all
    select '586', 'PAK', 'PK', 'Pakistan', 'PAKISTAN' union all
    select '585', 'PLW', 'PW', 'Palaos', 'PALAOS' union all
    select '275', 'PSE', 'PS', 'Palestine', 'ÉTAT DE PALESTINE' union all
    select '591', 'PAN', 'PA', 'Panama', 'PANAMA' union all
    select '598', 'PNG', 'PG', 'Papouasie-Nouvelle-Guinée', 'PAPOUASIE-NOUVELLE-GUINÉE' union all
    select '600', 'PRY', 'PY', 'Paraguay', 'PARAGUAY' union all
    select '528', 'NLD', 'NL', 'Pays-Bas', 'PAYS-BAS' union all
    select '604', 'PER', 'PE', 'Pérou', 'PÉROU' union all
    select '608', 'PHL', 'PH', 'Philippines', 'PHILIPPINES' union all
    select '612', 'PCN', 'PN', 'Îles Pitcairn', 'PITCAIRN' union all
    select '616', 'POL', 'PL', 'Pologne', 'POLOGNE' union all
    select '258', 'PYF', 'PF', 'Polynésie française', 'POLYNÉSIE FRANÇAISE' union all
    select '630', 'PRI', 'PR', 'Porto Rico', 'PORTO RICO' union all
    select '620', 'PRT', 'PT', 'Portugal', 'PORTUGAL' union all
    select '634', 'QAT', 'QA', 'Qatar', 'QATAR' union all
    select '638', 'REU', 'RE', 'La Réunion', 'RÉUNION' union all
    select '642', 'ROU', 'RO', 'Roumanie', 'ROUMANIE' union all
    select '826', 'GBR', 'GB', 'Royaume-Uni', 'ROYAUME-UNI' union all
    select '643', 'RUS', 'RU', 'Russie', 'RUSSIE, FÉDÉRATION DE' union all
    select '646', 'RWA', 'RW', 'Rwanda', 'RWANDA' union all
    select '732', 'ESH', 'EH', 'République arabe sahraouie démocratique', 'SAHARA OCCIDENTAL' union all
    select '652', 'BLM', 'BL', 'Saint-Barthélemy', 'SAINT-BARTHÉLEMY' union all
    select '659', 'KNA', 'KN', 'Saint-Christophe-et-Niévès', 'SAINT-KITTS-ET-NEVIS' union all
    select '674', 'SMR', 'SM', 'Saint-Marin', 'SAINT-MARIN' union all
    select '663', 'MAF', 'MF', 'Saint-Martin', 'SAINT-MARTIN (PARTIE FRANÇAISE)' union all
    select '534', 'SXM', 'SX', 'Saint-Martin', 'SAINT-MARTIN (PARTIE NÉERLANDAISE)' union all
    select '666', 'SPM', 'PM', 'Saint-Pierre-et-Miquelon', 'SAINT-PIERRE-ET-MIQUELON' union all
    select '336', 'VAT', 'VA', 'Saint-Siège (État de la Cité du Vatican)', 'SAINT-SIÈGE (ÉTAT DE LA CITÉ DU VATICAN)' union all
    select '670', 'VCT', 'VC', 'Saint-Vincent-et-les-Grenadines', 'SAINT-VINCENT-ET-LES-GRENADINES' union all
    select '654', 'SHN', 'SH', 'Sainte-Hélène, Ascension et Tristan da Cunha', 'SAINTE-HÉLÈNE, ASCENSION ET TRISTAN DA CUNHA' union all
    select '662', 'LCA', 'LC', 'Sainte-Lucie', 'SAINTE-LUCIE' union all
    select '090', 'SLB', 'SB', 'Îles Salomon', 'SALOMON, ÎLES' union all
    select '882', 'WSM', 'WS', 'Samoa', 'SAMOA' union all
    select '016', 'ASM', 'AS', 'Samoa américaines', 'SAMOA AMÉRICAINES' union all
    select '678', 'STP', 'ST', 'Sao Tomé-et-Principe', 'SAO TOMÉ-ET-PRINCIPE' union all
    select '686', 'SEN', 'SN', 'Sénégal', 'SÉNÉGAL' union all
    select '688', 'SRB', 'RS', 'Serbie', 'SERBIE' union all
    select '690', 'SYC', 'SC', 'Seychelles', 'SEYCHELLES' union all
    select '694', 'SLE', 'SL', 'Sierra Leone', 'SIERRA LEONE' union all
    select '702', 'SGP', 'SG', 'Singapour', 'SINGAPOUR' union all
    select '703', 'SVK', 'SK', 'Slovaquie', 'SLOVAQUIE' union all
    select '705', 'SVN', 'SI', 'Slovénie', 'SLOVÉNIE' union all
    select '706', 'SOM', 'SO', 'Somalie', 'SOMALIE' union all
    select '729', 'SDN', 'SD', 'Soudan', 'SOUDAN' union all
    select '728', 'SSD', 'SS', 'Soudan du Sud', 'SOUDAN DU SUD' union all
    select '144', 'LKA', 'LK', 'Sri Lanka', 'SRI LANKA' union all
    select '752', 'SWE', 'SE', 'Suède', 'SUÈDE' union all
    select '756', 'CHE', 'CH', 'Suisse', 'SUISSE' union all
    select '740', 'SUR', 'SR', 'Suriname', 'SURINAME' union all
    select '744', 'SJM', 'SJ', 'Svalbard et ile Jan Mayen', 'SVALBARD ET ÎLE JAN MAYEN' union all
    select '748', 'SWZ', 'SZ', 'Eswatini', 'ESWATINI' union all
    select '760', 'SYR', 'SY', 'Syrie', 'SYRIENNE, RÉPUBLIQUE ARABE' union all
    select '762', 'TJK', 'TJ', 'Tadjikistan', 'TADJIKISTAN' union all
    select '158', 'TWN', 'TW', 'Taïwan / (République de Chine (Taïwan))', 'TAÏWAN' union all
    select '834', 'TZA', 'TZ', 'Tanzanie', 'TANZANIE, RÉPUBLIQUE UNIE DE' union all
    select '148', 'TCD', 'TD', 'Tchad', 'TCHAD' union all
    select '203', 'CZE', 'CZ', 'Tchéquie', 'TCHÉQUIE' union all
    select '260', 'ATF', 'TF', 'Terres australes et antarctiques françaises', 'TERRES AUSTRALES FRANÇAISES' union all
    select '764', 'THA', 'TH', 'Thaïlande', 'THAÏLANDE' union all
    select '626', 'TLS', 'TL', 'Timor oriental', 'TIMOR-LESTE' union all
    select '768', 'TGO', 'TG', 'Togo', 'TOGO' union all
    select '772', 'TKL', 'TK', 'Tokelau', 'TOKELAU' union all
    select '776', 'TON', 'TO', 'Tonga', 'TONGA' union all
    select '780', 'TTO', 'TT', 'Trinité-et-Tobago', 'TRINITÉ-ET-TOBAGO' union all
    select '788', 'TUN', 'TN', 'Tunisie', 'TUNISIE' union all
    select '795', 'TKM', 'TM', 'Turkménistan', 'TURKMÉNISTAN' union all
    select '796', 'TCA', 'TC', 'Îles Turques-et-Caïques', 'TURKS ET CAÏQUES, ÎLES' union all
    select '792', 'TUR', 'TR', 'Turquie', 'TURQUIE' union all
    select '798', 'TUV', 'TV', 'Tuvalu', 'TUVALU' union all
    select '804', 'UKR', 'UA', 'Ukraine', 'UKRAINE' union all
    select '858', 'URY', 'UY', 'Uruguay', 'URUGUAY' union all
    select '548', 'VUT', 'VU', 'Vanuatu', 'VANUATU' union all
    select '862', 'VEN', 'VE', 'Venezuela', 'VENEZUELA, RÉPUBLIQUE BOLIVARIENNE DU' union all
    select '704', 'VNM', 'VN', 'Viêt Nam', 'VIET NAM' union all
    select '876', 'WLF', 'WF', 'Wallis-et-Futuna', 'WALLIS-ET-FUTUNA' union all
    select '887', 'YEM', 'YE', 'Yémen', 'YÉMEN' union all
    select '894', 'ZMB', 'ZM', 'Zambie', 'ZAMBIE' union all
    select '716', 'ZWE', 'ZW', 'Zimbabwe', 'ZIMBABWE'
)
insert into pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, source_id, source_code, histo_createur_id)
    select nextval('pays_id_seq'), code_iso, code_iso_alpha3, code_iso_alpha2, tmp.libelle, libelle_iso, s.id, 'SYGAL::'||code_iso, u.id
    from tmp, source s, utilisateur u
    where s.code = 'SYGAL::sygal' and u.username = 'sygal-app'
;

update pays set code_pays_apogee = '100', libelle_nationalite = 'Français(e)' where code_iso = '250';
update pays set code_pays_apogee = '101', libelle_nationalite = 'Danois(e)' where code_iso = '208';
update pays set code_pays_apogee = '102', libelle_nationalite = 'Islandais(e)' where code_iso = '352';
update pays set code_pays_apogee = '103', libelle_nationalite = 'Norvegien(ne)' where code_iso = '578';
update pays set code_pays_apogee = '104', libelle_nationalite = 'Suedois(e)' where code_iso = '752';
update pays set code_pays_apogee = '105', libelle_nationalite = 'Finlandais(e)' where code_iso = '246';
update pays set code_pays_apogee = '106', libelle_nationalite = 'Estonien(ne)' where code_iso = '233';
update pays set code_pays_apogee = '107', libelle_nationalite = 'Lettonien(ne)' where code_iso = '428';
update pays set code_pays_apogee = '108', libelle_nationalite = 'Lithuanien(ne)' where code_iso = '440';
update pays set code_pays_apogee = '109', libelle_nationalite = 'Allemand(e)' where code_iso = '276';
update pays set code_pays_apogee = '110', libelle_nationalite = 'Autrichien(ne)' where code_iso = '040';
update pays set code_pays_apogee = '111', libelle_nationalite = 'Bulgare' where code_iso = '100';
update pays set code_pays_apogee = '112', libelle_nationalite = 'Hongrois(e)' where code_iso = '348';
update pays set code_pays_apogee = '113', libelle_nationalite = 'Liechtenstein' where code_iso = '438';
update pays set code_pays_apogee = '114', libelle_nationalite = 'Roumain(e)' where code_iso = '642';
update pays set code_pays_apogee = '116', libelle_nationalite = 'Tcheque' where code_iso = '203';
update pays set code_pays_apogee = '117', libelle_nationalite = 'Slovaque' where code_iso = '703';
update pays set code_pays_apogee = '118', libelle_nationalite = 'Bosniaque' where code_iso = '070';
update pays set code_pays_apogee = '119', libelle_nationalite = 'Croate' where code_iso = '191';
update pays set code_pays_apogee = '121', libelle_nationalite = 'Serbe' where code_iso = '688';
update pays set code_pays_apogee = '122', libelle_nationalite = 'Polonais(e)' where code_iso = '616';
update pays set code_pays_apogee = '123', libelle_nationalite = 'Russe' where code_iso = '643';
update pays set code_pays_apogee = '125', libelle_nationalite = 'Albanais(e)' where code_iso = '008';
update pays set code_pays_apogee = '126', libelle_nationalite = 'Grec(Que)' where code_iso = '300';
update pays set code_pays_apogee = '127', libelle_nationalite = 'Italien(ne)' where code_iso = '380';
update pays set code_pays_apogee = '128', libelle_nationalite = 'Saint Marin' where code_iso = '674';
update pays set code_pays_apogee = '129', libelle_nationalite = 'Vatican(e)' where code_iso = '336';
update pays set code_pays_apogee = '130', libelle_nationalite = 'Andorran(ne)' where code_iso = '020';
update pays set code_pays_apogee = '131', libelle_nationalite = 'Belge' where code_iso = '056';
update pays set code_pays_apogee = '132', libelle_nationalite = 'Britannique' where code_iso = '826';
update pays set code_pays_apogee = '134', libelle_nationalite = 'Espagnol(e)' where code_iso = '724';
update pays set code_pays_apogee = '135', libelle_nationalite = 'Neerlandais(e)' where code_iso = '533';
update pays set code_pays_apogee = '136', libelle_nationalite = 'Irlandais(e)' where code_iso = '372';
update pays set code_pays_apogee = '137', libelle_nationalite = 'Luxembourgeois(e)' where code_iso = '442';
update pays set code_pays_apogee = '138', libelle_nationalite = 'Monegasque' where code_iso = '492';
update pays set code_pays_apogee = '139', libelle_nationalite = 'Portugais(e)' where code_iso = '620';
update pays set code_pays_apogee = '140', libelle_nationalite = 'Suisse' where code_iso = '756';
update pays set code_pays_apogee = '144', libelle_nationalite = 'Maltais(e)' where code_iso = '470';
update pays set code_pays_apogee = '145', libelle_nationalite = 'Slovene' where code_iso = '705';
update pays set code_pays_apogee = '148', libelle_nationalite = 'Bielorusse' where code_iso = '112';
update pays set code_pays_apogee = '151', libelle_nationalite = 'Moldave' where code_iso = '498';
update pays set code_pays_apogee = '155', libelle_nationalite = 'Ukrainien(ne)' where code_iso = '804';
update pays set code_pays_apogee = '201', libelle_nationalite = 'Saoudien(ne)' where code_iso = '682';
update pays set code_pays_apogee = '203', libelle_nationalite = 'Irakien(ne)' where code_iso = '368';
update pays set code_pays_apogee = '204', libelle_nationalite = 'Iranien(ne)' where code_iso = '364';
update pays set code_pays_apogee = '205', libelle_nationalite = 'Libanais(e)' where code_iso = '422';
update pays set code_pays_apogee = '206', libelle_nationalite = 'Syrien(ne)' where code_iso = '760';
update pays set code_pays_apogee = '207', libelle_nationalite = 'Israelien(ne)' where code_iso = '376';
update pays set code_pays_apogee = '208', libelle_nationalite = 'Turc (Turque)' where code_iso = '792';
update pays set code_pays_apogee = '212', libelle_nationalite = 'Afghan(e)' where code_iso = '004';
update pays set code_pays_apogee = '213', libelle_nationalite = 'Pakistanais(e)' where code_iso = '586';
update pays set code_pays_apogee = '214', libelle_nationalite = 'Bhoutan' where code_iso = '064';
update pays set code_pays_apogee = '215', libelle_nationalite = 'Nepalais(e)' where code_iso = '524';
update pays set code_pays_apogee = '216', libelle_nationalite = 'Chinois(e)' where code_iso = '156';
update pays set code_pays_apogee = '217', libelle_nationalite = 'Japonais(e)' where code_iso = '392';
update pays set code_pays_apogee = '219', libelle_nationalite = 'Thailandais(e)' where code_iso = '764';
update pays set code_pays_apogee = '220', libelle_nationalite = 'Philippin(ne)' where code_iso = '608';
update pays set code_pays_apogee = '222', libelle_nationalite = 'Jordanien(ne)' where code_iso = '400';
update pays set code_pays_apogee = '223', libelle_nationalite = 'Indien(ne)' where code_iso = '356';
update pays set code_pays_apogee = '224', libelle_nationalite = 'Birman(e)' where code_iso = '104';
update pays set code_pays_apogee = '225', libelle_nationalite = 'Brunei' where code_iso = '096';
update pays set code_pays_apogee = '226', libelle_nationalite = 'Singapourien(ne)' where code_iso = '702';
update pays set code_pays_apogee = '227', libelle_nationalite = 'Malais(e)' where code_iso = '458';
update pays set code_pays_apogee = '229', libelle_nationalite = 'Maldives' where code_iso = '462';
update pays set code_pays_apogee = '230', libelle_nationalite = 'Chinois(e)' where code_iso = '';
update pays set code_pays_apogee = '231', libelle_nationalite = 'Indonesien(ne)' where code_iso = '360';
update pays set code_pays_apogee = '234', libelle_nationalite = 'Cambodgien(ne)' where code_iso = '116';
update pays set code_pays_apogee = '235', libelle_nationalite = 'Sri Lankais(e)' where code_iso = '144';
update pays set code_pays_apogee = '236', libelle_nationalite = 'Chinois(e) Taiwan' where code_iso = '158';
update pays set code_pays_apogee = '238', libelle_nationalite = 'Nord Coreen(ne)' where code_iso = '408';
update pays set code_pays_apogee = '239', libelle_nationalite = 'Sud Coreen(ne)' where code_iso = '410';
update pays set code_pays_apogee = '240', libelle_nationalite = 'Koweitien(ne)' where code_iso = '414';
update pays set code_pays_apogee = '241', libelle_nationalite = 'Laotien(ne)' where code_iso = '418';
update pays set code_pays_apogee = '242', libelle_nationalite = 'Mongol(e)' where code_iso = '496';
update pays set code_pays_apogee = '243', libelle_nationalite = 'Vietnamien(ne)' where code_iso = '704';
update pays set code_pays_apogee = '246', libelle_nationalite = 'Bengali(e)' where code_iso = '050';
update pays set code_pays_apogee = '247', libelle_nationalite = 'Emirats Arabes Unis' where code_iso = '784';
update pays set code_pays_apogee = '248', libelle_nationalite = 'Qatari(e)' where code_iso = '634';
update pays set code_pays_apogee = '249', libelle_nationalite = 'Barheinien(ne)' where code_iso = '048';
update pays set code_pays_apogee = '250', libelle_nationalite = 'Omanais(e)' where code_iso = '512';
update pays set code_pays_apogee = '251', libelle_nationalite = 'Yemenite' where code_iso = '887';
update pays set code_pays_apogee = '252', libelle_nationalite = 'Armenien(e)' where code_iso = '051';
update pays set code_pays_apogee = '253', libelle_nationalite = 'Azeri(e)' where code_iso = '031';
update pays set code_pays_apogee = '254', libelle_nationalite = 'Chypriote' where code_iso = '196';
update pays set code_pays_apogee = '255', libelle_nationalite = 'Georgien(ne)' where code_iso = '268';
update pays set code_pays_apogee = '256', libelle_nationalite = 'Kazakh' where code_iso = '398';
update pays set code_pays_apogee = '257', libelle_nationalite = 'Kirghizistanais(e)' where code_iso = '417';
update pays set code_pays_apogee = '258', libelle_nationalite = 'Ouzbek' where code_iso = '860';
update pays set code_pays_apogee = '259', libelle_nationalite = 'Tadjik' where code_iso = '762';
update pays set code_pays_apogee = '260', libelle_nationalite = 'Turkmene' where code_iso = '795';
update pays set code_pays_apogee = '301', libelle_nationalite = 'Egyptien(ne)' where code_iso = '818';
update pays set code_pays_apogee = '302', libelle_nationalite = 'Liberian(e)' where code_iso = '430';
update pays set code_pays_apogee = '303', libelle_nationalite = 'Sud Africain(e)' where code_iso = '710';
update pays set code_pays_apogee = '304', libelle_nationalite = 'Gambien(ne)' where code_iso = '270';
update pays set code_pays_apogee = '309', libelle_nationalite = 'Tanzanien(ne)' where code_iso = '834';
update pays set code_pays_apogee = '310', libelle_nationalite = 'Zimbabweien(ne)' where code_iso = '716';
update pays set code_pays_apogee = '311', libelle_nationalite = 'Namibien(ne)' where code_iso = '516';
update pays set code_pays_apogee = '312', libelle_nationalite = 'Zairois(e)' where code_iso = '180';
update pays set code_pays_apogee = '314', libelle_nationalite = 'Guineen(ne) Equatori' where code_iso = '226';
update pays set code_pays_apogee = '315', libelle_nationalite = 'Ethiopien(ne)' where code_iso = '231';
update pays set code_pays_apogee = '316', libelle_nationalite = 'Libyen(ne)' where code_iso = '434';
update pays set code_pays_apogee = '318', libelle_nationalite = 'Somalien(ne)' where code_iso = '706';
update pays set code_pays_apogee = '321', libelle_nationalite = 'Burundais(e)' where code_iso = '108';
update pays set code_pays_apogee = '322', libelle_nationalite = 'Camerounais(e)' where code_iso = '120';
update pays set code_pays_apogee = '323', libelle_nationalite = 'Centrafricain(e)' where code_iso = '140';
update pays set code_pays_apogee = '324', libelle_nationalite = 'Congolais(e)' where code_iso = '178';
update pays set code_pays_apogee = '326', libelle_nationalite = 'Ivoirien(ne)' where code_iso = '384';
update pays set code_pays_apogee = '327', libelle_nationalite = 'Beninois(e)' where code_iso = '204';
update pays set code_pays_apogee = '328', libelle_nationalite = 'Gabonais(e)' where code_iso = '266';
update pays set code_pays_apogee = '329', libelle_nationalite = 'Ghaneen(ne)' where code_iso = '288';
update pays set code_pays_apogee = '330', libelle_nationalite = 'Guineen(ne)' where code_iso = '324';
update pays set code_pays_apogee = '331', libelle_nationalite = 'Burkinabe' where code_iso = '854';
update pays set code_pays_apogee = '332', libelle_nationalite = 'Kenyan(ne)' where code_iso = '404';
update pays set code_pays_apogee = '333', libelle_nationalite = 'Malgache' where code_iso = '450';
update pays set code_pays_apogee = '334', libelle_nationalite = 'Malawien(ne)' where code_iso = '454';
update pays set code_pays_apogee = '335', libelle_nationalite = 'Malien(ne)' where code_iso = '466';
update pays set code_pays_apogee = '336', libelle_nationalite = 'Mauritanien(ne)' where code_iso = '478';
update pays set code_pays_apogee = '337', libelle_nationalite = 'Nigerien(ne)' where code_iso = '562';
update pays set code_pays_apogee = '338', libelle_nationalite = 'Nigerian(e)' where code_iso = '566';
update pays set code_pays_apogee = '339', libelle_nationalite = 'Ougandais(e)' where code_iso = '800';
update pays set code_pays_apogee = '340', libelle_nationalite = 'Ruandais(e)' where code_iso = '646';
update pays set code_pays_apogee = '341', libelle_nationalite = 'Senegalais(e)' where code_iso = '686';
update pays set code_pays_apogee = '342', libelle_nationalite = 'Sierra Leone' where code_iso = '694';
update pays set code_pays_apogee = '343', libelle_nationalite = 'Soudanais(e)' where code_iso = '729';
update pays set code_pays_apogee = '344', libelle_nationalite = 'Tchadien(ne)' where code_iso = '148';
update pays set code_pays_apogee = '345', libelle_nationalite = 'Togolais(e)' where code_iso = '768';
update pays set code_pays_apogee = '346', libelle_nationalite = 'Zambien(ne)' where code_iso = '894';
update pays set code_pays_apogee = '347', libelle_nationalite = 'Botswanais(e)' where code_iso = '072';
update pays set code_pays_apogee = '348', libelle_nationalite = 'Lesotho' where code_iso = '426';
update pays set code_pays_apogee = '350', libelle_nationalite = 'Marocain(e)' where code_iso = '504';
update pays set code_pays_apogee = '351', libelle_nationalite = 'Tunisien(ne)' where code_iso = '788';
update pays set code_pays_apogee = '352', libelle_nationalite = 'Algerien(ne)' where code_iso = '012';
update pays set code_pays_apogee = '390', libelle_nationalite = 'Mauricien(ne)' where code_iso = '480';
update pays set code_pays_apogee = '391', libelle_nationalite = 'Swazilandais(e)' where code_iso = '748';
update pays set code_pays_apogee = '392', libelle_nationalite = 'Guineen(ne) Bissau' where code_iso = '624';
update pays set code_pays_apogee = '393', libelle_nationalite = 'Mozambiquois(e)' where code_iso = '508';
update pays set code_pays_apogee = '394', libelle_nationalite = 'Sao Tome Et Principe' where code_iso = '678';
update pays set code_pays_apogee = '395', libelle_nationalite = 'Angolais(e)' where code_iso = '024';
update pays set code_pays_apogee = '396', libelle_nationalite = 'Cap Verdien(ne)' where code_iso = '132';
update pays set code_pays_apogee = '397', libelle_nationalite = 'Comorien(ne)' where code_iso = '174';
update pays set code_pays_apogee = '398', libelle_nationalite = 'Seychelles' where code_iso = '690';
update pays set code_pays_apogee = '399', libelle_nationalite = 'Djiboutien(ne)' where code_iso = '262';
update pays set code_pays_apogee = '401', libelle_nationalite = 'Canadien(ne)' where code_iso = '124';
update pays set code_pays_apogee = '404', libelle_nationalite = 'Americain(e)' where code_iso = '840';
update pays set code_pays_apogee = '405', libelle_nationalite = 'Mexicain(e)' where code_iso = '484';
update pays set code_pays_apogee = '406', libelle_nationalite = 'Costa Ricain(e)' where code_iso = '188';
update pays set code_pays_apogee = '407', libelle_nationalite = 'Cubain(e)' where code_iso = '192';
update pays set code_pays_apogee = '408', libelle_nationalite = 'Dominicain(e)' where code_iso = '214';
update pays set code_pays_apogee = '409', libelle_nationalite = 'Guatemalteque' where code_iso = '320';
update pays set code_pays_apogee = '410', libelle_nationalite = 'Haitien(ne)' where code_iso = '332';
update pays set code_pays_apogee = '411', libelle_nationalite = 'Hondurien(ne)' where code_iso = '340';
update pays set code_pays_apogee = '412', libelle_nationalite = 'Nicaraguais(e)' where code_iso = '558';
update pays set code_pays_apogee = '413', libelle_nationalite = 'Panameen(ne)' where code_iso = '591';
update pays set code_pays_apogee = '414', libelle_nationalite = 'El Salvadorien(ne)' where code_iso = '222';
update pays set code_pays_apogee = '415', libelle_nationalite = 'Argentin(e)' where code_iso = '032';
update pays set code_pays_apogee = '416', libelle_nationalite = 'Bresilien(ne)' where code_iso = '076';
update pays set code_pays_apogee = '417', libelle_nationalite = 'Chilien(ne)' where code_iso = '152';
update pays set code_pays_apogee = '418', libelle_nationalite = 'Bolivien(ne)' where code_iso = '068';
update pays set code_pays_apogee = '419', libelle_nationalite = 'Colombien(ne)' where code_iso = '170';
update pays set code_pays_apogee = '420', libelle_nationalite = 'Equatorien(ne)' where code_iso = '218';
update pays set code_pays_apogee = '421', libelle_nationalite = 'Paraguayen(ne)' where code_iso = '600';
update pays set code_pays_apogee = '422', libelle_nationalite = 'Peruvien(ne)' where code_iso = '604';
update pays set code_pays_apogee = '423', libelle_nationalite = 'Uruguayen(ne)' where code_iso = '858';
update pays set code_pays_apogee = '424', libelle_nationalite = 'Venezuelien(ne)' where code_iso = '862';
update pays set code_pays_apogee = '426', libelle_nationalite = 'Jamaicain(e)' where code_iso = '388';
update pays set code_pays_apogee = '428', libelle_nationalite = 'Guyanais(e)' where code_iso = '328';
update pays set code_pays_apogee = '429', libelle_nationalite = 'Belize' where code_iso = '084';
update pays set code_pays_apogee = '433', libelle_nationalite = 'Trinite Et Tobago' where code_iso = '780';
update pays set code_pays_apogee = '434', libelle_nationalite = 'Barbade' where code_iso = '052';
update pays set code_pays_apogee = '435', libelle_nationalite = 'Grenade Etgrenadines' where code_iso = '308';
update pays set code_pays_apogee = '436', libelle_nationalite = 'Bahamas' where code_iso = '044';
update pays set code_pays_apogee = '437', libelle_nationalite = 'Surinamais(e)' where code_iso = '740';
update pays set code_pays_apogee = '441', libelle_nationalite = 'Antigua Et Barbuda' where code_iso = '028';
update pays set code_pays_apogee = '442', libelle_nationalite = 'St Christophe Nieves' where code_iso = '659';
update pays set code_pays_apogee = '501', libelle_nationalite = 'Australien(ne)' where code_iso = '036';
update pays set code_pays_apogee = '502', libelle_nationalite = 'Neo Zelandais(e)' where code_iso = '554';
update pays set code_pays_apogee = '506', libelle_nationalite = 'Samoan(ne)' where code_iso = '882';
update pays set code_pays_apogee = '507', libelle_nationalite = 'Nauru' where code_iso = '520';
update pays set code_pays_apogee = '514', libelle_nationalite = 'Vanuatu' where code_iso = '548';
update pays set code_pays_apogee = '515', libelle_nationalite = 'Ile Marshall' where code_iso = '584';
update pays set code_pays_apogee = '516', libelle_nationalite = 'Micronesien(ne)' where code_iso = '583';
update pays set code_pays_apogee = '508', libelle_nationalite = 'Fidji' where code_iso = '242';
update pays set code_pays_apogee = '513', libelle_nationalite = 'Kiribati' where code_iso = '296';
update pays set code_pays_apogee = '510', libelle_nationalite = 'Papouasie' where code_iso = '598';
update pays set code_pays_apogee = '512', libelle_nationalite = 'Salomon' where code_iso = '090';
update pays set code_pays_apogee = '503', libelle_nationalite = 'Britannique' where code_iso = '612';
update pays set code_pays_apogee = '431', libelle_nationalite = 'Neerlandais(e)' where code_iso = '';
update pays set code_pays_apogee = '425', libelle_nationalite = 'Britannique' where code_iso = '';
update pays set code_pays_apogee = '427', libelle_nationalite = 'Britannique' where code_iso = '';
update pays set code_pays_apogee = '432', libelle_nationalite = 'Americain(e)' where code_iso = '630';
update pays set code_pays_apogee = '306', libelle_nationalite = 'Britannique' where code_iso = '654';
update pays set code_pays_apogee = '308', libelle_nationalite = 'Britannique' where code_iso = '086';
update pays set code_pays_apogee = '313', libelle_nationalite = 'Espagnol(e)' where code_iso = '';
update pays set code_pays_apogee = '319', libelle_nationalite = 'Portugais(e)' where code_iso = '';
update pays set code_pays_apogee = '133', libelle_nationalite = 'Britannique' where code_iso = '292';
update pays set code_pays_apogee = '156', libelle_nationalite = 'Macedoine Ex. Rep Yougo' where code_iso = '807';
update pays set code_pays_apogee = '232', libelle_nationalite = 'Chinois(e)' where code_iso = '';
update pays set code_pays_apogee = '317', libelle_nationalite = 'Erythree' where code_iso = '232';
update pays set code_pays_apogee = '438', libelle_nationalite = 'Dominicain(e)' where code_iso = '212';
update pays set code_pays_apogee = '439', libelle_nationalite = 'Sainte-Lucien(e)' where code_iso = '662';
update pays set code_pays_apogee = '430', libelle_nationalite = 'Danois(e)' where code_iso = '304';
update pays set code_pays_apogee = '440', libelle_nationalite = 'Saint Vincent Et Grenadines Nord' where code_iso = '670';
update pays set code_pays_apogee = '505', libelle_nationalite = 'Americain(e)' where code_iso = '';
update pays set code_pays_apogee = '509', libelle_nationalite = 'Tongua Ou Friendly' where code_iso = '776';
update pays set code_pays_apogee = '511', libelle_nationalite = 'Tuvalu' where code_iso = '798';
update pays set code_pays_apogee = '261', libelle_nationalite = 'Palestinien(ne)' where code_iso = '275';
update pays set code_pays_apogee = '389', libelle_nationalite = 'Sahara Occidental' where code_iso = '732';
update pays set code_pays_apogee = '517', libelle_nationalite = 'Palaosien(ne)' where code_iso = '585';
update pays set code_pays_apogee = '518', libelle_nationalite = 'Polynesien(ne)' where code_iso = '';
update pays set code_pays_apogee = '120', libelle_nationalite = 'Montenegro' where code_iso = '499';
update pays set code_pays_apogee = '157', libelle_nationalite = 'Kosovo' where code_iso = '';
update pays set code_pays_apogee = '262', libelle_nationalite = 'Timor Oriental' where code_iso = '626';
update pays set code_pays_apogee = '519', libelle_nationalite = 'Serbe' where code_iso = '';
update pays set code_pays_apogee = '520', libelle_nationalite = 'Serbe' where code_iso = '';
update pays set code_pays_apogee = '521', libelle_nationalite = 'Congolais(e)' where code_iso = '';
update pays set code_pays_apogee = '158', libelle_nationalite = 'Britannique' where code_iso = '';
update pays set code_pays_apogee = '159', libelle_nationalite = 'Britannique' where code_iso = '';
