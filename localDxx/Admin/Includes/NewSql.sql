SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
--------------------
CREATE TABLE `Web_Class` (
  `id` int(11) NOT NULL,
  `name` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `orgid` text,
  `addtime` datetime NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--------------------
CREATE TABLE `Web_Clocking` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `taskbatch` int(255) NOT NULL,
  `class` int(11) NOT NULL,
  `addtime` datetime NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--------------------
CREATE TABLE `Web_Config` (
  `x` varchar(100) NOT NULL DEFAULT '',
  `j` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--------------------
INSERT INTO `Web_Config` (`x`, `j`) VALUES
('title', '青年大学习 - 打卡平台'),
('copyright', '乐炎网络科技（四川乐炎网络工作室研发）');
--------------------
CREATE TABLE `Web_Notice` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `gbatch` text NOT NULL,
  `msg` text NOT NULL,
  `type` int(1) NOT NULL,
  `addtime` datetime NOT NULL,
  `status` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--------------------
CREATE TABLE `Web_NoticeLooked` (
  `id` int(11) NOT NULL,
  `nbatch` int(111) NOT NULL,
  `uid` int(11) NOT NULL,
  `class` int(11) NOT NULL,
  `addtime` datetime NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--------------------
CREATE TABLE `Web_Task` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `gbatch` text NOT NULL,
  `stid` int(11) NOT NULL,
  `addtime` text NOT NULL,
  `endtime` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--------------------
CREATE TABLE `Web_User` (
  `id` int(11) NOT NULL,
  `username` varchar(32) DEFAULT NULL,
  `password` varchar(32) NOT NULL,
  `class` int(11) NOT NULL,
  `addtime` datetime NOT NULL,
  `uskey` varchar(10) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--------------------
ALTER TABLE `Web_Class`
  ADD PRIMARY KEY (`id`);
--------------------
ALTER TABLE `Web_Clocking`
  ADD PRIMARY KEY (`id`);
--------------------
ALTER TABLE `Web_Config`
  ADD PRIMARY KEY (`x`);
--------------------
ALTER TABLE `Web_Notice`
  ADD PRIMARY KEY (`id`);
--------------------
ALTER TABLE `Web_NoticeLooked`
  ADD PRIMARY KEY (`id`);
--------------------
ALTER TABLE `Web_Task`
  ADD PRIMARY KEY (`id`);
--------------------
ALTER TABLE `Web_User`
  ADD PRIMARY KEY (`id`);
--------------------
ALTER TABLE `Web_Class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--------------------
ALTER TABLE `Web_Clocking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--------------------
ALTER TABLE `Web_Notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--------------------
ALTER TABLE `Web_NoticeLooked`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--------------------
ALTER TABLE `Web_Task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--------------------
ALTER TABLE `Web_User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;