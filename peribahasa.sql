-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2025 at 01:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `peribahasa`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` varchar(11) NOT NULL,
  `AdminPassword` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `AdminPassword`) VALUES
('1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CategoryID` varchar(50) NOT NULL,
  `Category` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`CategoryID`, `Category`) VALUES
('bidalan', 'Bidalan'),
('katakatahikmat', 'Kata-Kata Hikmat'),
('kiasan', 'Kiasan'),
('pepatah', 'Pepatah'),
('perumpamaan', 'Perumpamaan');

-- --------------------------------------------------------

--
-- Table structure for table `clerk`
--

CREATE TABLE `clerk` (
  `ClerkID` varchar(25) NOT NULL,
  `ClerkName` varchar(25) NOT NULL,
  `ClerkPassword` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clerk`
--

INSERT INTO `clerk` (`ClerkID`, `ClerkName`, `ClerkPassword`) VALUES
('211', 'AHMAD', '$2y$10$ElJkaoVRFuZmgz3lW2IFLuQX7K.9WadEXhdY1whZ1JWSBuaSWzOMq');

-- --------------------------------------------------------

--
-- Table structure for table `community_chat`
--

CREATE TABLE `community_chat` (
  `MessageID` int(11) NOT NULL,
  `UserEmail` varchar(255) NOT NULL,
  `Message` text NOT NULL,
  `MessageType` enum('question','answer') NOT NULL,
  `ParentMessageID` int(11) DEFAULT NULL,
  `DatePosted` timestamp NOT NULL DEFAULT current_timestamp(),
  `Likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `community_chat`
--

INSERT INTO `community_chat` (`MessageID`, `UserEmail`, `Message`, `MessageType`, `ParentMessageID`, `DatePosted`, `Likes`) VALUES
(34, 'usman@gmail.com', 'lawanya lecturer yang bernama Dr Hasiah , bagai apa erk ?', 'question', NULL, '2025-02-10 08:29:57', 1),
(35, 'n@gmail.com', 'bagai bulan dipagar bintang', 'answer', NULL, '2025-02-10 08:48:23', 0);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `FavoriteID` int(11) NOT NULL,
  `UserEmail` varchar(255) NOT NULL,
  `PeriID` int(10) NOT NULL,
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_likes`
--

CREATE TABLE `message_likes` (
  `LikeID` int(11) NOT NULL,
  `MessageID` int(11) NOT NULL,
  `UserEmail` varchar(255) DEFAULT NULL,
  `DateLiked` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_likes`
--

INSERT INTO `message_likes` (`LikeID`, `MessageID`, `UserEmail`, `DateLiked`) VALUES
(10, 34, 'usman@gmail.com', '2025-02-10 08:43:38');

-- --------------------------------------------------------

--
-- Table structure for table `message_views`
--

CREATE TABLE `message_views` (
  `UserEmail` varchar(255) NOT NULL,
  `LastChecked` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_views`
--

INSERT INTO `message_views` (`UserEmail`, `LastChecked`) VALUES
('030422030451', '2025-02-10 09:32:52');

-- --------------------------------------------------------

--
-- Table structure for table `peribahasa`
--

CREATE TABLE `peribahasa` (
  `PeriID` int(10) NOT NULL,
  `PeriName` varchar(300) NOT NULL,
  `PeriMean` varchar(1000) NOT NULL,
  `CategoryID` varchar(50) NOT NULL,
  `ContohAyat` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peribahasa`
--

INSERT INTO `peribahasa` (`PeriID`, `PeriName`, `PeriMean`, `CategoryID`, `ContohAyat`) VALUES
(1, 'Bagai air di daun keladii', 'Sesuatu yang tidak tetap atau tidak berkekalan', 'perumpamaan', 'Jangan berharap terlalu tinggi pada sesuatu yang tidak pasti, seperti bagai air di daun keladi.'),
(2, 'Bagai anjing dengan kucing', 'Dua orang yang selalu berkelahi atau bermusuhan', 'perumpamaan', 'Mereka berdua selalu bertengkar seperti bagai anjing dengan kucing.'),
(3, 'Bagai pinang dibelah dua', 'Sangat mirip atau serupa', 'perumpamaan', 'Mereka kembar dan wajahnya hampir sama, seperti bagai pinang dibelah dua.'),
(4, 'Seperti katak di bawah tempurung', 'Seseorang yang berpandangan sempit atau tidak tahu dunia luar', 'perumpamaan', 'Dia tidak mahu menerima pendapat orang lain, seperti katak di bawah tempurung.'),
(5, 'Bagai pungguk merindukan bulan', 'Mengharapkan sesuatu yang mustahil diperoleh', 'perumpamaan', 'Dia masih berharap pada sesuatu yang mustahil, bagai pungguk merindukan bulan.'),
(6, 'Ada udang di balik batu', 'Ada maksud tersembunyi', 'kiasan', 'Dia kelihatan jujur, tetapi ada maksud tersembunyi seperti ada udang di balik batu.'),
(7, 'Berat sebelah', 'Tidak adil atau berat sebelah dalam membuat keputusan', 'kiasan', 'Keputusan itu tidak adil kerana hanya menguntungkan satu pihak, seperti berat sebelah.'),
(8, 'Panas setahun dihapus hujan sehari', 'Kebaikan yang banyak boleh hilang dengan satu kesalahan', 'kiasan', 'Kebaikan yang diberikan kadang-kadang dilupakan begitu sahaja, seperti panas setahun dihapus hujan sehari.'),
(9, 'Tangan di atas lebih baik daripada tangan di bawah', 'Memberi lebih baik daripada meminta', 'kiasan', 'Lebih baik memberi daripada meminta, kerana tangan di atas lebih baik daripada tangan di bawah.'),
(10, 'Air susu dibalas dengan air tuba', 'Kebaikan dibalas dengan kejahatan', 'kiasan', 'Jangan membalas kejahatan dengan kejahatan, kerana air susu dibalas dengan air tuba.'),
(11, 'Alah bisa tegal biasa', 'Sesuatu yang sukar akan menjadi mudah jika selalu dilatih', 'pepatah', 'Dengan latihan yang konsisten, akhirnya dia berjaya kerana alah bisa tegal biasa.'),
(12, 'Berakit-rakit ke hulu berenang-renang ke tepian', 'Bersakit-sakit dahulu bersenang-senang kemudian', 'pepatah', 'Kita harus berusaha dahulu sebelum menikmati hasil, seperti pepatah berakit-rakit ke hulu berenang-renang ke tepian.'),
(13, 'Tak lapuk dek hujan tak lekang dek panas', 'Sesuatu yang kekal atau tidak berubah', 'pepatah', 'Cinta sejati tak akan pudar seperti tak lapuk dek hujan tak lekang dek panas.'),
(14, 'Biar lambat asal selamat', 'Lebih baik perlahan tetapi selamat daripada terburu-buru tetapi celaka', 'pepatah', 'Dia memilih untuk berhati-hati dalam perniagaannya, biar lambat asal selamat.'),
(15, 'Genggam bara api biar sampai jadi arang', 'Tetap tabah menghadapi kesusahan hingga mencapai tujuan', 'pepatah', 'Walaupun banyak cabaran, dia tetap berusaha kerana menggenggam bara api biar sampai jadi arang.'),
(16, 'Yang buta peniup lesung', 'Orang yang tidak layak diberi tugas penting', 'bidalan', 'Dia selalu membuat kesilapan dalam tugasnya, bagaikan yang buta meniup lesung.'),
(17, 'Harapkan guruh di langit, air di tempayan dicurahkan', 'Mengharapkan sesuatu yang belum pasti sehingga yang ada terlepas', 'bidalan', 'Jangan harapkan sesuatu yang belum pasti, harapkan guruh di langit air di tempayan dicurahkan.'),
(18, 'Seperti telur di ujung tanduk', 'Berada dalam keadaan yang sangat berbahaya', 'bidalan', 'Situasinya sangat berbahaya seperti telur di ujung tanduk.'),
(19, 'Bagai mencurah air ke daun keladi', 'Memberikan nasihat kepada orang yang tidak mahu mendengar', 'bidalan', 'Memberikan nasihat kepada orang yang tidak mahu menerima ibarat mencurah air ke daun keladi.'),
(20, 'Ibarat menatang minyak yang penuh', 'Menjaga sesuatu dengan penuh hati-hati', 'bidalan', 'Dia menjaga anaknya dengan sangat berhati-hati, ibarat menatang minyak yang penuh.'),
(21, 'Masa itu emas', 'Waktu itu sangat berharga', 'katakatahikmat', 'Gunakan waktu dengan baik kerana masa itu emas.'),
(22, 'Ilmu itu pelita hidup', 'Ilmu pengetahuan menerangi kehidupan', 'katakatahikmat', 'Ilmu sangat penting dalam kehidupan, seperti ilmu itu pelita hidup.'),
(23, 'Rajin pangkal pandai', 'Dengan rajin belajar seseorang akan menjadi pandai', 'katakatahikmat', 'Dia belajar dengan rajin kerana dia tahu rajin pangkal pandai.'),
(24, 'Hemat pangkal kaya', 'Berjimat cermat membawa kepada kekayaan', 'katakatahikmat', 'Berjimat cermat akan membawa kekayaan, seperti pepatah hemat pangkal kaya.'),
(25, 'Malu bertanya sesat di jalan', 'Jangan malu untuk bertanya agar tidak tersalah jalan', 'katakatahikmat', 'Dia tidak segan bertanya agar tidak tersesat kerana malu bertanya sesat di jalan.'),
(28, 'Bagai bulan dipagar bintang', 'Sangat cantik atau rupawan', 'perumpamaan', 'Kecantikan gadis itu bagai bulan dipagar bintang.'),
(29, 'Bagai ikan pulang ke lubuk', 'Kembali ke tempat asal atau kampung halaman', 'perumpamaan', 'Setelah bertahun-tahun merantau, dia akhirnya pulang ke kampung bagai ikan pulang ke lubuk.'),
(30, 'Bagai burung dalam sangkar emas', 'Hidup mewah tetapi tidak bebas', 'perumpamaan', 'Walaupun dia hidup dalam kemewahan, tetapi dia tidak bahagia, bagai burung dalam sangkar emas.'),
(31, 'Bagai mentari menyinari bumi', 'Memberi manfaat kepada semua orang', 'perumpamaan', 'Kebaikan hatinya bagai mentari menyinari bumi, semua orang mendapat manfaat.'),
(32, 'Bagai manikam sudah diasah', 'Sangat indah dan berharga setelah dididik atau dibimbing', 'perumpamaan', 'Setelah mendapat pendidikan yang baik, dia menjadi bagai manikam sudah diasah.'),
(33, 'Bagai musim dengan ketika', 'Sesuatu yang sangat sesuai atau sepadan', 'kiasan', 'Pasangan itu sangat serasi, bagai musim dengan ketika.'),
(34, 'Bagai delima merekah', 'Senyuman yang sangat indah dan menarik', 'kiasan', 'Senyumannya bagai delima merekah, memikat hati setiap yang memandang.'),
(35, 'Bagai harimau mengaum', 'Suara yang sangat kuat dan berkuasa', 'kiasan', 'Suaranya bagai harimau mengaum, membuat semua orang terdiam mendengar.'),
(36, 'Bagai embun di hujung rumput', 'Sesuatu yang sangat sensitif dan mudah hilang', 'kiasan', 'Kepercayaan itu bagai embun di hujung rumput, sekali hilang sukar untuk kembali.'),
(37, 'Bagai bunga dedap', 'Kecantikan yang tidak memberi manfaat', 'pepatah', 'Jangan hanya cantik di luar tetapi tidak bermanfaat, bagai bunga dedap.'),
(38, 'Ada air, adalah ikan.', 'Ada penempatan adalah penduduk', 'pepatah', ''),
(39, 'Kalau tak kenal maka tak cinta', 'Tidak akan menyukai atau mencintai sesuatu jika tidak mengenalinya', 'pepatah', 'Dia belajar mengenali budaya tempatan kerana kalau tak kenal maka tak cinta.'),
(40, 'Bagai air mencari level', 'Sesuatu yang akan mencari keseimbangannya sendiri secara semula jadi', 'pepatah', 'Biarkan masalah itu selesai dengan sendirinya, bagai air mencari level.'),
(41, 'Seperti kaca terhempas ke batu', 'Sesuatu yang hancur berkecai dan tidak dapat diperbaiki lagi', 'pepatah', 'Hatinya seperti kaca terhempas ke batu setelah mengetahui kebenaran itu.'),
(42, 'Bagai mengukur baju di badan sendiri', 'Menilai sesuatu berdasarkan kemampuan atau keadaan diri sendiri', 'pepatah', 'Sebelum membuat keputusan, kita harus bagai mengukur baju di badan sendiri.'),
(43, 'Seperti ayam kehilangan induk', 'Keadaan yang sangat gelisah atau tidak tentu arah', 'bidalan', 'Sejak kehilangan pekerjaan, dia seperti ayam kehilangan induk.'),
(44, 'Bagai mencurah air ke pasir', 'Melakukan sesuatu yang sia-sia atau tidak mendatangkan hasil', 'bidalan', 'Nasihat yang diberikan kepadanya bagai mencurah air ke pasir.'),
(45, 'Bagai melepaskan anjing tersepit', 'Menolong orang yang kemudiannya membawa masalah', 'bidalan', 'Dia menyesal telah membantu orang itu, akhirnya bagai melepaskan anjing tersepit.'),
(46, 'Seperti ikan pulang ke lubuk', 'Kembali ke tempat asal atau kampung halaman', 'bidalan', 'Setelah berjaya di perantauan, dia pulang ke kampung seperti ikan pulang ke lubuk.'),
(47, 'Bagai kacang lupa akan kulitnya', 'Orang yang lupa akan asal-usulnya atau orang yang telah membantunya', 'bidalan', 'Setelah kaya, dia menjadi sombong bagai kacang lupa akan kulitnya.'),
(48, 'Bagai mendapat durian runtuh', 'Mendapat keuntungan atau rezeki secara tiba-tiba', 'katakatahikmat', 'Dia sangat gembira seperti mendapat durian runtuh apabila memenangi hadiah itu.'),
(49, 'Seperti buluh kasap', 'Seseorang yang kasar perangainya', 'katakatahikmat', 'Sikapnya seperti buluh kasap, menyakiti hati orang lain.'),
(50, 'Bagai air dengan api', 'Dua perkara yang bertentangan dan tidak dapat disatukan', 'katakatahikmat', 'Pendapat mereka bagai air dengan api, tidak pernah sehaluan.'),
(51, 'Seperti tikus membaiki labu', 'Seseorang yang cuba memperbaiki sesuatu tetapi malah memburukkan keadaan', 'katakatahikmat', 'Usahanya untuk menyelesaikan masalah seperti tikus membaiki labu.'),
(52, 'Bagai bergantung di akar lapuk', 'Berharap atau bergantung pada sesuatu yang tidak boleh dipercayai', 'katakatahikmat', 'Jangan terlalu mengharapkan janjinya, itu bagai bergantung di akar lapuk.'),
(53, 'Bagai air di lautan', 'Sesuatu yang tidak ada habisnya atau tiada kesudahan', 'perumpamaan', ''),
(54, 'Bagai buah keras', 'Sangat degil atau keras hati', 'perumpamaan', ''),
(55, 'Seperti hujan jatuh ke bumi', 'Mengharapkan sesuatu yang tidak pasti atau tidak mungkin terjadi', 'perumpamaan', ''),
(56, 'Bagai ular menyusur akar', 'Orang yang berpura-pura tidak tahu tetapi sebenarnya tahu segala-galanya', 'perumpamaan', ''),
(57, 'Bagaikan bulan di tengah malam', 'Sangat terang atau jelas', 'perumpamaan', ''),
(58, 'Duduk di atas pagar', 'Tidak mengambil keputusan atau berada dalam keadaan ragu-ragu', 'kiasan', ''),
(59, 'Bagai padi, semakin berisi semakin merunduk', 'Orang yang semakin berjaya semakin rendah hati', 'kiasan', ''),
(60, 'Terlajak perahu boleh diundur, terlajak kata buruk padahnya', 'Apa yang diucapkan tidak dapat diubah kembali', 'kiasan', ''),
(61, 'Seperti kucing dengan tikus', 'Dua orang yang selalu saling berselisih atau tidak sependapat', 'pepatah', ''),
(62, 'Bagai aur dengan tebing', 'Saling membantu atau bergantung antara satu sama lain', 'pepatah', ''),
(63, 'Tak kenal maka tak cinta', 'Jika tidak mengetahui atau tidak mengenali, kita tidak akan menyukai', 'pepatah', ''),
(64, 'Lepas dari mulut harimau, masuk ke mulut buaya', 'Berpindah dari keadaan buruk ke keadaan yang lebih buruk', 'pepatah', ''),
(65, 'Ibarat seperti kacang lupakan kulit', 'Melupakan jasa baik orang yang telah membantu', 'pepatah', ''),
(66, 'Bagai api dengan air', 'Sesuatu yang tidak dapat disatukan atau bertentangan', 'bidalan', ''),
(67, 'Patah tumbuh hilang berganti', 'Setiap yang hilang pasti akan digantikan dengan sesuatu yang baru', 'bidalan', ''),
(68, 'Seperti katak di bawah tempurung', 'Orang yang mempunyai pandangan sempit atau tidak tahu dunia luar', 'bidalan', ''),
(69, 'Gajah di pelupuk mata tak nampak, kuman di seberang lautan nampak', 'Melihat kesalahan orang lain tetapi tidak melihat kesalahan sendiri', 'bidalan', ''),
(70, 'Bagai melepaskan batuk di tangga', 'Melakukan sesuatu yang tidak berfaedah atau tidak sempurna', 'bidalan', ''),
(71, 'Kuman di seberang laut nampak, gajah di pelupuk mata tak nampak', 'Melihat kesalahan orang lain tetapi tidak melihat kesalahan sendiri', 'bidalan', ''),
(72, 'Bagaikan ikan di laut, asyik mencari makan', 'Mencari peluang untuk hidup atau bertahan', 'bidalan', ''),
(73, 'Seperti durian runtuh', 'Mendapat sesuatu yang sangat menguntungkan secara tiba-tiba', 'katakatahikmat', ''),
(74, 'Ada gula ada semut', 'Di mana ada keuntungan, di situ ada orang berkumpul', 'katakatahikmat', ''),
(75, 'Ada hujan ada panas, ada hari boleh balas.', 'Perbuatan jahat itu sewaktu-waktu akan mendapat balasan juga', 'katakatahikmat', ''),
(76, 'Tidak ada rotan, akar pun berguna', 'Jika tiada pilihan terbaik, yang biasa pun boleh digunakan', 'katakatahikmat', ''),
(77, 'Bersatu teguh, bercerai roboh', 'Kekuatan terletak pada kebersamaan', 'katakatahikmat', ''),
(78, 'Bagai hujan jatuh di tanah kering', 'Sesuatu yang sangat ditunggu-tunggu atau sangat diperlukan', 'perumpamaan', ''),
(79, 'Seperti langit dengan bumi', 'Perbezaan yang sangat besar atau tidak boleh dibandingkan', 'perumpamaan', ''),
(80, 'Bagaikan telur di hujung tanduk', 'Keadaan yang sangat bahaya atau tidak stabil', 'perumpamaan', ''),
(81, 'Tak sudi bertemu muka, macam katak hendak terbang', 'Mencuba sesuatu yang mustahil', 'perumpamaan', ''),
(82, 'Seperti api dalam sekam', 'Masalah yang tersembunyi tetapi akan meletus pada bila-bila masa', 'perumpamaan', ''),
(83, 'Bagai menarik rambut dalam tepung', 'Melakukan sesuatu dengan sangat hati-hati', 'perumpamaan', ''),
(84, 'Bagaikan menangguk di air keruh', 'Mencari keuntungan dalam keadaan yang tidak baik', 'kiasan', ''),
(85, 'Gigi dengan lidah', 'Hubungan yang sangat rapat, tetapi kadang kala berkonflik', 'kiasan', ''),
(86, 'Sambil menyelam minum air', 'Melakukan dua perkara dalam satu masa', 'kiasan', ''),
(87, 'Bagai menegakkan benang yang basah', 'Melakukan sesuatu yang sangat sukar atau mustahil', 'kiasan', ''),
(88, 'Ada harapan, ada usaha', 'Dengan harapan pasti ada usaha untuk mencapainya', 'pepatah', ''),
(89, 'Bersatu kita teguh, bercerai kita roboh', 'Jika bersama, kita kuat; jika berpisah, kita lemah', 'pepatah', ''),
(90, 'Bagaikan kupu-kupu yang terbang malam', 'Seseorang yang pergi ke tempat yang salah atau tidak sesuai', 'pepatah', ''),
(91, 'Lain padang, lain belalang', 'Setiap tempat atau keadaan mempunyai keunikan dan perbezaannya', 'pepatah', ''),
(92, 'Bagai membeli kucing dalam karung', 'Melakukan sesuatu tanpa mengetahui akibatnya', 'pepatah', ''),
(93, 'Bagai aur dengan rebung', 'Hubungan yang saling membantu dan menguntungkan', 'pepatah', ''),
(94, 'Seperti udang di balik batu', 'Sesuatu yang tersembunyi dan belum diketahui orang', 'bidalan', ''),
(95, 'Bagai kepala batu', 'Seseorang yang sangat keras kepala dan tidak mudah berubah fikiran', 'bidalan', ''),
(96, 'Seperti buah nangka dengan durian', 'Dua perkara yang sangat berlainan atau tidak sesuai', 'bidalan', ''),
(97, 'Bagaikan berpaut di dahan rapuh', 'Bergantung pada sesuatu yang tidak pasti atau tidak boleh dipercayai', 'bidalan', ''),
(98, 'Bagai menilai berat peluru dengan tangan', 'Membuat penilaian yang tidak tepat atau tidak sesuai', 'bidalan', ''),
(99, 'Seperti menunggu buah yang tak jatuh', 'Menunggu sesuatu yang tidak akan terjadi atau mustahil', 'katakatahikmat', ''),
(100, 'Di mana ada kemahuan, di situ ada jalan', 'Dengan kemahuan yang kuat, pasti ada cara untuk mencapainya', 'katakatahikmat', '');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `QuizID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `ClerkID` varchar(25) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `IsActive` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`QuizID`, `Title`, `Description`, `ClerkID`, `DateCreated`, `IsActive`) VALUES
(4, 'Teka-Teki', 'a', '211', '2025-02-10 08:27:27', 1),
(5, 'Teka-Teki', 'a', '211', '2025-02-10 08:34:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `AttemptID` int(11) NOT NULL,
  `QuizID` int(11) NOT NULL,
  `UserEmail` varchar(255) NOT NULL,
  `Score` int(11) NOT NULL,
  `DateAttempted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`AttemptID`, `QuizID`, `UserEmail`, `Score`, `DateAttempted`) VALUES
(31, 4, 'usman@gmail.com', 0, '2025-02-10 08:28:46'),
(32, 4, 'usman@gmail.com', 0, '2025-02-10 08:28:46'),
(33, 4, 'usman@gmail.com', 100, '2025-02-10 08:28:49'),
(34, 4, 'usman@gmail.com', 100, '2025-02-10 08:28:49'),
(35, 4, 'admin@test.com', 50, '2025-02-10 11:39:37'),
(36, 5, 'admin@test.com\', 50, '2025-02-10 11:39:37'),
(37, 4, 'admin@test.com\', 100, '2025-02-10 11:39:42'),
(38, 5, 'admin@test.com\', 100, '2025-02-10 11:39:42'),
(39, 4, 'admin@test.com\', 0, '2025-02-10 11:39:47'),
(40, 5, 'admin@test.com\', 0, '2025-02-10 11:39:47');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `QuestionID` int(11) NOT NULL,
  `QuizID` int(11) NOT NULL,
  `Question` text NOT NULL,
  `CorrectAnswer` text NOT NULL,
  `Option1` text NOT NULL,
  `Option2` text NOT NULL,
  `Option3` text NOT NULL,
  `Option4` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`QuestionID`, `QuizID`, `Question`, `CorrectAnswer`, `Option1`, `Option2`, `Option3`, `Option4`) VALUES
(4, 4, 'apakah huruf pertama dalam abjad ?', 'A', 'A', 'B', 'C', 'D'),
(5, 5, 'apakah huruf pertama dalam abjad ?', 'A', 'A', 'B', 'C', 'D');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserEmail` varchar(255) NOT NULL,
  `UserName` varchar(20) NOT NULL,
  `UserPassword` varchar(255) NOT NULL,
  `UserRole` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserEmail`, `UserName`, `UserPassword`, `UserRole`) VALUES
('b@gmail.com', 'Safwan', '$2y$10$k51wunn/tArbkP3Sp4lGj.nBi02nTlsjdDz.lutlnyFtwcEl8gSR6', 'user'),
('admin@test.com\', 'DR HASIAH', '$2y$10$EKS/S4xURtiKswViv0UemeQKooAwLSDOHhrK95xy7mM/qOBU/xJyO', 'user'),
('n@gmail.com', 'SYED', '$2y$10$G.RHlB5QxfaVkpDC.GRJJ.MN.E99y4Ab6UqzC.asZBCbq.ecfcoo2', 'user'),
('usman@gmail.com', 'USMAN', '$2y$10$IvtxWfgfzdxQoT/4tnaCSOvQdFI./kwodEQLG7aAzb8CjhKYDSuD.', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `clerk`
--
ALTER TABLE `clerk`
  ADD PRIMARY KEY (`ClerkID`);

--
-- Indexes for table `community_chat`
--
ALTER TABLE `community_chat`
  ADD PRIMARY KEY (`MessageID`),
  ADD KEY `UserIC` (`UserEmail`),
  ADD KEY `ParentMessageID` (`ParentMessageID`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`FavoriteID`),
  ADD KEY `UserIC` (`UserEmail`),
  ADD KEY `PeriID` (`PeriID`);

--
-- Indexes for table `message_likes`
--
ALTER TABLE `message_likes`
  ADD PRIMARY KEY (`LikeID`),
  ADD UNIQUE KEY `unique_like` (`MessageID`,`UserEmail`),
  ADD KEY `UserIC` (`UserEmail`);

--
-- Indexes for table `message_views`
--
ALTER TABLE `message_views`
  ADD PRIMARY KEY (`UserEmail`);

--
-- Indexes for table `peribahasa`
--
ALTER TABLE `peribahasa`
  ADD PRIMARY KEY (`PeriID`),
  ADD KEY `CategoryID` (`CategoryID`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`QuizID`),
  ADD KEY `ClerkID` (`ClerkID`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`AttemptID`),
  ADD KEY `QuizID` (`QuizID`),
  ADD KEY `idx_user_quiz` (`UserEmail`,`QuizID`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`QuestionID`),
  ADD KEY `idx_quiz` (`QuizID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserEmail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `community_chat`
--
ALTER TABLE `community_chat`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `FavoriteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `message_likes`
--
ALTER TABLE `message_likes`
  MODIFY `LikeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `peribahasa`
--
ALTER TABLE `peribahasa`
  MODIFY `PeriID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `QuizID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `AttemptID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `QuestionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `community_chat`
--
ALTER TABLE `community_chat`
  ADD CONSTRAINT `community_chat_ibfk_2` FOREIGN KEY (`ParentMessageID`) REFERENCES `community_chat` (`MessageID`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_chat_ibfk_3` FOREIGN KEY (`UserEmail`) REFERENCES `user` (`UserEmail`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`PeriID`) REFERENCES `peribahasa` (`PeriID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_3` FOREIGN KEY (`UserEmail`) REFERENCES `user` (`UserEmail`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `message_likes`
--
ALTER TABLE `message_likes`
  ADD CONSTRAINT `message_likes_ibfk_1` FOREIGN KEY (`MessageID`) REFERENCES `community_chat` (`MessageID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_likes_ibfk_2` FOREIGN KEY (`UserEmail`) REFERENCES `user` (`UserEmail`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `peribahasa`
--
ALTER TABLE `peribahasa`
  ADD CONSTRAINT `peribahasa_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `category` (`CategoryID`);

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`ClerkID`) REFERENCES `clerk` (`ClerkID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`QuizID`) REFERENCES `quiz` (`QuizID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`UserEmail`) REFERENCES `user` (`UserEmail`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`QuizID`) REFERENCES `quiz` (`QuizID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
