-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2021-11-14 20:53:35
-- 服务器版本： 5.6.50-log
-- PHP 版本： 7.2.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `l_ci_grades`
--
CREATE DATABASE IF NOT EXISTS `l_ci_grades` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `l_ci_grades`;

-- --------------------------------------------------------

--
-- 表的结构 `Web_Admin`
--

CREATE TABLE `Web_Admin` (
  `id` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `addtime` datetime NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `Web_Admin`
--

INSERT INTO `Web_Admin` (`id`, `username`, `password`, `addtime`, `status`) VALUES
(1, 'Admin', '54529dd65e0d826ac312e4ea5f93492e', '2020-10-07 22:55:44', 0);

-- --------------------------------------------------------

--
-- 表的结构 `Web_Config`
--

CREATE TABLE `Web_Config` (
  `x` varchar(100) NOT NULL DEFAULT '',
  `j` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `Web_Config`
--

INSERT INTO `Web_Config` (`x`, `j`) VALUES
('title', '青年大学习 - 打卡平台'),
('copyright', '乐炎网络科技（四川乐炎网络工作室研发）');

-- --------------------------------------------------------

--
-- 表的结构 `Web_Grade`
--

CREATE TABLE `Web_Grade` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `gradeid` text NOT NULL,
  `orgid` int(11) NOT NULL,
  `databasename` varchar(32) NOT NULL,
  `addtime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `Web_Notice`
--

CREATE TABLE `Web_Notice` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `gbatch` text NOT NULL,
  `msg` text NOT NULL,
  `type` int(1) NOT NULL,
  `addtime` datetime NOT NULL,
  `status` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `Web_Task`
--

CREATE TABLE `Web_Task` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `gbatch` text NOT NULL,
  `stid` int(11) NOT NULL,
  `addtime` text NOT NULL,
  `endtime` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `Web_Admin`
--
ALTER TABLE `Web_Admin`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `Web_Config`
--
ALTER TABLE `Web_Config`
  ADD PRIMARY KEY (`x`);

--
-- 表的索引 `Web_Grade`
--
ALTER TABLE `Web_Grade`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `Web_Notice`
--
ALTER TABLE `Web_Notice`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `Web_Task`
--
ALTER TABLE `Web_Task`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `Web_Admin`
--
ALTER TABLE `Web_Admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `Web_Grade`
--
ALTER TABLE `Web_Grade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `Web_Notice`
--
ALTER TABLE `Web_Notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `Web_Task`
--
ALTER TABLE `Web_Task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
