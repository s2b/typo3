#
# Table structure for table 'sys_redirect'
#
CREATE TABLE sys_redirect (
	source_host varchar(255) DEFAULT '' NOT NULL,
	source_path varchar(2048) DEFAULT '' NOT NULL,
	# @todo: DB analyzer would remove default 307 even though TCA default is 307 already. Needs another look.
	target_statuscode int(11) DEFAULT '307' NOT NULL,
	hitcount int(11) DEFAULT '0' NOT NULL,
	KEY index_source (source_host(80),source_path(80))
);
