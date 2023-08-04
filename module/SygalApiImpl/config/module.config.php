<?php

use Laminas\Log\Logger;
use UnicaenDbImport\Domain\Operation;

return [
    'import' => [
        'synchros' => [
            [
                'name' => 'inscription-administrative',
                'order' => 150,
                'source' => [
                    'name' => 'sygal',
                    'code' => 'app',
                    'table' => 'src_inscription_administrative',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                ],
                'destination' => [
                    'name' => 'application',
                    'table' => 'inscription_administrative',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                    'id_strategy' => 'SEQUENCE',
                    'id_sequence' => null,
                ],
            ],

            /**
             * Prototype : mise à jour de source_code doctorant.
             *
             * Pourquoi ? Sera peut-être utile si un jour Pégase nous signale les changements d'INE d'apprenant
             *            (l'INE est utilisé dans SyGAL comme source_code).
             *
             *       create table tmp_doctorant_source_code
             *       (
             *          id bigserial,
             *          insert_date timestamp(0) default ('now'::text)::timestamp without time zone,
             *          source_id bigint not null,
             *          source_code_old varchar(64) not null,
             *          source_code varchar(64) not null,
             *          histo_creation timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
             *          histo_modification timestamp(0),
             *          histo_destruction timestamp(0),
             *          histo_createur_id bigint not null,
             *          histo_modificateur_id bigint,
             *          histo_destructeur_id bigint
             *       );
             *
             *       create index tmp_doctorant_source_code_source_code_index
             *          on tmp_doctorant_source_code (source_code);
             *       create index tmp_doctorant_source_code_source_id_index
             *          on tmp_doctorant_source_code (source_id);
             *       create unique index tmp_doctorant_source_code_unique_index
             *          on tmp_doctorant_source_code (source_id, source_code);
             *
             *       insert into tmp_doctorant_source_code(source_id, source_code_old, source_code, histo_createur_id)
             *          select s.id, 'INSA::1304000614x', 'INSA::___1304000614x', 1
             *          from source s where s.code = 'INSA::physalis'
             *       ;
             *       select * from tmp_doctorant_source_code;
             *       -- select * from src_doctorant_source_code;
             *       select * from doctorant where source_code like 'INSA::%' and histo_destruction is null;
             *       select * from doctorant where source_code = 'INSA::1304000614x';
             *       select * from doctorant where id = 57863;
             *       select * from v_diff_doctorant ;
             *
             *       --drop view src_doctorant_source_code;
             *       create or replace view src_doctorant_source_code as
             *          select t.id,
             *          src.id as source_id,
             *          tmp.source_code
             *          from tmp_doctorant_source_code tmp
             *          join doctorant t on tmp.source_code_old = t.source_code
             *          join source src on src.id = tmp.source_id
             *       ;
             */
//            [
//                'name' => 'doctorant_source_code',
//                'order' => 0,
//                'source' => [
//                    'name' => 'application',
//                    'code' => 'app',
//                    'table' => 'src_doctorant_source_code',
//                    'connection' => 'default',
//                    'source_code_column' => 'id',
//                ],
//                'destination' => [
//                    'name' => 'application',
//                    'table' => 'doctorant',
//                    'connection' => 'default',
//                    'source_code_column' => 'id',
//                ],
//                'operations' => [
//                    Operation::OPERATION_UPDATE, // seule opération
//                ],
//            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            \Laminas\Log\PsrLoggerAbstractAdapterFactory::class,
        ],
    ],
    'psr_log' => [
        'inscription_resource_logger' => [
            'writers' => [
                'stream' => [
                    'name' => 'stream',
                    'priority' => 1,
                    'options' => [
                        'stream' => '/dev/null',
                        'filters' => Logger::INFO,
                    ],
                ],
            ],
        ],
    ],
];
