SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `crawls` (
  `id` int(11) NOT NULL,
  `source_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `added_torrents` int(11) NOT NULL,
  `updated_torrents` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `queries` (
  `id` int(11) NOT NULL,
  `query` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(1) NOT NULL,
  `count` int(11) NOT NULL,
  `first_date` int(11) NOT NULL,
  `last_date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `rtindex` (
  `id` int(11) NOT NULL,
  `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(40) NOT NULL,
  `source_id` tinyint(4) NOT NULL,
  `seeds` int(11) NOT NULL,
  `peers` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `url` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `indexed` int(11) NOT NULL,
  `category` tinyint(4) NOT NULL,
  `size` int(11) NOT NULL,
  `files` int(11) NOT NULL,
  `uploader` varchar(25) NOT NULL,
  `parent` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `sources` (
  `id` int(11) NOT NULL,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `torrents` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `trackers` (
  `id` int(11) NOT NULL,
  `url` text NOT NULL,
  `seeds` int(11) NOT NULL,
  `leech` int(11) NOT NULL,
  `checked` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `crawls`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `query` (`query`);


ALTER TABLE `rtindex`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indexed` (`indexed`),
  ADD KEY `url` (`url`),
  ADD KEY `source_id` (`source_id`),
  ADD KEY `hash` (`hash`);


ALTER TABLE `sources`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `trackers`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `crawls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `rtindex`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13661666;

ALTER TABLE `trackers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
