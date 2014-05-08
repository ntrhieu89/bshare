-- drop the database if exists
drop database if exists bshare;

-- create a new database
create database bshare;
use bshare;

-- create table
create table users (
	userid int not null auto_increment,
	username varchar(30) not null unique,
	email varchar(50) not null unique,
	password varchar(150) not null,
	alias varchar(30) not null,
	avatar blob,
	jointime datetime not null,
	lastlogintime timestamp not null,
	lastupdatetime timestamp not null,
	primary key (userid),
	index (email, alias)
)engine=innodb;

create table friendship(
	inviterid int not null,
	inviteeid int not null,
	status tinyint not null,
	lastupdatetime timestamp not null,
	primary key (inviterid, inviteeid),
	index (inviterid, inviteeid),
	constraint foreign key (inviterid) references users(userid) on delete cascade on update cascade,
	constraint foreign key (inviteeid) references users(userid) on delete cascade on update cascade
)engine=innodb;

create table sessions (
	sessid varchar(36) not null,
	userid int not null,
	starttime datetime not null,
	endtime datetime,	
	primary key (sessid),
	constraint foreign key (userid) references users(userid) on delete cascade on update cascade
)engine=innodb;

create table bills (
	billid int not null auto_increment,
	creatorid int not null,
	billdesc varchar(100) not null,
	amount double unsigned not null,
	tip double unsigned not null,
	isdone bit not null,
	createddate datetime not null,
	lastupdatetime timestamp not null,
	isdeleted bit not null,
	primary key (billid),
	index (createddate, creatorid),
	constraint foreign key (creatorid) references users(userid) on delete cascade on update cascade
)engine=innodb; 

create table billrequests (
	billrequestid int not null auto_increment,
	billid int not null,
	requesteeid int not null,
	requestdate datetime not null,
	isconfirmed bit not null,
	isdeleted bit not null,
	primary key (billrequestid),
	index (billid, requesteeid),
	constraint foreign key (billid) references bills(billid) on delete cascade on update cascade
)engine=innodb;