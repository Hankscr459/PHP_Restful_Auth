-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2021-02-26 09:36:26
-- 伺服器版本： 10.4.17-MariaDB
-- PHP 版本： 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `tasksdb`
--

-- --------------------------------------------------------

--
-- 資料表結構 `tblsessions`
--

CREATE TABLE `tblsessions` (
  `id` bigint(20) NOT NULL COMMENT 'Session ID',
  `userid` bigint(20) NOT NULL COMMENT 'User ID',
  `accesstoken` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Access Token',
  `accesstokenexpiry` datetime NOT NULL COMMENT 'Access Token Expiry',
  `refreshtoken` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Refresh Token',
  `refreshtokenexpiry` datetime NOT NULL COMMENT 'Refresh Token Expiry Date/Time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sessions table';

-- --------------------------------------------------------

--
-- 資料表結構 `tbltasks`
--

CREATE TABLE `tbltasks` (
  `id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `completed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tasks table';

--
-- 傾印資料表的資料 `tbltasks`
--

INSERT INTO `tbltasks` (`id`, `title`, `description`, `deadline`, `completed`) VALUES
(1, 'Title 1', 'description 1', '2021-02-15 16:46:11', 'Y'),
(2, 'This is a test post updated', 'description 2', '2021-02-22 16:55:56', 'N'),
(4, 'Title 4', 'description 4', '2021-02-22 16:55:56', 'N'),
(5, 'Title 5', 'description', '2021-02-18 16:44:07', 'Y'),
(7, 'Title 7', 'description', NULL, 'N'),
(8, 'Title 8', 'description', '2021-02-22 16:55:56', 'Y'),
(10, 'Title 9', 'description', NULL, 'N'),
(11, 'Title 10', 'description', '2021-02-19 16:46:11', 'Y'),
(12, 'Title 11', 'description 11', NULL, 'Y'),
(13, 'Title 12', 'description 12', '2021-02-22 16:54:43', 'N'),
(14, 'Title 13', 'description', '2021-02-15 16:46:11', 'Y'),
(15, 'Title 14', 'description 14', '2021-02-23 16:58:57', 'N'),
(16, 'Title 15', 'description', '2021-02-18 16:44:07', 'N'),
(17, 'Title 16', 'description', '2021-02-23 16:44:07', 'Y'),
(18, 'This is a test post', 'The is a description', '2021-02-11 17:00:00', 'N'),
(19, 'This is a test post', 'The is a description', '2021-02-11 17:00:00', 'N');

-- --------------------------------------------------------

--
-- 資料表結構 `tblusers`
--

CREATE TABLE `tblusers` (
  `id` bigint(20) NOT NULL COMMENT 'User ID',
  `fullname` varchar(255) NOT NULL COMMENT 'Users Full Name',
  `username` varchar(255) NOT NULL COMMENT 'Users Username ',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'User Passeord',
  `useractive` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is User Active',
  `loginattempts` int(1) NOT NULL DEFAULT 0 COMMENT 'Attempts to log in'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Users Table';

--
-- 傾印資料表的資料 `tblusers`
--

INSERT INTO `tblusers` (`id`, `fullname`, `username`, `password`, `useractive`, `loginattempts`) VALUES
(1, 'hank andrew', 'hank', '$2y$10$iI2AGLMVLZJ6rqKTFPhz1eIg/XmLniuwE.4izD5simVtEiQeJhPu6', 'Y', 0),
(2, 'black andrew', 'black', '$2y$10$oELyRiapw2krpM4zv6rQFuAtvP6JOmvDN.s5akgQreKlLdVScSBAq', 'Y', 0);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `tblsessions`
--
ALTER TABLE `tblsessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accesstoken` (`accesstoken`),
  ADD UNIQUE KEY `refreshtoken` (`refreshtoken`),
  ADD KEY `sessionuserid_fk` (`userid`);

--
-- 資料表索引 `tbltasks`
--
ALTER TABLE `tbltasks`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tblsessions`
--
ALTER TABLE `tblsessions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Session ID', AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tbltasks`
--
ALTER TABLE `tbltasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'User ID', AUTO_INCREMENT=3;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `tblsessions`
--
ALTER TABLE `tblsessions`
  ADD CONSTRAINT `sessionuserid_fk` FOREIGN KEY (`userid`) REFERENCES `tblusers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
