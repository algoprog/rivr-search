SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `trackers` (`id`, `url`, `seeds`, `leech`, `checked`) VALUES
(1, 'udp://tracker.openbittorrent.com:80', 0, 0, '2015-11-25 21:57:00'),
(2, 'udp://tracker.coppersurfer.tk:6969', 0, 0, '2015-11-25 21:57:33'),
(3, 'udp://9.rarbg.com:2710', 0, 0, '2015-11-25 21:57:50'),
(4, 'udp://9.rarbg.me:2710', 0, 0, '2015-11-25 21:58:03'),
(5, 'udp://glotorrents.pw:6969', 0, 0, '2015-11-25 21:58:15'),
(6, 'udp://tracker.trackerfix.com:80', 0, 0, '2015-11-25 21:58:27'),
(7, 'udp://tracker.leechers-paradise.org:6969', 0, 0, '2015-11-25 21:58:39'),
(8, 'http://glotorrents.com:6969/announce', 0, 0, '2015-11-25 21:58:51'),
(9, 'http://mgtracker.org:2710/announce', 0, 0, '2015-11-25 21:59:01');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
