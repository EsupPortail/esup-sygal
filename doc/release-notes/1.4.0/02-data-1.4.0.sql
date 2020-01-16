
--
-- Inversion des étapes du WF Diffusion et Attestations.
--

update WF_ETAPE set ORDRE = 13  where CODE = 'AUTORISATION_DIFFUSION_THESE' ;
update WF_ETAPE set ORDRE = 18  where CODE = 'ATTESTATIONS' ;
update WF_ETAPE set ORDRE = 213 where CODE = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE' ;
update WF_ETAPE set ORDRE = 218 where CODE = 'ATTESTATIONS_VERSION_CORRIGEE' ;

-- COMMIT ;
