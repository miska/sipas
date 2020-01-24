CREATE TABLE pastes (
  `pid` char(16) NOT NULL,
  `title` char(48) DEFAULT '',
  `author` char(48) DEFAULT '',
  `lang` char(48) DEFAULT 'text',
  `private` boolean NOT NULL,
  `created` int(10) NOT NULL,
  `expire` int(10) NOT NULL DEFAULT 0,
  `ip` char(50) DEFAULT '',
  `login` char(160) NOT NULL DEFAULT '',
  `replyto` char(16) DEFAULT '',
  PRIMARY KEY (`pid`)
);

-- statement

CREATE TABLE paste_datas (
  `pid` char(16) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`pid`),
  FOREIGN KEY (`pid`) REFERENCES pastes(`pid`) ON DELETE CASCADE
);
