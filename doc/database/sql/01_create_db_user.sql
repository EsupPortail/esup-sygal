--
-- DATABASE, USER, ETC.
--

-- Databases
create database :dbname with ENCODING = 'UTF-8';
-- Users
create user :dbuser with encrypted password :dbpassword NOSUPERUSER NOCREATEDB;
-- Grants
grant all privileges on database :dbname to :dbuser;
