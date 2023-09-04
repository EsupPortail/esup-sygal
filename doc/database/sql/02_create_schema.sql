--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.11
-- Dumped by pg_dump version 15.4 (Ubuntu 15.4-1.pgdg20.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- Name: unaccent; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA public;


--
-- Name: EXTENSION unaccent; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION unaccent IS 'text search dictionary that removes accents';


--
-- Name: avis_enum; Type: TYPE; Schema: public; Owner: :dbuser
--

CREATE TYPE public.avis_enum AS ENUM (
    'Favorable',
    'Défavorable'
);


ALTER TYPE public.avis_enum OWNER TO :dbuser;

--
-- Name: comprise_entre(timestamp without time zone, timestamp without time zone, timestamp without time zone, numeric); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.comprise_entre(date_debut timestamp without time zone, date_fin timestamp without time zone, date_obs timestamp without time zone DEFAULT NULL::timestamp without time zone, inclusif numeric DEFAULT 0) RETURNS numeric
    LANGUAGE plpgsql STABLE SECURITY DEFINER
    AS $$
DECLARE
    d_deb timestamp;
    d_fin timestamp;
    d_obs timestamp;
    res   NUMERIC;
BEGIN
    IF inclusif = 1 THEN
        d_obs := date_trunc('day', COALESCE(date_obs, current_timestamp));
        d_deb := date_trunc('day', COALESCE(date_debut, d_obs));
        d_fin := date_trunc('day', COALESCE(date_fin, d_obs));
        IF d_obs BETWEEN d_deb AND d_fin THEN
            RETURN 1;
        ELSE
            RETURN 0;
        END IF;
    ELSE
        d_obs := date_trunc('day', COALESCE(date_obs, current_timestamp));
        d_deb := date_trunc('day', date_debut);
        d_fin := date_trunc('day', date_fin);

        IF d_deb IS NOT NULL AND NOT d_deb <= d_obs THEN
            RETURN 0;
        END IF;
        IF d_fin IS NOT NULL AND NOT d_obs < d_fin THEN
            RETURN 0;
        END IF;
        RETURN 1;
    END IF;
END;
$$;


ALTER FUNCTION public.comprise_entre(date_debut timestamp without time zone, date_fin timestamp without time zone, date_obs timestamp without time zone, inclusif numeric) OWNER TO :dbuser;

--
-- Name: individu_haystack(text, text, text, text, text); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.individu_haystack(nom_usuel text, nom_patronymique text, prenom1 text, email text, source_code text) RETURNS character varying
    LANGUAGE plpgsql STABLE SECURITY DEFINER
    AS $$
BEGIN
    return trim(str_reduce(
                                                                                    coalesce(NOM_USUEL, '') || ' ' ||
                                                                                    coalesce(PRENOM1, '') || ' ' ||
                                                                                    coalesce(NOM_PATRONYMIQUE, '') || ' ' ||
                                                                                    coalesce(PRENOM1, '') || ' ' ||
                                                                                    coalesce(PRENOM1, '') || ' ' ||
                                                                                    coalesce(NOM_USUEL, '') || ' ' ||
                                                                                    coalesce(PRENOM1, '') || ' ' ||
                                                                                    coalesce(NOM_PATRONYMIQUE, '') || ' ' ||
                                                                                    coalesce(EMAIL, '') || ' ' ||
                                                                                    coalesce(SOURCE_CODE, '')
        ));
END;
$$;


ALTER FUNCTION public.individu_haystack(nom_usuel text, nom_patronymique text, prenom1 text, email text, source_code text) OWNER TO :dbuser;

--
-- Name: privilege__grant_privilege_to_profile(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.privilege__grant_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
  -- insertion dans 'profil_privilege' (si pas déjà fait)
  insert into profil_privilege (privilege_id, profil_id)
  select p.id as privilege_id, profil.id as profil_id
  from profil
         join categorie_privilege cp on cp.code = categoryCode
         join privilege p on p.categorie_id = cp.id and p.code = privilegeCode
  where profil.role_id = profileroleid
    and not exists(
          select * from profil_privilege where privilege_id = p.id and profil_id = profil.id
    );

  perform privilege__update_role_privilege();
END;
$$;


ALTER FUNCTION public.privilege__grant_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) OWNER TO :dbuser;

--
-- Name: privilege__revoke_privilege_to_profile(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.privilege__revoke_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
  delete
  from profil_privilege pp1
  where exists(
                select *
                from profil_privilege pp
                       join profil on pp.profil_id = profil.id and role_id = profileRoleId
                       join privilege p on pp.privilege_id = p.id
                       join categorie_privilege cp on p.categorie_id = cp.id
                where p.code = privilegeCode
                  and cp.code = categoryCode
                  and pp.profil_id = pp1.profil_id
                  and pp.privilege_id = pp1.privilege_id
          );

  perform privilege__update_role_privilege();
END;
$$;


ALTER FUNCTION public.privilege__revoke_privilege_to_profile(categorycode character varying, privilegecode character varying, profileroleid character varying) OWNER TO :dbuser;

--
-- Name: privilege__update_role_privilege(); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.privilege__update_role_privilege() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
  -- création des 'role_privilege' manquants d'après le contenu de 'profil_to_role' et de 'profil_privilege'
  insert into role_privilege (role_id, privilege_id)
  select p2r.role_id, pp.privilege_id
  from profil_to_role p2r
         join profil pr on pr.id = p2r.profil_id
         join profil_privilege pp on pp.profil_id = pr.id
  where not exists(
          select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
    );

  -- suppression des 'role_privilege' en trop d'après le contenu de 'profil_to_role' et de 'profil_privilege'
  delete from role_privilege rp
  where not exists (
          select *
          from profil_to_role p2r
                 join profil_privilege pp on pp.profil_id = p2r.profil_id
          where rp.role_id = p2r.role_id and rp.privilege_id = pp.privilege_id
    );
END;
$$;


ALTER FUNCTION public.privilege__update_role_privilege() OWNER TO :dbuser;

--
-- Name: str_reduce(text); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.str_reduce(str text) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
--     RETURN utl_raw.cast_to_varchar2(str COLLATE "binary_ai");
--     return unaccent_string(str);
    return lower(unaccent(str));
END;
$$;


ALTER FUNCTION public.str_reduce(str text) OWNER TO :dbuser;

--
-- Name: transfert_these(bigint, bigint); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.transfert_these(fromtheseid bigint, totheseid bigint) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- select 'update '||rpad(table_name, 35)||' set '||column_name||' = toTheseId where '||column_name||' = fromTheseId ;' from information_schema.columns
-- where column_name ilike '%these_id%' and
--         table_name not ilike 'v\_%' and
--         table_name not ilike 'src_%' and
--         table_name not ilike 'tmp_%';

    update soutenance_proposition   set histo_destruction = now(), histo_destructeur_id = 1 where these_id = toTheseId ;

    update attestation              set these_id = toTheseId where these_id = fromTheseId ;
    update diffusion                set these_id = toTheseId where these_id = fromTheseId ;
    update fichier_these            set these_id = toTheseId where these_id = fromTheseId ;
    update metadonnee_these         set these_id = toTheseId where these_id = fromTheseId ;
    update rapport                  set these_id = toTheseId where these_id = fromTheseId ;
    update rdv_bu                   set these_id = toTheseId where these_id = fromTheseId ;
    update soutenance_intervention  set these_id = toTheseId where these_id = fromTheseId ;
    update soutenance_proposition   set these_id = toTheseId where these_id = fromTheseId ;
    update validation                          set these_id = toTheseId where these_id = fromTheseId ;
END;
$$;


ALTER FUNCTION public.transfert_these(fromtheseid bigint, totheseid bigint) OWNER TO :dbuser;

--
-- Name: trigger_fct_individu_rech_update(); Type: FUNCTION; Schema: public; Owner: :dbuser
--

CREATE FUNCTION public.trigger_fct_individu_rech_update() RETURNS trigger
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
BEGIN
    IF TG_OP = 'INSERT' THEN
        insert into INDIVIDU_RECH(ID, HAYSTACK)
        values (NEW.ID, individu_haystack(NEW.NOM_USUEL, NEW.NOM_PATRONYMIQUE, NEW.PRENOM1, NEW.EMAIL, NEW.SOURCE_CODE));
    END IF;
    IF TG_OP = 'UPDATE' THEN
        UPDATE INDIVIDU_RECH
        SET HAYSTACK = individu_haystack(NEW.NOM_USUEL, NEW.NOM_PATRONYMIQUE, NEW.PRENOM1, NEW.EMAIL, NEW.SOURCE_CODE)
        where ID = NEW.ID;
    END IF;
    IF TG_OP = 'DELETE' THEN
        delete from INDIVIDU_RECH where id = OLD.ID;
    END IF;
    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;

END
$$;


ALTER FUNCTION public.trigger_fct_individu_rech_update() OWNER TO :dbuser;

SET default_tablespace = '';

--
-- Name: acteur; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.acteur (
    id bigint NOT NULL,
    individu_id bigint NOT NULL,
    these_id bigint NOT NULL,
    role_id bigint NOT NULL,
    qualite character varying(200),
    lib_role_compl character varying(200),
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone,
    acteur_etablissement_id bigint,
    acteur_uniterech_id bigint
);


ALTER TABLE public.acteur OWNER TO :dbuser;

--
-- Name: acteur_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.acteur_id_seq
    START WITH 270596
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.acteur_id_seq OWNER TO :dbuser;

--
-- Name: api_log; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.api_log (
    id bigint NOT NULL,
    req_uri character varying(2000) NOT NULL,
    req_start_date timestamp without time zone NOT NULL,
    req_end_date timestamp without time zone,
    req_status character varying(32),
    req_response text,
    req_etablissement character varying(64),
    req_table character varying(64)
);


ALTER TABLE public.api_log OWNER TO :dbuser;

--
-- Name: TABLE api_log; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.api_log IS 'Logs des appels aux API des établissements.';


--
-- Name: api_log_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.api_log_id_seq
    START WITH 2002607
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.api_log_id_seq OWNER TO :dbuser;

--
-- Name: attestation; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.attestation (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    ver_depo_est_ver_ref boolean DEFAULT false NOT NULL,
    ex_impr_conform_ver_depo boolean,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    version_corrigee boolean DEFAULT false NOT NULL,
    creation_auto boolean DEFAULT false NOT NULL
);


ALTER TABLE public.attestation OWNER TO :dbuser;

--
-- Name: attestation_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.attestation_id_seq
    START WITH 10946
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.attestation_id_seq OWNER TO :dbuser;

--
-- Name: categorie_privilege; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.categorie_privilege (
    id bigint NOT NULL,
    code character varying(150) NOT NULL,
    libelle character varying(200) NOT NULL,
    ordre bigint
);


ALTER TABLE public.categorie_privilege OWNER TO :dbuser;

--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.categorie_privilege_id_seq
    START WITH 3200
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.categorie_privilege_id_seq OWNER TO :dbuser;

--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.categorie_privilege_id_seq OWNED BY public.categorie_privilege.id;


--
-- Name: csi_membre; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.csi_membre (
    id integer NOT NULL,
    these_id bigint NOT NULL,
    genre character varying(1) NOT NULL,
    qualite bigint NOT NULL,
    etablissement character varying(128) NOT NULL,
    role_id character varying(64) NOT NULL,
    exterieur character varying(3),
    email character varying(256),
    acteur_id bigint,
    visio boolean DEFAULT false NOT NULL,
    nom character varying(256),
    prenom character varying(256),
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.csi_membre OWNER TO :dbuser;

--
-- Name: csi_membre_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.csi_membre_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.csi_membre_id_seq OWNER TO :dbuser;

--
-- Name: csi_membre_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.csi_membre_id_seq OWNED BY public.csi_membre.id;


--
-- Name: diffusion; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.diffusion (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    droit_auteur_ok boolean DEFAULT false NOT NULL,
    autoris_mel smallint DEFAULT 0 NOT NULL,
    autoris_embargo_duree character varying(20),
    autoris_motif character varying(2000),
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    certif_charte_diff boolean DEFAULT false NOT NULL,
    confident boolean DEFAULT false NOT NULL,
    confident_date_fin timestamp without time zone,
    orcid character varying(200),
    nnt character varying(30),
    hal_id character varying(100),
    version_corrigee boolean DEFAULT false NOT NULL,
    creation_auto boolean DEFAULT false NOT NULL
);


ALTER TABLE public.diffusion OWNER TO :dbuser;

--
-- Name: COLUMN diffusion.droit_auteur_ok; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.diffusion.droit_auteur_ok IS 'Je garantis que tous les documents de la version mise en ligne sont libres de droits ou que j''ai acquis les droits afférents pour la reproduction et la représentation sur tous supports';


--
-- Name: COLUMN diffusion.autoris_mel; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.diffusion.autoris_mel IS 'J''autorise la mise en ligne de la version de diffusion de la thèse sur Internet';


--
-- Name: COLUMN diffusion.autoris_embargo_duree; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.diffusion.autoris_embargo_duree IS 'Durée de l''embargo éventuel';


--
-- Name: COLUMN diffusion.certif_charte_diff; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.diffusion.certif_charte_diff IS 'En cochant cette case, je certifie avoir pris connaissance de la charte de diffusion des thèses en vigueur à la date de signature de la convention de mise en ligne';


--
-- Name: COLUMN diffusion.confident; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.diffusion.confident IS 'La thèse est-elle confidentielle ?';


--
-- Name: diffusion_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.diffusion_id_seq
    START WITH 11091
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.diffusion_id_seq OWNER TO :dbuser;

--
-- Name: doctorant; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.doctorant (
    id bigint NOT NULL,
    etablissement_id bigint NOT NULL,
    individu_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone,
    ine character varying(64),
    source_code_sav character varying(64)
);


ALTER TABLE public.doctorant OWNER TO :dbuser;

--
-- Name: TABLE doctorant; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.doctorant IS 'Doctorant par établissement.';


--
-- Name: doctorant_compl_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.doctorant_compl_id_seq
    START WITH 801
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.doctorant_compl_id_seq OWNER TO :dbuser;

--
-- Name: doctorant_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.doctorant_id_seq
    START WITH 48308
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.doctorant_id_seq OWNER TO :dbuser;

--
-- Name: doctorant_mission_enseignement; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.doctorant_mission_enseignement (
    id integer NOT NULL,
    doctorant_id integer NOT NULL,
    annee_univ integer NOT NULL,
    histo_creation timestamp without time zone DEFAULT now() NOT NULL,
    histo_createur_id integer DEFAULT 1 NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id integer,
    histo_destruction timestamp without time zone,
    histo_destructeur_id integer
);


ALTER TABLE public.doctorant_mission_enseignement OWNER TO :dbuser;

--
-- Name: doctorant_mission_enseignement_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.doctorant_mission_enseignement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.doctorant_mission_enseignement_id_seq OWNER TO :dbuser;

--
-- Name: doctorant_mission_enseignement_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.doctorant_mission_enseignement_id_seq OWNED BY public.doctorant_mission_enseignement.id;


--
-- Name: domaine_scientifique; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.domaine_scientifique (
    id bigint NOT NULL,
    libelle character varying(128) NOT NULL
);


ALTER TABLE public.domaine_scientifique OWNER TO :dbuser;

--
-- Name: domaine_scientifique_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.domaine_scientifique_id_seq
    START WITH 21
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.domaine_scientifique_id_seq OWNER TO :dbuser;

--
-- Name: ecole_doct; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.ecole_doct (
    id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    structure_id bigint NOT NULL,
    theme character varying(1024),
    offre_these character varying(2047)
);


ALTER TABLE public.ecole_doct OWNER TO :dbuser;

--
-- Name: ecole_doct_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.ecole_doct_id_seq
    START WITH 101
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.ecole_doct_id_seq OWNER TO :dbuser;

--
-- Name: etablissement; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.etablissement (
    id bigint NOT NULL,
    structure_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modification timestamp without time zone,
    histo_destruction timestamp without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    domaine character varying(50),
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    est_membre boolean DEFAULT false NOT NULL,
    est_associe boolean DEFAULT false NOT NULL,
    est_comue boolean DEFAULT false NOT NULL,
    est_etab_inscription boolean DEFAULT false NOT NULL,
    signature_convocation_id bigint,
    email_assistance character varying(64),
    email_bibliotheque character varying(64),
    email_doctorat character varying(64),
    est_ced boolean DEFAULT false NOT NULL
);


ALTER TABLE public.etablissement OWNER TO :dbuser;

--
-- Name: COLUMN etablissement.domaine; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.etablissement.domaine IS 'Domaine DNS de l''établissement tel que présent dans l''EPPN Shibboleth, ex: unicaen.fr.';


--
-- Name: COLUMN etablissement.est_ced; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.etablissement.est_ced IS 'Indique si cet établissement est un Collège des écoles doctorales';


--
-- Name: etablissement_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.etablissement_id_seq
    START WITH 10643
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.etablissement_id_seq OWNER TO :dbuser;

--
-- Name: etablissement_rattach; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.etablissement_rattach (
    id bigint NOT NULL,
    unite_id bigint NOT NULL,
    etablissement_id bigint NOT NULL
);


ALTER TABLE public.etablissement_rattach OWNER TO :dbuser;

--
-- Name: etablissement_rattach_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.etablissement_rattach_id_seq
    START WITH 421
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.etablissement_rattach_id_seq OWNER TO :dbuser;

--
-- Name: faq; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.faq (
    id bigint NOT NULL,
    question character varying(2000) NOT NULL,
    reponse character varying(2000) NOT NULL,
    ordre bigint
);


ALTER TABLE public.faq OWNER TO :dbuser;

--
-- Name: faq_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.faq_id_seq
    START WITH 101
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.faq_id_seq OWNER TO :dbuser;

--
-- Name: fichier; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.fichier (
    id bigint NOT NULL,
    uuid character varying(60) NOT NULL,
    nature_id bigint DEFAULT 1 NOT NULL,
    nom character varying(255) NOT NULL,
    nom_original character varying(255) DEFAULT 'NULL'::character varying NOT NULL,
    type_mime character varying(128) NOT NULL,
    taille bigint NOT NULL,
    description character varying(256),
    version_fichier_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    permanent_id character varying(50)
);


ALTER TABLE public.fichier OWNER TO :dbuser;

--
-- Name: fichier_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.fichier_id_seq
    START WITH 30318
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.fichier_id_seq OWNER TO :dbuser;

--
-- Name: fichier_these; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.fichier_these (
    id bigint NOT NULL,
    fichier_id bigint NOT NULL,
    these_id bigint NOT NULL,
    est_annexe boolean DEFAULT false NOT NULL,
    est_expurge boolean DEFAULT false NOT NULL,
    est_conforme boolean,
    retraitement character varying(50),
    est_partiel boolean DEFAULT false NOT NULL
);


ALTER TABLE public.fichier_these OWNER TO :dbuser;

--
-- Name: fichier_these_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.fichier_these_id_seq
    START WITH 22447
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.fichier_these_id_seq OWNER TO :dbuser;

--
-- Name: fichier_these_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.fichier_these_id_seq OWNED BY public.fichier_these.id;


--
-- Name: financement; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.financement (
    id bigint NOT NULL,
    source_id bigint NOT NULL,
    these_id bigint,
    annee bigint NOT NULL,
    origine_financement_id bigint NOT NULL,
    complement_financement character varying(256),
    quotite_financement character varying(8),
    date_debut timestamp without time zone,
    date_fin timestamp without time zone,
    source_code character varying(64) DEFAULT 'NULL'::character varying NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    code_type_financement character varying(8),
    libelle_type_financement character varying(100)
);


ALTER TABLE public.financement OWNER TO :dbuser;

--
-- Name: financement_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.financement_id_seq
    START WITH 17061
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.financement_id_seq OWNER TO :dbuser;

--
-- Name: formation_enquete_categorie; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_enquete_categorie (
    id integer NOT NULL,
    libelle character varying(1024) NOT NULL,
    description text,
    ordre integer NOT NULL,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_enquete_categorie OWNER TO :dbuser;

--
-- Name: formation_enquete_categorie_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_enquete_categorie_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_enquete_categorie_id_seq OWNER TO :dbuser;

--
-- Name: formation_enquete_categorie_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_enquete_categorie_id_seq OWNED BY public.formation_enquete_categorie.id;


--
-- Name: formation_enquete_question; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_enquete_question (
    id integer NOT NULL,
    libelle character varying(1024) NOT NULL,
    description text,
    ordre integer NOT NULL,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone,
    categorie_id integer
);


ALTER TABLE public.formation_enquete_question OWNER TO :dbuser;

--
-- Name: formation_enquete_question_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_enquete_question_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_enquete_question_id_seq OWNER TO :dbuser;

--
-- Name: formation_enquete_question_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_enquete_question_id_seq OWNED BY public.formation_enquete_question.id;


--
-- Name: formation_enquete_reponse; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_enquete_reponse (
    id integer NOT NULL,
    inscription_id integer NOT NULL,
    question_id integer NOT NULL,
    niveau integer NOT NULL,
    description text,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_enquete_reponse OWNER TO :dbuser;

--
-- Name: formation_enquete_reponse_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_enquete_reponse_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_enquete_reponse_id_seq OWNER TO :dbuser;

--
-- Name: formation_enquete_reponse_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_enquete_reponse_id_seq OWNED BY public.formation_enquete_reponse.id;


--
-- Name: formation_etat; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_etat (
    code character varying(1) NOT NULL,
    libelle character varying(1024),
    description text,
    icone character varying(1024),
    couleur character varying(1024),
    ordre bigint
);


ALTER TABLE public.formation_etat OWNER TO :dbuser;

--
-- Name: formation_formateur; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_formateur (
    id integer NOT NULL,
    individu_id integer NOT NULL,
    session_id integer NOT NULL,
    description text,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_formateur OWNER TO :dbuser;

--
-- Name: formation_formateur_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_formateur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_formateur_id_seq OWNER TO :dbuser;

--
-- Name: formation_formateur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_formateur_id_seq OWNED BY public.formation_formateur.id;


--
-- Name: formation_formation; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_formation (
    id integer NOT NULL,
    module_id integer,
    libelle character varying(1024) NOT NULL,
    description text,
    lien character varying(1024),
    site_id integer,
    responsable_id integer,
    modalite character varying(1),
    type character varying(1),
    type_structure_id integer,
    taille_liste_principale integer,
    taille_liste_complementaire integer,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_formation OWNER TO :dbuser;

--
-- Name: formation_formation_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_formation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_formation_id_seq OWNER TO :dbuser;

--
-- Name: formation_formation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_formation_id_seq OWNED BY public.formation_formation.id;


--
-- Name: formation_inscription; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_inscription (
    id integer NOT NULL,
    session_id integer NOT NULL,
    doctorant_id integer NOT NULL,
    liste character varying(1),
    description text,
    validation_enquete timestamp without time zone,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_inscription OWNER TO :dbuser;

--
-- Name: formation_inscription_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_inscription_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_inscription_id_seq OWNER TO :dbuser;

--
-- Name: formation_inscription_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_inscription_id_seq OWNED BY public.formation_inscription.id;


--
-- Name: formation_module; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_module (
    id integer NOT NULL,
    libelle text NOT NULL,
    description text,
    lien text,
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id integer NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id integer,
    histo_destruction timestamp without time zone,
    histo_destructeur_id integer
);


ALTER TABLE public.formation_module OWNER TO :dbuser;

--
-- Name: formation_module_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_module_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_module_id_seq OWNER TO :dbuser;

--
-- Name: formation_module_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_module_id_seq OWNED BY public.formation_module.id;


--
-- Name: formation_presence; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_presence (
    id integer NOT NULL,
    inscription_id integer NOT NULL,
    seance_id integer NOT NULL,
    temoin character varying(1),
    description text,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_presence OWNER TO :dbuser;

--
-- Name: formation_presence_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_presence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_presence_id_seq OWNER TO :dbuser;

--
-- Name: formation_presence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_presence_id_seq OWNED BY public.formation_presence.id;


--
-- Name: formation_seance; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_seance (
    id integer NOT NULL,
    session_id integer NOT NULL,
    debut timestamp without time zone NOT NULL,
    fin timestamp without time zone NOT NULL,
    lieu character varying(1024),
    description text,
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_seance OWNER TO :dbuser;

--
-- Name: formation_seance_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_seance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_seance_id_seq OWNER TO :dbuser;

--
-- Name: formation_seance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_seance_id_seq OWNED BY public.formation_seance.id;


--
-- Name: formation_session; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_session (
    id integer NOT NULL,
    session_index integer,
    formation_id integer NOT NULL,
    description text,
    taille_liste_principale integer,
    taille_liste_complementaire integer,
    site_id integer,
    responsable_id integer,
    modalite character varying(1),
    type character varying(1),
    type_structure_id integer,
    etat_code character varying(1),
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone,
    date_fermeture_inscription timestamp without time zone
);


ALTER TABLE public.formation_session OWNER TO :dbuser;

--
-- Name: formation_session_etat_heurodatage; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_session_etat_heurodatage (
    id integer NOT NULL,
    session_id integer NOT NULL,
    etat_id character varying(1) NOT NULL,
    heurodatage timestamp without time zone NOT NULL,
    utilisateur_id integer NOT NULL
);


ALTER TABLE public.formation_session_etat_heurodatage OWNER TO :dbuser;

--
-- Name: formation_session_etat_heurodatage_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_session_etat_heurodatage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_session_etat_heurodatage_id_seq OWNER TO :dbuser;

--
-- Name: formation_session_etat_heurodatage_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_session_etat_heurodatage_id_seq OWNED BY public.formation_session_etat_heurodatage.id;


--
-- Name: formation_session_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_session_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_session_id_seq OWNER TO :dbuser;

--
-- Name: formation_session_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_session_id_seq OWNED BY public.formation_session.id;


--
-- Name: formation_session_structure_valide; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.formation_session_structure_valide (
    id integer NOT NULL,
    session_id integer NOT NULL,
    structure_id integer NOT NULL,
    lieu character varying(1024),
    histo_createur_id integer NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_modificateur_id integer,
    histo_modification timestamp without time zone,
    histo_destructeur_id integer,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.formation_session_structure_valide OWNER TO :dbuser;

--
-- Name: formation_session_structure_valide_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.formation_session_structure_valide_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.formation_session_structure_valide_id_seq OWNER TO :dbuser;

--
-- Name: formation_session_structure_valide_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.formation_session_structure_valide_id_seq OWNED BY public.formation_session_structure_valide.id;


--
-- Name: horodatage_horodatage; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.horodatage_horodatage (
    id integer NOT NULL,
    date timestamp without time zone NOT NULL,
    user_id integer NOT NULL,
    type character varying(1024) NOT NULL,
    complement character varying(1024)
);


ALTER TABLE public.horodatage_horodatage OWNER TO :dbuser;

--
-- Name: horodatage_horodatage_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.horodatage_horodatage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.horodatage_horodatage_id_seq OWNER TO :dbuser;

--
-- Name: horodatage_horodatage_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.horodatage_horodatage_id_seq OWNED BY public.horodatage_horodatage.id;


--
-- Name: import_log; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.import_log (
    type character varying(128) NOT NULL,
    name character varying(128) NOT NULL,
    success boolean NOT NULL,
    log text NOT NULL,
    started_on timestamp without time zone NOT NULL,
    ended_on timestamp without time zone NOT NULL,
    import_hash character varying(64),
    id bigint NOT NULL,
    has_problems boolean DEFAULT false NOT NULL
);


ALTER TABLE public.import_log OWNER TO :dbuser;

--
-- Name: import_log_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.import_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.import_log_id_seq OWNER TO :dbuser;

--
-- Name: import_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.import_log_id_seq OWNED BY public.import_log.id;


--
-- Name: import_notif; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.import_notif (
    id bigint NOT NULL,
    table_name character varying(50) NOT NULL,
    column_name character varying(50) NOT NULL,
    operation character varying(50) DEFAULT 'UPDATE'::character varying NOT NULL,
    to_value character varying(1000),
    description character varying(200),
    url character varying(1000) NOT NULL
);


ALTER TABLE public.import_notif OWNER TO :dbuser;

--
-- Name: import_notif_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.import_notif_id_seq
    START WITH 21
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.import_notif_id_seq OWNER TO :dbuser;

--
-- Name: import_obs_notif; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.import_obs_notif (
    id bigint NOT NULL,
    import_observ_id bigint NOT NULL,
    notif_id bigint NOT NULL
);


ALTER TABLE public.import_obs_notif OWNER TO :dbuser;

--
-- Name: import_obs_result_notif; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.import_obs_result_notif (
    id bigint NOT NULL,
    import_observ_result_id bigint NOT NULL,
    notif_result_id bigint NOT NULL
);


ALTER TABLE public.import_obs_result_notif OWNER TO :dbuser;

--
-- Name: import_observ; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.import_observ (
    id bigint NOT NULL,
    code character varying(50) NOT NULL,
    table_name character varying(50) NOT NULL,
    column_name character varying(50) NOT NULL,
    operation character varying(50) DEFAULT 'UPDATE'::character varying NOT NULL,
    to_value character varying(1000),
    description character varying(200),
    enabled boolean DEFAULT false NOT NULL,
    filter text
);


ALTER TABLE public.import_observ OWNER TO :dbuser;

--
-- Name: import_observ_etab_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.import_observ_etab_id_seq
    START WITH 21
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.import_observ_etab_id_seq OWNER TO :dbuser;

--
-- Name: import_observ_etab_resu_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.import_observ_etab_resu_id_seq
    START WITH 497604
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.import_observ_etab_resu_id_seq OWNER TO :dbuser;

--
-- Name: import_observ_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.import_observ_id_seq
    START WITH 21
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.import_observ_id_seq OWNER TO :dbuser;

--
-- Name: import_observ_result; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.import_observ_result (
    id bigint NOT NULL,
    import_observ_id bigint NOT NULL,
    date_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    source_code character varying(64) NOT NULL,
    resultat text NOT NULL,
    date_notif timestamp without time zone,
    date_limite_notif timestamp without time zone,
    z_ioer_id bigint
);


ALTER TABLE public.import_observ_result OWNER TO :dbuser;

--
-- Name: import_observ_result_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.import_observ_result_id_seq
    START WITH 600225
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.import_observ_result_id_seq OWNER TO :dbuser;

--
-- Name: indicateur; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.indicateur (
    id bigint NOT NULL,
    libelle character varying(128) NOT NULL,
    description character varying(1024),
    requete character varying(2048),
    actif bigint,
    display_as character varying(128),
    class character varying(128)
);


ALTER TABLE public.indicateur OWNER TO :dbuser;

--
-- Name: indicateur_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.indicateur_id_seq
    START WITH 161
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.indicateur_id_seq OWNER TO :dbuser;

--
-- Name: individu; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.individu (
    id bigint NOT NULL,
    type character varying(32),
    civilite character varying(5),
    nom_usuel character varying(60) NOT NULL,
    nom_patronymique character varying(60),
    prenom1 character varying(60) NOT NULL,
    prenom2 character varying(60),
    prenom3 character varying(60),
    email character varying(255),
    date_naissance timestamp without time zone,
    nationalite character varying(128),
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone,
    supann_id character varying(30),
    etablissement_id bigint,
    pays_id_nationalite bigint,
    id_ref character varying(32),
    source_code_sav character varying(64)
);


ALTER TABLE public.individu OWNER TO :dbuser;

--
-- Name: individu_compl; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.individu_compl (
    id integer NOT NULL,
    individu_id integer NOT NULL,
    email character varying(1024),
    etablissement_id integer,
    unite_id integer,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::date NOT NULL,
    histo_createur_id integer NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id integer,
    histo_destruction timestamp without time zone,
    histo_destructeur_id integer
);


ALTER TABLE public.individu_compl OWNER TO :dbuser;

--
-- Name: individu_compl_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.individu_compl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.individu_compl_id_seq OWNER TO :dbuser;

--
-- Name: individu_compl_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.individu_compl_id_seq OWNED BY public.individu_compl.id;


--
-- Name: individu_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.individu_id_seq
    START WITH 1076322
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.individu_id_seq OWNER TO :dbuser;

--
-- Name: individu_rech; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.individu_rech (
    id bigint NOT NULL,
    haystack text
);


ALTER TABLE public.individu_rech OWNER TO :dbuser;

--
-- Name: individu_role; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.individu_role (
    id bigint NOT NULL,
    individu_id bigint,
    role_id bigint
);


ALTER TABLE public.individu_role OWNER TO :dbuser;

--
-- Name: TABLE individu_role; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.individu_role IS 'Attributions à des individus de rôles sans lien avec une thèse en particulier, ex: bureau des doctorats.';


--
-- Name: individu_role_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.individu_role_id_seq
    START WITH 4846
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.individu_role_id_seq OWNER TO :dbuser;

--
-- Name: information; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.information (
    id bigint NOT NULL,
    titre character varying(256) NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    contenu text NOT NULL,
    priorite bigint DEFAULT 0 NOT NULL,
    est_visible boolean DEFAULT true NOT NULL,
    langue_id character varying(64) NOT NULL
);


ALTER TABLE public.information OWNER TO :dbuser;

--
-- Name: information_fichier_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.information_fichier_id_seq
    START WITH 481
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.information_fichier_id_seq OWNER TO :dbuser;

--
-- Name: information_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.information_id_seq
    START WITH 241
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.information_id_seq OWNER TO :dbuser;

--
-- Name: information_langue; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.information_langue (
    id character varying(64) NOT NULL,
    libelle character varying(128),
    drapeau text
);


ALTER TABLE public.information_langue OWNER TO :dbuser;

--
-- Name: liste_diff; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.liste_diff (
    id bigint NOT NULL,
    adresse character varying(256) NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.liste_diff OWNER TO :dbuser;

--
-- Name: liste_diff_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.liste_diff_id_seq
    START WITH 81
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.liste_diff_id_seq OWNER TO :dbuser;

--
-- Name: mail_confirmation; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.mail_confirmation (
    id bigint NOT NULL,
    individu_id bigint NOT NULL,
    email character varying(256) NOT NULL,
    etat character varying(1),
    code character varying(64),
    refus_liste_diff boolean DEFAULT false NOT NULL
);


ALTER TABLE public.mail_confirmation OWNER TO :dbuser;

--
-- Name: COLUMN mail_confirmation.refus_liste_diff; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.mail_confirmation.refus_liste_diff IS 'Refus de recevoir les messages des listes de diffusion sur cette adresse';


--
-- Name: mail_confirmation_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.mail_confirmation_id_seq
    START WITH 19446
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.mail_confirmation_id_seq OWNER TO :dbuser;

--
-- Name: metadonnee_these; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.metadonnee_these (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    titre character varying(2048) NOT NULL,
    langue character varying(40) NOT NULL,
    resume text DEFAULT 'NULL'::text NOT NULL,
    resume_anglais text DEFAULT 'NULL'::text NOT NULL,
    mots_cles_libres_fr character varying(1024) NOT NULL,
    mots_cles_rameau character varying(1024),
    titre_autre_langue character varying(2048) NOT NULL,
    mots_cles_libres_ang character varying(1024)
);


ALTER TABLE public.metadonnee_these OWNER TO :dbuser;

--
-- Name: metadonnee_these_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.metadonnee_these_id_seq
    START WITH 9606
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.metadonnee_these_id_seq OWNER TO :dbuser;

--
-- Name: these; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.these (
    id bigint NOT NULL,
    etablissement_id bigint DEFAULT 2,
    doctorant_id bigint NOT NULL,
    ecole_doct_id bigint,
    unite_rech_id bigint,
    besoin_expurge boolean DEFAULT false NOT NULL,
    cod_unit_rech character varying(50),
    correc_autorisee character varying(30),
    date_autoris_soutenance timestamp without time zone,
    date_fin_confid timestamp without time zone,
    date_prem_insc timestamp without time zone,
    date_prev_soutenance timestamp without time zone,
    date_soutenance timestamp without time zone,
    etat_these character varying(20),
    lib_disc character varying(200),
    lib_etab_cotut character varying(100),
    lib_pays_cotut character varying(40),
    lib_unit_rech character varying(200),
    resultat smallint,
    soutenance_autoris character varying(1),
    tem_avenant_cotut character varying(1),
    titre character varying(2048),
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone,
    correc_autorisee_forcee character varying(30),
    date_abandon timestamp without time zone,
    date_transfert timestamp without time zone,
    correc_effectuee character varying(30) DEFAULT 'null'::character varying,
    correc_date_butoir_avec_sursis date,
    code_sise_disc character varying(64),
    source_code_sav character varying(64)
);


ALTER TABLE public.these OWNER TO :dbuser;

--
-- Name: TABLE these; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.these IS 'Thèses par établissement.';


--
-- Name: these_annee_univ; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.these_annee_univ (
    id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    these_id bigint,
    annee_univ bigint,
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.these_annee_univ OWNER TO :dbuser;

--
-- Name: type_validation; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.type_validation (
    id bigint NOT NULL,
    code character varying(50) NOT NULL,
    libelle character varying(100)
);


ALTER TABLE public.type_validation OWNER TO :dbuser;

--
-- Name: validation; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.validation (
    id bigint NOT NULL,
    type_validation_id bigint NOT NULL,
    these_id bigint NOT NULL,
    individu_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint DEFAULT 1 NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint DEFAULT 1 NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.validation OWNER TO :dbuser;

--
-- Name: nature_fichier; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.nature_fichier (
    id bigint NOT NULL,
    code character varying(50) DEFAULT 'NULL'::character varying NOT NULL,
    libelle character varying(100) DEFAULT 'NULL'::character varying
);


ALTER TABLE public.nature_fichier OWNER TO :dbuser;

--
-- Name: structure; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.structure (
    id bigint NOT NULL,
    sigle character varying(40),
    libelle character varying(300) DEFAULT 'NULL'::character varying NOT NULL,
    chemin_logo character varying(200),
    type_structure_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    code character varying(64) DEFAULT 'NULL'::character varying NOT NULL,
    est_ferme boolean DEFAULT false,
    adresse character varying(1024),
    telephone character varying(64),
    fax character varying(64),
    email character varying(64),
    site_web character varying(512),
    id_ref character varying(1024),
    id_hal character varying(128)
);


ALTER TABLE public.structure OWNER TO :dbuser;

--
-- Name: unite_rech; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unite_rech (
    id bigint NOT NULL,
    etab_support character varying(500),
    autres_etab character varying(500),
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    structure_id bigint NOT NULL,
    rnsr_id character varying(128)
);


ALTER TABLE public.unite_rech OWNER TO :dbuser;

--
-- Name: structure_substit; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.structure_substit (
    id bigint NOT NULL,
    from_structure_id bigint NOT NULL,
    to_structure_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_destruction timestamp without time zone,
    histo_createur_id bigint,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.structure_substit OWNER TO :dbuser;

--
-- Name: role; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.role (
    id bigint NOT NULL,
    code character varying(50) NOT NULL,
    libelle character varying(200) NOT NULL,
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    role_id character varying(64) NOT NULL,
    is_default boolean DEFAULT false,
    ldap_filter character varying(255),
    attrib_auto boolean DEFAULT false NOT NULL,
    these_dep boolean DEFAULT false NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone,
    structure_id bigint,
    type_structure_dependant_id bigint,
    ordre_affichage character varying(32) DEFAULT 'zzz'::character varying NOT NULL
);


ALTER TABLE public.role OWNER TO :dbuser;

--
-- Name: COLUMN role.ordre_affichage; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.role.ordre_affichage IS 'Chaîne de caractères utilisée pour trier les rôles ; l''astuce consiste à concaténer cette valeur aux autres critères de tri.';


--
-- Name: mv_recherche_these; Type: MATERIALIZED VIEW; Schema: public; Owner: :dbuser
--

CREATE MATERIALIZED VIEW public.mv_recherche_these AS
 WITH acteurs AS (
         SELECT a_1.these_id,
            i.nom_usuel,
            a_1.individu_id
           FROM (((public.individu i
             JOIN public.acteur a_1 ON ((i.id = a_1.individu_id)))
             JOIN public.these t_1 ON ((t_1.id = a_1.these_id)))
             JOIN public.role r ON (((a_1.role_id = r.id) AND ((r.code)::text = ANY (ARRAY[('D'::character varying)::text, ('K'::character varying)::text])))))
        )
 SELECT ('now'::text)::timestamp without time zone AS date_creation,
    t.source_code AS code_these,
    d.source_code AS code_doctorant,
    ed.source_code AS code_ecole_doct,
    btrim(public.str_reduce((((((((((((((((((((((('code-ed{'::text || COALESCE((eds.code)::text, ''::text)) || '} '::text) || 'code-ur{'::text) || COALESCE((urs.code)::text, ''::text)) || '} '::text) || 'titre{'::text) || (t.titre)::text) || '} '::text) || 'doctorant-numero{'::text) || substr((d.source_code)::text, ("position"((d.source_code)::text, '::'::text) + 2))) || '} '::text) || 'doctorant-nom{'::text) || (id.nom_patronymique)::text) || ' '::text) || (id.nom_usuel)::text) || '} '::text) || 'doctorant-prenom{'::text) || (id.prenom1)::text) || '} '::text) || 'directeur-nom{'::text) || COALESCE((ia.nom_usuel)::text, ''::text)) || '} '::text))) AS haystack
   FROM ((((((((public.these t
     JOIN public.doctorant d ON ((d.id = t.doctorant_id)))
     JOIN public.individu id ON ((id.id = d.individu_id)))
     LEFT JOIN public.ecole_doct ed ON ((t.ecole_doct_id = ed.id)))
     LEFT JOIN public.structure eds ON ((ed.structure_id = eds.id)))
     LEFT JOIN public.unite_rech ur ON ((t.unite_rech_id = ur.id)))
     LEFT JOIN public.structure urs ON ((ur.structure_id = urs.id)))
     LEFT JOIN public.acteur a ON ((a.these_id = t.id)))
     LEFT JOIN public.individu ia ON ((ia.id = a.individu_id)))
  WITH NO DATA;


ALTER TABLE public.mv_recherche_these OWNER TO :dbuser;

--
-- Name: nature_fichier_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.nature_fichier_id_seq
    START WITH 141
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.nature_fichier_id_seq OWNER TO :dbuser;

--
-- Name: notif; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.notif (
    id bigint NOT NULL,
    code character varying(100) NOT NULL,
    description character varying(255) NOT NULL,
    recipients character varying(500),
    template text NOT NULL,
    enabled bigint DEFAULT 1 NOT NULL
);


ALTER TABLE public.notif OWNER TO :dbuser;

--
-- Name: notif_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.notif_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.notif_id_seq OWNER TO :dbuser;

--
-- Name: notif_mail; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.notif_mail (
    id bigint NOT NULL,
    mail_from character varying(1024),
    mail_to character varying(1024) NOT NULL,
    subject character varying(1024),
    body_text text,
    sent_on timestamp without time zone
);


ALTER TABLE public.notif_mail OWNER TO :dbuser;

--
-- Name: notif_mail_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.notif_mail_id_seq
    START WITH 11505
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.notif_mail_id_seq OWNER TO :dbuser;

--
-- Name: notif_result; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.notif_result (
    id bigint NOT NULL,
    notif_id bigint NOT NULL,
    subject character varying(255) NOT NULL,
    body text NOT NULL,
    sent_on timestamp without time zone NOT NULL,
    error text
);


ALTER TABLE public.notif_result OWNER TO :dbuser;

--
-- Name: notif_result_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.notif_result_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.notif_result_id_seq OWNER TO :dbuser;

--
-- Name: origine_financement; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.origine_financement (
    id bigint NOT NULL,
    code character varying(64) NOT NULL,
    libelle_long character varying(256) NOT NULL,
    source_id bigint,
    libelle_court character varying(64),
    source_code character varying(64) NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE public.origine_financement OWNER TO :dbuser;

--
-- Name: origine_financement_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.origine_financement_id_seq
    START WITH 201
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.origine_financement_id_seq OWNER TO :dbuser;

--
-- Name: parametre; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.parametre (
    id character varying(256) NOT NULL,
    description character varying(256) NOT NULL,
    valeur character varying(256) NOT NULL
);


ALTER TABLE public.parametre OWNER TO :dbuser;

--
-- Name: pays; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.pays (
    id bigint NOT NULL,
    code_iso character varying(3) NOT NULL,
    code_iso_alpha3 character varying(3) NOT NULL,
    code_iso_alpha2 character varying(2) NOT NULL,
    libelle character varying(128) NOT NULL,
    libelle_iso character varying(128) NOT NULL,
    libelle_nationalite character varying(64),
    code_pays_apogee character varying(3),
    histo_creation timestamp without time zone DEFAULT now() NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    source_id bigint NOT NULL,
    source_code character varying(64)
);


ALTER TABLE public.pays OWNER TO :dbuser;

--
-- Name: TABLE pays; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.pays IS 'Liste des pays selon la norme internationale de codification des pays ISO 3166-1';


--
-- Name: pays_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.pays_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pays_id_seq OWNER TO :dbuser;

--
-- Name: privilege; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.privilege (
    id bigint NOT NULL,
    categorie_id bigint NOT NULL,
    code character varying(150) NOT NULL,
    libelle character varying(200) NOT NULL,
    ordre bigint
);


ALTER TABLE public.privilege OWNER TO :dbuser;

--
-- Name: privilege_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.privilege_id_seq
    START WITH 681
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.privilege_id_seq OWNER TO :dbuser;

--
-- Name: privilege_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.privilege_id_seq OWNED BY public.privilege.id;


--
-- Name: profil; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.profil (
    id bigint NOT NULL,
    libelle character varying(100) NOT NULL,
    role_id character varying(100) NOT NULL,
    structure_type bigint,
    description character varying(1024),
    ordre bigint DEFAULT 0
);


ALTER TABLE public.profil OWNER TO :dbuser;

--
-- Name: profil_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.profil_id_seq
    START WITH 61
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.profil_id_seq OWNER TO :dbuser;

--
-- Name: profil_privilege; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.profil_privilege (
    privilege_id bigint NOT NULL,
    profil_id bigint NOT NULL
);


ALTER TABLE public.profil_privilege OWNER TO :dbuser;

--
-- Name: profil_to_role; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.profil_to_role (
    profil_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.profil_to_role OWNER TO :dbuser;

--
-- Name: rapport; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.rapport (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    fichier_id bigint NOT NULL,
    annee_univ bigint NOT NULL,
    est_final boolean,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modification timestamp without time zone,
    histo_destruction timestamp without time zone,
    type_rapport_id bigint NOT NULL
);


ALTER TABLE public.rapport OWNER TO :dbuser;

--
-- Name: rapport_activite; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.rapport_activite (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    fichier_id bigint,
    annee_univ bigint NOT NULL,
    est_fin_contrat boolean NOT NULL,
    par_directeur_these boolean DEFAULT false NOT NULL,
    par_directeur_these_motif text,
    description_projet_recherche text,
    principaux_resultats_obtenus text,
    productions_scientifiques text,
    formations_specifiques text,
    formations_transversales text,
    actions_diffusion_culture_scientifique text,
    autres_activites text,
    calendrier_previonnel_finalisation text,
    preparation_apres_these text,
    perspectives_apres_these text,
    commentaires text,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modification timestamp without time zone,
    histo_destruction timestamp without time zone,
    z_old_rapport_id bigint
);


ALTER TABLE public.rapport_activite OWNER TO :dbuser;

--
-- Name: rapport_activite_avis; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.rapport_activite_avis (
    id bigint NOT NULL,
    rapport_id bigint NOT NULL,
    avis_id bigint,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modification timestamp without time zone,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.rapport_activite_avis OWNER TO :dbuser;

--
-- Name: rapport_activite_avis_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.rapport_activite_avis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.rapport_activite_avis_id_seq OWNER TO :dbuser;

--
-- Name: rapport_activite_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.rapport_activite_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.rapport_activite_id_seq OWNER TO :dbuser;

--
-- Name: rapport_activite_validation; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.rapport_activite_validation (
    id bigint NOT NULL,
    type_validation_id bigint NOT NULL,
    rapport_id bigint NOT NULL,
    individu_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint DEFAULT 1 NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint DEFAULT 1 NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.rapport_activite_validation OWNER TO :dbuser;

--
-- Name: rapport_activite_validation_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.rapport_activite_validation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.rapport_activite_validation_id_seq OWNER TO :dbuser;

--
-- Name: rapport_avis; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.rapport_avis (
    id bigint NOT NULL,
    rapport_id bigint NOT NULL,
    avis public.avis_enum,
    commentaires text,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint DEFAULT 1 NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint DEFAULT 1 NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    avis_id bigint
);


ALTER TABLE public.rapport_avis OWNER TO :dbuser;

--
-- Name: rapport_avis_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.rapport_avis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.rapport_avis_id_seq OWNER TO :dbuser;

--
-- Name: rapport_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.rapport_id_seq
    START WITH 7453
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.rapport_id_seq OWNER TO :dbuser;

--
-- Name: rapport_validation; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.rapport_validation (
    id bigint NOT NULL,
    type_validation_id bigint NOT NULL,
    rapport_id bigint NOT NULL,
    individu_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint DEFAULT 1 NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint DEFAULT 1 NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.rapport_validation OWNER TO :dbuser;

--
-- Name: rapport_validation_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.rapport_validation_id_seq
    START WITH 504
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.rapport_validation_id_seq OWNER TO :dbuser;

--
-- Name: rdv_bu; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.rdv_bu (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    coord_doctorant character varying(2000),
    dispo_doctorant character varying(2000),
    mots_cles_rameau character varying(1024),
    convention_mel_signee boolean DEFAULT false NOT NULL,
    exempl_papier_fourni boolean,
    version_archivable_fournie boolean DEFAULT false NOT NULL,
    divers text,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.rdv_bu OWNER TO :dbuser;

--
-- Name: COLUMN rdv_bu.convention_mel_signee; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.rdv_bu.convention_mel_signee IS 'Convention de mise en ligne signée ?';


--
-- Name: COLUMN rdv_bu.exempl_papier_fourni; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.rdv_bu.exempl_papier_fourni IS 'Exemplaire papier remis ?';


--
-- Name: COLUMN rdv_bu.version_archivable_fournie; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.rdv_bu.version_archivable_fournie IS 'Témoin indiquant si une version archivable de la thèse existe';


--
-- Name: rdv_bu_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.rdv_bu_id_seq
    START WITH 9431
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.rdv_bu_id_seq OWNER TO :dbuser;

--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.role_id_seq
    START WITH 1141
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.role_id_seq OWNER TO :dbuser;

--
-- Name: role_privilege; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.role_privilege (
    role_id bigint NOT NULL,
    privilege_id bigint NOT NULL
);


ALTER TABLE public.role_privilege OWNER TO :dbuser;

--
-- Name: source; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.source (
    id bigint NOT NULL,
    code character varying(64) NOT NULL,
    libelle character varying(128) NOT NULL,
    importable boolean NOT NULL,
    etablissement_id bigint
);


ALTER TABLE public.source OWNER TO :dbuser;

--
-- Name: TABLE source; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.source IS 'Sources de données, importables ou non, ex: Apogée, Physalis.';


--
-- Name: soutenance_avis; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_avis (
    id bigint NOT NULL,
    proposition_id bigint NOT NULL,
    membre_id bigint NOT NULL,
    avis character varying(64),
    motif character varying(1024),
    validation_id bigint,
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    fichierthese_id integer
);


ALTER TABLE public.soutenance_avis OWNER TO :dbuser;

--
-- Name: soutenance_avis_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_avis_id_seq
    START WITH 2022
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_avis_id_seq OWNER TO :dbuser;

--
-- Name: soutenance_etat; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_etat (
    id bigint NOT NULL,
    code character varying(63) NOT NULL,
    libelle character varying(255) NOT NULL,
    histo_creation timestamp without time zone DEFAULT now() NOT NULL,
    histo_createur_id bigint DEFAULT 1 NOT NULL,
    histo_modification timestamp without time zone DEFAULT now() NOT NULL,
    histo_modificateur_id bigint DEFAULT 1 NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    ordre integer
);


ALTER TABLE public.soutenance_etat OWNER TO :dbuser;

--
-- Name: soutenance_etat_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_etat_id_seq
    START WITH 21
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_etat_id_seq OWNER TO :dbuser;

--
-- Name: soutenance_horodatage; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_horodatage (
    proposition_id integer NOT NULL,
    horodatage_id integer NOT NULL
);


ALTER TABLE public.soutenance_horodatage OWNER TO :dbuser;

--
-- Name: soutenance_intervention; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_intervention (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    type_intervention bigint NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    complement text
);


ALTER TABLE public.soutenance_intervention OWNER TO :dbuser;

--
-- Name: soutenance_intervention_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_intervention_id_seq
    START WITH 21
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_intervention_id_seq OWNER TO :dbuser;

--
-- Name: soutenance_justificatif; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_justificatif (
    id bigint NOT NULL,
    proposition_id bigint NOT NULL,
    fichier_id bigint NOT NULL,
    membre_id bigint,
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.soutenance_justificatif OWNER TO :dbuser;

--
-- Name: soutenance_justificatif_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_justificatif_id_seq
    START WITH 3124
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_justificatif_id_seq OWNER TO :dbuser;

--
-- Name: soutenance_membre; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_membre (
    id bigint NOT NULL,
    proposition_id bigint NOT NULL,
    genre character varying(1) NOT NULL,
    qualite bigint NOT NULL,
    etablissement character varying(128) NOT NULL,
    role_id character varying(64) NOT NULL,
    exterieur character varying(3),
    email character varying(256),
    acteur_id bigint,
    visio boolean DEFAULT false NOT NULL,
    nom character varying(256),
    prenom character varying(256),
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    clef character varying(64),
    adresse text
);


ALTER TABLE public.soutenance_membre OWNER TO :dbuser;

--
-- Name: soutenance_membre_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_membre_id_seq
    START WITH 7816
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_membre_id_seq OWNER TO :dbuser;

--
-- Name: soutenance_proposition; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_proposition (
    id bigint NOT NULL,
    these_id bigint NOT NULL,
    dateprev timestamp without time zone,
    lieu character varying(256),
    rendu_rapport timestamp without time zone,
    confidentialite timestamp without time zone,
    label_europeen boolean DEFAULT false NOT NULL,
    manuscrit_anglais boolean DEFAULT false NOT NULL,
    soutenance_anglais boolean DEFAULT false NOT NULL,
    huit_clos boolean DEFAULT false NOT NULL,
    exterieur boolean DEFAULT false NOT NULL,
    nouveau_titre character varying(2048),
    etat_id bigint NOT NULL,
    sursis character varying(1),
    adresse_exacte character varying(2048),
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.soutenance_proposition OWNER TO :dbuser;

--
-- Name: soutenance_proposition_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_proposition_id_seq
    START WITH 5273
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_proposition_id_seq OWNER TO :dbuser;

--
-- Name: soutenance_qualite; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_qualite (
    id bigint NOT NULL,
    libelle character varying(128) NOT NULL,
    rang character varying(1) NOT NULL,
    hdr character varying(1) NOT NULL,
    emeritat character varying(1) NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    justificatif character varying(1) DEFAULT 'N'::character varying NOT NULL
);


ALTER TABLE public.soutenance_qualite OWNER TO :dbuser;

--
-- Name: soutenance_qualite_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_qualite_id_seq
    START WITH 74
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_qualite_id_seq OWNER TO :dbuser;

--
-- Name: soutenance_qualite_sup; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.soutenance_qualite_sup (
    id bigint NOT NULL,
    qualite_id bigint NOT NULL,
    libelle character varying(255) NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.soutenance_qualite_sup OWNER TO :dbuser;

--
-- Name: soutenance_qualite_sup_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.soutenance_qualite_sup_id_seq
    START WITH 6
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.soutenance_qualite_sup_id_seq OWNER TO :dbuser;

--
-- Name: tmp_acteur; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_acteur (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    individu_id character varying(64) NOT NULL,
    these_id character varying(64) NOT NULL,
    role_id character varying(64) NOT NULL,
    lib_cps character varying(200),
    cod_cps character varying(50),
    cod_roj_compl character varying(50),
    lib_roj_compl character varying(200),
    tem_hab_rch_per character varying(1),
    tem_rap_recu character varying(1),
    acteur_etablissement_id character varying(64),
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_acteur OWNER TO :dbuser;

--
-- Name: src_acteur; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_acteur AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    i.id AS individu_id,
    t.id AS these_id,
    r.id AS role_id,
    eact.id AS acteur_etablissement_id,
    tmp.lib_cps AS qualite,
    tmp.lib_roj_compl AS lib_role_compl
   FROM (((((public.tmp_acteur tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.individu i ON (((i.source_code)::text = (tmp.individu_id)::text)))
     JOIN public.these t ON (((t.source_code)::text = (tmp.these_id)::text)))
     JOIN public.role r ON ((((r.source_code)::text = (tmp.role_id)::text) AND ((r.code)::text = 'P'::text))))
     LEFT JOIN public.etablissement eact ON (((eact.source_code)::text = (tmp.acteur_etablissement_id)::text)))
UNION ALL
 SELECT NULL::text AS id,
    ((tmp.source_code)::text || 'P'::text) AS source_code,
    src.id AS source_id,
    i.id AS individu_id,
    t.id AS these_id,
    r_pj.id AS role_id,
    eact.id AS acteur_etablissement_id,
    tmp.lib_cps AS qualite,
    NULL::character varying AS lib_role_compl
   FROM ((((((public.tmp_acteur tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.individu i ON (((i.source_code)::text = (tmp.individu_id)::text)))
     JOIN public.these t ON (((t.source_code)::text = (tmp.these_id)::text)))
     JOIN public.role r ON ((((r.source_code)::text = (tmp.role_id)::text) AND ((r.code)::text = 'M'::text))))
     JOIN public.role r_pj ON ((((r_pj.code)::text = 'P'::text) AND (r_pj.structure_id = r.structure_id))))
     LEFT JOIN public.etablissement eact ON (((eact.source_code)::text = (tmp.acteur_etablissement_id)::text)))
  WHERE ((tmp.lib_roj_compl)::text = 'Président du jury'::text)
UNION ALL
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    i.id AS individu_id,
    t.id AS these_id,
    r.id AS role_id,
    eact.id AS acteur_etablissement_id,
    tmp.lib_cps AS qualite,
    NULL::character varying AS lib_role_compl
   FROM (((((public.tmp_acteur tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.individu i ON (((i.source_code)::text = (tmp.individu_id)::text)))
     JOIN public.these t ON (((t.source_code)::text = (tmp.these_id)::text)))
     JOIN public.role r ON ((((r.source_code)::text = (tmp.role_id)::text) AND ((r.code)::text <> 'P'::text))))
     LEFT JOIN public.etablissement eact ON (((eact.source_code)::text = (tmp.acteur_etablissement_id)::text)));


ALTER TABLE public.src_acteur OWNER TO :dbuser;

--
-- Name: tmp_doctorant; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_doctorant (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    individu_id character varying(64) NOT NULL,
    ine character varying(64),
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_doctorant OWNER TO :dbuser;

--
-- Name: src_doctorant; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_doctorant AS
 SELECT NULL::text AS id,
    tmp.source_code,
    tmp.ine,
    src.id AS source_id,
    i.id AS individu_id,
    e.id AS etablissement_id
   FROM (((public.tmp_doctorant tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.etablissement e ON ((e.id = src.etablissement_id)))
     JOIN public.individu i ON (((i.source_code)::text = (tmp.individu_id)::text)));


ALTER TABLE public.src_doctorant OWNER TO :dbuser;

--
-- Name: tmp_ecole_doct; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_ecole_doct (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    structure_id character varying(64) NOT NULL,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_ecole_doct OWNER TO :dbuser;

--
-- Name: src_ecole_doct; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_ecole_doct AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    s.id AS structure_id
   FROM ((public.tmp_ecole_doct tmp
     JOIN public.structure s ON (((s.source_code)::text = (tmp.structure_id)::text)))
     JOIN public.source src ON ((src.id = tmp.source_id)));


ALTER TABLE public.src_ecole_doct OWNER TO :dbuser;

--
-- Name: tmp_etablissement; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_etablissement (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    structure_id character varying(64) NOT NULL,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_etablissement OWNER TO :dbuser;

--
-- Name: src_etablissement; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_etablissement AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    s.id AS structure_id
   FROM ((public.tmp_etablissement tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.structure s ON (((s.source_code)::text = (tmp.structure_id)::text)));


ALTER TABLE public.src_etablissement OWNER TO :dbuser;

--
-- Name: tmp_financement; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_financement (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    these_id character varying(50) NOT NULL,
    annee character varying(50) NOT NULL,
    origine_financement_id character varying(50) NOT NULL,
    complement_financement character varying(200),
    quotite_financement character varying(50),
    date_debut_financement timestamp without time zone,
    date_fin_financement timestamp without time zone,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    code_type_financement character varying(8),
    libelle_type_financement character varying(100),
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_financement OWNER TO :dbuser;

--
-- Name: src_financement; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_financement AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    t.id AS these_id,
    (tmp.annee)::numeric AS annee,
    ofi.id AS origine_financement_id,
    tmp.complement_financement,
    tmp.quotite_financement,
    tmp.date_debut_financement AS date_debut,
    tmp.date_fin_financement AS date_fin,
    tmp.code_type_financement,
    tmp.libelle_type_financement
   FROM (((public.tmp_financement tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.these t ON (((t.source_code)::text = (tmp.these_id)::text)))
     JOIN public.origine_financement ofi ON (((ofi.source_code)::text = (tmp.origine_financement_id)::text)));


ALTER TABLE public.src_financement OWNER TO :dbuser;

--
-- Name: tmp_individu; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_individu (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    type character varying(32),
    civ character varying(5),
    lib_nom_usu_ind character varying(60) NOT NULL,
    lib_nom_pat_ind character varying(60) NOT NULL,
    lib_pr1_ind character varying(60) NOT NULL,
    lib_pr2_ind character varying(60),
    lib_pr3_ind character varying(60),
    email character varying(255),
    dat_nai_per timestamp without time zone,
    lib_nat character varying(128),
    supann_id character varying(30),
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint,
    histo_destruction timestamp(0) without time zone,
    codepaysnationalite character varying(64),
    cod_pay_nat character varying(3)
);


ALTER TABLE public.tmp_individu OWNER TO :dbuser;

--
-- Name: src_individu; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_individu AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    tmp.type,
    tmp.supann_id,
    tmp.civ AS civilite,
    tmp.lib_nom_usu_ind AS nom_usuel,
    tmp.lib_nom_pat_ind AS nom_patronymique,
    tmp.lib_pr1_ind AS prenom1,
    tmp.lib_pr2_ind AS prenom2,
    tmp.lib_pr3_ind AS prenom3,
    tmp.email,
    tmp.dat_nai_per AS date_naissance,
    tmp.lib_nat AS nationalite,
    p.id AS pays_id_nationalite
   FROM ((public.tmp_individu tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     LEFT JOIN public.pays p ON (((p.code_pays_apogee)::text = (tmp.cod_pay_nat)::text)));


ALTER TABLE public.src_individu OWNER TO :dbuser;

--
-- Name: tmp_origine_financement; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_origine_financement (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    cod_ofi character varying(50) NOT NULL,
    lic_ofi character varying(50) NOT NULL,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    lib_ofi character varying(200) NOT NULL,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint
);


ALTER TABLE public.tmp_origine_financement OWNER TO :dbuser;

--
-- Name: src_origine_financement; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_origine_financement AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    tmp.cod_ofi AS code,
    tmp.lic_ofi AS libelle_court,
    tmp.lib_ofi AS libelle_long
   FROM (public.tmp_origine_financement tmp
     JOIN public.source src ON ((src.id = tmp.source_id)));


ALTER TABLE public.src_origine_financement OWNER TO :dbuser;

--
-- Name: tmp_role; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_role (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    lic_roj character varying(50),
    lib_roj character varying(200),
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint,
    histo_modification timestamp(0) without time zone
);


ALTER TABLE public.tmp_role OWNER TO :dbuser;

--
-- Name: src_role; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_role AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    tmp.lib_roj AS libelle,
    ltrim(substr((tmp.source_code)::text, strpos((tmp.source_code)::text, '::'::text)), ':'::text) AS code,
    (((tmp.lib_roj)::text || ' '::text) || (s.code)::text) AS role_id,
    true AS these_dep,
    s.id AS structure_id,
    NULL::bigint AS type_structure_dependant_id
   FROM (((public.tmp_role tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.etablissement e ON ((e.id = src.etablissement_id)))
     JOIN public.structure s ON ((s.id = e.structure_id)));


ALTER TABLE public.src_role OWNER TO :dbuser;

--
-- Name: tmp_structure; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_structure (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    type_structure_id character varying(64) NOT NULL,
    code_pays character varying(64),
    libelle_pays character varying(200),
    code character varying(64),
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    libelle character varying(200) NOT NULL,
    sigle character varying(64),
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp(0) without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_structure OWNER TO :dbuser;

--
-- Name: type_structure; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.type_structure (
    id bigint NOT NULL,
    code character varying(50) NOT NULL,
    libelle character varying(100)
);


ALTER TABLE public.type_structure OWNER TO :dbuser;

--
-- Name: src_structure; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_structure AS
 SELECT NULL::text AS id,
    tmp.source_code,
    ltrim(substr((tmp.source_code)::text, strpos((tmp.source_code)::text, '::'::text)), ':'::text) AS code,
    src.id AS source_id,
    ts.id AS type_structure_id,
    tmp.sigle,
    tmp.libelle
   FROM ((public.tmp_structure tmp
     JOIN public.type_structure ts ON (((ts.code)::text = (tmp.type_structure_id)::text)))
     JOIN public.source src ON ((src.id = tmp.source_id)));


ALTER TABLE public.src_structure OWNER TO :dbuser;

--
-- Name: tmp_these; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_these (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    dat_abandon timestamp without time zone,
    dat_aut_sou_ths timestamp without time zone,
    unite_rech_id character varying(64),
    dat_sou_ths timestamp without time zone,
    lib_int1_dis character varying(200),
    lib_etab_cotut character varying(100),
    lib_pays_cotut character varying(40),
    cod_neg_tre character varying(1),
    tem_avenant_cotut character varying(1),
    lib_ths character varying(2048),
    dat_transfert_dep timestamp without time zone,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    correction_effectuee character varying(30) DEFAULT 'null'::character varying,
    dat_fin_cfd_ths timestamp without time zone,
    dat_deb_ths timestamp without time zone,
    correction_possible character varying(30),
    doctorant_id character varying(64) NOT NULL,
    ecole_doct_id character varying(64),
    dat_prev_sou timestamp without time zone,
    annee_univ_1ere_insc bigint,
    tem_sou_aut_ths character varying(1),
    eta_ths character varying(20),
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    histo_destruction timestamp(0) without time zone,
    codesisediscipline character varying(64),
    code_sise_disc character varying(64)
);


ALTER TABLE public.tmp_these OWNER TO :dbuser;

--
-- Name: version_fichier; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.version_fichier (
    id bigint NOT NULL,
    code character varying(16) NOT NULL,
    libelle character varying(128) NOT NULL
);


ALTER TABLE public.version_fichier OWNER TO :dbuser;

--
-- Name: src_these; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_these AS
 WITH version_corrigee_deposee AS (
         SELECT t.source_code,
            f.id AS fichier_id
           FROM ((((public.fichier_these ft
             JOIN public.these t ON ((ft.these_id = t.id)))
             JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
             JOIN public.nature_fichier nf ON (((f.nature_id = nf.id) AND ((nf.code)::text = 'THESE_PDF'::text))))
             JOIN public.version_fichier vf ON (((f.version_fichier_id = vf.id) AND ((vf.code)::text = 'VOC'::text))))
          WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false) AND (ft.retraitement IS NULL))
        )
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    e.id AS etablissement_id,
    d.id AS doctorant_id,
    ed.id AS ecole_doct_id,
    ur.id AS unite_rech_id,
    tmp.lib_ths AS titre,
    tmp.eta_ths AS etat_these,
    (tmp.cod_neg_tre)::numeric AS resultat,
    tmp.code_sise_disc,
    tmp.lib_int1_dis AS lib_disc,
    tmp.dat_deb_ths AS date_prem_insc,
    tmp.dat_prev_sou AS date_prev_soutenance,
    tmp.dat_sou_ths AS date_soutenance,
    tmp.dat_fin_cfd_ths AS date_fin_confid,
    tmp.lib_etab_cotut,
    tmp.lib_pays_cotut,
    tmp.correction_possible AS correc_autorisee,
    (
        CASE
            WHEN (vcd.source_code IS NOT NULL) THEN 'O'::character varying
            ELSE tmp.correction_effectuee
        END)::character varying(30) AS correc_effectuee,
    tmp.tem_sou_aut_ths AS soutenance_autoris,
    tmp.dat_aut_sou_ths AS date_autoris_soutenance,
    tmp.tem_avenant_cotut,
    tmp.dat_abandon AS date_abandon,
    tmp.dat_transfert_dep AS date_transfert
   FROM ((((((public.tmp_these tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.etablissement e ON ((e.id = src.etablissement_id)))
     JOIN public.doctorant d ON (((d.source_code)::text = (tmp.doctorant_id)::text)))
     LEFT JOIN public.ecole_doct ed ON (((ed.source_code)::text = (tmp.ecole_doct_id)::text)))
     LEFT JOIN public.unite_rech ur ON (((ur.source_code)::text = (tmp.unite_rech_id)::text)))
     LEFT JOIN version_corrigee_deposee vcd ON (((vcd.source_code)::text = (tmp.source_code)::text)));


ALTER TABLE public.src_these OWNER TO :dbuser;

--
-- Name: tmp_these_annee_univ; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_these_annee_univ (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    annee_univ bigint,
    these_id character varying(50) NOT NULL,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_destructeur_id bigint,
    histo_modificateur_id bigint
);


ALTER TABLE public.tmp_these_annee_univ OWNER TO :dbuser;

--
-- Name: src_these_annee_univ; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_these_annee_univ AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    t.id AS these_id,
    tmp.annee_univ
   FROM ((((public.tmp_these_annee_univ tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.etablissement e ON ((e.structure_id = src.etablissement_id)))
     JOIN public.structure s ON ((s.id = e.structure_id)))
     JOIN public.these t ON (((t.source_code)::text = (tmp.these_id)::text)));


ALTER TABLE public.src_these_annee_univ OWNER TO :dbuser;

--
-- Name: tmp_titre_acces; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_titre_acces (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    these_id character varying(50) NOT NULL,
    titre_acces_interne_externe character varying(1),
    libelle_titre_acces character varying(200),
    type_etb_titre_acces character varying(50),
    libelle_etb_titre_acces character varying(200),
    code_dept_titre_acces character varying(20),
    code_pays_titre_acces character varying(20),
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint,
    histo_modification timestamp(0) without time zone
);


ALTER TABLE public.tmp_titre_acces OWNER TO :dbuser;

--
-- Name: src_titre_acces; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_titre_acces AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    t.id AS these_id,
    tmp.titre_acces_interne_externe,
    tmp.libelle_titre_acces,
    tmp.type_etb_titre_acces,
    tmp.libelle_etb_titre_acces,
    tmp.code_dept_titre_acces,
    tmp.code_pays_titre_acces
   FROM ((((public.tmp_titre_acces tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.etablissement e ON ((e.structure_id = src.etablissement_id)))
     JOIN public.structure s ON ((s.id = e.structure_id)))
     JOIN public.these t ON (((t.source_code)::text = (tmp.these_id)::text)));


ALTER TABLE public.src_titre_acces OWNER TO :dbuser;

--
-- Name: tmp_unite_rech; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_unite_rech (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    structure_id character varying(64) NOT NULL,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_unite_rech OWNER TO :dbuser;

--
-- Name: src_unite_rech; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_unite_rech AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    s.id AS structure_id
   FROM ((public.tmp_unite_rech tmp
     JOIN public.structure s ON (((s.source_code)::text = (tmp.structure_id)::text)))
     JOIN public.source src ON ((src.id = tmp.source_id)));


ALTER TABLE public.src_unite_rech OWNER TO :dbuser;

--
-- Name: tmp_variable; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.tmp_variable (
    id bigint NOT NULL,
    insert_date timestamp(0) without time zone DEFAULT ('now'::text)::timestamp without time zone,
    source_id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    cod_vap character varying(50),
    lib_vap character varying(300),
    par_vap character varying(200),
    date_deb_validite timestamp without time zone NOT NULL,
    date_fin_validite timestamp without time zone NOT NULL,
    source_insert_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    histo_creation timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL,
    histo_modification timestamp(0) without time zone,
    histo_destruction timestamp(0) without time zone,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint,
    histo_destructeur_id bigint
);


ALTER TABLE public.tmp_variable OWNER TO :dbuser;

--
-- Name: src_variable; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.src_variable AS
 SELECT NULL::text AS id,
    tmp.source_code,
    src.id AS source_id,
    e.id AS etablissement_id,
    tmp.cod_vap AS code,
    tmp.lib_vap AS description,
    tmp.par_vap AS valeur,
    tmp.date_deb_validite,
    tmp.date_fin_validite
   FROM (((public.tmp_variable tmp
     JOIN public.source src ON ((src.id = tmp.source_id)))
     JOIN public.etablissement e ON ((e.structure_id = src.etablissement_id)))
     JOIN public.structure s ON ((s.id = e.structure_id)));


ALTER TABLE public.src_variable OWNER TO :dbuser;

--
-- Name: step_star_log; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.step_star_log (
    id bigint NOT NULL,
    these_id bigint,
    started_on timestamp without time zone NOT NULL,
    ended_on timestamp without time zone NOT NULL,
    success boolean NOT NULL,
    operation character varying(64) NOT NULL,
    log text NOT NULL,
    command character varying(256) NOT NULL,
    tef_file_content_hash character varying(64),
    tef_file_content text,
    has_problems boolean DEFAULT false NOT NULL,
    tag character varying(32)
);


ALTER TABLE public.step_star_log OWNER TO :dbuser;

--
-- Name: step_star_log_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.step_star_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.step_star_log_id_seq OWNER TO :dbuser;

--
-- Name: step_star_log_id_seq1; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.step_star_log_id_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.step_star_log_id_seq1 OWNER TO :dbuser;

--
-- Name: step_star_log_id_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.step_star_log_id_seq1 OWNED BY public.step_star_log.id;


--
-- Name: structure_document; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.structure_document (
    id bigint NOT NULL,
    nature_id bigint NOT NULL,
    structure_id bigint NOT NULL,
    etablissement_id bigint,
    fichier_id bigint NOT NULL,
    histo_creation timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone,
    histo_modificateur_id bigint,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.structure_document OWNER TO :dbuser;

--
-- Name: structure_document_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.structure_document_id_seq
    START WITH 161
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.structure_document_id_seq OWNER TO :dbuser;

--
-- Name: structure_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.structure_id_seq
    START WITH 11086
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.structure_id_seq OWNER TO :dbuser;

--
-- Name: structure_substit_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.structure_substit_id_seq
    START WITH 401
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.structure_substit_id_seq OWNER TO :dbuser;

--
-- Name: sync_log; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.sync_log (
    id bigint NOT NULL,
    date_sync timestamp without time zone NOT NULL,
    message text NOT NULL,
    table_name character varying(30),
    source_code character varying(200)
);


ALTER TABLE public.sync_log OWNER TO :dbuser;

--
-- Name: sync_log_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.sync_log_id_seq
    START WITH 1095004
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.sync_log_id_seq OWNER TO :dbuser;

--
-- Name: synchro_log; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.synchro_log (
    id bigint NOT NULL,
    log_date timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    start_date timestamp without time zone NOT NULL,
    finish_date timestamp without time zone NOT NULL,
    status character varying(50) NOT NULL,
    sql text NOT NULL,
    message text
);


ALTER TABLE public.synchro_log OWNER TO :dbuser;

--
-- Name: synchro_log_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.synchro_log_id_seq
    START WITH 1474516
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.synchro_log_id_seq OWNER TO :dbuser;

--
-- Name: these_annee_univ_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.these_annee_univ_id_seq
    START WITH 92082
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.these_annee_univ_id_seq OWNER TO :dbuser;

--
-- Name: these_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.these_id_seq
    START WITH 49453
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.these_id_seq OWNER TO :dbuser;

--
-- Name: titre_acces; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.titre_acces (
    id bigint NOT NULL,
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    these_id bigint,
    titre_acces_interne_externe character varying(1),
    libelle_titre_acces character varying(200),
    type_etb_titre_acces character varying(50),
    libelle_etb_titre_acces character varying(200),
    code_dept_titre_acces character varying(20),
    code_pays_titre_acces character varying(20),
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone
);


ALTER TABLE public.titre_acces OWNER TO :dbuser;

--
-- Name: titre_acces_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.titre_acces_id_seq
    START WITH 17881
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.titre_acces_id_seq OWNER TO :dbuser;

--
-- Name: tmp_acteur_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_acteur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_acteur_id_seq OWNER TO :dbuser;

--
-- Name: tmp_acteur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_acteur_id_seq OWNED BY public.tmp_acteur.id;


--
-- Name: tmp_doctorant_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_doctorant_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_doctorant_id_seq OWNER TO :dbuser;

--
-- Name: tmp_doctorant_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_doctorant_id_seq OWNED BY public.tmp_doctorant.id;


--
-- Name: tmp_ecole_doct_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_ecole_doct_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_ecole_doct_id_seq OWNER TO :dbuser;

--
-- Name: tmp_ecole_doct_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_ecole_doct_id_seq OWNED BY public.tmp_ecole_doct.id;


--
-- Name: tmp_etablissement_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_etablissement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_etablissement_id_seq OWNER TO :dbuser;

--
-- Name: tmp_etablissement_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_etablissement_id_seq OWNED BY public.tmp_etablissement.id;


--
-- Name: tmp_financement_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_financement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_financement_id_seq OWNER TO :dbuser;

--
-- Name: tmp_financement_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_financement_id_seq OWNED BY public.tmp_financement.id;


--
-- Name: tmp_individu_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_individu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_individu_id_seq OWNER TO :dbuser;

--
-- Name: tmp_individu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_individu_id_seq OWNED BY public.tmp_individu.id;


--
-- Name: tmp_origine_financement_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_origine_financement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_origine_financement_id_seq OWNER TO :dbuser;

--
-- Name: tmp_origine_financement_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_origine_financement_id_seq OWNED BY public.tmp_origine_financement.id;


--
-- Name: tmp_role_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_role_id_seq OWNER TO :dbuser;

--
-- Name: tmp_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_role_id_seq OWNED BY public.tmp_role.id;


--
-- Name: tmp_structure_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_structure_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_structure_id_seq OWNER TO :dbuser;

--
-- Name: tmp_structure_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_structure_id_seq OWNED BY public.tmp_structure.id;


--
-- Name: tmp_these_annee_univ_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_these_annee_univ_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_these_annee_univ_id_seq OWNER TO :dbuser;

--
-- Name: tmp_these_annee_univ_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_these_annee_univ_id_seq OWNED BY public.tmp_these_annee_univ.id;


--
-- Name: tmp_these_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_these_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_these_id_seq OWNER TO :dbuser;

--
-- Name: tmp_these_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_these_id_seq OWNED BY public.tmp_these.id;


--
-- Name: tmp_titre_acces_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_titre_acces_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_titre_acces_id_seq OWNER TO :dbuser;

--
-- Name: tmp_titre_acces_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_titre_acces_id_seq OWNED BY public.tmp_titre_acces.id;


--
-- Name: tmp_unite_rech_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_unite_rech_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_unite_rech_id_seq OWNER TO :dbuser;

--
-- Name: tmp_unite_rech_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_unite_rech_id_seq OWNED BY public.tmp_unite_rech.id;


--
-- Name: tmp_variable_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.tmp_variable_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tmp_variable_id_seq OWNER TO :dbuser;

--
-- Name: tmp_variable_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.tmp_variable_id_seq OWNED BY public.tmp_variable.id;


--
-- Name: type_rapport; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.type_rapport (
    id bigint NOT NULL,
    code character varying(50) NOT NULL,
    libelle_court character varying(64) NOT NULL,
    libelle_long character varying(128) NOT NULL
);


ALTER TABLE public.type_rapport OWNER TO :dbuser;

--
-- Name: type_validation_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.type_validation_id_seq
    START WITH 21
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.type_validation_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_alerte_alerte_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_alerte_alerte_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_alerte_alerte_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_alerte_alerte; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_alerte_alerte (
    id bigint DEFAULT nextval('public.unicaen_alerte_alerte_id_seq'::regclass) NOT NULL,
    code character varying(64) NOT NULL,
    title text NOT NULL,
    text text NOT NULL,
    severity character varying(64),
    duration smallint DEFAULT 0 NOT NULL,
    dismissible boolean DEFAULT true
);


ALTER TABLE public.unicaen_alerte_alerte OWNER TO :dbuser;

--
-- Name: TABLE unicaen_alerte_alerte; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_alerte_alerte IS 'Messages d''alerte';


--
-- Name: COLUMN unicaen_alerte_alerte.code; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte.code IS 'Code littéral unique de cette alerte';


--
-- Name: COLUMN unicaen_alerte_alerte.title; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte.title IS 'Titre/intitulé de cette alerte';


--
-- Name: COLUMN unicaen_alerte_alerte.text; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte.text IS 'Texte de cette alerte';


--
-- Name: COLUMN unicaen_alerte_alerte.severity; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte.severity IS 'Sévérité (classe CSS) associée à cette alerte : success, info, warning, danger';


--
-- Name: COLUMN unicaen_alerte_alerte.duration; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte.duration IS 'Durée d''affichage de cette alerte en millisecondes (0 = infini)';


--
-- Name: COLUMN unicaen_alerte_alerte.dismissible; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte.dismissible IS 'Indique si cette alerte peut être fermée par l''utilisateur';


--
-- Name: unicaen_alerte_alerte_planning_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_alerte_alerte_planning_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_alerte_alerte_planning_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_alerte_alerte_planning; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_alerte_alerte_planning (
    id bigint DEFAULT nextval('public.unicaen_alerte_alerte_planning_id_seq'::regclass) NOT NULL,
    alerte_id bigint NOT NULL,
    start_date timestamp without time zone DEFAULT now() NOT NULL,
    end_date timestamp without time zone NOT NULL,
    severity character varying(64)
);


ALTER TABLE public.unicaen_alerte_alerte_planning OWNER TO :dbuser;

--
-- Name: TABLE unicaen_alerte_alerte_planning; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_alerte_alerte_planning IS 'Plannings d''affichage des alertes';


--
-- Name: COLUMN unicaen_alerte_alerte_planning.alerte_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte_planning.alerte_id IS 'Identifiant de l''alerte concernée';


--
-- Name: COLUMN unicaen_alerte_alerte_planning.start_date; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte_planning.start_date IS 'Date et heure de début de la période d''affichage de l''alerte';


--
-- Name: COLUMN unicaen_alerte_alerte_planning.end_date; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte_planning.end_date IS 'Date et heure de fin de la période d''affichage de l''alerte';


--
-- Name: COLUMN unicaen_alerte_alerte_planning.severity; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_alerte_alerte_planning.severity IS 'Sévérité remplaçant celle de l''alerte sur cette période';


--
-- Name: unicaen_avis_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_avis (
    id bigint DEFAULT nextval('public.unicaen_avis_id_seq'::regclass) NOT NULL,
    avis_type_id bigint NOT NULL,
    avis_valeur_id bigint NOT NULL
);


ALTER TABLE public.unicaen_avis OWNER TO :dbuser;

--
-- Name: TABLE unicaen_avis; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_avis IS 'Avis';


--
-- Name: COLUMN unicaen_avis.avis_type_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis.avis_type_id IS 'Identifiant du type de cet avis';


--
-- Name: COLUMN unicaen_avis.avis_valeur_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis.avis_valeur_id IS 'Identifiant de la valeur de cet vis';


--
-- Name: unicaen_avis_complem_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_complem_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_complem_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis_complem; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_avis_complem (
    id bigint DEFAULT nextval('public.unicaen_avis_complem_id_seq'::regclass) NOT NULL,
    avis_id bigint NOT NULL,
    avis_type_complem_id bigint NOT NULL,
    valeur text
);


ALTER TABLE public.unicaen_avis_complem OWNER TO :dbuser;

--
-- Name: TABLE unicaen_avis_complem; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_avis_complem IS 'Compléments apportés aux avis';


--
-- Name: COLUMN unicaen_avis_complem.avis_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_complem.avis_id IS 'Identifiant de l''avis concerné';


--
-- Name: COLUMN unicaen_avis_complem.avis_type_complem_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_complem.avis_type_complem_id IS 'Identifiant du complement attendu';


--
-- Name: COLUMN unicaen_avis_complem.valeur; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_complem.valeur IS 'Valeur du complement apportée';


--
-- Name: unicaen_avis_type_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_type_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis_type; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_avis_type (
    id bigint DEFAULT nextval('public.unicaen_avis_type_id_seq'::regclass) NOT NULL,
    code character varying(64) NOT NULL,
    libelle character varying(128) NOT NULL,
    description character varying(128),
    ordre smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public.unicaen_avis_type OWNER TO :dbuser;

--
-- Name: TABLE unicaen_avis_type; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_avis_type IS 'Types d''avis existants';


--
-- Name: COLUMN unicaen_avis_type.code; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type.code IS 'Code littéral unique de ce type d''avis';


--
-- Name: COLUMN unicaen_avis_type.libelle; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type.libelle IS 'Libellé de ce type d''avis';


--
-- Name: COLUMN unicaen_avis_type.description; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type.description IS 'Description éventuelle de ce type d''avis';


--
-- Name: COLUMN unicaen_avis_type.ordre; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type.ordre IS 'Entier permettant d''ordonner les types d''avis';


--
-- Name: unicaen_avis_type_valeur_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_type_valeur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_type_valeur_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis_type_valeur; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_avis_type_valeur (
    id bigint DEFAULT nextval('public.unicaen_avis_type_valeur_id_seq'::regclass) NOT NULL,
    avis_type_id bigint NOT NULL,
    avis_valeur_id bigint NOT NULL
);


ALTER TABLE public.unicaen_avis_type_valeur OWNER TO :dbuser;

--
-- Name: TABLE unicaen_avis_type_valeur; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_avis_type_valeur IS 'Valeurs d''avis autorisées par type d''avis';


--
-- Name: COLUMN unicaen_avis_type_valeur.avis_type_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur.avis_type_id IS 'Identifiant du type d''avis concerné';


--
-- Name: COLUMN unicaen_avis_type_valeur.avis_valeur_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur.avis_valeur_id IS 'Identifiant de la valeur d''avis autorisée';


--
-- Name: unicaen_avis_type_valeur_complem_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_type_valeur_complem_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_type_valeur_complem_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis_type_valeur_complem; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_avis_type_valeur_complem (
    id bigint DEFAULT nextval('public.unicaen_avis_type_valeur_complem_id_seq'::regclass) NOT NULL,
    avis_type_valeur_id bigint NOT NULL,
    parent_id bigint,
    code character varying(128) NOT NULL,
    libelle character varying(128) NOT NULL,
    type character varying(64) NOT NULL,
    ordre integer NOT NULL,
    obligatoire boolean DEFAULT false NOT NULL,
    obligatoire_un_au_moins boolean DEFAULT false NOT NULL
);


ALTER TABLE public.unicaen_avis_type_valeur_complem OWNER TO :dbuser;

--
-- Name: TABLE unicaen_avis_type_valeur_complem; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_avis_type_valeur_complem IS 'Compléments possibles selon le type d''avis et la valeur de l''avis';


--
-- Name: COLUMN unicaen_avis_type_valeur_complem.avis_type_valeur_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur_complem.avis_type_valeur_id IS 'Identifiant du type+valeur d''avis permettant ce complément';


--
-- Name: COLUMN unicaen_avis_type_valeur_complem.parent_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur_complem.parent_id IS 'Identifiant du complément parent éventuel de type checkbox uniquement (pour affichage et required conditionnel)';


--
-- Name: COLUMN unicaen_avis_type_valeur_complem.libelle; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur_complem.libelle IS 'Libellé du complément';


--
-- Name: COLUMN unicaen_avis_type_valeur_complem.type; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur_complem.type IS 'Type de valeur attendue pour ce complément (textarea, checkbox, checkbox+textarea, select, etc.)';


--
-- Name: COLUMN unicaen_avis_type_valeur_complem.obligatoire; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur_complem.obligatoire IS 'Témoin indiquant si une valeur est requise pour ce complément';


--
-- Name: COLUMN unicaen_avis_type_valeur_complem.obligatoire_un_au_moins; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_type_valeur_complem.obligatoire_un_au_moins IS 'Témoin indiquant si une valeur est requise pour l''un au moins des compléments ayant ce même témoin à `true`';


--
-- Name: unicaen_avis_type_valeur_complem_ordre_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_type_valeur_complem_ordre_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_type_valeur_complem_ordre_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis_type_valeur_complem_ordre_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.unicaen_avis_type_valeur_complem_ordre_seq OWNED BY public.unicaen_avis_type_valeur_complem.ordre;


--
-- Name: unicaen_avis_valeur_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_valeur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_valeur_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis_valeur; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_avis_valeur (
    id bigint DEFAULT nextval('public.unicaen_avis_valeur_id_seq'::regclass) NOT NULL,
    code character varying(64) NOT NULL,
    valeur character varying(128) NOT NULL,
    valeur_bool boolean,
    tags character varying(64),
    ordre integer NOT NULL,
    description character varying(128)
);


ALTER TABLE public.unicaen_avis_valeur OWNER TO :dbuser;

--
-- Name: TABLE unicaen_avis_valeur; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.unicaen_avis_valeur IS 'Valeurs d''avis possibles';


--
-- Name: COLUMN unicaen_avis_valeur.code; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_valeur.code IS 'Code littéral unique de ce complément';


--
-- Name: COLUMN unicaen_avis_valeur.valeur; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_valeur.valeur IS 'Valeur (ex : Favorable, Défavorable, etc.)';


--
-- Name: COLUMN unicaen_avis_valeur.valeur_bool; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_valeur.valeur_bool IS 'Éventuelle valeur booléenne équivalente';


--
-- Name: COLUMN unicaen_avis_valeur.tags; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_valeur.tags IS 'Éventuels tags associés à cette valeur (ex : classe CSS "success")';


--
-- Name: COLUMN unicaen_avis_valeur.ordre; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_valeur.ordre IS 'Entier permettant d''ordonner les valeurs';


--
-- Name: COLUMN unicaen_avis_valeur.description; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.unicaen_avis_valeur.description IS 'Description éventuelle de cette valeur';


--
-- Name: unicaen_avis_valeur_ordre_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_avis_valeur_ordre_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_avis_valeur_ordre_seq OWNER TO :dbuser;

--
-- Name: unicaen_avis_valeur_ordre_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.unicaen_avis_valeur_ordre_seq OWNED BY public.unicaen_avis_valeur.ordre;


--
-- Name: unicaen_parametre_categorie; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_parametre_categorie (
    id integer NOT NULL,
    code character varying(1024) NOT NULL,
    libelle character varying(1024) NOT NULL,
    description text,
    ordre integer DEFAULT 9999
);


ALTER TABLE public.unicaen_parametre_categorie OWNER TO :dbuser;

--
-- Name: unicaen_parametre_categorie_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_parametre_categorie_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_parametre_categorie_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_parametre_categorie_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.unicaen_parametre_categorie_id_seq OWNED BY public.unicaen_parametre_categorie.id;


--
-- Name: unicaen_parametre_parametre; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_parametre_parametre (
    id integer NOT NULL,
    categorie_id integer NOT NULL,
    code character varying(1024) NOT NULL,
    libelle character varying(1024) NOT NULL,
    description text,
    valeurs_possibles text,
    valeur text,
    ordre integer DEFAULT 9999
);


ALTER TABLE public.unicaen_parametre_parametre OWNER TO :dbuser;

--
-- Name: unicaen_parametre_parametre_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_parametre_parametre_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_parametre_parametre_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_parametre_parametre_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.unicaen_parametre_parametre_id_seq OWNED BY public.unicaen_parametre_parametre.id;


--
-- Name: unicaen_renderer_macro; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_renderer_macro (
    id integer NOT NULL,
    code character varying(256) NOT NULL,
    description text,
    variable_name character varying(256) NOT NULL,
    methode_name character varying(256) NOT NULL
);


ALTER TABLE public.unicaen_renderer_macro OWNER TO :dbuser;

--
-- Name: unicaen_renderer_macro_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_renderer_macro_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_renderer_macro_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_renderer_macro_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.unicaen_renderer_macro_id_seq OWNED BY public.unicaen_renderer_macro.id;


--
-- Name: unicaen_renderer_rendu; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_renderer_rendu (
    id integer NOT NULL,
    template_id integer,
    date_generation timestamp without time zone NOT NULL,
    sujet text NOT NULL,
    corps text NOT NULL
);


ALTER TABLE public.unicaen_renderer_rendu OWNER TO :dbuser;

--
-- Name: unicaen_renderer_rendu_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_renderer_rendu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_renderer_rendu_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_renderer_rendu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.unicaen_renderer_rendu_id_seq OWNED BY public.unicaen_renderer_rendu.id;


--
-- Name: unicaen_renderer_template; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unicaen_renderer_template (
    id integer NOT NULL,
    code character varying(256) NOT NULL,
    description text,
    document_type character varying(256) NOT NULL,
    document_sujet text NOT NULL,
    document_corps text NOT NULL,
    document_css text,
    namespace character varying(256)
);


ALTER TABLE public.unicaen_renderer_template OWNER TO :dbuser;

--
-- Name: unicaen_renderer_template_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unicaen_renderer_template_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unicaen_renderer_template_id_seq OWNER TO :dbuser;

--
-- Name: unicaen_renderer_template_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: :dbuser
--

ALTER SEQUENCE public.unicaen_renderer_template_id_seq OWNED BY public.unicaen_renderer_template.id;


--
-- Name: unite_domaine_linker; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.unite_domaine_linker (
    unite_id bigint NOT NULL,
    domaine_id bigint NOT NULL
);


ALTER TABLE public.unite_domaine_linker OWNER TO :dbuser;

--
-- Name: unite_rech_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.unite_rech_id_seq
    START WITH 741
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.unite_rech_id_seq OWNER TO :dbuser;

--
-- Name: user_token; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.user_token (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    token character varying(256) NOT NULL,
    action character varying(256),
    actions_count bigint DEFAULT 0 NOT NULL,
    actions_max_count bigint DEFAULT 0 NOT NULL,
    created_on timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    expired_on timestamp without time zone NOT NULL,
    last_used_on timestamp without time zone,
    sent_on timestamp without time zone
);


ALTER TABLE public.user_token OWNER TO :dbuser;

--
-- Name: TABLE user_token; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.user_token IS 'Jetons d''authentification utilisateur';


--
-- Name: COLUMN user_token.user_id; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.user_id IS 'Identifiant unique de l''utilisateur';


--
-- Name: COLUMN user_token.token; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.token IS 'Le jeton !';


--
-- Name: COLUMN user_token.action; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.action IS 'Spécification de l''action précise autorisée, le cas échéant';


--
-- Name: COLUMN user_token.actions_count; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.actions_count IS 'Nombre d''utilisation du jeton';


--
-- Name: COLUMN user_token.actions_max_count; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.actions_max_count IS 'Nombre maximum d''utilisations du jeton autorisée (0 = pas de limite)';


--
-- Name: COLUMN user_token.created_on; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.created_on IS 'Date de création du jeton';


--
-- Name: COLUMN user_token.expired_on; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.expired_on IS 'Date d''expiration du jeton';


--
-- Name: COLUMN user_token.last_used_on; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON COLUMN public.user_token.last_used_on IS 'Date de dernière utilisation du jeton';


--
-- Name: user_token_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.user_token_id_seq
    START WITH 161
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.user_token_id_seq OWNER TO :dbuser;

--
-- Name: utilisateur; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.utilisateur (
    id bigint NOT NULL,
    username character varying(255),
    email character varying(255),
    display_name character varying(100),
    password character varying(128) NOT NULL,
    state bigint DEFAULT 1 NOT NULL,
    last_role_id bigint,
    individu_id bigint,
    password_reset_token character varying(256),
    created_at timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone,
    nom character varying(128),
    prenom character varying(128)
);


ALTER TABLE public.utilisateur OWNER TO :dbuser;

--
-- Name: TABLE utilisateur; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.utilisateur IS 'Comptes utilisateurs s''étant déjà connecté à l''application + comptes avec mot de passe local.';


--
-- Name: utilisateur_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.utilisateur_id_seq
    START WITH 45543
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.utilisateur_id_seq OWNER TO :dbuser;

--
-- Name: v_diff_acteur; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_acteur AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.individu_id <> s.individu_id) OR ((d.individu_id IS NULL) AND (s.individu_id IS NOT NULL)) OR ((d.individu_id IS NOT NULL) AND (s.individu_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_individu_id,
                CASE
                    WHEN ((d.these_id <> s.these_id) OR ((d.these_id IS NULL) AND (s.these_id IS NOT NULL)) OR ((d.these_id IS NOT NULL) AND (s.these_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_these_id,
                CASE
                    WHEN ((d.role_id <> s.role_id) OR ((d.role_id IS NULL) AND (s.role_id IS NOT NULL)) OR ((d.role_id IS NOT NULL) AND (s.role_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_role_id,
                CASE
                    WHEN ((d.acteur_etablissement_id <> s.acteur_etablissement_id) OR ((d.acteur_etablissement_id IS NULL) AND (s.acteur_etablissement_id IS NOT NULL)) OR ((d.acteur_etablissement_id IS NOT NULL) AND (s.acteur_etablissement_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_acteur_etablissement_id,
                CASE
                    WHEN (((d.qualite)::text <> (s.qualite)::text) OR ((d.qualite IS NULL) AND (s.qualite IS NOT NULL)) OR ((d.qualite IS NOT NULL) AND (s.qualite IS NULL))) THEN 1
                    ELSE 0
                END AS u_qualite,
                CASE
                    WHEN (((d.lib_role_compl)::text <> (s.lib_role_compl)::text) OR ((d.lib_role_compl IS NULL) AND (s.lib_role_compl IS NOT NULL)) OR ((d.lib_role_compl IS NOT NULL) AND (s.lib_role_compl IS NULL))) THEN 1
                    ELSE 0
                END AS u_lib_role_compl,
            s.source_id AS s_source_id,
            s.individu_id AS s_individu_id,
            s.these_id AS s_these_id,
            s.role_id AS s_role_id,
            s.acteur_etablissement_id AS s_acteur_etablissement_id,
            s.qualite AS s_qualite,
            s.lib_role_compl AS s_lib_role_compl,
            d.source_id AS d_source_id,
            d.individu_id AS d_individu_id,
            d.these_id AS d_these_id,
            d.role_id AS d_role_id,
            d.acteur_etablissement_id AS d_acteur_etablissement_id,
            d.qualite AS d_qualite,
            d.lib_role_compl AS d_lib_role_compl
           FROM ((public.acteur d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_acteur s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_individu_id,
    diff.u_these_id,
    diff.u_role_id,
    diff.u_acteur_etablissement_id,
    diff.u_qualite,
    diff.u_lib_role_compl,
    diff.s_source_id,
    diff.s_individu_id,
    diff.s_these_id,
    diff.s_role_id,
    diff.s_acteur_etablissement_id,
    diff.s_qualite,
    diff.s_lib_role_compl,
    diff.d_source_id,
    diff.d_individu_id,
    diff.d_these_id,
    diff.d_role_id,
    diff.d_acteur_etablissement_id,
    diff.d_qualite,
    diff.d_lib_role_compl
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < ((((((diff.u_source_id + diff.u_individu_id) + diff.u_these_id) + diff.u_role_id) + diff.u_acteur_etablissement_id) + diff.u_qualite) + diff.u_lib_role_compl))));


ALTER TABLE public.v_diff_acteur OWNER TO :dbuser;

--
-- Name: v_diff_doctorant; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_doctorant AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN (((d.ine)::text <> (s.ine)::text) OR ((d.ine IS NULL) AND (s.ine IS NOT NULL)) OR ((d.ine IS NOT NULL) AND (s.ine IS NULL))) THEN 1
                    ELSE 0
                END AS u_ine,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.individu_id <> s.individu_id) OR ((d.individu_id IS NULL) AND (s.individu_id IS NOT NULL)) OR ((d.individu_id IS NOT NULL) AND (s.individu_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_individu_id,
                CASE
                    WHEN ((d.etablissement_id <> s.etablissement_id) OR ((d.etablissement_id IS NULL) AND (s.etablissement_id IS NOT NULL)) OR ((d.etablissement_id IS NOT NULL) AND (s.etablissement_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_etablissement_id,
            s.ine AS s_ine,
            s.source_id AS s_source_id,
            s.individu_id AS s_individu_id,
            s.etablissement_id AS s_etablissement_id,
            d.ine AS d_ine,
            d.source_id AS d_source_id,
            d.individu_id AS d_individu_id,
            d.etablissement_id AS d_etablissement_id
           FROM ((public.doctorant d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_doctorant s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_ine,
    diff.u_source_id,
    diff.u_individu_id,
    diff.u_etablissement_id,
    diff.s_ine,
    diff.s_source_id,
    diff.s_individu_id,
    diff.s_etablissement_id,
    diff.d_ine,
    diff.d_source_id,
    diff.d_individu_id,
    diff.d_etablissement_id
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < (((diff.u_ine + diff.u_source_id) + diff.u_individu_id) + diff.u_etablissement_id))));


ALTER TABLE public.v_diff_doctorant OWNER TO :dbuser;

--
-- Name: v_diff_ecole_doct; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_ecole_doct AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.structure_id <> s.structure_id) OR ((d.structure_id IS NULL) AND (s.structure_id IS NOT NULL)) OR ((d.structure_id IS NOT NULL) AND (s.structure_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_structure_id,
            s.source_id AS s_source_id,
            s.structure_id AS s_structure_id,
            d.source_id AS d_source_id,
            d.structure_id AS d_structure_id
           FROM ((public.ecole_doct d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_ecole_doct s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_structure_id,
    diff.s_source_id,
    diff.s_structure_id,
    diff.d_source_id,
    diff.d_structure_id
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < (diff.u_source_id + diff.u_structure_id))));


ALTER TABLE public.v_diff_ecole_doct OWNER TO :dbuser;

--
-- Name: v_diff_etablissement; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_etablissement AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.structure_id <> s.structure_id) OR ((d.structure_id IS NULL) AND (s.structure_id IS NOT NULL)) OR ((d.structure_id IS NOT NULL) AND (s.structure_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_structure_id,
            s.source_id AS s_source_id,
            s.structure_id AS s_structure_id,
            d.source_id AS d_source_id,
            d.structure_id AS d_structure_id
           FROM ((public.etablissement d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_etablissement s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_structure_id,
    diff.s_source_id,
    diff.s_structure_id,
    diff.d_source_id,
    diff.d_structure_id
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < (diff.u_source_id + diff.u_structure_id))));


ALTER TABLE public.v_diff_etablissement OWNER TO :dbuser;

--
-- Name: v_diff_financement; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_financement AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.these_id <> s.these_id) OR ((d.these_id IS NULL) AND (s.these_id IS NOT NULL)) OR ((d.these_id IS NOT NULL) AND (s.these_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_these_id,
                CASE
                    WHEN (((d.annee)::numeric <> s.annee) OR ((d.annee IS NULL) AND (s.annee IS NOT NULL)) OR ((d.annee IS NOT NULL) AND (s.annee IS NULL))) THEN 1
                    ELSE 0
                END AS u_annee,
                CASE
                    WHEN ((d.origine_financement_id <> s.origine_financement_id) OR ((d.origine_financement_id IS NULL) AND (s.origine_financement_id IS NOT NULL)) OR ((d.origine_financement_id IS NOT NULL) AND (s.origine_financement_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_origine_financement_id,
                CASE
                    WHEN (((d.complement_financement)::text <> (s.complement_financement)::text) OR ((d.complement_financement IS NULL) AND (s.complement_financement IS NOT NULL)) OR ((d.complement_financement IS NOT NULL) AND (s.complement_financement IS NULL))) THEN 1
                    ELSE 0
                END AS u_complement_financement,
                CASE
                    WHEN (((d.quotite_financement)::text <> (s.quotite_financement)::text) OR ((d.quotite_financement IS NULL) AND (s.quotite_financement IS NOT NULL)) OR ((d.quotite_financement IS NOT NULL) AND (s.quotite_financement IS NULL))) THEN 1
                    ELSE 0
                END AS u_quotite_financement,
                CASE
                    WHEN ((d.date_debut <> s.date_debut) OR ((d.date_debut IS NULL) AND (s.date_debut IS NOT NULL)) OR ((d.date_debut IS NOT NULL) AND (s.date_debut IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_debut,
                CASE
                    WHEN ((d.date_fin <> s.date_fin) OR ((d.date_fin IS NULL) AND (s.date_fin IS NOT NULL)) OR ((d.date_fin IS NOT NULL) AND (s.date_fin IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_fin,
                CASE
                    WHEN (((d.code_type_financement)::text <> (s.code_type_financement)::text) OR ((d.code_type_financement IS NULL) AND (s.code_type_financement IS NOT NULL)) OR ((d.code_type_financement IS NOT NULL) AND (s.code_type_financement IS NULL))) THEN 1
                    ELSE 0
                END AS u_code_type_financement,
                CASE
                    WHEN (((d.libelle_type_financement)::text <> (s.libelle_type_financement)::text) OR ((d.libelle_type_financement IS NULL) AND (s.libelle_type_financement IS NOT NULL)) OR ((d.libelle_type_financement IS NOT NULL) AND (s.libelle_type_financement IS NULL))) THEN 1
                    ELSE 0
                END AS u_libelle_type_financement,
            s.source_id AS s_source_id,
            s.these_id AS s_these_id,
            s.annee AS s_annee,
            s.origine_financement_id AS s_origine_financement_id,
            s.complement_financement AS s_complement_financement,
            s.quotite_financement AS s_quotite_financement,
            s.date_debut AS s_date_debut,
            s.date_fin AS s_date_fin,
            s.code_type_financement AS s_code_type_financement,
            s.libelle_type_financement AS s_libelle_type_financement,
            d.source_id AS d_source_id,
            d.these_id AS d_these_id,
            d.annee AS d_annee,
            d.origine_financement_id AS d_origine_financement_id,
            d.complement_financement AS d_complement_financement,
            d.quotite_financement AS d_quotite_financement,
            d.date_debut AS d_date_debut,
            d.date_fin AS d_date_fin,
            d.code_type_financement AS d_code_type_financement,
            d.libelle_type_financement AS d_libelle_type_financement
           FROM ((public.financement d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_financement s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_these_id,
    diff.u_annee,
    diff.u_origine_financement_id,
    diff.u_complement_financement,
    diff.u_quotite_financement,
    diff.u_date_debut,
    diff.u_date_fin,
    diff.u_code_type_financement,
    diff.u_libelle_type_financement,
    diff.s_source_id,
    diff.s_these_id,
    diff.s_annee,
    diff.s_origine_financement_id,
    diff.s_complement_financement,
    diff.s_quotite_financement,
    diff.s_date_debut,
    diff.s_date_fin,
    diff.s_code_type_financement,
    diff.s_libelle_type_financement,
    diff.d_source_id,
    diff.d_these_id,
    diff.d_annee,
    diff.d_origine_financement_id,
    diff.d_complement_financement,
    diff.d_quotite_financement,
    diff.d_date_debut,
    diff.d_date_fin,
    diff.d_code_type_financement,
    diff.d_libelle_type_financement
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < (((((((((diff.u_source_id + diff.u_these_id) + diff.u_annee) + diff.u_origine_financement_id) + diff.u_complement_financement) + diff.u_quotite_financement) + diff.u_date_debut) + diff.u_date_fin) + diff.u_code_type_financement) + diff.u_libelle_type_financement))));


ALTER TABLE public.v_diff_financement OWNER TO :dbuser;

--
-- Name: v_diff_individu; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_individu AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN (((d.type)::text <> (s.type)::text) OR ((d.type IS NULL) AND (s.type IS NOT NULL)) OR ((d.type IS NOT NULL) AND (s.type IS NULL))) THEN 1
                    ELSE 0
                END AS u_type,
                CASE
                    WHEN (((d.supann_id)::text <> (s.supann_id)::text) OR ((d.supann_id IS NULL) AND (s.supann_id IS NOT NULL)) OR ((d.supann_id IS NOT NULL) AND (s.supann_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_supann_id,
                CASE
                    WHEN (((d.civilite)::text <> (s.civilite)::text) OR ((d.civilite IS NULL) AND (s.civilite IS NOT NULL)) OR ((d.civilite IS NOT NULL) AND (s.civilite IS NULL))) THEN 1
                    ELSE 0
                END AS u_civilite,
                CASE
                    WHEN (((d.nom_usuel)::text <> (s.nom_usuel)::text) OR ((d.nom_usuel IS NULL) AND (s.nom_usuel IS NOT NULL)) OR ((d.nom_usuel IS NOT NULL) AND (s.nom_usuel IS NULL))) THEN 1
                    ELSE 0
                END AS u_nom_usuel,
                CASE
                    WHEN (((d.nom_patronymique)::text <> (s.nom_patronymique)::text) OR ((d.nom_patronymique IS NULL) AND (s.nom_patronymique IS NOT NULL)) OR ((d.nom_patronymique IS NOT NULL) AND (s.nom_patronymique IS NULL))) THEN 1
                    ELSE 0
                END AS u_nom_patronymique,
                CASE
                    WHEN (((d.prenom1)::text <> (s.prenom1)::text) OR ((d.prenom1 IS NULL) AND (s.prenom1 IS NOT NULL)) OR ((d.prenom1 IS NOT NULL) AND (s.prenom1 IS NULL))) THEN 1
                    ELSE 0
                END AS u_prenom1,
                CASE
                    WHEN (((d.prenom2)::text <> (s.prenom2)::text) OR ((d.prenom2 IS NULL) AND (s.prenom2 IS NOT NULL)) OR ((d.prenom2 IS NOT NULL) AND (s.prenom2 IS NULL))) THEN 1
                    ELSE 0
                END AS u_prenom2,
                CASE
                    WHEN (((d.prenom3)::text <> (s.prenom3)::text) OR ((d.prenom3 IS NULL) AND (s.prenom3 IS NOT NULL)) OR ((d.prenom3 IS NOT NULL) AND (s.prenom3 IS NULL))) THEN 1
                    ELSE 0
                END AS u_prenom3,
                CASE
                    WHEN (((d.email)::text <> (s.email)::text) OR ((d.email IS NULL) AND (s.email IS NOT NULL)) OR ((d.email IS NOT NULL) AND (s.email IS NULL))) THEN 1
                    ELSE 0
                END AS u_email,
                CASE
                    WHEN ((d.date_naissance <> s.date_naissance) OR ((d.date_naissance IS NULL) AND (s.date_naissance IS NOT NULL)) OR ((d.date_naissance IS NOT NULL) AND (s.date_naissance IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_naissance,
                CASE
                    WHEN (((d.nationalite)::text <> (s.nationalite)::text) OR ((d.nationalite IS NULL) AND (s.nationalite IS NOT NULL)) OR ((d.nationalite IS NOT NULL) AND (s.nationalite IS NULL))) THEN 1
                    ELSE 0
                END AS u_nationalite,
                CASE
                    WHEN ((d.pays_id_nationalite <> s.pays_id_nationalite) OR ((d.pays_id_nationalite IS NULL) AND (s.pays_id_nationalite IS NOT NULL)) OR ((d.pays_id_nationalite IS NOT NULL) AND (s.pays_id_nationalite IS NULL))) THEN 1
                    ELSE 0
                END AS u_pays_id_nationalite,
            s.source_id AS s_source_id,
            s.type AS s_type,
            s.supann_id AS s_supann_id,
            s.civilite AS s_civilite,
            s.nom_usuel AS s_nom_usuel,
            s.nom_patronymique AS s_nom_patronymique,
            s.prenom1 AS s_prenom1,
            s.prenom2 AS s_prenom2,
            s.prenom3 AS s_prenom3,
            s.email AS s_email,
            s.date_naissance AS s_date_naissance,
            s.nationalite AS s_nationalite,
            s.pays_id_nationalite AS s_pays_id_nationalite,
            d.source_id AS d_source_id,
            d.type AS d_type,
            d.supann_id AS d_supann_id,
            d.civilite AS d_civilite,
            d.nom_usuel AS d_nom_usuel,
            d.nom_patronymique AS d_nom_patronymique,
            d.prenom1 AS d_prenom1,
            d.prenom2 AS d_prenom2,
            d.prenom3 AS d_prenom3,
            d.email AS d_email,
            d.date_naissance AS d_date_naissance,
            d.nationalite AS d_nationalite,
            d.pays_id_nationalite AS d_pays_id_nationalite
           FROM ((public.individu d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_individu s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_type,
    diff.u_supann_id,
    diff.u_civilite,
    diff.u_nom_usuel,
    diff.u_nom_patronymique,
    diff.u_prenom1,
    diff.u_prenom2,
    diff.u_prenom3,
    diff.u_email,
    diff.u_date_naissance,
    diff.u_nationalite,
    diff.u_pays_id_nationalite,
    diff.s_source_id,
    diff.s_type,
    diff.s_supann_id,
    diff.s_civilite,
    diff.s_nom_usuel,
    diff.s_nom_patronymique,
    diff.s_prenom1,
    diff.s_prenom2,
    diff.s_prenom3,
    diff.s_email,
    diff.s_date_naissance,
    diff.s_nationalite,
    diff.s_pays_id_nationalite,
    diff.d_source_id,
    diff.d_type,
    diff.d_supann_id,
    diff.d_civilite,
    diff.d_nom_usuel,
    diff.d_nom_patronymique,
    diff.d_prenom1,
    diff.d_prenom2,
    diff.d_prenom3,
    diff.d_email,
    diff.d_date_naissance,
    diff.d_nationalite,
    diff.d_pays_id_nationalite
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < ((((((((((((diff.u_source_id + diff.u_type) + diff.u_supann_id) + diff.u_civilite) + diff.u_nom_usuel) + diff.u_nom_patronymique) + diff.u_prenom1) + diff.u_prenom2) + diff.u_prenom3) + diff.u_email) + diff.u_date_naissance) + diff.u_nationalite) + diff.u_pays_id_nationalite))));


ALTER TABLE public.v_diff_individu OWNER TO :dbuser;

--
-- Name: v_diff_origine_financement; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_origine_financement AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN (((d.code)::text <> (s.code)::text) OR ((d.code IS NULL) AND (s.code IS NOT NULL)) OR ((d.code IS NOT NULL) AND (s.code IS NULL))) THEN 1
                    ELSE 0
                END AS u_code,
                CASE
                    WHEN (((d.libelle_court)::text <> (s.libelle_court)::text) OR ((d.libelle_court IS NULL) AND (s.libelle_court IS NOT NULL)) OR ((d.libelle_court IS NOT NULL) AND (s.libelle_court IS NULL))) THEN 1
                    ELSE 0
                END AS u_libelle_court,
                CASE
                    WHEN (((d.libelle_long)::text <> (s.libelle_long)::text) OR ((d.libelle_long IS NULL) AND (s.libelle_long IS NOT NULL)) OR ((d.libelle_long IS NOT NULL) AND (s.libelle_long IS NULL))) THEN 1
                    ELSE 0
                END AS u_libelle_long,
            s.source_id AS s_source_id,
            s.code AS s_code,
            s.libelle_court AS s_libelle_court,
            s.libelle_long AS s_libelle_long,
            d.source_id AS d_source_id,
            d.code AS d_code,
            d.libelle_court AS d_libelle_court,
            d.libelle_long AS d_libelle_long
           FROM ((public.origine_financement d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_origine_financement s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_code,
    diff.u_libelle_court,
    diff.u_libelle_long,
    diff.s_source_id,
    diff.s_code,
    diff.s_libelle_court,
    diff.s_libelle_long,
    diff.d_source_id,
    diff.d_code,
    diff.d_libelle_court,
    diff.d_libelle_long
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < (((diff.u_source_id + diff.u_code) + diff.u_libelle_court) + diff.u_libelle_long))));


ALTER TABLE public.v_diff_origine_financement OWNER TO :dbuser;

--
-- Name: v_diff_role; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_role AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN (((d.libelle)::text <> (s.libelle)::text) OR ((d.libelle IS NULL) AND (s.libelle IS NOT NULL)) OR ((d.libelle IS NOT NULL) AND (s.libelle IS NULL))) THEN 1
                    ELSE 0
                END AS u_libelle,
                CASE
                    WHEN (((d.code)::text <> s.code) OR ((d.code IS NULL) AND (s.code IS NOT NULL)) OR ((d.code IS NOT NULL) AND (s.code IS NULL))) THEN 1
                    ELSE 0
                END AS u_code,
                CASE
                    WHEN (((d.role_id)::text <> s.role_id) OR ((d.role_id IS NULL) AND (s.role_id IS NOT NULL)) OR ((d.role_id IS NOT NULL) AND (s.role_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_role_id,
                CASE
                    WHEN ((d.these_dep <> s.these_dep) OR ((d.these_dep IS NULL) AND (s.these_dep IS NOT NULL)) OR ((d.these_dep IS NOT NULL) AND (s.these_dep IS NULL))) THEN 1
                    ELSE 0
                END AS u_these_dep,
                CASE
                    WHEN ((d.structure_id <> s.structure_id) OR ((d.structure_id IS NULL) AND (s.structure_id IS NOT NULL)) OR ((d.structure_id IS NOT NULL) AND (s.structure_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_structure_id,
                CASE
                    WHEN ((d.type_structure_dependant_id <> s.type_structure_dependant_id) OR ((d.type_structure_dependant_id IS NULL) AND (s.type_structure_dependant_id IS NOT NULL)) OR ((d.type_structure_dependant_id IS NOT NULL) AND (s.type_structure_dependant_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_type_structure_dependant_id,
            s.source_id AS s_source_id,
            s.libelle AS s_libelle,
            s.code AS s_code,
            s.role_id AS s_role_id,
            s.these_dep AS s_these_dep,
            s.structure_id AS s_structure_id,
            s.type_structure_dependant_id AS s_type_structure_dependant_id,
            d.source_id AS d_source_id,
            d.libelle AS d_libelle,
            d.code AS d_code,
            d.role_id AS d_role_id,
            d.these_dep AS d_these_dep,
            d.structure_id AS d_structure_id,
            d.type_structure_dependant_id AS d_type_structure_dependant_id
           FROM ((public.role d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_role s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_libelle,
    diff.u_code,
    diff.u_role_id,
    diff.u_these_dep,
    diff.u_structure_id,
    diff.u_type_structure_dependant_id,
    diff.s_source_id,
    diff.s_libelle,
    diff.s_code,
    diff.s_role_id,
    diff.s_these_dep,
    diff.s_structure_id,
    diff.s_type_structure_dependant_id,
    diff.d_source_id,
    diff.d_libelle,
    diff.d_code,
    diff.d_role_id,
    diff.d_these_dep,
    diff.d_structure_id,
    diff.d_type_structure_dependant_id
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < ((((((diff.u_source_id + diff.u_libelle) + diff.u_code) + diff.u_role_id) + diff.u_these_dep) + diff.u_structure_id) + diff.u_type_structure_dependant_id))));


ALTER TABLE public.v_diff_role OWNER TO :dbuser;

--
-- Name: v_diff_structure; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_structure AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN (((d.code)::text <> s.code) OR ((d.code IS NULL) AND (s.code IS NOT NULL)) OR ((d.code IS NOT NULL) AND (s.code IS NULL))) THEN 1
                    ELSE 0
                END AS u_code,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.type_structure_id <> s.type_structure_id) OR ((d.type_structure_id IS NULL) AND (s.type_structure_id IS NOT NULL)) OR ((d.type_structure_id IS NOT NULL) AND (s.type_structure_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_type_structure_id,
                CASE
                    WHEN (((d.sigle)::text <> (s.sigle)::text) OR ((d.sigle IS NULL) AND (s.sigle IS NOT NULL)) OR ((d.sigle IS NOT NULL) AND (s.sigle IS NULL))) THEN 1
                    ELSE 0
                END AS u_sigle,
                CASE
                    WHEN (((d.libelle)::text <> (s.libelle)::text) OR ((d.libelle IS NULL) AND (s.libelle IS NOT NULL)) OR ((d.libelle IS NOT NULL) AND (s.libelle IS NULL))) THEN 1
                    ELSE 0
                END AS u_libelle,
            s.code AS s_code,
            s.source_id AS s_source_id,
            s.type_structure_id AS s_type_structure_id,
            s.sigle AS s_sigle,
            s.libelle AS s_libelle,
            d.code AS d_code,
            d.source_id AS d_source_id,
            d.type_structure_id AS d_type_structure_id,
            d.sigle AS d_sigle,
            d.libelle AS d_libelle
           FROM ((public.structure d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_structure s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_code,
    diff.u_source_id,
    diff.u_type_structure_id,
    diff.u_sigle,
    diff.u_libelle,
    diff.s_code,
    diff.s_source_id,
    diff.s_type_structure_id,
    diff.s_sigle,
    diff.s_libelle,
    diff.d_code,
    diff.d_source_id,
    diff.d_type_structure_id,
    diff.d_sigle,
    diff.d_libelle
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < ((((diff.u_code + diff.u_source_id) + diff.u_type_structure_id) + diff.u_sigle) + diff.u_libelle))));


ALTER TABLE public.v_diff_structure OWNER TO :dbuser;

--
-- Name: v_diff_these; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_these AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.etablissement_id <> s.etablissement_id) OR ((d.etablissement_id IS NULL) AND (s.etablissement_id IS NOT NULL)) OR ((d.etablissement_id IS NOT NULL) AND (s.etablissement_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_etablissement_id,
                CASE
                    WHEN ((d.doctorant_id <> s.doctorant_id) OR ((d.doctorant_id IS NULL) AND (s.doctorant_id IS NOT NULL)) OR ((d.doctorant_id IS NOT NULL) AND (s.doctorant_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_doctorant_id,
                CASE
                    WHEN ((d.ecole_doct_id <> s.ecole_doct_id) OR ((d.ecole_doct_id IS NULL) AND (s.ecole_doct_id IS NOT NULL)) OR ((d.ecole_doct_id IS NOT NULL) AND (s.ecole_doct_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_ecole_doct_id,
                CASE
                    WHEN ((d.unite_rech_id <> s.unite_rech_id) OR ((d.unite_rech_id IS NULL) AND (s.unite_rech_id IS NOT NULL)) OR ((d.unite_rech_id IS NOT NULL) AND (s.unite_rech_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_unite_rech_id,
                CASE
                    WHEN (((d.titre)::text <> (s.titre)::text) OR ((d.titre IS NULL) AND (s.titre IS NOT NULL)) OR ((d.titre IS NOT NULL) AND (s.titre IS NULL))) THEN 1
                    ELSE 0
                END AS u_titre,
                CASE
                    WHEN (((d.etat_these)::text <> (s.etat_these)::text) OR ((d.etat_these IS NULL) AND (s.etat_these IS NOT NULL)) OR ((d.etat_these IS NOT NULL) AND (s.etat_these IS NULL))) THEN 1
                    ELSE 0
                END AS u_etat_these,
                CASE
                    WHEN (((d.resultat)::numeric <> s.resultat) OR ((d.resultat IS NULL) AND (s.resultat IS NOT NULL)) OR ((d.resultat IS NOT NULL) AND (s.resultat IS NULL))) THEN 1
                    ELSE 0
                END AS u_resultat,
                CASE
                    WHEN (((d.code_sise_disc)::text <> (s.code_sise_disc)::text) OR ((d.code_sise_disc IS NULL) AND (s.code_sise_disc IS NOT NULL)) OR ((d.code_sise_disc IS NOT NULL) AND (s.code_sise_disc IS NULL))) THEN 1
                    ELSE 0
                END AS u_code_sise_disc,
                CASE
                    WHEN (((d.lib_disc)::text <> (s.lib_disc)::text) OR ((d.lib_disc IS NULL) AND (s.lib_disc IS NOT NULL)) OR ((d.lib_disc IS NOT NULL) AND (s.lib_disc IS NULL))) THEN 1
                    ELSE 0
                END AS u_lib_disc,
                CASE
                    WHEN ((d.date_prem_insc <> s.date_prem_insc) OR ((d.date_prem_insc IS NULL) AND (s.date_prem_insc IS NOT NULL)) OR ((d.date_prem_insc IS NOT NULL) AND (s.date_prem_insc IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_prem_insc,
                CASE
                    WHEN ((d.date_prev_soutenance <> s.date_prev_soutenance) OR ((d.date_prev_soutenance IS NULL) AND (s.date_prev_soutenance IS NOT NULL)) OR ((d.date_prev_soutenance IS NOT NULL) AND (s.date_prev_soutenance IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_prev_soutenance,
                CASE
                    WHEN ((d.date_soutenance <> s.date_soutenance) OR ((d.date_soutenance IS NULL) AND (s.date_soutenance IS NOT NULL)) OR ((d.date_soutenance IS NOT NULL) AND (s.date_soutenance IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_soutenance,
                CASE
                    WHEN ((d.date_fin_confid <> s.date_fin_confid) OR ((d.date_fin_confid IS NULL) AND (s.date_fin_confid IS NOT NULL)) OR ((d.date_fin_confid IS NOT NULL) AND (s.date_fin_confid IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_fin_confid,
                CASE
                    WHEN (((d.lib_etab_cotut)::text <> (s.lib_etab_cotut)::text) OR ((d.lib_etab_cotut IS NULL) AND (s.lib_etab_cotut IS NOT NULL)) OR ((d.lib_etab_cotut IS NOT NULL) AND (s.lib_etab_cotut IS NULL))) THEN 1
                    ELSE 0
                END AS u_lib_etab_cotut,
                CASE
                    WHEN (((d.lib_pays_cotut)::text <> (s.lib_pays_cotut)::text) OR ((d.lib_pays_cotut IS NULL) AND (s.lib_pays_cotut IS NOT NULL)) OR ((d.lib_pays_cotut IS NOT NULL) AND (s.lib_pays_cotut IS NULL))) THEN 1
                    ELSE 0
                END AS u_lib_pays_cotut,
                CASE
                    WHEN (((d.correc_autorisee)::text <> (s.correc_autorisee)::text) OR ((d.correc_autorisee IS NULL) AND (s.correc_autorisee IS NOT NULL)) OR ((d.correc_autorisee IS NOT NULL) AND (s.correc_autorisee IS NULL))) THEN 1
                    ELSE 0
                END AS u_correc_autorisee,
                CASE
                    WHEN (((d.correc_effectuee)::text <> (s.correc_effectuee)::text) OR ((d.correc_effectuee IS NULL) AND (s.correc_effectuee IS NOT NULL)) OR ((d.correc_effectuee IS NOT NULL) AND (s.correc_effectuee IS NULL))) THEN 1
                    ELSE 0
                END AS u_correc_effectuee,
                CASE
                    WHEN (((d.soutenance_autoris)::text <> (s.soutenance_autoris)::text) OR ((d.soutenance_autoris IS NULL) AND (s.soutenance_autoris IS NOT NULL)) OR ((d.soutenance_autoris IS NOT NULL) AND (s.soutenance_autoris IS NULL))) THEN 1
                    ELSE 0
                END AS u_soutenance_autoris,
                CASE
                    WHEN ((d.date_autoris_soutenance <> s.date_autoris_soutenance) OR ((d.date_autoris_soutenance IS NULL) AND (s.date_autoris_soutenance IS NOT NULL)) OR ((d.date_autoris_soutenance IS NOT NULL) AND (s.date_autoris_soutenance IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_autoris_soutenance,
                CASE
                    WHEN (((d.tem_avenant_cotut)::text <> (s.tem_avenant_cotut)::text) OR ((d.tem_avenant_cotut IS NULL) AND (s.tem_avenant_cotut IS NOT NULL)) OR ((d.tem_avenant_cotut IS NOT NULL) AND (s.tem_avenant_cotut IS NULL))) THEN 1
                    ELSE 0
                END AS u_tem_avenant_cotut,
                CASE
                    WHEN ((d.date_abandon <> s.date_abandon) OR ((d.date_abandon IS NULL) AND (s.date_abandon IS NOT NULL)) OR ((d.date_abandon IS NOT NULL) AND (s.date_abandon IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_abandon,
                CASE
                    WHEN ((d.date_transfert <> s.date_transfert) OR ((d.date_transfert IS NULL) AND (s.date_transfert IS NOT NULL)) OR ((d.date_transfert IS NOT NULL) AND (s.date_transfert IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_transfert,
            s.source_id AS s_source_id,
            s.etablissement_id AS s_etablissement_id,
            s.doctorant_id AS s_doctorant_id,
            s.ecole_doct_id AS s_ecole_doct_id,
            s.unite_rech_id AS s_unite_rech_id,
            s.titre AS s_titre,
            s.etat_these AS s_etat_these,
            s.resultat AS s_resultat,
            s.code_sise_disc AS s_code_sise_disc,
            s.lib_disc AS s_lib_disc,
            s.date_prem_insc AS s_date_prem_insc,
            s.date_prev_soutenance AS s_date_prev_soutenance,
            s.date_soutenance AS s_date_soutenance,
            s.date_fin_confid AS s_date_fin_confid,
            s.lib_etab_cotut AS s_lib_etab_cotut,
            s.lib_pays_cotut AS s_lib_pays_cotut,
            s.correc_autorisee AS s_correc_autorisee,
            s.correc_effectuee AS s_correc_effectuee,
            s.soutenance_autoris AS s_soutenance_autoris,
            s.date_autoris_soutenance AS s_date_autoris_soutenance,
            s.tem_avenant_cotut AS s_tem_avenant_cotut,
            s.date_abandon AS s_date_abandon,
            s.date_transfert AS s_date_transfert,
            d.source_id AS d_source_id,
            d.etablissement_id AS d_etablissement_id,
            d.doctorant_id AS d_doctorant_id,
            d.ecole_doct_id AS d_ecole_doct_id,
            d.unite_rech_id AS d_unite_rech_id,
            d.titre AS d_titre,
            d.etat_these AS d_etat_these,
            d.resultat AS d_resultat,
            d.code_sise_disc AS d_code_sise_disc,
            d.lib_disc AS d_lib_disc,
            d.date_prem_insc AS d_date_prem_insc,
            d.date_prev_soutenance AS d_date_prev_soutenance,
            d.date_soutenance AS d_date_soutenance,
            d.date_fin_confid AS d_date_fin_confid,
            d.lib_etab_cotut AS d_lib_etab_cotut,
            d.lib_pays_cotut AS d_lib_pays_cotut,
            d.correc_autorisee AS d_correc_autorisee,
            d.correc_effectuee AS d_correc_effectuee,
            d.soutenance_autoris AS d_soutenance_autoris,
            d.date_autoris_soutenance AS d_date_autoris_soutenance,
            d.tem_avenant_cotut AS d_tem_avenant_cotut,
            d.date_abandon AS d_date_abandon,
            d.date_transfert AS d_date_transfert
           FROM ((public.these d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_these s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_etablissement_id,
    diff.u_doctorant_id,
    diff.u_ecole_doct_id,
    diff.u_unite_rech_id,
    diff.u_titre,
    diff.u_etat_these,
    diff.u_resultat,
    diff.u_code_sise_disc,
    diff.u_lib_disc,
    diff.u_date_prem_insc,
    diff.u_date_prev_soutenance,
    diff.u_date_soutenance,
    diff.u_date_fin_confid,
    diff.u_lib_etab_cotut,
    diff.u_lib_pays_cotut,
    diff.u_correc_autorisee,
    diff.u_correc_effectuee,
    diff.u_soutenance_autoris,
    diff.u_date_autoris_soutenance,
    diff.u_tem_avenant_cotut,
    diff.u_date_abandon,
    diff.u_date_transfert,
    diff.s_source_id,
    diff.s_etablissement_id,
    diff.s_doctorant_id,
    diff.s_ecole_doct_id,
    diff.s_unite_rech_id,
    diff.s_titre,
    diff.s_etat_these,
    diff.s_resultat,
    diff.s_code_sise_disc,
    diff.s_lib_disc,
    diff.s_date_prem_insc,
    diff.s_date_prev_soutenance,
    diff.s_date_soutenance,
    diff.s_date_fin_confid,
    diff.s_lib_etab_cotut,
    diff.s_lib_pays_cotut,
    diff.s_correc_autorisee,
    diff.s_correc_effectuee,
    diff.s_soutenance_autoris,
    diff.s_date_autoris_soutenance,
    diff.s_tem_avenant_cotut,
    diff.s_date_abandon,
    diff.s_date_transfert,
    diff.d_source_id,
    diff.d_etablissement_id,
    diff.d_doctorant_id,
    diff.d_ecole_doct_id,
    diff.d_unite_rech_id,
    diff.d_titre,
    diff.d_etat_these,
    diff.d_resultat,
    diff.d_code_sise_disc,
    diff.d_lib_disc,
    diff.d_date_prem_insc,
    diff.d_date_prev_soutenance,
    diff.d_date_soutenance,
    diff.d_date_fin_confid,
    diff.d_lib_etab_cotut,
    diff.d_lib_pays_cotut,
    diff.d_correc_autorisee,
    diff.d_correc_effectuee,
    diff.d_soutenance_autoris,
    diff.d_date_autoris_soutenance,
    diff.d_tem_avenant_cotut,
    diff.d_date_abandon,
    diff.d_date_transfert
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < ((((((((((((((((((((((diff.u_source_id + diff.u_etablissement_id) + diff.u_doctorant_id) + diff.u_ecole_doct_id) + diff.u_unite_rech_id) + diff.u_titre) + diff.u_etat_these) + diff.u_resultat) + diff.u_code_sise_disc) + diff.u_lib_disc) + diff.u_date_prem_insc) + diff.u_date_prev_soutenance) + diff.u_date_soutenance) + diff.u_date_fin_confid) + diff.u_lib_etab_cotut) + diff.u_lib_pays_cotut) + diff.u_correc_autorisee) + diff.u_correc_effectuee) + diff.u_soutenance_autoris) + diff.u_date_autoris_soutenance) + diff.u_tem_avenant_cotut) + diff.u_date_abandon) + diff.u_date_transfert))));


ALTER TABLE public.v_diff_these OWNER TO :dbuser;

--
-- Name: v_diff_these_annee_univ; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_these_annee_univ AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.these_id <> s.these_id) OR ((d.these_id IS NULL) AND (s.these_id IS NOT NULL)) OR ((d.these_id IS NOT NULL) AND (s.these_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_these_id,
                CASE
                    WHEN ((d.annee_univ <> s.annee_univ) OR ((d.annee_univ IS NULL) AND (s.annee_univ IS NOT NULL)) OR ((d.annee_univ IS NOT NULL) AND (s.annee_univ IS NULL))) THEN 1
                    ELSE 0
                END AS u_annee_univ,
            s.source_id AS s_source_id,
            s.these_id AS s_these_id,
            s.annee_univ AS s_annee_univ,
            d.source_id AS d_source_id,
            d.these_id AS d_these_id,
            d.annee_univ AS d_annee_univ
           FROM ((public.these_annee_univ d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_these_annee_univ s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_these_id,
    diff.u_annee_univ,
    diff.s_source_id,
    diff.s_these_id,
    diff.s_annee_univ,
    diff.d_source_id,
    diff.d_these_id,
    diff.d_annee_univ
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < ((diff.u_source_id + diff.u_these_id) + diff.u_annee_univ))));


ALTER TABLE public.v_diff_these_annee_univ OWNER TO :dbuser;

--
-- Name: v_diff_titre_acces; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_titre_acces AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.these_id <> s.these_id) OR ((d.these_id IS NULL) AND (s.these_id IS NOT NULL)) OR ((d.these_id IS NOT NULL) AND (s.these_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_these_id,
                CASE
                    WHEN (((d.titre_acces_interne_externe)::text <> (s.titre_acces_interne_externe)::text) OR ((d.titre_acces_interne_externe IS NULL) AND (s.titre_acces_interne_externe IS NOT NULL)) OR ((d.titre_acces_interne_externe IS NOT NULL) AND (s.titre_acces_interne_externe IS NULL))) THEN 1
                    ELSE 0
                END AS u_titre_acces_interne_externe,
                CASE
                    WHEN (((d.libelle_titre_acces)::text <> (s.libelle_titre_acces)::text) OR ((d.libelle_titre_acces IS NULL) AND (s.libelle_titre_acces IS NOT NULL)) OR ((d.libelle_titre_acces IS NOT NULL) AND (s.libelle_titre_acces IS NULL))) THEN 1
                    ELSE 0
                END AS u_libelle_titre_acces,
                CASE
                    WHEN (((d.type_etb_titre_acces)::text <> (s.type_etb_titre_acces)::text) OR ((d.type_etb_titre_acces IS NULL) AND (s.type_etb_titre_acces IS NOT NULL)) OR ((d.type_etb_titre_acces IS NOT NULL) AND (s.type_etb_titre_acces IS NULL))) THEN 1
                    ELSE 0
                END AS u_type_etb_titre_acces,
                CASE
                    WHEN (((d.libelle_etb_titre_acces)::text <> (s.libelle_etb_titre_acces)::text) OR ((d.libelle_etb_titre_acces IS NULL) AND (s.libelle_etb_titre_acces IS NOT NULL)) OR ((d.libelle_etb_titre_acces IS NOT NULL) AND (s.libelle_etb_titre_acces IS NULL))) THEN 1
                    ELSE 0
                END AS u_libelle_etb_titre_acces,
                CASE
                    WHEN (((d.code_dept_titre_acces)::text <> (s.code_dept_titre_acces)::text) OR ((d.code_dept_titre_acces IS NULL) AND (s.code_dept_titre_acces IS NOT NULL)) OR ((d.code_dept_titre_acces IS NOT NULL) AND (s.code_dept_titre_acces IS NULL))) THEN 1
                    ELSE 0
                END AS u_code_dept_titre_acces,
                CASE
                    WHEN (((d.code_pays_titre_acces)::text <> (s.code_pays_titre_acces)::text) OR ((d.code_pays_titre_acces IS NULL) AND (s.code_pays_titre_acces IS NOT NULL)) OR ((d.code_pays_titre_acces IS NOT NULL) AND (s.code_pays_titre_acces IS NULL))) THEN 1
                    ELSE 0
                END AS u_code_pays_titre_acces,
            s.source_id AS s_source_id,
            s.these_id AS s_these_id,
            s.titre_acces_interne_externe AS s_titre_acces_interne_externe,
            s.libelle_titre_acces AS s_libelle_titre_acces,
            s.type_etb_titre_acces AS s_type_etb_titre_acces,
            s.libelle_etb_titre_acces AS s_libelle_etb_titre_acces,
            s.code_dept_titre_acces AS s_code_dept_titre_acces,
            s.code_pays_titre_acces AS s_code_pays_titre_acces,
            d.source_id AS d_source_id,
            d.these_id AS d_these_id,
            d.titre_acces_interne_externe AS d_titre_acces_interne_externe,
            d.libelle_titre_acces AS d_libelle_titre_acces,
            d.type_etb_titre_acces AS d_type_etb_titre_acces,
            d.libelle_etb_titre_acces AS d_libelle_etb_titre_acces,
            d.code_dept_titre_acces AS d_code_dept_titre_acces,
            d.code_pays_titre_acces AS d_code_pays_titre_acces
           FROM ((public.titre_acces d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_titre_acces s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_these_id,
    diff.u_titre_acces_interne_externe,
    diff.u_libelle_titre_acces,
    diff.u_type_etb_titre_acces,
    diff.u_libelle_etb_titre_acces,
    diff.u_code_dept_titre_acces,
    diff.u_code_pays_titre_acces,
    diff.s_source_id,
    diff.s_these_id,
    diff.s_titre_acces_interne_externe,
    diff.s_libelle_titre_acces,
    diff.s_type_etb_titre_acces,
    diff.s_libelle_etb_titre_acces,
    diff.s_code_dept_titre_acces,
    diff.s_code_pays_titre_acces,
    diff.d_source_id,
    diff.d_these_id,
    diff.d_titre_acces_interne_externe,
    diff.d_libelle_titre_acces,
    diff.d_type_etb_titre_acces,
    diff.d_libelle_etb_titre_acces,
    diff.d_code_dept_titre_acces,
    diff.d_code_pays_titre_acces
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < (((((((diff.u_source_id + diff.u_these_id) + diff.u_titre_acces_interne_externe) + diff.u_libelle_titre_acces) + diff.u_type_etb_titre_acces) + diff.u_libelle_etb_titre_acces) + diff.u_code_dept_titre_acces) + diff.u_code_pays_titre_acces))));


ALTER TABLE public.v_diff_titre_acces OWNER TO :dbuser;

--
-- Name: v_diff_unite_rech; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_unite_rech AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.structure_id <> s.structure_id) OR ((d.structure_id IS NULL) AND (s.structure_id IS NOT NULL)) OR ((d.structure_id IS NOT NULL) AND (s.structure_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_structure_id,
            s.source_id AS s_source_id,
            s.structure_id AS s_structure_id,
            d.source_id AS d_source_id,
            d.structure_id AS d_structure_id
           FROM ((public.unite_rech d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_unite_rech s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_structure_id,
    diff.s_source_id,
    diff.s_structure_id,
    diff.d_source_id,
    diff.d_structure_id
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < (diff.u_source_id + diff.u_structure_id))));


ALTER TABLE public.v_diff_unite_rech OWNER TO :dbuser;

--
-- Name: variable; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.variable (
    id bigint NOT NULL,
    etablissement_id bigint NOT NULL,
    description character varying(300) NOT NULL,
    valeur character varying(200) NOT NULL,
    source_code character varying(64) NOT NULL,
    source_id bigint NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_destructeur_id bigint,
    histo_destruction timestamp without time zone,
    histo_modificateur_id bigint,
    histo_modification timestamp without time zone,
    date_deb_validite timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    date_fin_validite timestamp without time zone DEFAULT to_date('9999-12-31'::text, 'YYYY-MM-DD'::text) NOT NULL,
    code character varying(64)
);


ALTER TABLE public.variable OWNER TO :dbuser;

--
-- Name: TABLE variable; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.variable IS 'Variables d''environnement concernant un établissement, ex: nom de l''établissement, nom du président, etc.';


--
-- Name: v_diff_variable; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_diff_variable AS
 WITH diff AS (
         SELECT COALESCE(s.source_code, d.source_code) AS source_code,
            COALESCE(s.source_id, d.source_id) AS source_id,
                CASE
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NULL)) THEN 'insert'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'update'::text
                    WHEN ((s.source_code IS NOT NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NOT NULL) AND (d.histo_destruction <= ('now'::text)::timestamp(0) without time zone))) THEN 'undelete'::text
                    WHEN ((s.source_code IS NULL) AND (d.source_code IS NOT NULL) AND ((d.histo_destruction IS NULL) OR (d.histo_destruction > ('now'::text)::timestamp(0) without time zone))) THEN 'delete'::text
                    ELSE NULL::text
                END AS operation,
                CASE
                    WHEN ((d.source_id <> s.source_id) OR ((d.source_id IS NULL) AND (s.source_id IS NOT NULL)) OR ((d.source_id IS NOT NULL) AND (s.source_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_source_id,
                CASE
                    WHEN ((d.etablissement_id <> s.etablissement_id) OR ((d.etablissement_id IS NULL) AND (s.etablissement_id IS NOT NULL)) OR ((d.etablissement_id IS NOT NULL) AND (s.etablissement_id IS NULL))) THEN 1
                    ELSE 0
                END AS u_etablissement_id,
                CASE
                    WHEN (((d.code)::text <> (s.code)::text) OR ((d.code IS NULL) AND (s.code IS NOT NULL)) OR ((d.code IS NOT NULL) AND (s.code IS NULL))) THEN 1
                    ELSE 0
                END AS u_code,
                CASE
                    WHEN (((d.description)::text <> (s.description)::text) OR ((d.description IS NULL) AND (s.description IS NOT NULL)) OR ((d.description IS NOT NULL) AND (s.description IS NULL))) THEN 1
                    ELSE 0
                END AS u_description,
                CASE
                    WHEN (((d.valeur)::text <> (s.valeur)::text) OR ((d.valeur IS NULL) AND (s.valeur IS NOT NULL)) OR ((d.valeur IS NOT NULL) AND (s.valeur IS NULL))) THEN 1
                    ELSE 0
                END AS u_valeur,
                CASE
                    WHEN ((d.date_deb_validite <> s.date_deb_validite) OR ((d.date_deb_validite IS NULL) AND (s.date_deb_validite IS NOT NULL)) OR ((d.date_deb_validite IS NOT NULL) AND (s.date_deb_validite IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_deb_validite,
                CASE
                    WHEN ((d.date_fin_validite <> s.date_fin_validite) OR ((d.date_fin_validite IS NULL) AND (s.date_fin_validite IS NOT NULL)) OR ((d.date_fin_validite IS NOT NULL) AND (s.date_fin_validite IS NULL))) THEN 1
                    ELSE 0
                END AS u_date_fin_validite,
            s.source_id AS s_source_id,
            s.etablissement_id AS s_etablissement_id,
            s.code AS s_code,
            s.description AS s_description,
            s.valeur AS s_valeur,
            s.date_deb_validite AS s_date_deb_validite,
            s.date_fin_validite AS s_date_fin_validite,
            d.source_id AS d_source_id,
            d.etablissement_id AS d_etablissement_id,
            d.code AS d_code,
            d.description AS d_description,
            d.valeur AS d_valeur,
            d.date_deb_validite AS d_date_deb_validite,
            d.date_fin_validite AS d_date_fin_validite
           FROM ((public.variable d
             JOIN public.source src ON (((src.id = d.source_id) AND (src.importable = true))))
             FULL JOIN public.src_variable s ON (((s.source_id = d.source_id) AND ((s.source_code)::text = (d.source_code)::text))))
        )
 SELECT diff.source_code,
    diff.source_id,
    diff.operation,
    diff.u_source_id,
    diff.u_etablissement_id,
    diff.u_code,
    diff.u_description,
    diff.u_valeur,
    diff.u_date_deb_validite,
    diff.u_date_fin_validite,
    diff.s_source_id,
    diff.s_etablissement_id,
    diff.s_code,
    diff.s_description,
    diff.s_valeur,
    diff.s_date_deb_validite,
    diff.s_date_fin_validite,
    diff.d_source_id,
    diff.d_etablissement_id,
    diff.d_code,
    diff.d_description,
    diff.d_valeur,
    diff.d_date_deb_validite,
    diff.d_date_fin_validite
   FROM diff
  WHERE ((diff.operation IS NOT NULL) AND ((diff.operation = 'undelete'::text) OR (0 < ((((((diff.u_source_id + diff.u_etablissement_id) + diff.u_code) + diff.u_description) + diff.u_valeur) + diff.u_date_deb_validite) + diff.u_date_fin_validite))));


ALTER TABLE public.v_diff_variable OWNER TO :dbuser;

--
-- Name: v_individu_insa_double; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_individu_insa_double AS
 WITH comptes(supann_id, nb) AS (
         SELECT individu.supann_id,
            count(*) AS count
           FROM public.individu
          WHERE ((individu.source_id = 2) AND ((individu.type)::text = 'doctorant'::text))
          GROUP BY individu.supann_id
         HAVING (count(*) > 1)
        )
 SELECT comptes.nb,
    i.id,
    i.type,
    i.civilite,
    i.nom_usuel,
    i.nom_patronymique,
    i.prenom1,
    i.prenom2,
    i.prenom3,
    i.email,
    i.date_naissance,
    i.nationalite,
    i.source_code,
    i.source_id,
    i.histo_createur_id,
    i.histo_creation,
    i.histo_modificateur_id,
    i.histo_modification,
    i.histo_destructeur_id,
    i.histo_destruction,
    i.supann_id,
    i.etablissement_id,
    i.pays_id_nationalite,
    i.id_ref
   FROM comptes,
    public.individu i
  WHERE (((i.supann_id)::text = (comptes.supann_id)::text) AND (i.source_id = 2));


ALTER TABLE public.v_individu_insa_double OWNER TO :dbuser;

--
-- Name: validite_fichier; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.validite_fichier (
    id bigint NOT NULL,
    fichier_id bigint NOT NULL,
    est_valide boolean,
    message text,
    log text,
    histo_createur_id bigint NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint,
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL
);


ALTER TABLE public.validite_fichier OWNER TO :dbuser;

--
-- Name: v_situ_archivab_va; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_archivab_va AS
 SELECT ft.these_id,
    ft.retraitement,
    vf.est_valide
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VA'::text))))
     JOIN public.validite_fichier vf ON ((vf.fichier_id = f.id)))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_archivab_va OWNER TO :dbuser;

--
-- Name: v_situ_archivab_vac; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_archivab_vac AS
 SELECT ft.these_id,
    ft.retraitement,
    vf.est_valide
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VAC'::text))))
     JOIN public.validite_fichier vf ON ((vf.fichier_id = f.id)))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_archivab_vac OWNER TO :dbuser;

--
-- Name: v_situ_archivab_vo; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_archivab_vo AS
 SELECT ft.these_id,
    vf.est_valide
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VO'::text))))
     JOIN public.validite_fichier vf ON ((vf.fichier_id = f.id)))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_archivab_vo OWNER TO :dbuser;

--
-- Name: v_situ_archivab_voc; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_archivab_voc AS
 SELECT ft.these_id,
    vf.est_valide
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VOC'::text))))
     JOIN public.validite_fichier vf ON ((vf.fichier_id = f.id)))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_archivab_voc OWNER TO :dbuser;

--
-- Name: v_situ_attestations; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_attestations AS
 SELECT a.these_id,
    a.id AS attestation_id
   FROM public.attestation a
  WHERE ((a.version_corrigee = false) AND (a.histo_destructeur_id IS NULL));


ALTER TABLE public.v_situ_attestations OWNER TO :dbuser;

--
-- Name: v_situ_attestations_voc; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_attestations_voc AS
 SELECT a.these_id,
    a.id AS attestation_id
   FROM (((public.attestation a
     JOIN public.fichier_these ft ON (((ft.these_id = a.these_id) AND (ft.est_annexe = false) AND (ft.est_expurge = false))))
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VOC'::text))))
  WHERE ((a.version_corrigee = true) AND (a.histo_destructeur_id IS NULL));


ALTER TABLE public.v_situ_attestations_voc OWNER TO :dbuser;

--
-- Name: v_situ_autoris_diff_these; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_autoris_diff_these AS
 SELECT d.these_id,
    d.id AS diffusion_id
   FROM public.diffusion d
  WHERE ((d.version_corrigee = false) AND (d.histo_destructeur_id IS NULL));


ALTER TABLE public.v_situ_autoris_diff_these OWNER TO :dbuser;

--
-- Name: v_situ_autoris_diff_these_voc; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_autoris_diff_these_voc AS
 SELECT d.these_id,
    d.id AS diffusion_id
   FROM (((public.diffusion d
     JOIN public.fichier_these ft ON (((ft.these_id = d.these_id) AND (ft.est_annexe = false) AND (ft.est_expurge = false))))
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VOC'::text))))
  WHERE ((d.version_corrigee = true) AND (d.histo_destructeur_id IS NULL));


ALTER TABLE public.v_situ_autoris_diff_these_voc OWNER TO :dbuser;

--
-- Name: v_situ_depot_pv_sout; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_pv_sout AS
 SELECT ft.these_id,
    f.id AS fichier_id
   FROM ((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.nature_fichier nf ON (((f.nature_id = nf.id) AND ((nf.code)::text = 'PV_SOUTENANCE'::text))));


ALTER TABLE public.v_situ_depot_pv_sout OWNER TO :dbuser;

--
-- Name: v_situ_depot_rapport_sout; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_rapport_sout AS
 SELECT ft.these_id,
    f.id AS fichier_id
   FROM ((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.nature_fichier nf ON (((f.nature_id = nf.id) AND ((nf.code)::text = 'RAPPORT_SOUTENANCE'::text))));


ALTER TABLE public.v_situ_depot_rapport_sout OWNER TO :dbuser;

--
-- Name: v_situ_depot_va; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_va AS
 SELECT ft.these_id,
    f.id AS fichier_id
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.nature_fichier nf ON (((f.nature_id = nf.id) AND ((nf.code)::text = 'THESE_PDF'::text))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VA'::text))))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_depot_va OWNER TO :dbuser;

--
-- Name: v_situ_depot_vac; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_vac AS
 SELECT ft.these_id,
    f.id AS fichier_id
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.nature_fichier nf ON (((f.nature_id = nf.id) AND ((nf.code)::text = 'THESE_PDF'::text))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VAC'::text))))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_depot_vac OWNER TO :dbuser;

--
-- Name: v_situ_depot_vc_valid_doct; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_vc_valid_doct AS
 SELECT v.these_id,
        CASE
            WHEN (v.id IS NOT NULL) THEN 1
            ELSE 0
        END AS valide
   FROM (public.validation v
     JOIN public.type_validation tv ON (((v.type_validation_id = tv.id) AND ((tv.code)::text = 'DEPOT_THESE_CORRIGEE'::text))))
  WHERE (v.histo_destructeur_id IS NULL);


ALTER TABLE public.v_situ_depot_vc_valid_doct OWNER TO :dbuser;

--
-- Name: v_situ_depot_vc_valid_pres; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_vc_valid_pres AS
 WITH validations_attendues AS (
         SELECT a.these_id,
            a.individu_id,
            tv.id AS type_validation_id
           FROM ((public.acteur a
             JOIN public.role r ON (((a.role_id = r.id) AND ((r.code)::text = 'P'::text))))
             JOIN public.type_validation tv ON (((tv.code)::text = 'CORRECTION_THESE'::text)))
          WHERE (a.histo_destruction IS NULL)
        ), validations_dt_existantes AS (
         SELECT DISTINCT v_1.these_id,
            tv.id AS type_validation_id
           FROM (((public.validation v_1
             JOIN public.type_validation tv ON (((v_1.type_validation_id = tv.id) AND ((tv.code)::text = 'CORRECTION_THESE'::text))))
             JOIN public.acteur a ON (((v_1.these_id = a.these_id) AND (v_1.individu_id = a.individu_id) AND (a.histo_destructeur_id IS NULL))))
             JOIN public.role r ON (((a.role_id = r.id) AND ((r.code)::text = 'D'::text))))
          WHERE (v_1.histo_destructeur_id IS NULL)
        )
 SELECT ((va.these_id || '_'::text) || va.individu_id) AS id,
    va.these_id,
    va.individu_id,
        CASE
            WHEN ((v.id IS NOT NULL) OR (vdte.these_id IS NOT NULL)) THEN 1
            ELSE 0
        END AS valide
   FROM ((validations_attendues va
     LEFT JOIN public.validation v ON (((v.these_id = va.these_id) AND (v.individu_id = va.individu_id) AND (v.histo_destructeur_id IS NULL) AND (v.type_validation_id = va.type_validation_id))))
     LEFT JOIN validations_dt_existantes vdte ON (((vdte.these_id = va.these_id) AND (vdte.type_validation_id = va.type_validation_id))));


ALTER TABLE public.v_situ_depot_vc_valid_pres OWNER TO :dbuser;

--
-- Name: v_situ_depot_vc_valid_pres_new; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_vc_valid_pres_new AS
 WITH validations_attendues AS (
         SELECT a.these_id,
            a.individu_id,
            tv.id AS type_validation_id
           FROM ((public.acteur a
             JOIN public.role r ON (((a.role_id = r.id) AND ((r.code)::text = 'P'::text))))
             JOIN public.type_validation tv ON (((tv.code)::text = 'CORRECTION_THESE'::text)))
          WHERE ((a.histo_destruction IS NULL) AND (NOT (EXISTS ( SELECT v_1.id,
                    v_1.type_validation_id,
                    v_1.these_id,
                    v_1.individu_id,
                    v_1.histo_creation,
                    v_1.histo_createur_id,
                    v_1.histo_modification,
                    v_1.histo_modificateur_id,
                    v_1.histo_destruction,
                    v_1.histo_destructeur_id,
                    type_validation.id,
                    type_validation.code,
                    type_validation.libelle
                   FROM (public.validation v_1
                     JOIN public.type_validation ON (((v_1.type_validation_id = type_validation.id) AND ((type_validation.code)::text = 'CORRECTION_THESE'::text))))
                  WHERE ((v_1.these_id = a.these_id) AND (v_1.type_validation_id = tv.id) AND (v_1.histo_destruction IS NULL))))))
        )
 SELECT ((va.these_id || '_'::text) || va.individu_id) AS id,
    va.these_id,
    va.individu_id,
        CASE
            WHEN (v.id IS NOT NULL) THEN 1
            ELSE 0
        END AS valide
   FROM (validations_attendues va
     LEFT JOIN public.validation v ON (((v.these_id = va.these_id) AND (v.individu_id = va.individu_id) AND (v.histo_destructeur_id IS NULL) AND (v.type_validation_id = va.type_validation_id))));


ALTER TABLE public.v_situ_depot_vc_valid_pres_new OWNER TO :dbuser;

--
-- Name: v_situ_depot_vo; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_vo AS
 SELECT ft.these_id,
    f.id AS fichier_id
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.nature_fichier nf ON (((f.nature_id = nf.id) AND ((nf.code)::text = 'THESE_PDF'::text))))
     JOIN public.version_fichier vf ON (((f.version_fichier_id = vf.id) AND ((vf.code)::text = 'VO'::text))))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false) AND (ft.retraitement IS NULL));


ALTER TABLE public.v_situ_depot_vo OWNER TO :dbuser;

--
-- Name: v_situ_depot_voc; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_depot_voc AS
 SELECT ft.these_id,
    f.id AS fichier_id
   FROM (((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.nature_fichier nf ON (((f.nature_id = nf.id) AND ((nf.code)::text = 'THESE_PDF'::text))))
     JOIN public.version_fichier vf ON (((f.version_fichier_id = vf.id) AND ((vf.code)::text = 'VOC'::text))))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false) AND (ft.retraitement IS NULL));


ALTER TABLE public.v_situ_depot_voc OWNER TO :dbuser;

--
-- Name: v_situ_rdv_bu_saisie_doct; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_rdv_bu_saisie_doct AS
 SELECT r.these_id,
        CASE
            WHEN ((r.coord_doctorant IS NOT NULL) AND (r.dispo_doctorant IS NOT NULL)) THEN 1
            ELSE 0
        END AS ok
   FROM public.rdv_bu r;


ALTER TABLE public.v_situ_rdv_bu_saisie_doct OWNER TO :dbuser;

--
-- Name: v_situ_rdv_bu_validation_bu; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_rdv_bu_validation_bu AS
 SELECT v.these_id,
        CASE
            WHEN (v.id IS NOT NULL) THEN 1
            ELSE 0
        END AS valide
   FROM (public.validation v
     JOIN public.type_validation tv ON (((v.type_validation_id = tv.id) AND ((tv.code)::text = 'RDV_BU'::text))))
  WHERE (v.histo_destructeur_id IS NULL);


ALTER TABLE public.v_situ_rdv_bu_validation_bu OWNER TO :dbuser;

--
-- Name: v_situ_signalement_these; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_signalement_these AS
 SELECT d.these_id,
    d.id AS description_id
   FROM public.metadonnee_these d;


ALTER TABLE public.v_situ_signalement_these OWNER TO :dbuser;

--
-- Name: v_situ_validation_page_couv; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_validation_page_couv AS
 SELECT v.these_id,
        CASE
            WHEN (v.id IS NOT NULL) THEN 1
            ELSE 0
        END AS valide
   FROM (public.validation v
     JOIN public.type_validation tv ON (((v.type_validation_id = tv.id) AND ((tv.code)::text = 'PAGE_DE_COUVERTURE'::text))))
  WHERE (v.histo_destructeur_id IS NULL);


ALTER TABLE public.v_situ_validation_page_couv OWNER TO :dbuser;

--
-- Name: v_situ_verif_va; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_verif_va AS
 SELECT ft.these_id,
    ft.est_conforme
   FROM ((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VA'::text))))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_verif_va OWNER TO :dbuser;

--
-- Name: v_situ_verif_vac; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_verif_vac AS
 SELECT ft.these_id,
    ft.est_conforme
   FROM ((public.fichier_these ft
     JOIN public.fichier f ON (((ft.fichier_id = f.id) AND (f.histo_destruction IS NULL))))
     JOIN public.version_fichier v ON (((f.version_fichier_id = v.id) AND ((v.code)::text = 'VAC'::text))))
  WHERE ((ft.est_annexe = false) AND (ft.est_expurge = false));


ALTER TABLE public.v_situ_verif_vac OWNER TO :dbuser;

--
-- Name: v_situ_version_papier_corrigee; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_situ_version_papier_corrigee AS
 SELECT v.these_id,
    v.id AS validation_id
   FROM (public.validation v
     JOIN public.type_validation tv ON ((tv.id = v.type_validation_id)))
  WHERE ((tv.code)::text = 'VERSION_PAPIER_CORRIGEE'::text);


ALTER TABLE public.v_situ_version_papier_corrigee OWNER TO :dbuser;

--
-- Name: v_these_annee_univ_first; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_these_annee_univ_first AS
 WITH firsts(source_code) AS (
         SELECT DISTINCT first_value(these_annee_univ.source_code) OVER (PARTITION BY these_annee_univ.these_id ORDER BY these_annee_univ.annee_univ) AS first_value
           FROM public.these_annee_univ
          WHERE (these_annee_univ.histo_destruction IS NULL)
        )
 SELECT au.id,
    au.source_code,
    au.source_id,
    au.these_id,
    au.annee_univ,
    au.histo_createur_id,
    au.histo_creation,
    au.histo_modificateur_id,
    au.histo_modification,
    au.histo_destructeur_id,
    au.histo_destruction
   FROM (public.these_annee_univ au
     JOIN firsts fi ON (((au.source_code)::text = (fi.source_code)::text)));


ALTER TABLE public.v_these_annee_univ_first OWNER TO :dbuser;

--
-- Name: wf_etape; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.wf_etape (
    id bigint NOT NULL,
    code character varying(128) NOT NULL,
    ordre bigint DEFAULT 1 NOT NULL,
    chemin bigint DEFAULT 1 NOT NULL,
    obligatoire boolean DEFAULT true NOT NULL,
    route character varying(200) NOT NULL,
    libelle_acteur character varying(150) NOT NULL,
    libelle_autres character varying(150) NOT NULL,
    desc_non_franchie character varying(250) NOT NULL,
    desc_sans_objectif character varying(250)
);


ALTER TABLE public.wf_etape OWNER TO :dbuser;

--
-- Name: v_wf_etape_pertin; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_wf_etape_pertin AS
 SELECT (alias38.these_id)::numeric AS these_id,
    (alias38.etape_id)::numeric AS etape_id,
    alias38.code,
    alias38.ordre,
    row_number() OVER (ORDER BY 1::integer, 2::integer, 3::integer, 4::integer) AS id
   FROM ( SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'VALIDATION_PAGE_DE_COUVERTURE'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ORIGINALE'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'AUTORISATION_DIFFUSION_THESE'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'ATTESTATIONS'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'SIGNALEMENT_THESE'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ORIGINALE'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM ((public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ARCHIVAGE'::text)))
             JOIN public.v_situ_archivab_vo situ ON (((situ.these_id = t.id) AND (situ.est_valide = false))))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM ((public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ARCHIVAGE'::text)))
             JOIN public.v_situ_archivab_vo situ ON (((situ.these_id = t.id) AND (situ.est_valide = false))))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM ((public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'VERIFICATION_VERSION_ARCHIVAGE'::text)))
             JOIN public.v_situ_archivab_va situ ON (((situ.these_id = t.id) AND (situ.est_valide = true))))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'RDV_BU_SAISIE_DOCTORANT'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'RDV_BU_SAISIE_BU'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'RDV_BU_VALIDATION_BU'::text)))
          WHERE ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text]))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'::text)))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'::text)))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'ATTESTATIONS_VERSION_CORRIGEE'::text)))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'::text)))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM ((public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'::text)))
             JOIN public.v_situ_archivab_voc situ ON (((situ.these_id = t.id) AND (situ.est_valide = false))))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM ((public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'::text)))
             JOIN public.v_situ_archivab_voc situ ON (((situ.these_id = t.id) AND (situ.est_valide = false))))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM ((public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'::text)))
             JOIN public.v_situ_archivab_vac situ ON (((situ.these_id = t.id) AND (situ.est_valide = true))))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'::text)))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'::text)))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])))
        UNION ALL
         SELECT t.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre
           FROM (public.these t
             JOIN public.wf_etape e ON (((e.code)::text = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'::text)))
          WHERE (((t.correc_autorisee IS NOT NULL) OR ((t.correc_autorisee_forcee IS NOT NULL) AND ((t.correc_autorisee_forcee)::text <> 'aucune'::text)) OR ((t.correc_effectuee)::text = 'O'::text)) AND ((t.etat_these)::text = ANY (ARRAY[('E'::character varying)::text, ('S'::character varying)::text])) AND (EXISTS ( SELECT d.id
                   FROM public.diffusion d
                  WHERE ((d.these_id = t.id) AND (d.version_corrigee = true) AND (d.autoris_mel = ANY (ARRAY[0, 1]))))))) alias38;


ALTER TABLE public.v_wf_etape_pertin OWNER TO :dbuser;

--
-- Name: v_workflow; Type: VIEW; Schema: public; Owner: :dbuser
--

CREATE VIEW public.v_workflow AS
 SELECT t.these_id,
    t.etape_id,
    t.code,
    t.ordre,
    t.franchie,
    t.resultat,
    t.objectif,
    dense_rank() OVER (PARTITION BY t.these_id, t.franchie ORDER BY t.ordre) AS dense_rank,
        CASE
            WHEN ((t.franchie = 1) OR (dense_rank() OVER (PARTITION BY t.these_id, t.franchie ORDER BY t.ordre) = 1)) THEN true
            ELSE false
        END AS atteignable,
        CASE
            WHEN ((dense_rank() OVER (PARTITION BY t.these_id, t.franchie ORDER BY t.ordre) = 1) AND (t.franchie = 0)) THEN true
            ELSE false
        END AS courante,
    row_number() OVER (ORDER BY 1::integer, 2::integer) AS id
   FROM (( SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.valide IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.valide IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'VALIDATION_PAGE_DE_COUVERTURE'::text)))
             LEFT JOIN public.v_situ_validation_page_couv v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ORIGINALE'::text)))
             LEFT JOIN public.v_situ_depot_vo v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.diffusion_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.diffusion_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'AUTORISATION_DIFFUSION_THESE'::text)))
             LEFT JOIN public.v_situ_autoris_diff_these v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.attestation_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.attestation_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'ATTESTATIONS'::text)))
             LEFT JOIN public.v_situ_attestations v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.description_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.description_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'SIGNALEMENT_THESE'::text)))
             LEFT JOIN public.v_situ_signalement_these v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.these_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN ((v_1.est_valide IS NULL) OR (v_1.est_valide = false)) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ORIGINALE'::text)))
             LEFT JOIN public.v_situ_archivab_vo v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM (((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ARCHIVAGE'::text)))
             LEFT JOIN public.v_situ_depot_va v_1 ON ((v_1.these_id = t_1.id)))
             LEFT JOIN public.fichier f ON ((f.id = v_1.fichier_id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.est_valide IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN ((v_1.est_valide IS NULL) OR (v_1.est_valide = false)) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ARCHIVAGE'::text)))
             LEFT JOIN public.v_situ_archivab_va v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.est_conforme IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN ((v_1.est_conforme IS NULL) OR (v_1.est_conforme = false)) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'VERIFICATION_VERSION_ARCHIVAGE'::text)))
             LEFT JOIN public.v_situ_verif_va v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
            COALESCE(v_1.ok, 0) AS franchie,
            (
                CASE
                    WHEN (rdv.coord_doctorant IS NULL) THEN 0
                    ELSE 1
                END +
                CASE
                    WHEN (rdv.dispo_doctorant IS NULL) THEN 0
                    ELSE 1
                END) AS resultat,
            2 AS objectif
           FROM (((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'RDV_BU_SAISIE_DOCTORANT'::text)))
             LEFT JOIN public.v_situ_rdv_bu_saisie_doct v_1 ON ((v_1.these_id = t_1.id)))
             LEFT JOIN public.rdv_bu rdv ON ((rdv.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
            COALESCE(v_1.valide, 0) AS franchie,
            COALESCE(v_1.valide, 0) AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'RDV_BU_VALIDATION_BU'::text)))
             LEFT JOIN public.v_situ_rdv_bu_validation_bu v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'::text)))
             LEFT JOIN public.v_situ_depot_voc v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.diffusion_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.diffusion_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'::text)))
             LEFT JOIN public.v_situ_autoris_diff_these_voc v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.attestation_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.attestation_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'ATTESTATIONS_VERSION_CORRIGEE'::text)))
             LEFT JOIN public.v_situ_attestations_voc v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.these_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN ((v_1.est_valide IS NULL) OR (v_1.est_valide = false)) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'::text)))
             LEFT JOIN public.v_situ_archivab_voc v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS franchie,
                CASE
                    WHEN (v_1.fichier_id IS NULL) THEN 0
                    ELSE 1
                END AS resultat,
            1 AS objectif
           FROM (((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'::text)))
             LEFT JOIN public.v_situ_depot_vac v_1 ON ((v_1.these_id = t_1.id)))
             LEFT JOIN public.fichier f ON ((f.id = v_1.fichier_id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.est_valide = true) THEN 1
                    ELSE 0
                END AS franchie,
                CASE
                    WHEN (v_1.est_valide = true) THEN 1
                    ELSE 0
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'::text)))
             LEFT JOIN public.v_situ_archivab_vac v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
                CASE
                    WHEN (v_1.est_conforme = true) THEN 1
                    ELSE 0
                END AS franchie,
                CASE
                    WHEN (v_1.est_conforme = true) THEN 1
                    ELSE 0
                END AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'::text)))
             LEFT JOIN public.v_situ_verif_vac v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT t_1.id AS these_id,
            e.id AS etape_id,
            e.code,
            e.ordre,
            COALESCE(v_1.valide, 0) AS franchie,
            COALESCE(v_1.valide, 0) AS resultat,
            1 AS objectif
           FROM ((public.these t_1
             JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'::text)))
             LEFT JOIN public.v_situ_depot_vc_valid_doct v_1 ON ((v_1.these_id = t_1.id)))
        UNION ALL
         SELECT alias22.these_id,
            alias22.etape_id,
            alias22.code,
            alias22.ordre,
            alias22.franchie,
            alias22.resultat,
            alias22.objectif
           FROM ( WITH tmp AS (
                         SELECT v_situ_depot_vc_valid_pres.these_id,
                            sum(v_situ_depot_vc_valid_pres.valide) AS resultat,
                            count(v_situ_depot_vc_valid_pres.valide) AS objectif
                           FROM public.v_situ_depot_vc_valid_pres
                          GROUP BY v_situ_depot_vc_valid_pres.these_id
                        )
                 SELECT t_1.id AS these_id,
                    e.id AS etape_id,
                    e.code,
                    e.ordre,
                        CASE
                            WHEN (COALESCE(v_1.resultat, (0)::bigint) = v_1.objectif) THEN 1
                            ELSE 0
                        END AS franchie,
                    COALESCE(v_1.resultat, (0)::bigint) AS resultat,
                    v_1.objectif
                   FROM ((public.these t_1
                     JOIN public.wf_etape e ON (((e.code)::text = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'::text)))
                     LEFT JOIN tmp v_1 ON ((v_1.these_id = t_1.id)))) alias22
        UNION ALL
         SELECT alias26.these_id,
            alias26.etape_id,
            alias26.code,
            alias26.ordre,
            alias26.franchie,
            alias26."?column?",
            alias26."?column?_1" AS "?column?"
           FROM ( WITH tmp_last AS (
                         SELECT v_situ_version_papier_corrigee.these_id,
                            count(v_situ_version_papier_corrigee.these_id) AS resultat
                           FROM public.v_situ_version_papier_corrigee
                          GROUP BY v_situ_version_papier_corrigee.these_id
                        )
                 SELECT t_1.id AS these_id,
                    e.id AS etape_id,
                    e.code,
                    e.ordre,
                    COALESCE(tl.resultat, (0)::bigint) AS franchie,
                    0,
                    1
                   FROM ((public.these t_1
                     JOIN public.wf_etape e ON (((e.code)::text = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'::text)))
                     LEFT JOIN tmp_last tl ON ((tl.these_id = t_1.id)))) alias26(these_id, etape_id, code, ordre, franchie, "?column?", "?column?_1")) t
     JOIN public.v_wf_etape_pertin v ON ((((t.these_id)::numeric = v.these_id) AND ((t.etape_id)::numeric = v.etape_id))));


ALTER TABLE public.v_workflow OWNER TO :dbuser;

--
-- Name: validation_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.validation_id_seq
    START WITH 34601
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.validation_id_seq OWNER TO :dbuser;

--
-- Name: validite_fichier_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.validite_fichier_id_seq
    START WITH 19501
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.validite_fichier_id_seq OWNER TO :dbuser;

--
-- Name: variable_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.variable_id_seq
    START WITH 128
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.variable_id_seq OWNER TO :dbuser;

--
-- Name: wf_etape_id_seq; Type: SEQUENCE; Schema: public; Owner: :dbuser
--

CREATE SEQUENCE public.wf_etape_id_seq
    START WITH 81
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 20;


ALTER TABLE public.wf_etape_id_seq OWNER TO :dbuser;

--
-- Name: z_doctorant_compl; Type: TABLE; Schema: public; Owner: :dbuser
--

CREATE TABLE public.z_doctorant_compl (
    id bigint NOT NULL,
    doctorant_id bigint NOT NULL,
    persopass character varying(50),
    email_pro character varying(100),
    histo_creation timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_createur_id bigint NOT NULL,
    histo_modification timestamp without time zone DEFAULT ('now'::text)::timestamp without time zone NOT NULL,
    histo_modificateur_id bigint NOT NULL,
    histo_destruction timestamp without time zone,
    histo_destructeur_id bigint
);


ALTER TABLE public.z_doctorant_compl OWNER TO :dbuser;

--
-- Name: TABLE z_doctorant_compl; Type: COMMENT; Schema: public; Owner: :dbuser
--

COMMENT ON TABLE public.z_doctorant_compl IS 'Table obsolète conservée un temps';


--
-- Name: categorie_privilege id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.categorie_privilege ALTER COLUMN id SET DEFAULT nextval('public.categorie_privilege_id_seq'::regclass);


--
-- Name: csi_membre id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.csi_membre ALTER COLUMN id SET DEFAULT nextval('public.csi_membre_id_seq'::regclass);


--
-- Name: doctorant_mission_enseignement id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.doctorant_mission_enseignement ALTER COLUMN id SET DEFAULT nextval('public.doctorant_mission_enseignement_id_seq'::regclass);


--
-- Name: fichier_these id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.fichier_these ALTER COLUMN id SET DEFAULT nextval('public.fichier_these_id_seq'::regclass);


--
-- Name: formation_enquete_categorie id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_enquete_categorie ALTER COLUMN id SET DEFAULT nextval('public.formation_enquete_categorie_id_seq'::regclass);


--
-- Name: formation_enquete_question id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_enquete_question ALTER COLUMN id SET DEFAULT nextval('public.formation_enquete_question_id_seq'::regclass);


--
-- Name: formation_enquete_reponse id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_enquete_reponse ALTER COLUMN id SET DEFAULT nextval('public.formation_enquete_reponse_id_seq'::regclass);


--
-- Name: formation_formateur id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_formateur ALTER COLUMN id SET DEFAULT nextval('public.formation_formateur_id_seq'::regclass);


--
-- Name: formation_formation id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_formation ALTER COLUMN id SET DEFAULT nextval('public.formation_formation_id_seq'::regclass);


--
-- Name: formation_inscription id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_inscription ALTER COLUMN id SET DEFAULT nextval('public.formation_inscription_id_seq'::regclass);


--
-- Name: formation_module id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_module ALTER COLUMN id SET DEFAULT nextval('public.formation_module_id_seq'::regclass);


--
-- Name: formation_presence id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_presence ALTER COLUMN id SET DEFAULT nextval('public.formation_presence_id_seq'::regclass);


--
-- Name: formation_seance id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_seance ALTER COLUMN id SET DEFAULT nextval('public.formation_seance_id_seq'::regclass);


--
-- Name: formation_session id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_session ALTER COLUMN id SET DEFAULT nextval('public.formation_session_id_seq'::regclass);


--
-- Name: formation_session_etat_heurodatage id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_session_etat_heurodatage ALTER COLUMN id SET DEFAULT nextval('public.formation_session_etat_heurodatage_id_seq'::regclass);


--
-- Name: formation_session_structure_valide id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.formation_session_structure_valide ALTER COLUMN id SET DEFAULT nextval('public.formation_session_structure_valide_id_seq'::regclass);


--
-- Name: horodatage_horodatage id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.horodatage_horodatage ALTER COLUMN id SET DEFAULT nextval('public.horodatage_horodatage_id_seq'::regclass);


--
-- Name: import_log id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.import_log ALTER COLUMN id SET DEFAULT nextval('public.import_log_id_seq'::regclass);


--
-- Name: individu_compl id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.individu_compl ALTER COLUMN id SET DEFAULT nextval('public.individu_compl_id_seq'::regclass);


--
-- Name: privilege id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.privilege ALTER COLUMN id SET DEFAULT nextval('public.privilege_id_seq'::regclass);


--
-- Name: step_star_log id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.step_star_log ALTER COLUMN id SET DEFAULT nextval('public.step_star_log_id_seq1'::regclass);


--
-- Name: tmp_acteur id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_acteur ALTER COLUMN id SET DEFAULT nextval('public.tmp_acteur_id_seq'::regclass);


--
-- Name: tmp_doctorant id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_doctorant ALTER COLUMN id SET DEFAULT nextval('public.tmp_doctorant_id_seq'::regclass);


--
-- Name: tmp_ecole_doct id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_ecole_doct ALTER COLUMN id SET DEFAULT nextval('public.tmp_ecole_doct_id_seq'::regclass);


--
-- Name: tmp_etablissement id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_etablissement ALTER COLUMN id SET DEFAULT nextval('public.tmp_etablissement_id_seq'::regclass);


--
-- Name: tmp_financement id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_financement ALTER COLUMN id SET DEFAULT nextval('public.tmp_financement_id_seq'::regclass);


--
-- Name: tmp_individu id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_individu ALTER COLUMN id SET DEFAULT nextval('public.tmp_individu_id_seq'::regclass);


--
-- Name: tmp_origine_financement id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_origine_financement ALTER COLUMN id SET DEFAULT nextval('public.tmp_origine_financement_id_seq'::regclass);


--
-- Name: tmp_role id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_role ALTER COLUMN id SET DEFAULT nextval('public.tmp_role_id_seq'::regclass);


--
-- Name: tmp_structure id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_structure ALTER COLUMN id SET DEFAULT nextval('public.tmp_structure_id_seq'::regclass);


--
-- Name: tmp_these id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_these ALTER COLUMN id SET DEFAULT nextval('public.tmp_these_id_seq'::regclass);


--
-- Name: tmp_these_annee_univ id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_these_annee_univ ALTER COLUMN id SET DEFAULT nextval('public.tmp_these_annee_univ_id_seq'::regclass);


--
-- Name: tmp_titre_acces id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_titre_acces ALTER COLUMN id SET DEFAULT nextval('public.tmp_titre_acces_id_seq'::regclass);


--
-- Name: tmp_unite_rech id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_unite_rech ALTER COLUMN id SET DEFAULT nextval('public.tmp_unite_rech_id_seq'::regclass);


--
-- Name: tmp_variable id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.tmp_variable ALTER COLUMN id SET DEFAULT nextval('public.tmp_variable_id_seq'::regclass);


--
-- Name: unicaen_avis_type_valeur_complem ordre; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.unicaen_avis_type_valeur_complem ALTER COLUMN ordre SET DEFAULT nextval('public.unicaen_avis_type_valeur_complem_ordre_seq'::regclass);


--
-- Name: unicaen_avis_valeur ordre; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.unicaen_avis_valeur ALTER COLUMN ordre SET DEFAULT nextval('public.unicaen_avis_valeur_ordre_seq'::regclass);


--
-- Name: unicaen_parametre_categorie id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.unicaen_parametre_categorie ALTER COLUMN id SET DEFAULT nextval('public.unicaen_parametre_categorie_id_seq'::regclass);


--
-- Name: unicaen_parametre_parametre id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.unicaen_parametre_parametre ALTER COLUMN id SET DEFAULT nextval('public.unicaen_parametre_parametre_id_seq'::regclass);


--
-- Name: unicaen_renderer_macro id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.unicaen_renderer_macro ALTER COLUMN id SET DEFAULT nextval('public.unicaen_renderer_macro_id_seq'::regclass);


--
-- Name: unicaen_renderer_rendu id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.unicaen_renderer_rendu ALTER COLUMN id SET DEFAULT nextval('public.unicaen_renderer_rendu_id_seq'::regclass);


--
-- Name: unicaen_renderer_template id; Type: DEFAULT; Schema: public; Owner: :dbuser
--

ALTER TABLE ONLY public.unicaen_renderer_template ALTER COLUMN id SET DEFAULT nextval('public.unicaen_renderer_template_id_seq'::regclass);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

