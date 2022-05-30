CREATE TABLE vars (var_id integer not null primary key autoincrement, var_name text not null, var_value text);
CREATE INDEX vars_id on vars(var_id);
CREATE INDEX vars_var_name on vars(var_name);
INSERT INTO vars (var_name, var_value) values ('dbVersion', '0');
CREATE TABLE users (uid integer not null primary key autoincrement, login text not null, password text not null, enabled integer, real_name text, e_mail text, phone text);
CREATE UNIQUE INDEX users_login on users(login);
CREATE INDEX users_real_name on users(real_name);
CREATE UNIQUE INDEX users_e_mail on users(e_mail);
CREATE INDEX users_phone on users(phone);
CREATE TABLE groups (gid integer not null primary key autoincrement, acronym text not null, name text not null);
CREATE UNIQUE INDEX groups_acronym on groups(acronym);
CREATE UNIQUE INDEX groups_name on groups(name);
CREATE TABLE users_groups (uid integer, gid integer);
CREATE INDEX users_groups_uid on users_groups(uid);
CREATE INDEX users_groups_gid on users_groups(gid);
CREATE UNIQUE INDEX users_groups_uid_gid on users_groups(uid, gid);
CREATE TABLE api_methods(aid text not null primary key, api text not null, method text not null, request_method text not null);
CREATE UNIQUE INDEX api_methods_uniq on api_methods(api, method, request_method);
CREATE TABLE users_rights(uid integer not null, aid text not null, allow integer);
CREATE UNIQUE INDEX users_rights_uniq on users_rights(uid, aid);
CREATE TABLE groups_rights(gid integer not null, aid text not null, allow integer);
CREATE UNIQUE INDEX groups_rights_uniq on groups_rights(gid, aid);
CREATE TABLE api_methods_common(aid text not null primary key);
CREATE TABLE api_methods_personal(aid text not null primary key);
CREATE TABLE core_vars (var_id integer not null primary key autoincrement, var_name text not null, var_value text);
CREATE INDEX core_vars_id on core_vars(var_id);
CREATE INDEX core_vars_var_name on core_vars(var_name);
INSERT INTO core_vars (var_name, var_value) values ('dbVersion', '0');
CREATE TABLE core_users (uid integer not null primary key autoincrement, login text not null, password text not null, enabled integer, real_name text, e_mail text, phone text);
CREATE UNIQUE INDEX core_users_login on core_users(login);
CREATE INDEX core_users_real_name on core_users(real_name);
CREATE UNIQUE INDEX core_users_e_mail on core_users(e_mail);
CREATE INDEX core_users_phone on core_users(phone);
CREATE TABLE core_groups (gid integer not null primary key autoincrement, acronym text not null, name text not null);
CREATE UNIQUE INDEX core_groups_acronym on core_groups(acronym);
CREATE UNIQUE INDEX core_groups_name on core_groups(name);
CREATE TABLE core_users_groups (uid integer, gid integer);
CREATE INDEX core_users_groups_uid on core_users_groups(uid);
CREATE INDEX core_users_groups_gid on core_users_groups(gid);
CREATE UNIQUE INDEX core_users_groups_uid_gid on core_users_groups(uid, gid);
CREATE TABLE core_api_methods(aid text not null primary key, api text not null, method text not null, request_method text not null);
CREATE UNIQUE INDEX core_api_methods_uniq on core_api_methods(api, method, request_method);
CREATE TABLE core_users_rights(uid integer not null, aid text not null, allow integer);
CREATE UNIQUE INDEX core_users_rights_uniq on core_users_rights(uid, aid);
CREATE TABLE core_groups_rights(gid integer not null, aid text not null, allow integer);
CREATE UNIQUE INDEX core_groups_rights_uniq on core_groups_rights(gid, aid);
CREATE TABLE core_api_methods_common(aid text not null primary key);
CREATE TABLE core_api_methods_personal(aid text not null primary key);
CREATE TABLE buildings (bid integer not null primary key autoincrement, address text not null, guid text);
CREATE UNIQUE INDEX buildings_guid on buildings(guid);
CREATE UNIQUE INDEX buildings_address on buildings(address);
CREATE TABLE entrances (eid integer not null primary key autoincrement, bid integer not null, entrance text not null);
CREATE INDEX entrances_bid on entrances(bid);
CREATE TABLE flats (fid integer not null primary key autoincrement, eid integer not null, flat_number integer, floor integer);
CREATE INDEX flats_eid on flats(eid);
CREATE INDEX flats_floor on flats(floor);
CREATE UNIQUE INDEX flats_flat_number on flats(flat_number);