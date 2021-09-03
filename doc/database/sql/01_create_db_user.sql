--
-- DATABASE, USER, ETC.
--

-- Databases
create database sygal with ENCODING = 'UTF-8';
-- Users
create user ad_sygal with encrypted password 'MOT_DE_PASSE_PAR_DEFAUT' NOSUPERUSER NOCREATEDB;
-- Grants
grant all privileges on database sygal to ad_sygal;
