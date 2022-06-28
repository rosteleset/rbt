-- panels
CREATE TABLE domophones
(
    domophone_id integer not null primary key autoincrement,
    enabled integer not null,
    model text not null,
    server text not null,
    ip text not null,
    port integer not null,
    credentials text not null,                                                                                          -- plaintext:login:password, token:token, or something else
    caller_id text not null,
    dtmf text not null,
    comment text
);
CREATE INDEX domophones_ip on domophones(ip);
CREATE UNIQUE INDEX domophones_ip_port on domophones(ip, port);