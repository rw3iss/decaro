
CREATE TABLE IF NOT EXISTS clients (
	id int(11) NOT NULL AUTO_INCREMENT,
	name varchar(256) NOT NULL,
	address varchar(256),
	address2 varchar(256),
	city varchar(128),
	state varchar(128),
	zipcode varchar(12),
	phone varchar(256),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS orders (
	id int(11) NOT NULL AUTO_INCREMENT,
	status varchar(128) NOT NULL,
	date_created DATETIME,
	client_id int references clients(id),
	department varchar(256),
	origin_address varchar(256),
	origin_city varchar(128),
	origin_state varchar(64),
	origin_zipcode varchar(12),
	description varchar(512),
	pieces int,
	weight varchar(12),
	ready_time DATETIME,
	close_time DATETIME,
	additional varchar(512),
	fuel_surcharge float,
	payment_type int,
	third_party_address varchar(256),
	third_party_city varchar(128),
	third_party_state varchar(64),
	third_party_zipcode varchar(12),
  PRIMARY KEY (id),
  KEY client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE orders DROP COLUMN payment_type;
ALTER TABLE orders ADD COLUMN payment_type varchar(24);


CREATE TABLE IF NOT EXISTS users (
	id int(11) NOT NULL AUTO_INCREMENT,
	username varchar(128) NOT NULL,
	firstname varchar(128) NOT NULL,
	lastname varchar(128) NOT NULL,
	email varchar(128) NOT NULL,
	address varchar(256),
	address2 varchar(256),
	city varchar(128),
	state varchar(128),
	zipcode varchar(12),
	phone varchar(256),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE users ADD COLUMN role varchar(128);

CREATE TABLE IF NOT EXISTS user_roles (
	id int(11) NOT NULL AUTO_INCREMENT,
	name varchar(256) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO user_roles (name) VALUES ('administrator');
INSERT INTO user_roles (name) VALUES ('moderator');
INSERT INTO user_roles (name) VALUES ('employee');
INSERT INTO user_roles (name) VALUES ('client');
INSERT INTO user_roles (name) VALUES ('user');


CREATE TABLE IF NOT EXISTS settings (
	id int(11) NOT NULL AUTO_INCREMENT,
	name varchar(256) NOT NULL,
	value varchar(256),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE orders ADD COLUMN pod_signature varchar(128);
ALTER TABLE orders ADD COLUMN pod_date DATETIME;
ALTER TABLE orders ADD COLUMN pod_total varchar(64);

ALTER TABLE orders ADD COLUMN order_number varchar(128);



CREATE TABLE IF NOT EXISTS invoices (
	id int(11) NOT NULL AUTO_INCREMENT,
	client_id int(11) NOT NULL,
	order_ids varchar(256),
	date_from DATETIME,
	date_to DATETIME,
	total float,
	date_due_by DATETIME,
	date_paid DATETIME,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table orders DROP department;

ALTER TABLE orders ADD COLUMN client_station_id int(11);

ALTER TABLE orders ADD COLUMN destination_address varchar(256);
ALTER TABLE orders ADD COLUMN destination_city varchar(128);
ALTER TABLE orders ADD COLUMN destination_state varchar(64);
ALTER TABLE orders ADD COLUMN destination_zipcode varchar(12);

CREATE TABLE IF NOT EXISTS client_stations (
	id int(11) NOT NULL AUTO_INCREMENT,
	client_id int(11) NOT NULL,
	name varchar(256),
	address varchar(256),
	address2 varchar(256),
	city varchar(128),
	state varchar(128),
	zipcode varchar(12),
	phone_number varchar(256),
	fax_number varchar(256),
	contact varchar(256),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table clients drop address;
alter table clients drop address2;
alter table clients drop city;
alter table clients drop state;
alter table clients drop zipcode;
alter table clients drop phone;

alter table users add column password varchar(64);

alter table invoices add column date_created datetime;

alter table invoices add column invoice_number varchar(64);

alter table orders add column origin_address2 varchar(128);
alter table orders add column destination_address2 varchar(128);
alter table orders add column third_party_address2 varchar(128);

insert into users (username, firstname, lastname, email, role, password) 
	values ('rw3iss', 'Ryan', 'Weiss', 'rw3iss@gmail.com', 'ADMINISTRATOR', 'qazokm');
insert into users (username, firstname, lastname, email, role, password) 
	values ('test', 'Test', 'User', 'test@nomail.com', 'ADMINISTRATOR', 'test');

alter table invoices add column client_station_id int;

alter table orders add column destination_name varchar(128);
alter table orders add column customer_number varchar(128);
alter table orders add column shipper_enabled tinyint;
alter table orders add column shipper_name varchar(128);
alter table orders add column delivery_type varchar(128);