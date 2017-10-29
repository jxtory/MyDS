-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 2017-10-29 02:59:04
-- 服务器版本： 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myds`
--

-- --------------------------------------------------------

--
-- 表的结构 `myds_runinfo_log`
--

CREATE TABLE `myds_runinfo_log` (
  `id` int(11) NOT NULL COMMENT 'id',
  `startDate` int(11) DEFAULT NULL COMMENT 'startDate',
  `endDate` int(11) DEFAULT NULL COMMENT 'endDate',
  `runDate` int(11) DEFAULT NULL COMMENT 'runDate',
  `find_url` int(11) DEFAULT NULL COMMENT 'find_url',
  `recordTotal` int(11) DEFAULT NULL COMMENT 'recordTotal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `myds_spider_datas`
--

CREATE TABLE `myds_spider_datas` (
  `id` int(11) NOT NULL COMMENT 'id',
  `contents` text COMMENT 'contents',
  `ruleordom` text COMMENT 'htmllabel',
  `rod_asname` varchar(50) DEFAULT NULL COMMENT 'RuleOrDom AsName',
  `from_url` varchar(1024) DEFAULT NULL COMMENT 'from_url',
  `on_Url_depth` int(2) DEFAULT NULL COMMENT 'on_url_depth'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `myds_urllisttask`
--

CREATE TABLE `myds_urllisttask` (
  `id` int(11) NOT NULL COMMENT 'id',
  `pid` int(11) DEFAULT NULL COMMENT 'pid',
  `url` varchar(1024) DEFAULT NULL COMMENT 'url',
  `encryption_url` varchar(255) DEFAULT NULL COMMENT 'md5url',
  `on_depth` int(2) DEFAULT NULL COMMENT 'depth',
  `http_statuscode` varchar(20) DEFAULT NULL COMMENT 'http_statuscode'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `myds_visited_url`
--

CREATE TABLE `myds_visited_url` (
  `id` int(11) NOT NULL COMMENT 'id',
  `url` varchar(1024) DEFAULT NULL COMMENT 'url',
  `encryption_url` varchar(255) DEFAULT NULL COMMENT 'md5url',
  `title` varchar(255) DEFAULT NULL COMMENT 'htmltitle',
  `on_depth` int(2) DEFAULT NULL COMMENT 'depth',
  `http_statuscode` varchar(20) DEFAULT NULL COMMENT 'http_statuscode',
  `filesize` varchar(255) DEFAULT NULL COMMENT 'filesize',
  `oid` int(11) DEFAULT NULL COMMENT 'oid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `myds_runinfo_log`
--
ALTER TABLE `myds_runinfo_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `myds_spider_datas`
--
ALTER TABLE `myds_spider_datas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `myds_urllisttask`
--
ALTER TABLE `myds_urllisttask`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `myds_visited_url`
--
ALTER TABLE `myds_visited_url`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `myds_runinfo_log`
--
ALTER TABLE `myds_runinfo_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';
--
-- 使用表AUTO_INCREMENT `myds_spider_datas`
--
ALTER TABLE `myds_spider_datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';
--
-- 使用表AUTO_INCREMENT `myds_urllisttask`
--
ALTER TABLE `myds_urllisttask`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';
--
-- 使用表AUTO_INCREMENT `myds_visited_url`
--
ALTER TABLE `myds_visited_url`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
