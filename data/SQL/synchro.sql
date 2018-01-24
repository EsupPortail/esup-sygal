--
-- Activer le job de synchronisation.
--
BEGIN
  DBMS_SCHEDULER.enable(name=>'"SODOCT"."synchronisation"');
END;

--
-- Désactiver le job de synchronisation.
--
BEGIN
  DBMS_SCHEDULER.disable(name=>'"SODOCT"."synchronisation"', force => TRUE);
END;