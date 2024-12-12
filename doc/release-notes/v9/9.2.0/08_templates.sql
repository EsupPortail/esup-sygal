--
-- 9.2.0
--

--
-- Renommage de variables (macros).
--

update unicaen_renderer_macro set variable_name = 'admissionConventionFormationDoctorale'
                              where variable_name in ('admissionConventionFormationDoctoraleData',
                                                      'conventionFormationDoctorale');
update unicaen_renderer_macro set variable_name = 'admission'
                              where variable_name in ('admissionRecapitulatif');

update unicaen_renderer_macro set variable_name = 'admissionOperation'
                              where variable_name = 'admissionValidation';
update unicaen_renderer_macro set variable_name = 'admissionOperation'
                              where variable_name = 'admissionAvis';

update unicaen_renderer_macro set variable_name = 'soutenanceProposition'
                              where variable_name = 'soutenance';

update unicaen_renderer_macro set variable_name = 'admission',
                                  methode_name = 'getOperationAttenduNotificationAnomalies'
                              where code = 'AdmissionOperationAttenduNotification#Anomalies';
update unicaen_renderer_macro set variable_name = 'admission',
                                  methode_name = 'getAdmissionAvisNotificationAnomalies'
                              where code = 'AdmissionAvisNotification#Anomalies';

update unicaen_renderer_macro set code = replace(code, 'AdmissionRecapitulatif', 'Admission')
                              where code like 'AdmissionRecapitulatif#%';
update unicaen_renderer_template set document_corps = replace(document_corps, '[AdmissionRecapitulatif#', '[Admission#'),
                                     document_sujet = replace(document_sujet, '[AdmissionRecapitulatif#', '[Admission#')
                                 where document_corps like '%AdmissionRecapitulatif#%';

update unicaen_renderer_macro set code = 'AdmissionOperation#LibelleType',
                                  variable_name = 'admissionOperation',
                                  methode_name = 'getLibelleType'
                              where code = 'TypeValidation#Libelle';
update unicaen_renderer_template set document_corps = replace(document_corps, '[TypeValidation#Libelle', '[AdmissionOperation#LibelleType'),
                                     document_sujet = replace(document_sujet, '[TypeValidation#Libelle', '[AdmissionOperation#LibelleType')
                                 where document_corps like '%TypeValidation#Libelle%';

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionOperation#LibelleType', '<p>Retourne le libellé du type d''opération (validation, avis)</p>', 'admissionOperation', 'getLibelleType');


update unicaen_renderer_macro set code = 'SoutenanceMembre#Denomination',
                                  variable_name = 'soutenanceMembre',
                                  methode_name = 'getDenomination'
where code = 'Membre#Denomination';
update unicaen_renderer_template set document_corps = replace(document_corps, '[Membre#Denomination', '[SoutenanceMembre#Denomination'),
                                     document_sujet = replace(document_sujet, '[Membre#Denomination', '[SoutenanceMembre#Denomination')
where document_corps like '%Membre#Denomination%';

update unicaen_renderer_macro set code = 'SoutenanceMembre#MembresPouvantEtrePresidentDuJuryAsUl',
                                  variable_name = 'soutenanceMembre',
                                  methode_name = 'getMembresPouvantEtrePresidentDuJuryAsUl'
where code = 'Membre#MembresPouvantEtrePresidentDuJuryAsUl';
update unicaen_renderer_template set document_corps = replace(document_corps, '[Membre#MembresPouvantEtrePresidentDuJuryAsUl', '[SoutenanceMembre#MembresPouvantEtrePresidentDuJuryAsUl'),
                                     document_sujet = replace(document_sujet, '[Membre#MembresPouvantEtrePresidentDuJuryAsUl', '[SoutenanceMembre#MembresPouvantEtrePresidentDuJuryAsUl')
where document_corps like '%Membre#MembresPouvantEtrePresidentDuJuryAsUl%';


select * from unicaen_renderer_template where code = 'ADMISSION_OPERATION_ATTENDUE';
select * from unicaen_renderer_macro where variable_name = 'admissionOperation';
