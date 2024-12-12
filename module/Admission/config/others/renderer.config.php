<?php

namespace Admission;

use Admission\Renderer\AdmissionTemplateVariable;
use Admission\Renderer\AdmissionConventionFormationDoctoraleTemplateVariable;
use Admission\Renderer\AdmissionEtudiantTemplateVariable;
use Admission\Renderer\AdmissionFinancementTemplateVariable;
use Admission\Renderer\AdmissionInscriptionTemplateVariable;
use Admission\Renderer\AdmissionOperationTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return array(
    'renderer' => [
        'template_variables' => [
            'factories' => [
                AdmissionTemplateVariable::class => InvokableFactory::class,
                AdmissionEtudiantTemplateVariable::class => InvokableFactory::class,
                AdmissionInscriptionTemplateVariable::class => InvokableFactory::class,
                AdmissionFinancementTemplateVariable::class => InvokableFactory::class,
                AdmissionOperationTemplateVariable::class => InvokableFactory::class,
                AdmissionConventionFormationDoctoraleTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'admission' => AdmissionTemplateVariable::class,
                'admissionEtudiant' => AdmissionEtudiantTemplateVariable::class,
                'admissionInscription' => AdmissionInscriptionTemplateVariable::class,
                'admissionFinancement' => AdmissionFinancementTemplateVariable::class,
                'admissionConventionFormationDoctorale' => AdmissionConventionFormationDoctoraleTemplateVariable::class,
                'admissionOperation' => AdmissionOperationTemplateVariable::class,
                'admissionValidation' => AdmissionOperationTemplateVariable::class, // NB : admissionValidation <=> admissionOperation
                'admissionAvis' => AdmissionOperationTemplateVariable::class, // NB : admissionAvis <=> admissionOperation
            ],
        ],
    ],
);
