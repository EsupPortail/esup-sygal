--
-- DATABASE, USER, ETC.
--

-- Databases
create database {DBNAME} with ENCODING = 'UTF-8';
-- Users
create user {DBUSER} with encrypted password 'MOT_DE_PASSE_PAR_DEFAUT' NOSUPERUSER NOCREATEDB;
-- Grants
grant all privileges on database {DBNAME} to {DBUSER};
