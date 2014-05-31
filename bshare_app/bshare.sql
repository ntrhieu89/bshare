-- drop the database if exists
-- drop database if exists bshare;

-- create a new database
-- create database bshare;
-- use bshare;

drop table if exists billrequests;
drop table if exists emailrequests;
drop table if exists bills;
drop table if exists ci_sessions;
drop table if exists friendship;
drop table if exists users;

-- create table
create table users (
	userid int not null auto_increment,
	username varchar(30) not null unique,
	email varchar(50) not null unique,
	password varchar(150) not null,
	salt varchar(150) not null,
	alias varchar(30) not null,
	avatar blob,
	isactive bit not null,
	jointime datetime not null,
	lastupdatetime timestamp not null default current_timestamp,
	lastlogintime timestamp not null,
	primary key (userid),
	index (email, alias)
)engine=innodb;

create table friendship(
	inviterid int not null,
	inviteeid int not null,
	status tinyint not null,
	lastupdatetime timestamp not null default current_timestamp,
	primary key (inviterid, inviteeid),
	index (inviterid, inviteeid),
	constraint foreign key (inviterid) references users(userid) on delete cascade on update cascade,
	constraint foreign key (inviteeid) references users(userid) on delete cascade on update cascade
)engine=innodb;

create table if not exists `ci_sessions` (
	session_id varchar(40) DEFAULT '0' NOT NULL,
	ip_address varchar(45) DEFAULT '0' NOT NULL,
	user_agent varchar(120) NOT NULL,
	last_activity int(10) unsigned DEFAULT 0 NOT NULL,
	user_data text NOT NULL,
	PRIMARY KEY (session_id),
	KEY `last_activity_idx` (`last_activity`)
);

create table bills (
	billid int not null auto_increment,
	creatorid int not null,
	billdesc varchar(100) not null,
	amount double unsigned not null,
	tip double unsigned not null,
	isdone bit not null,
	createdtime datetime not null,
	lastupdatetime timestamp not null default current_timestamp,
	isdeleted bit not null,
	primary key (billid),
	index (createdtime, creatorid),
	constraint foreign key (creatorid) references users(userid) on delete cascade on update cascade
)engine=innodb; 

create table emailrequests (
	billid int not null,
	fullname varchar(100) not null,
	email varchar(50) not null,
	requestdate datetime not null,
	isconfirmed bit not null,
	isdeleted bit not null,
	lastupdatetime timestamp not null default current_timestamp,
	primary key (billid, email),
	index (billid),
	constraint foreign key (billid) references bills(billid) on delete cascade on update cascade
)engine=innodb;

create table billrequests (
	billrequestid int not null auto_increment,
	billid int not null,
	requesteeid int not null,
	requestdate datetime not null,
	isconfirmed bit not null,
	isdeleted bit not null,
	lastupdatetime timestamp not null default current_timestamp,
	primary key (billrequestid),
	index (billid, requesteeid),
	constraint foreign key (billid) references bills(billid) on delete cascade on update cascade
)engine=innodb;