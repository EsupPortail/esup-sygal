--
-- Tous les utilisateurs ayant un rôle attribué manuellement.
--
select 'SoDoct', u.DISPLAY_NAME, ur.ROLE_ID
from utilisateur u
  join USER_ROLE_LINKER url on url.USER_ID = u.id
  join user_role ur on ur.id = url.ROLE_ID
order by u.DISPLAY_NAME
;