-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 30 Maj 2009, 15:53
-- Wersja serwera: 5.0.51
-- Wersja PHP: 5.2.6-1+lenny3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `ok6_devel`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `address_book`
--

CREATE TABLE IF NOT EXISTS `address_book` (
  `address_book_id` int(11) NOT NULL auto_increment,
  `customers_id` int(11) NOT NULL,
  `entry_gender` char(1) collate utf8_polish_ci NOT NULL,
  `entry_company` varchar(32) collate utf8_polish_ci default NULL,
  `entry_nip` varchar(32) character set ucs2 collate ucs2_polish_ci NOT NULL,
  `entry_firstname` varchar(32) collate utf8_polish_ci NOT NULL,
  `entry_lastname` varchar(32) collate utf8_polish_ci NOT NULL,
  `entry_street_address` varchar(64) collate utf8_polish_ci NOT NULL,
  `entry_suburb` varchar(32) collate utf8_polish_ci default NULL,
  `entry_postcode` varchar(10) collate utf8_polish_ci NOT NULL,
  `entry_city` varchar(32) collate utf8_polish_ci NOT NULL,
  `entry_state` varchar(32) collate utf8_polish_ci default NULL,
  `entry_country_id` int(11) NOT NULL default '0',
  `entry_zone_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`address_book_id`),
  KEY `idx_address_book_customers_id` (`customers_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `address_book`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `address_format`
--

CREATE TABLE IF NOT EXISTS `address_format` (
  `address_format_id` int(11) NOT NULL auto_increment,
  `address_format` varchar(128) collate utf8_polish_ci NOT NULL,
  `address_summary` varchar(48) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`address_format_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=6 ;

--
-- Zrzut danych tabeli `address_format`
--

INSERT INTO `address_format` (`address_format_id`, `address_format`, `address_summary`) VALUES
(1, '$firstname $lastname$cr$streets$cr$city, $postcode$cr$statecomma$country', '$city / $country'),
(2, '$firstname $lastname$cr$streets$cr$city, $state    $postcode$cr$country', '$city, $state / $country'),
(3, '$firstname $lastname$cr$streets$cr$city$cr$postcode - $statecomma$country', '$state / $country'),
(4, '$firstname $lastname$cr$streets$cr$city ($postcode)$cr$country', '$postcode / $country'),
(5, '$firstname $lastname$cr$streets$cr$postcode $city$cr$country', '$city / $country');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `administrators`
--

CREATE TABLE IF NOT EXISTS `administrators` (
  `id` int(11) NOT NULL auto_increment,
  `user_name` varchar(32) character set utf8 collate utf8_bin NOT NULL,
  `user_password` varchar(40) collate utf8_polish_ci NOT NULL,
  `user_script` varchar(256) collate utf8_polish_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=7 ;

--
-- Zrzut danych tabeli `administrators`
--

INSERT INTO `administrators` (`id`, `user_name`, `user_password`, `user_script`) VALUES
(1, 'admin', '39a89a28169b53c7e58438558adc5e37:7b', 'backup.php?selected_box=tools'),
(3, 'pracownik', '39a89a28169b53c7e58438558adc5e37:7b', 'orders.php?selected_box=customers'),
(2, 'wlasciciel', '39a89a28169b53c7e58438558adc5e37:7b', 'stats_products_viewed.php?selected_box=reports');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `administrators_audyt`
--

CREATE TABLE IF NOT EXISTS `administrators_audyt` (
  `id` int(11) NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ip` varchar(16) collate utf8_polish_ci NOT NULL,
  `hostname` varchar(128) collate utf8_polish_ci NOT NULL,
  `url` varchar(256) collate utf8_polish_ci NOT NULL,
  `adminid` int(11) NOT NULL,
  `type` varchar(8) collate utf8_polish_ci NOT NULL COMMENT 'insert / update / delete',
  `table` varchar(64) collate utf8_polish_ci NOT NULL,
  `query` text collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `administrators_audyt`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `administrators_files`
--

CREATE TABLE IF NOT EXISTS `administrators_files` (
  `id` int(11) NOT NULL auto_increment,
  `file_name` varchar(128) collate utf8_polish_ci NOT NULL,
  `groupid` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `groupid` (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=56 ;

--
-- Zrzut danych tabeli `administrators_files`
--

INSERT INTO `administrators_files` (`id`, `file_name`, `groupid`) VALUES
(1, 'administrators.php', 1),
(2, 'administrators_groups.php', 1),
(3, 'configuration.php', 1),
(4, 'categories.php', 3),
(5, 'products_attributes.php', 4),
(6, 'manufacturers.php', 3),
(7, 'reviews.php', 3),
(8, 'specials.php', 3),
(9, 'products_expected.php', 4),
(10, 'modules.php', 1),
(11, 'customers.php', 3),
(12, 'orders.php', 3),
(13, 'countries.php', 1),
(14, 'zones.php', 1),
(15, 'geo_zones.php', 1),
(16, 'tax_classes.php', 1),
(17, 'tax_rates.php', 1),
(18, 'currencies.php', 1),
(19, 'languages.php', 1),
(20, 'orders_status.php', 1),
(21, 'stats_products_viewed.php', 2),
(22, 'stats_products_purchased.php', 2),
(23, 'stats_customers.php', 2),
(24, 'backup.php', 1),
(25, 'banner_manager.php', 4),
(26, 'cache.php', 4),
(27, 'define_language.php', 4),
(28, 'file_manager.php', 1),
(29, 'mail.php', 1),
(30, 'newsletters.php', 1),
(31, 'server_info.php', 1),
(32, 'whos_online.php', 1),
(33, 'administrators_files.php', 1),
(34, 'stats_administrators_audyt.php', 2),
(35, 'infobox_configuration.php', 1),
(37, 'allegro_configuration.php', 1),
(38, 'allegro_categories.php', 3),
(39, 'allegro_options.php', 3),
(40, 'allegro_sell.php', 3),
(42, 'invoice.php', 3),
(43, 'packingslip.php', 3),
(44, 'seo_product.php', 1),
(45, 'seo_category.php', 1),
(46, 'seo_script.php', 1),
(47, 'seo_global.php', 1),
(48, 'seo.php', 1),
(49, 'product_excel.php', 3),
(50, 'porownywarki_assoc_categories.php', 1),
(51, 'porownywarki_ceneo_pobierz_kategorie.php', 1),
(52, 'porownywarki_import_categories.php', 1),
(53, 'porownywarki_magoo_pobierz_kategorie.php', 1),
(54, 'porownywarki_show_report.php', 1),
(55, 'porownywarki_swistak_pobierz_kategorie.php', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `administrators_groups`
--

CREATE TABLE IF NOT EXISTS `administrators_groups` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(64) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=6 ;

--
-- Zrzut danych tabeli `administrators_groups`
--

INSERT INTO `administrators_groups` (`id`, `group_name`) VALUES
(1, 'Super user'),
(2, 'Właściciel'),
(3, 'Pracownik'),
(4, 'Ukryte funkcje');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `administrator_to_groups`
--

CREATE TABLE IF NOT EXISTS `administrator_to_groups` (
  `id` int(11) NOT NULL auto_increment,
  `adminid` int(11) NOT NULL,
  `groupid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=42 ;

--
-- Zrzut danych tabeli `administrator_to_groups`
--

INSERT INTO `administrator_to_groups` (`id`, `adminid`, `groupid`) VALUES
(41, 2, 3),
(38, 1, 3),
(37, 1, 1),
(40, 2, 2),
(39, 3, 3);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `allegro_configuration`
--

CREATE TABLE IF NOT EXISTS `allegro_configuration` (
  `allegro_configuration_id` int(11) NOT NULL auto_increment,
  `allegro_configuration_group` varchar(16) collate utf8_polish_ci NOT NULL,
  `allegro_configuration_key` varchar(32) collate utf8_polish_ci NOT NULL,
  `allegro_configuration_value` text collate utf8_polish_ci NOT NULL,
  `update_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`allegro_configuration_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=19 ;

--
-- Zrzut danych tabeli `allegro_configuration`
--

INSERT INTO `allegro_configuration` (`allegro_configuration_id`, `allegro_configuration_group`, `allegro_configuration_key`, `allegro_configuration_value`, `update_timestamp`) VALUES
(1, 'configuration', 'posrednikLogin', 'multishop', '2009-02-20 16:50:25'),
(2, 'configuration', 'posrednikPassword', 'multishop', '2009-02-20 16:50:25'),
(3, 'configuration', 'allegroWebApiCode', '93485b00b9', '2009-02-20 16:51:07'),
(4, 'configuration', 'testwebapiLogin', 'Walaszek-pl', '2009-02-20 16:51:50'),
(5, 'configuration', 'testwebapiPassword', 'reksio126', '2009-02-20 16:51:50'),
(6, 'configuration', 'allegroDefaultComment', 'Wszystko OK! Dziękuję i pozdrawiam.', '2009-02-21 10:50:35'),
(7, 'categories_id', '2', '1840', '2009-02-20 17:16:18'),
(8, 'categories_id', '6', '1836', '2009-02-20 17:16:31'),
(9, 'categories_id', '5', '1839', '2009-02-20 17:16:40'),
(10, 'options', '228/4', '0', '2009-02-20 17:24:41'),
(11, 'options', '228/10', '214', '2009-02-20 17:24:46'),
(12, 'options', '228/11', 'NeverCity', '2009-02-20 17:24:59'),
(13, 'options', '228/12', '1', '2009-02-20 17:25:06'),
(14, 'options', '228/13', '1,2,4,8,16,32', '2009-02-20 17:25:17'),
(15, 'options', '228/14', '1,2,4,8,16', '2009-02-20 17:25:24'),
(16, 'options', '228/25', '15', '2009-02-20 17:25:35'),
(17, 'options', '228/26', '25', '2009-02-20 17:25:39'),
(18, 'options', '228/27', 'A za granice jeszcze drożej...', '2009-02-20 17:25:59');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `banners`
--

CREATE TABLE IF NOT EXISTS `banners` (
  `banners_id` int(11) NOT NULL auto_increment,
  `banners_title` varchar(64) collate utf8_polish_ci NOT NULL,
  `banners_url` varchar(255) collate utf8_polish_ci NOT NULL,
  `banners_image` varchar(64) collate utf8_polish_ci NOT NULL,
  `banners_group` varchar(10) collate utf8_polish_ci NOT NULL,
  `banners_html_text` text collate utf8_polish_ci,
  `expires_impressions` int(7) default '0',
  `expires_date` datetime default NULL,
  `date_scheduled` datetime default NULL,
  `date_added` datetime NOT NULL,
  `date_status_change` datetime default NULL,
  `status` int(1) NOT NULL default '1',
  PRIMARY KEY  (`banners_id`),
  KEY `idx_banners_group` (`banners_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `banners`
--

INSERT INTO `banners` (`banners_id`, `banners_title`, `banners_url`, `banners_image`, `banners_group`, `banners_html_text`, `expires_impressions`, `expires_date`, `date_scheduled`, `date_added`, `date_status_change`, `status`) VALUES
(1, 'osCommerce', 'http://www.oscommerce.com', 'banners/oscommerce.gif', '468x50', '', 0, NULL, NULL, '2008-12-12 19:02:38', NULL, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `banners_history`
--

CREATE TABLE IF NOT EXISTS `banners_history` (
  `banners_history_id` int(11) NOT NULL auto_increment,
  `banners_id` int(11) NOT NULL,
  `banners_shown` int(5) NOT NULL default '0',
  `banners_clicked` int(5) NOT NULL default '0',
  `banners_history_date` datetime NOT NULL,
  PRIMARY KEY  (`banners_history_id`),
  KEY `idx_banners_history_banners_id` (`banners_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=52 ;

--
-- Zrzut danych tabeli `banners_history`
--

INSERT INTO `banners_history` (`banners_history_id`, `banners_id`, `banners_shown`, `banners_clicked`, `banners_history_date`) VALUES
(1, 1, 140, 1, '2008-12-12 19:08:25'),
(2, 1, 34, 0, '2008-12-13 00:32:10'),
(3, 1, 390, 0, '2008-12-18 11:59:32'),
(4, 1, 1, 0, '2008-12-20 12:53:53'),
(5, 1, 93, 0, '2008-12-24 10:13:15'),
(6, 1, 3, 0, '2008-12-27 16:46:43'),
(7, 1, 5, 0, '2009-01-02 12:21:35'),
(8, 1, 2, 0, '2009-01-04 15:18:23'),
(9, 1, 1, 0, '2009-01-05 20:35:24'),
(10, 1, 30, 0, '2009-01-07 12:42:23'),
(11, 1, 1, 0, '2009-01-09 15:16:47'),
(12, 1, 5, 0, '2009-01-10 19:35:30'),
(13, 1, 138, 0, '2009-01-14 12:07:54'),
(14, 1, 2, 0, '2009-01-16 12:55:02'),
(15, 1, 3, 0, '2009-01-17 11:06:07'),
(16, 1, 1, 0, '2009-01-21 10:11:03'),
(17, 1, 1, 0, '2009-01-24 13:19:35'),
(18, 1, 6, 0, '2009-01-30 16:07:31'),
(19, 1, 5, 0, '2009-01-31 14:41:06'),
(20, 1, 1, 0, '2009-02-01 21:41:01'),
(21, 1, 10, 0, '2009-02-02 10:49:27'),
(22, 1, 8, 0, '2009-02-06 10:54:59'),
(23, 1, 14, 0, '2009-02-07 17:39:03'),
(24, 1, 1, 0, '2009-02-13 17:29:11'),
(25, 1, 2, 0, '2009-02-14 13:05:42'),
(26, 1, 20, 0, '2009-02-15 12:04:45'),
(27, 1, 1, 0, '2009-02-18 12:11:46'),
(28, 1, 3, 0, '2009-02-20 16:39:13'),
(29, 1, 18, 0, '2009-02-21 11:27:01'),
(30, 1, 1, 0, '2009-02-23 00:10:15'),
(31, 1, 14, 0, '2009-02-26 15:58:31'),
(32, 1, 1, 0, '2009-02-27 11:41:09'),
(33, 1, 4, 0, '2009-03-01 22:37:30'),
(34, 1, 46, 0, '2009-03-03 11:14:56'),
(35, 1, 2, 0, '2009-03-04 16:13:15'),
(36, 1, 1, 0, '2009-03-05 18:57:18'),
(37, 1, 1, 0, '2009-03-06 10:33:42'),
(38, 1, 2, 0, '2009-03-09 00:05:17'),
(39, 1, 1, 0, '2009-03-10 07:28:48'),
(40, 1, 18, 0, '2009-03-11 12:28:40'),
(41, 1, 18, 0, '2009-03-14 19:49:22'),
(42, 1, 2, 0, '2009-03-16 11:03:20'),
(43, 1, 10, 0, '2009-03-19 01:54:52'),
(44, 1, 6, 0, '2009-03-22 22:43:49'),
(45, 1, 3, 0, '2009-03-24 22:08:12'),
(46, 1, 3, 0, '2009-03-25 01:15:12'),
(47, 1, 4, 0, '2009-03-30 18:26:54'),
(48, 1, 1, 0, '2009-05-08 00:43:15'),
(49, 1, 1, 0, '2009-05-12 16:21:18'),
(50, 1, 22, 0, '2009-05-22 23:33:00'),
(51, 1, 19, 0, '2009-05-30 15:17:08');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `categories_id` int(11) NOT NULL auto_increment,
  `categories_image` varchar(64) collate utf8_polish_ci default NULL,
  `parent_id` int(11) NOT NULL default '0',
  `sort_order` int(3) default NULL,
  `date_added` datetime default NULL,
  `last_modified` datetime default NULL,
  PRIMARY KEY  (`categories_id`),
  KEY `idx_categories_parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=21 ;

--
-- Zrzut danych tabeli `categories`
--

INSERT INTO `categories` (`categories_id`, `categories_image`, `parent_id`, `sort_order`, `date_added`, `last_modified`) VALUES
(1, 'category_hardware.gif', 0, 1, '2008-12-12 19:02:38', '2009-01-04 19:21:21'),
(2, 'category_software.gif', 0, 2, '2008-12-12 19:02:38', NULL),
(3, 'category_dvd_movies.gif', 0, 3, '2008-12-12 19:02:38', NULL),
(4, 'subcategory_graphic_cards.gif', 1, 0, '2008-12-12 19:02:38', NULL),
(5, 'subcategory_printers.gif', 1, 0, '2008-12-12 19:02:38', NULL),
(6, 'subcategory_monitors.gif', 1, 0, '2008-12-12 19:02:38', NULL),
(7, 'subcategory_speakers.gif', 1, 0, '2008-12-12 19:02:38', '2008-12-12 21:15:07'),
(8, 'subcategory_keyboards.gif', 1, 0, '2008-12-12 19:02:38', NULL),
(9, 'subcategory_mice.gif', 1, 0, '2008-12-12 19:02:38', NULL),
(10, 'subcategory_action.gif', 3, 0, '2008-12-12 19:02:38', NULL),
(11, 'subcategory_science_fiction.gif', 3, 0, '2008-12-12 19:02:38', NULL),
(12, 'subcategory_comedy.gif', 3, 0, '2008-12-12 19:02:38', NULL),
(13, 'subcategory_cartoons.gif', 3, 0, '2008-12-12 19:02:38', '2008-12-12 21:15:42'),
(14, 'subcategory_thriller.gif', 3, 0, '2008-12-12 19:02:38', NULL),
(15, 'subcategory_drama.gif', 3, 0, '2008-12-12 19:02:38', NULL),
(16, 'subcategory_memory.gif', 1, 0, '2008-12-12 19:02:38', NULL),
(17, 'subcategory_cdrom_drives.gif', 1, 0, '2008-12-12 19:02:38', NULL),
(18, 'subcategory_simulation.gif', 2, 0, '2008-12-12 19:02:38', NULL),
(19, 'subcategory_action_games.gif', 2, 0, '2008-12-12 19:02:38', NULL),
(20, 'subcategory_strategy.gif', 2, 0, '2008-12-12 19:02:38', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `categories_description`
--

CREATE TABLE IF NOT EXISTS `categories_description` (
  `categories_id` int(11) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '1',
  `categories_name` varchar(32) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`categories_id`,`language_id`),
  KEY `idx_categories_name` (`categories_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `categories_description`
--

INSERT INTO `categories_description` (`categories_id`, `language_id`, `categories_name`) VALUES
(20, 1, 'Strategie'),
(19, 1, 'Gry akcji'),
(1, 1, 'Sprzęt'),
(2, 1, 'Oprogramowanie'),
(3, 1, 'Filmy DVD'),
(4, 1, 'Karty graficzne'),
(5, 1, 'Drukarki'),
(6, 1, 'Monitory'),
(7, 1, 'Głośniki'),
(8, 1, 'Klawiatury'),
(9, 1, 'Myszki'),
(10, 1, 'Filmy akcji'),
(11, 1, 'Filmy SF'),
(12, 1, 'Komedie'),
(13, 1, 'Kreskówki'),
(14, 1, 'Dreszczowce'),
(15, 1, 'Dramat'),
(16, 1, 'Memory'),
(17, 1, 'CD-ROM'),
(18, 1, 'Symulacje');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `configuration`
--

CREATE TABLE IF NOT EXISTS `configuration` (
  `configuration_id` int(11) NOT NULL auto_increment,
  `configuration_title` varchar(255) collate utf8_polish_ci NOT NULL,
  `configuration_key` varchar(255) collate utf8_polish_ci NOT NULL,
  `configuration_value` varchar(255) collate utf8_polish_ci NOT NULL,
  `configuration_description` varchar(255) collate utf8_polish_ci NOT NULL,
  `configuration_group_id` int(11) NOT NULL,
  `sort_order` int(5) default NULL,
  `last_modified` datetime default NULL,
  `date_added` datetime NOT NULL,
  `use_function` varchar(255) collate utf8_polish_ci default NULL,
  `set_function` varchar(255) collate utf8_polish_ci default NULL,
  PRIMARY KEY  (`configuration_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=483 ;

--
-- Zrzut danych tabeli `configuration`
--

INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES
(483, 'Duży Obrazek Szerokość (powiększenie)', 'MAX_IMAGE_WIDTH', '640', 'Szerokość w pikselach (small images)', 4, 9, NULL, '2008-12-12 19:02:38', NULL, NULL);
INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES
(484, 'Duży Obrazek Wysokość (powiększenie)', 'MAX_IMAGE_HEIGHT', '480', 'Wysokość w pikselach (small images)', 4, 10, NULL, '2008-12-12 19:02:38', NULL, NULL);
INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES
(514, 'Pokaż puste kategorie', 'SHOW_EMPTY_CATEGORIES', 'false', 'Wpisz true jeżeli chcesz pokazywać puste kategorie', 1, 19, '2009-05-30 20:26:55', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), ');
INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES
(1, 'Nazwa Sklepu', 'STORE_NAME', 'eurola', 'Nazwa Sklepu', 1, 1, '2009-03-01 20:50:16', '2008-12-12 19:02:38', NULL, NULL),
(2, 'Właściciel Sklepu', 'STORE_OWNER', 'Nazwa właściciela sklepu', 'Nazwa właściciela Sklepu', 1, 2, '2008-12-18 12:09:25', '2008-12-12 19:02:38', NULL, NULL),
(3, 'Adres E-Mail', 'STORE_OWNER_EMAIL_ADDRESS', 'pawel@walaszek.pl', 'E-mail Właściciela Sklepu', 1, 3, NULL, '2008-12-12 19:02:38', NULL, NULL),
(4, 'E-Mail - Od kogo', 'EMAIL_FROM', '"Nazwa sklepu" <pawel@walaszek.pl>', 'E-mail address używany w (wysyłanych) e-mailach', 1, 4, '2008-12-18 12:09:42', '2008-12-12 19:02:38', NULL, NULL),
(5, 'Państwo', 'STORE_COUNTRY', '170', 'Kraj w którym znajduje się sklep to: <br><br><b>Proszę pamiętać o aktualizacji strefy</b>.</b>', 1, 6, '2008-12-12 22:14:38', '2008-12-12 19:02:38', 'tep_get_country_name', 'tep_cfg_pull_down_country_list('),
(6, 'Strefa', 'STORE_ZONE', '202', 'Strefa w której znajduje się sklep', 1, 7, '2008-12-12 22:14:47', '2008-12-12 19:02:38', 'tep_cfg_get_zone_name', 'tep_cfg_pull_down_zone_list('),
(7, 'Oczekiwane - Porządek Sortowania', 'EXPECTED_PRODUCTS_SORT', 'desc', 'Porządek sortowania dla boxu produktów oczekujących.', 1, 8, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''asc'', ''desc''), '),
(8, 'Oczekiwane - Pola do Sortowanie', 'EXPECTED_PRODUCTS_FIELD', 'products_name', 'Kolumna według której mają być sortowane produkty oczekujące.', 1, 9, '2008-12-18 12:10:57', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''products_name'', ''date_expected''), '),
(9, 'Przełącz na Domyślną Walute Języka', 'USE_DEFAULT_LANGUAGE_CURRENCY', 'true', 'Przełącz automatycznie walutę podczas zmiany języka', 1, 10, '2008-12-18 12:10:45', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(10, 'Wyślij dodatkowe maile z zamówieniem do', 'SEND_EXTRA_ORDER_EMAILS_TO', '', 'Wyślij dodatkowy mail z zamówieniem na adres, w tym formacie: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', 1, 11, NULL, '2008-12-12 19:02:38', NULL, NULL),
(11, 'Use Search-Engine Safe URLs (still in development)', 'SEARCH_ENGINE_FRIENDLY_URLS', 'false', 'Use search-engine safe urls for all site links', 1, 12, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(12, 'Pokaż koszyk po dodaniu produktu', 'DISPLAY_CART', 'true', 'Wyświetl koszyk po dodaniu do niego produktu (lub powróć do ostatniej strony)', 1, 14, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(13, 'Pozwól gościom powiadamiać znajomych', 'ALLOW_GUEST_TO_TELL_A_FRIEND', 'false', 'Pozwól gościom informować znajomych o produktach', 1, 15, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(14, 'Domyślny operator wyszukiwania', 'ADVANCED_SEARCH_DEFAULT_OPERATOR', 'and', 'Domyślny operator wyszukiwarki', 1, 17, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''and'', ''or''), '),
(15, 'Adres Sklepu i numer telefonu', 'STORE_NAME_ADDRESS', 'Store Name\nAddress\nCountry\nPhone', 'Tu wstaw nazwę firmy, adres, numer telefonu, dane te będą widoczne na drukowalnych dokumentach oraz online', 1, 18, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_textarea('),
(16, 'Pokaż liczebność kategorii', 'SHOW_COUNTS', 'true', 'Wpisz true jeżeli chcesz pokazywać liczebność kategorii', 1, 19, '2009-03-12 16:16:22', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(17, 'Podatek ilość miejsc', 'TAX_DECIMAL_PLACES', '0', 'Ilość miejsc po przecinu dla podatku', 1, 20, NULL, '2008-12-12 19:02:38', NULL, NULL),
(18, 'Pokazuj ceny z podatkiem', 'DISPLAY_PRICE_WITH_TAX', 'true', 'Wyświetlaj Ceny z podatkiem (true) lub dodaj podatek na końcu (false)', 1, 21, '2008-12-12 22:16:20', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(19, 'Imię', 'ENTRY_FIRST_NAME_MIN_LENGTH', '2', 'Minimalna ilość znaków dla Imienia', 2, 1, NULL, '2008-12-12 19:02:38', NULL, NULL),
(20, 'Nazwisko', 'ENTRY_LAST_NAME_MIN_LENGTH', '2', 'Minimalna ilość znaków dla nazwiska', 2, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(21, 'Data urodzenia', 'ENTRY_DOB_MIN_LENGTH', '10', 'Minimalna ilość znaków dla danty urodzenia', 2, 3, NULL, '2008-12-12 19:02:38', NULL, NULL),
(22, 'E-Mail Adres', 'ENTRY_EMAIL_ADDRESS_MIN_LENGTH', '6', 'Minimalna ilość znaków dla adresu e-mail', 2, 4, NULL, '2008-12-12 19:02:38', NULL, NULL),
(23, 'Ulica', 'ENTRY_STREET_ADDRESS_MIN_LENGTH', '5', 'Minimalna ilość znaków dla ulicy', 2, 5, NULL, '2008-12-12 19:02:38', NULL, NULL),
(24, 'Firma', 'ENTRY_COMPANY_MIN_LENGTH', '2', 'minimalna ilosc znaków dla nazwy firmy', 2, 6, NULL, '2008-12-12 19:02:38', NULL, NULL),
(25, 'Kod pocztowy', 'ENTRY_POSTCODE_MIN_LENGTH', '4', 'Minimalna liczba znaków dla kodu pocztowego', 2, 7, NULL, '2008-12-12 19:02:38', NULL, NULL),
(26, 'Miasto', 'ENTRY_CITY_MIN_LENGTH', '3', 'Minimalna liczba znaków dla miasta', 2, 8, NULL, '2008-12-12 19:02:38', NULL, NULL),
(27, 'Wojewódzwo', 'ENTRY_STATE_MIN_LENGTH', '2', 'Minimalna liczba znaków dla województwa', 2, 9, NULL, '2008-12-12 19:02:38', NULL, NULL),
(28, 'Numer telefonu', 'ENTRY_TELEPHONE_MIN_LENGTH', '3', 'Minimalna liczna znaków dla numeru telefonu', 2, 10, NULL, '2008-12-12 19:02:38', NULL, NULL),
(29, 'Hasło', 'ENTRY_PASSWORD_MIN_LENGTH', '5', 'Minimalna długość hasła', 2, 11, NULL, '2008-12-12 19:02:38', NULL, NULL),
(30, 'Imię Właściciela Karty', 'CC_OWNER_MIN_LENGTH', '3', 'Minimalna długość Imienia posadacza karty kredytowej', 2, 12, NULL, '2008-12-12 19:02:38', NULL, NULL),
(31, 'Numer Karty kredytowej', 'CC_NUMBER_MIN_LENGTH', '10', 'Minimalna liczba znaków dla numeru karty kredytowej', 2, 13, NULL, '2008-12-12 19:02:38', NULL, NULL),
(32, 'Opinie ilość znakow', 'REVIEW_TEXT_MIN_LENGTH', '50', 'Minimalna długość opinii', 2, 14, NULL, '2008-12-12 19:02:38', NULL, NULL),
(33, 'Best Sellers', 'MIN_DISPLAY_BESTSELLERS', '1', 'Minimalna liczba bestsellerów do wyświetlenia', 2, 15, NULL, '2008-12-12 19:02:38', NULL, NULL),
(34, 'Kupili także', 'MIN_DISPLAY_ALSO_PURCHASED', '1', 'Minimalna liczba wyświetlanych produktów dla ''Klient kupił także'' box', 2, 16, NULL, '2008-12-12 19:02:38', NULL, NULL),
(35, 'Wpisy w Książce Adresowej', 'MAX_ADDRESS_BOOK_ENTRIES', '5', 'Maksymalna liczba wpisów do książki adresowej', 3, 1, NULL, '2008-12-12 19:02:38', NULL, NULL),
(36, 'Wyniki wyszukiwania', 'MAX_DISPLAY_SEARCH_RESULTS', '20', 'Liczba wynikow wyszukiwania', 3, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(37, 'Linki Strony', 'MAX_DISPLAY_PAGE_LINKS', '5', 'Liczba ''number'' linków dla strony', 3, 3, NULL, '2008-12-12 19:02:38', NULL, NULL),
(38, 'Promocje', 'MAX_DISPLAY_SPECIAL_PRODUCTS', '9', 'Maksymalna liczba produktów w Promocji do wyświetlenia', 3, 4, NULL, '2008-12-12 19:02:38', NULL, NULL),
(39, 'Moduł nowych produktów', 'MAX_DISPLAY_NEW_PRODUCTS', '9', 'Maksymalna liczba nowych produktów do wyświetlenia w kategorii', 3, 5, NULL, '2008-12-12 19:02:38', NULL, NULL),
(40, 'Produkty oczekujące', 'MAX_DISPLAY_UPCOMING_PRODUCTS', '10', 'Maksymalna liczba produktów oczekiwanych', 3, 6, NULL, '2008-12-12 19:02:38', NULL, NULL),
(41, 'Producenci', 'MAX_DISPLAY_MANUFACTURERS_IN_A_LIST', '0', 'Maksymalna liczba producentów wyświetlanych w boksie producenci, jeżeli liczba producentów przekroczy ta wartość, zostaną oni wyświetleni w liście rozwijanej', 3, 7, NULL, '2008-12-12 19:02:38', NULL, NULL),
(42, 'Wybór Producenta', 'MAX_MANUFACTURERS_LIST', '1', 'Lista producentów; jeżeli wartość będzie ''1'' producenci będą wyświetleni w liście rozwijanej. Inaczej, zostanie wyświetlona lista z określoną liczbą wierszy.', 3, 7, NULL, '2008-12-12 19:02:38', NULL, NULL),
(43, 'Ilość zanków w nazwie producenta', 'MAX_DISPLAY_MANUFACTURER_NAME_LEN', '15', 'Box producentów; maksymalna długość nazwy producenta do wyświetlenia', 3, 8, NULL, '2008-12-12 19:02:38', NULL, NULL),
(44, 'Nowe Opinie', 'MAX_DISPLAY_NEW_REVIEWS', '6', 'Maksymalna liczba nowych opini do wyświetlenia', 3, 9, NULL, '2008-12-12 19:02:38', NULL, NULL),
(45, 'Losowy wybór opinii', 'MAX_RANDOM_SELECT_REVIEWS', '10', 'Z ilu ostatnich rekordów mają być wybierane losowe recenzje', 3, 10, NULL, '2008-12-12 19:02:38', NULL, NULL),
(46, 'Losowy wybór nowych produktów', 'MAX_RANDOM_SELECT_NEW', '10', 'Z ilu ostatnich rekordów mają być wybierane nowe produkty do wyświetlenia', 3, 11, NULL, '2008-12-12 19:02:38', NULL, NULL),
(47, 'Losowy wybór Promocji', 'MAX_RANDOM_SELECT_SPECIALS', '10', 'Z ilu rekordów mają być wybierane Promocje do wyświetlenia', 3, 12, NULL, '2008-12-12 19:02:38', NULL, NULL),
(48, 'Liczba kategorii w wierszu', 'MAX_DISPLAY_CATEGORIES_PER_ROW', '3', 'Ile kategorii wyświetlić w jednym wierszu?', 3, 13, NULL, '2008-12-12 19:02:38', NULL, NULL),
(49, 'Nowe Produkty - Liczba', 'MAX_DISPLAY_PRODUCTS_NEW', '10', 'Maksymalna liczba nowych produktów wyświetlanych na stronie', 3, 14, NULL, '2008-12-12 19:02:38', NULL, NULL),
(50, 'Best Sellers', 'MAX_DISPLAY_BESTSELLERS', '10', 'Maksymalna liczba bestsellerów wyświetlanych na stronie', 3, 15, NULL, '2008-12-12 19:02:38', NULL, NULL),
(51, 'Polecane - Liczba', 'MAX_DISPLAY_ALSO_PURCHASED', '6', 'Maksymalna liczba produktów w boksie polecane ''This Customer Also Purchased'' box', 3, 16, NULL, '2008-12-12 19:02:38', NULL, NULL),
(52, 'Box historii zamówień klienta', 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', '6', 'Maksymalna liczba produktów wyświetlana w boksie historia zamówień', 3, 17, NULL, '2008-12-12 19:02:38', NULL, NULL),
(53, 'Historia zamówień', 'MAX_DISPLAY_ORDER_HISTORY', '10', 'Maksymalna liczba produktów wyświetlana na stronie historii zamówień', 3, 18, NULL, '2008-12-12 19:02:38', NULL, NULL),
(54, 'Product Quantities In Shopping Cart', 'MAX_QTY_IN_CART', '0', 'Maximum number of product quantities that can be added to the shopping cart (0 for no limit)', 3, 19, '2008-12-12 22:17:44', '2008-12-12 19:02:38', NULL, NULL),
(55, 'Mały Obrazek Szerokość (Lista Produktów)', 'SMALL_IMAGE_WIDTH', '100', 'Szerokość w pikselach (small images)', 4, 1, NULL, '2008-12-12 19:02:38', NULL, NULL),
(56, 'Mały Obrazek Wysokość (Lista Produktów)', 'SMALL_IMAGE_HEIGHT', '80', 'Wysokość w pikselach (small images)', 4, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(57, 'Główny Obrazek Szerokość', 'HEADING_IMAGE_WIDTH', '57', 'Szerokość w pikselach (heading images)', 4, 3, NULL, '2008-12-12 19:02:38', NULL, NULL),
(58, 'Główny Obrazek Wysokość', 'HEADING_IMAGE_HEIGHT', '40', 'Wyskość w pikselach (heading images)', 4, 4, NULL, '2008-12-12 19:02:38', NULL, NULL),
(59, 'Szerokość Obrazka Podkategorii', 'SUBCATEGORY_IMAGE_WIDTH', '100', 'Szerokość w pikselach (subcategory images)', 4, 5, NULL, '2008-12-12 19:02:38', NULL, NULL),
(60, 'Wysokość Obrazka Podkategorii', 'SUBCATEGORY_IMAGE_HEIGHT', '57', 'Wysokość w pikselach (subcategory images)', 4, 6, NULL, '2008-12-12 19:02:38', NULL, NULL),
(61, 'Przelicz Rozmiar Obraazka', 'CONFIG_CALCULATE_IMAGE_SIZE', 'true', 'Przelicz rozmiar obrazków?', 4, 7, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(62, 'Obrazek Wymagany', 'IMAGE_REQUIRED', 'true', 'Czy obrazek jest wymagany?', 4, 8, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(63, 'Płeć', 'ACCOUNT_GENDER', 'false', 'Pokaż płeć w danych konta do klienta', 5, 1, '2008-12-12 19:53:32', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(64, 'Data urodzenia', 'ACCOUNT_DOB', 'false', 'Pokaż datę urodzenia w danych do konta klienta', 5, 2, '2008-12-12 19:53:27', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(65, 'Firma', 'ACCOUNT_COMPANY', 'true', 'Pokaż nazwę firmy w danych do konta klienta', 5, 3, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(66, 'Dzielnica', 'ACCOUNT_SUBURB', 'false', 'Pokaż dzielnicę w danych do konta klienta', 5, 4, '2008-12-18 17:08:38', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(67, 'Województwo', 'ACCOUNT_STATE', 'true', 'Pokaż województwo w danych do konta klienta', 5, 5, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(68, 'Zainstalowane Moduły - Płatność', 'MODULE_PAYMENT_INSTALLED', 'advance.php;cod.php;ecard.php;przelewy24.php', 'Lista modułów płatności (nazwy plików) to pole jest automatycznie aktualizowane nie wymaga edycji.(Przykład: cc.php;cod.php;paypal.php)', 6, 0, '2009-02-18 13:08:18', '2008-12-12 19:02:38', NULL, NULL),
(69, 'Zainstalowane Moduły - Suma Zamówienia', 'MODULE_ORDER_TOTAL_INSTALLED', 'ot_subtotal.php;ot_shipping.php;ot_tax.php;ot_total.php', 'Lista modułu Suma Zamówienia, aktualizowana jest automatycznie i nie wymaga edycji (Przykład: ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php)', 6, 0, '2008-12-24 11:19:05', '2008-12-12 19:02:38', NULL, NULL),
(70, 'Zainstalowane Moduły - Wysyłka', 'MODULE_SHIPPING_INSTALLED', 'dp.php;flat.php;dp2.php;flat2.php;item.php;pickup.php;table.php', 'Lista modułów wysyłki, jest aktualizowana automatycznie akualizowana i nie wymaga edycji. (Przykład: ups.php;flat.php;item.php)', 6, 0, '2008-12-18 21:31:36', '2008-12-12 19:02:38', NULL, NULL),
(455, 'Domyślny status zamówienia', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0', 'Ustaw status zamówień realizowanych tą formą płatności', 6, 0, NULL, '2008-12-18 22:12:06', 'tep_get_order_status_name', 'tep_cfg_pull_down_order_statuses('),
(452, 'Włączony', 'MODULE_PAYMENT_COD_STATUS', 'True', 'Włącza (true) lub nie (false) moduł płatności.', 6, 1, NULL, '2008-12-18 22:12:06', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(453, 'Strefa Płatności', 'MODULE_PAYMENT_COD_ZONE', '0', 'Jeżeli wybrano strefę ten rodzaj płatności będzie aktywny tylko dla niej.', 6, 2, NULL, '2008-12-18 22:12:06', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(75, 'Enable Credit Card Module', 'MODULE_PAYMENT_CC_STATUS', 'True', 'Do you want to accept credit card payments?', 6, 0, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(76, 'Split Credit Card E-Mail Address', 'MODULE_PAYMENT_CC_EMAIL', '', 'If an e-mail address is entered, the middle digits of the credit card number will be sent to the e-mail address (the outside digits are stored in the database with the middle digits censored)', 6, 0, NULL, '2008-12-12 19:02:38', NULL, NULL),
(77, 'Sort order of display.', 'MODULE_PAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', 6, 0, NULL, '2008-12-12 19:02:38', NULL, NULL),
(78, 'Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 2, NULL, '2008-12-12 19:02:38', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(79, 'Set Order Status', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', 6, 0, NULL, '2008-12-12 19:02:38', 'tep_get_order_status_name', 'tep_cfg_pull_down_order_statuses('),
(317, 'Włącz pobranie dla płatności', 'MODULE_PAYMENT_POBRANIEPP_TYPE', 'cod', 'Rodzaj platnosci dla naliczania oplaty', 6, 7, NULL, '2008-12-18 18:31:16', NULL, NULL),
(318, 'Wlacz pobranie dla wysylki', 'MODULE_PAYMENT_POBRANIEPP_S_TYPE', 'flat', 'Rodzaj wysyłki, dla której naliczana jest opłata', 6, 8, NULL, '2008-12-18 18:31:16', NULL, NULL),
(316, 'Minimalna wartosc', 'MODULE_PAYMENT_POBRANIEPP_MINIMUM', '', 'Minimalna wartosc zamowienia dla naliczania oplaty', 6, 2, NULL, '2008-12-18 18:31:16', NULL, NULL),
(311, 'Pokazuj', 'MODULE_PAYMENT_POBRANIEPP_STATUS', 'tak', 'Czy chcesz wlaczyc oplate za Pobranie?', 6, 1, NULL, '2008-12-18 18:31:16', NULL, 'tep_cfg_select_option(array(''tak'', ''nie''), '),
(85, 'Domyślna Waluta', 'DEFAULT_CURRENCY', 'PLN', 'Domyślna waluta', 6, 0, NULL, '2008-12-12 19:02:38', NULL, NULL),
(86, 'Domyślny Język', 'DEFAULT_LANGUAGE', 'pl', 'Domyślny język', 6, 0, NULL, '2008-12-12 19:02:38', NULL, NULL),
(87, 'Domyślny status Zamówienia', 'DEFAULT_ORDERS_STATUS_ID', '4', 'Po złożeniu nowego zamówienia otrzyma ono taki status.', 6, 0, NULL, '2008-12-12 19:02:38', NULL, NULL),
(88, 'Wyświetlanie Wysyłki', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'Czy chcesz wyświetlić koszt wysyłki?', 6, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(89, 'Kolejność', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '2', 'Kolejność.', 6, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(90, 'Pozwól na Darmową Wysyłkę', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Czy chcesz zezwolić na bepłatną wysyłkę?', 6, 3, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(91, 'Darmowa Wysyłka Powyżej Kwoty Zamówienia', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Powyżej tej kwoty wysyłka będzie darmowa.', 6, 4, NULL, '2008-12-12 19:02:38', 'currencies->format', NULL),
(92, 'Darmowa Przesyłka Dla Wysyłek', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Dla takiego rodzaju przesyłek opcja będzie dostępna.', 6, 5, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''national'', ''international'', ''both''), '),
(93, 'Wyświetlaj Podsumę', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 'Czy chcesz wyświetlić podsumę zamówienia?', 6, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(94, 'Kolejność', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '1', 'Kolejność sortowania.', 6, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(95, 'Pokazuj Podatek', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 'Czy chcesz pokazać wartość podatku?', 6, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(96, 'Kolejność', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '3', 'Kolejność sortowania.', 6, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(97, 'Pokazuj Sumę Zamówienia', 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 'Czy chcesz pokazać sumę zamówienia?', 6, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(98, 'Kolejność', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '4', 'Kolejność.', 6, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(99, 'Państwo Wysyłki', 'SHIPPING_ORIGIN_COUNTRY', '170', 'Wybierz Państwo do objęcia stawkami wysyłki.', 7, 1, '2008-12-12 19:52:43', '2008-12-12 19:02:38', 'tep_get_country_name', 'tep_cfg_pull_down_country_list('),
(100, 'Kod Pocztowy', 'SHIPPING_ORIGIN_ZIP', 'NONE', 'Wybierz kod pocztowy do objęcia stawkami wysyłki.', 7, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(101, 'Maksymalna Waga Przesyłanej Paczki', 'SHIPPING_MAX_WEIGHT', '50', 'Podaj jaką wagę przesyłki maksymalnie możesz wysłać.', 7, 3, NULL, '2008-12-12 19:02:38', NULL, NULL),
(102, 'Tara.', 'SHIPPING_BOX_WEIGHT', '3', 'Jaka jest waga typowych małych i średnich paczek?', 7, 4, NULL, '2008-12-12 19:02:38', NULL, NULL),
(103, 'Więkssze Paczki Opłaty Procentowe.', 'SHIPPING_BOX_PADDING', '10', 'dla 10% wstaw 10', 7, 5, NULL, '2008-12-12 19:02:38', NULL, NULL),
(104, 'Pokaż Obrazek Produktu', 'PRODUCT_LIST_IMAGE', '1', 'Czy chcesz wyświetlać obrazek produktu?', 8, 1, NULL, '2008-12-12 19:02:38', NULL, NULL),
(105, 'Pokaż Producenta', 'PRODUCT_LIST_MANUFACTURER', '0', 'Czy chcesz pokazać nazwę producenta?', 8, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(106, 'Pokaż Model', 'PRODUCT_LIST_MODEL', '0', 'Czy chcesz pokazać model?', 8, 3, NULL, '2008-12-12 19:02:38', NULL, NULL),
(107, 'Pokaż Nazwę Produktu', 'PRODUCT_LIST_NAME', '2', 'Czy chcesz pokazać nazwę produktu?', 8, 4, NULL, '2008-12-12 19:02:38', NULL, NULL),
(108, 'Pokaż Cenę Produktu', 'PRODUCT_LIST_PRICE', '3', 'Czy chcesz pokazać cenę produktu?', 8, 5, NULL, '2008-12-12 19:02:38', NULL, NULL),
(109, 'Pokaż Liczbę Produktów', 'PRODUCT_LIST_QUANTITY', '0', 'Czy chcesz pokazać cenę produktu?', 8, 6, NULL, '2008-12-12 19:02:38', NULL, NULL),
(110, 'Pokaż Wagę Produktów', 'PRODUCT_LIST_WEIGHT', '0', 'Czy chcesz pokazać wagę produktów?', 8, 7, NULL, '2008-12-12 19:02:38', NULL, NULL),
(111, 'Pokazuj Kolumnę Kup teraz', 'PRODUCT_LIST_BUY_NOW', '4', 'Czy chcesz pokazać kolumnę kup teraz?', 8, 8, NULL, '2008-12-12 19:02:38', NULL, NULL),
(112, 'Pokaż Kategorie/Filtr Producentów (0=disable; 1=enable)', 'PRODUCT_LIST_FILTER', '1', 'Czy chcesz pokazać filtr kategoria/producent?', 8, 9, NULL, '2008-12-12 19:02:38', NULL, NULL),
(113, 'Location of Prev/Next Navigation Bar (1-top, 2-bottom, 3-both)', 'PREV_NEXT_BAR_LOCATION', '2', 'Sets the location of the Prev/Next Navigation Bar (1-top, 2-bottom, 3-both)', 8, 10, NULL, '2008-12-12 19:02:38', NULL, NULL),
(114, 'Sprawdź Stan Magazynu', 'STOCK_CHECK', 'true', 'Sprawdzaj czy produkt jest w magazynie w wystarczającej ilości', 9, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(115, 'Zmniejszaj Stan Magazynu', 'STOCK_LIMITED', 'true', 'Po dokonaniu zamówienia zmniejsz stan magazynu o liczbę zamówionych produktów', 9, 2, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(116, 'Pozwól Zamówić', 'STOCK_ALLOW_CHECKOUT', 'false', 'Pozwalaj zamówić produkt nawet jeżeli nie ma go w magazynie', 9, 3, '2009-01-14 13:37:35', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(117, 'Zaznacz Brak w Magazynie', 'STOCK_MARK_PRODUCT_OUT_OF_STOCK', '***', 'Wyświetlaj informację z takim oznaczeniem jeżeli klient będzie chciałzamówić więcej niż znajduje się w magazynie', 9, 4, NULL, '2008-12-12 19:02:38', NULL, NULL),
(118, 'Uzupelnienie magazynu', 'STOCK_REORDER_LEVEL', '5', 'Wpisz stan krytyczny dla magazynu', 9, 5, NULL, '2008-12-12 19:02:38', NULL, NULL),
(119, 'Store Page Parse Time', 'STORE_PAGE_PARSE_TIME', 'false', 'Store the time it takes to parse a page', 10, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(120, 'Lokalizacja', 'STORE_PAGE_PARSE_TIME_LOG', '/var/log/www/tep/page_parse_time.log', 'Directory and filename of the page parse time log', 10, 2, NULL, '2008-12-12 19:02:38', NULL, NULL),
(121, 'Format Daty', 'STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S', 'Format daty', 10, 3, NULL, '2008-12-12 19:02:38', NULL, NULL),
(122, 'Display The Page Parse Time', 'DISPLAY_PAGE_PARSE_TIME', 'true', 'Display the page parse time (store page parse time must be enabled)', 10, 4, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(123, 'Baza Danych', 'STORE_DB_TRANSACTIONS', 'false', 'Store the database queries in the page parse time log (PHP4 only)', 10, 5, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(124, 'Używaj Cache', 'USE_CACHE', 'false', 'Czy chcesz używać cache?', 11, 1, '2008-12-18 15:43:16', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(125, 'Lokalizacja Cache', 'DIR_FS_CACHE', 'tmp/', 'Lokalizacja zapisu plików cache', 11, 2, '2008-12-12 22:48:14', '2008-12-12 19:02:38', NULL, NULL),
(126, 'Metoda Wysyłki E-Maili', 'EMAIL_TRANSPORT', 'smtp', 'Wybierz metodę wysyłki e-maili', 12, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''sendmail'', ''smtp''),'),
(127, 'E-Mail Linefeeds', 'EMAIL_LINEFEED', 'LF', 'Defines the character sequence used to separate mail headers.', 12, 2, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''LF'', ''CRLF''),'),
(128, 'Używaj HTML', 'EMAIL_USE_HTML', 'false', 'Wysyłaj e-maile w HTML', 12, 3, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''),'),
(129, 'Weryfikuj Adres E-Mail przez DNS', 'ENTRY_EMAIL_ADDRESS_CHECK', 'false', 'Czy weryfikować poprzez serwer DNS?', 12, 4, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(130, 'Wystłaj E-Maile', 'SEND_EMAILS', 'true', 'Czy wysylać e-maile?', 12, 5, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(131, 'Download', 'DOWNLOAD_ENABLED', 'false', 'Zezwól na ściąganie plików.', 13, 1, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(132, 'Download Z Katalogu', 'DOWNLOAD_BY_REDIRECT', 'false', 'Użyj adresu internetowego do ściągania plików.', 13, 2, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(133, 'Czas Ważności', 'DOWNLOAD_MAX_DAYS', '7', 'Wpisz liczbę dni po upływie których ściąganie będzie. 0 oznacza bez limitu.', 13, 3, NULL, '2008-12-12 19:02:38', NULL, ''),
(134, 'Maksymalna liczba pobrań', 'DOWNLOAD_MAX_COUNT', '5', 'Wpisz maksymalną liczbę pobrań. 0 oznacza że pobranie nie jest możliwe.', 13, 4, NULL, '2008-12-12 19:02:38', NULL, ''),
(135, 'Dostępna Kompresja GZip', 'GZIP_COMPRESSION', 'true', 'Udostępnij kompresję HTTP GZip .', 14, 1, '2008-12-12 19:50:40', '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''true'', ''false''), '),
(136, 'Poziom Kompresji', 'GZIP_LEVEL', '9', 'Wpisz stopień kompresji 0-9 (0 = minimum, 9 = maximum).', 14, 2, '2008-12-12 22:17:56', '2008-12-12 19:02:38', NULL, NULL),
(137, 'Katalog Sesji', 'SESSION_WRITE_DIRECTORY', '/tmp', 'Lokalizacja zapisu sesji.', 15, 1, NULL, '2008-12-12 19:02:38', NULL, NULL),
(138, 'Wymuś Cookies', 'SESSION_FORCE_COOKIE_USE', 'False', 'Czy chcesz wymusić używanie cookies.', 15, 2, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(139, 'Sprawdź SSL Sesji ID', 'SESSION_CHECK_SSL_SESSION_ID', 'False', 'Weryfikuj SSL_SESSION_ID z każdym odświeżeniem bezpiecznej strony HTTPS.', 15, 3, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(140, 'Sprawdzanie Konta', 'SESSION_CHECK_USER_AGENT', 'False', 'Weryfikuj przegladarkę z każdym odświeżeniem strony.', 15, 4, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(141, 'Sprawdź Adres IP', 'SESSION_CHECK_IP_ADDRESS', 'False', 'Weryfikuj IP z każdym odświeżeniem strony.', 15, 5, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(142, 'Blokuj Spiders dla Sesji', 'SESSION_BLOCK_SPIDERS', 'True', 'Blokuj spiders przed rozpoczęciem sesji.', 15, 6, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(143, 'Ponów Sesje', 'SESSION_RECREATE', 'False', 'Otwórz nową sesje kiedy klient się loguje lub zakłada nowe konto (PHP >=4.1 wymagane).', 15, 7, NULL, '2008-12-12 19:02:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(454, 'Kolejność wyświetlania.', 'MODULE_PAYMENT_COD_SORT_ORDER', '0', 'Kolejność wyświetlania. Najniższe wyświetlane są na początku.', 6, 0, NULL, '2008-12-18 22:12:06', NULL, NULL),
(315, 'Przelicz podatek', 'MODULE_PAYMENT_POBRANIEPP_CALC_TAX', 'nie', 'Przelicz podatek dla zmienionej oplaty.', 6, 5, NULL, '2008-12-18 18:31:16', NULL, 'tep_cfg_select_option(array(''tak'', ''nie''), '),
(312, 'Sortowanie', 'MODULE_PAYMENT_POBRANIEPP_SORT_ORDER', '111', 'Porzadek sortowania.', 6, 2, NULL, '2008-12-18 18:31:16', NULL, NULL),
(313, 'Rodzaj pobrania', 'MODULE_PAYMENT_POBRANIEPP_HOWTO', 'wpłata STANDARD', 'Sposób w jaki zostaną przekazane nam pieniądze', 6, 5, NULL, '2008-12-18 18:31:16', NULL, 'tep_cfg_select_option(array(''wpłata STANDARD''), '),
(479, 'Automatyczny wybor waluty rozliczen', 'MODULE_PAYMENT_ECARD_TRYUSERCURRENCY', 'True', 'Probuje ustawic rozliczenia w walucie zwiazanej z jezykiem wybranym przez uzytkownika. Jezeli niemozliwe lub brak waluty, ustawiana jest domyslna.', 5, 12, NULL, '2009-02-18 13:08:18', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(478, 'Waluta rozliczen', 'MODULE_PAYMENT_ECARD_CURRENCY', '985', 'Domyslna waluta rozliczen', 5, 11, NULL, '2009-02-18 13:08:18', NULL, 'ecard_cfg_pull_down_currency( 12,'),
(476, 'Jezyk interfejsu platnosci', 'MODULE_PAYMENT_ECARD_LANGUAGE', 'PL', 'Domyslny jezyk interfejsu platnosci', 5, 9, NULL, '2009-02-18 13:08:18', NULL, 'ecard_cfg_pull_down_languages( 12,'),
(477, 'Automatyczne ustawianie jezyka', 'MODULE_PAYMENT_ECARD_TRYUSERLANGUAGE', 'True', 'Probuje ustawic interfejs platnosci w jezyku klienta. Jezeli nie obslugiwany ustawia domyslny.', 5, 10, NULL, '2009-02-18 13:08:18', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(475, 'Autodeposit', 'MODULE_PAYMENT_ECARD_AUTODEPOSIT', 'True', 'AUTODEPOSIT wlaczone?', 4, 8, NULL, '2009-02-18 13:08:18', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(474, 'Ustaw status zamówienia', 'MODULE_PAYMENT_ECARD_ORDER_STATUS_ID', '0', 'Jaki status zamowienia ustawic po dokonanej platnosci', 4, 7, NULL, '2009-02-18 13:08:18', 'tep_get_order_status_name', 'tep_cfg_pull_down_order_statuses('),
(473, 'Prefiks w tytule platnosci:', 'MODULE_PAYMENT_ECARD_ORDERTITLE', 'Twój sklep', 'Prefiks w tytule platnosci w eCard', 3, 6, NULL, '2009-02-18 13:08:18', NULL, NULL),
(256, 'Zamówienie bez zakładania konta', 'PURCHASE_WITHOUT_ACCOUNT', 'yes', 'Czy zezwalasz klientom zamawiać bez zakładania konta?', 5, 10, '2008-12-18 17:06:04', '2008-12-18 16:07:56', NULL, 'tep_cfg_select_option(array(''yes'', ''no''), '),
(314, 'Wliczac Podatek', 'MODULE_PAYMENT_POBRANIEPP_INC_TAX', 'tak', 'Wlicz podatek do przeliczen.', 6, 6, NULL, '2008-12-18 18:31:16', NULL, 'tep_cfg_select_option(array(''tak'', ''nie''), '),
(472, 'Polozenie w liscie mozliwych platnosci', 'MODULE_PAYMENT_ECARD_SORT_ORDER', '0', 'Kolejnosc w liscie dostepnych platnosci.', 3, 5, NULL, '2009-02-18 13:08:18', NULL, NULL),
(257, 'Zamówienie bez konta a adres wysyłki', 'PURCHASE_WITHOUT_ACCOUNT_SEPARATE_SHIPPING', 'yes', 'Czy zezwalasz klientom bez konta na osobny adres wysyłki?', 5, 11, '2008-12-18 17:06:11', '2008-12-18 16:07:56', NULL, 'tep_cfg_select_option(array(''yes'', ''no''), '),
(336, 'Ustaw Status Zamówienia', 'MODULE_PAYMENT_PRZELEWY24_ORDER_STATUS_ID', '0', 'Ustaw status zamówień realizowanych tą formą płatności', 6, 0, NULL, '2008-12-18 18:38:38', 'tep_get_order_status_name', 'tep_cfg_pull_down_order_statuses('),
(471, 'Haslo dla Merchanta:', 'MODULE_PAYMENT_ECARD_MERCHANT_PASSWD', 'e1c2a3', 'Haslo wlasciwe dla merchanta', 2, 4, NULL, '2009-02-18 13:08:18', NULL, NULL),
(333, 'ID w serwisie Przelewy24.pl', 'MODULE_PAYMENT_PRZELEWY24_ID', '0000', 'Numer ID jakim posługujesz się w serwisie Przelewy24', 6, 4, NULL, '2008-12-18 18:38:38', NULL, NULL),
(334, 'Kolejność wyświetlania.', 'MODULE_PAYMENT_PRZELEWY24_SORT_ORDER', '3', 'Kolejność wyświetlania. Najniższe wyświetlane są na początku.', 6, 0, NULL, '2008-12-18 18:38:38', NULL, NULL),
(335, 'Strefa Płatności', 'MODULE_PAYMENT_PRZELEWY24_ZONE', '0', 'Jeżeli wybrano strefę ten rodzaj płatności będzie aktywny tylko dla niej.', 6, 2, NULL, '2008-12-18 18:38:38', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(470, 'Identyfikator merchanta', 'MODULE_PAYMENT_ECARD_MERCHANT_ID', '2729393', 'Identyfikator sklepu (merchantID) uzyskany od firmy eCard', 2, 3, NULL, '2009-02-18 13:08:17', NULL, NULL),
(469, 'Twoj adres e-mail:', 'MODULE_PAYMENT_ECARD_ID', 'ty@twojsklep.pl', 'Adres poczty elektronicznej uzywany w uslugach eCard', 1, 2, NULL, '2009-02-18 13:08:17', NULL, NULL),
(468, 'Wlaczyc platnosci eCard?', 'MODULE_PAYMENT_ECARD_STATUS', 'True', 'Uaktywnic platnosci z eCard?', 1, 1, NULL, '2009-02-18 13:08:17', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(458, 'Discount Percentage', 'MODULE_FIXED_PAYMENT_CHG_AMOUNT', '2', 'Amount of Discount.', 6, 7, NULL, '2008-12-24 11:19:01', NULL, NULL),
(325, 'Discount Percentage', 'MODULE_FIXED_PAYMENT_CHG_AMOUNT', '2', 'Amount of Discount.', 6, 7, NULL, '2008-12-18 18:38:05', NULL, NULL),
(332, 'Włącz Moduł Przelewy24', 'MODULE_PAYMENT_PRZELEWY24_STATUS', 'True', 'Chcesz uruchomić płatności przez Przelewy24?', 6, 3, NULL, '2008-12-18 18:38:38', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(341, 'Kolejność wyświetlania.', 'MODULE_PAYMENT_ADVANCE_SORT_ORDER', '0', 'Kolejność wyświetlania. Najniższe wyświetlane są na początku.', 6, 0, NULL, '2008-12-18 20:54:18', NULL, NULL),
(340, 'Włączony', 'MODULE_PAYMENT_ADVANCE_STATUS', 'True', 'Włącza (true) lub nie (false) moduł płatności.', 6, 1, NULL, '2008-12-18 20:54:18', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(342, 'Domyślny status zamówienia', 'MODULE_PAYMENT_ADVANCE_ORDER_STATUS_ID', '0', 'Ustaw status zamówień realizowanych tą formą płatności', 6, 0, NULL, '2008-12-18 20:54:18', 'tep_get_order_status_name', 'tep_cfg_pull_down_order_statuses('),
(357, 'Przesyłka kurierska', 'MODULE_SHIPPING_DP2_STATUS', 'True', 'Czy chcesz oferować wysyłkę za pośrednictwem firmy kurierskiej?', 6, 0, NULL, '2008-12-18 21:03:24', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(398, 'Stawki za poszczególne paczki', 'MODULE_SHIPPING_DP_COST_1', '5:16.50,10:20.50,20:28.50', 'Wpisz zakres wagowy oraz koszt przesyłki, np. 0-3:8.50.', 6, 0, NULL, '2008-12-18 21:03:42', NULL, NULL),
(395, 'Z jakiego kraju wysyłka', 'MODULE_SHIPPING_DP_ZONE', '2', '', 6, 0, NULL, '2008-12-18 21:03:42', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(396, 'Numer do sortowania', 'MODULE_SHIPPING_DP_SORT_ORDER', '1', '', 6, 0, NULL, '2008-12-18 21:03:42', NULL, NULL),
(397, 'Kraj docelowy', 'MODULE_SHIPPING_DP_COUNTRIES_1', 'PL', 'Oddzielana przecinkami lista kodów ISO krajów', 6, 0, NULL, '2008-12-18 21:03:42', NULL, NULL),
(393, 'Koszt pakowania', 'MODULE_SHIPPING_DP_HANDLING', '0', '', 6, 0, NULL, '2008-12-18 21:03:42', NULL, NULL),
(394, 'Stawka podatkowa', 'MODULE_SHIPPING_DP_TAX_CLASS', '0', '', 6, 0, NULL, '2008-12-18 21:03:42', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes('),
(358, 'Koszt pakowania', 'MODULE_SHIPPING_DP2_HANDLING', '0', '', 6, 0, NULL, '2008-12-18 21:03:24', NULL, NULL),
(359, 'Stawka podatkowa', 'MODULE_SHIPPING_DP2_TAX_CLASS', '2', '', 6, 0, NULL, '2008-12-18 21:03:24', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes('),
(360, 'Z jakiego kraju wysyłka', 'MODULE_SHIPPING_DP2_ZONE', '2', '', 6, 0, NULL, '2008-12-18 21:03:24', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(361, 'Nr do sortowania', 'MODULE_SHIPPING_DP2_SORT_ORDER', '3', '', 6, 0, NULL, '2008-12-18 21:03:24', NULL, NULL),
(362, 'Kraj docelowy', 'MODULE_SHIPPING_DP2_COUNTRIES_1', 'PL', 'Oddzielana przecinkami lista kodów ISO krajów', 6, 0, NULL, '2008-12-18 21:03:24', NULL, NULL),
(363, 'Stawki za poszczególne przesyłki', 'MODULE_SHIPPING_DP2_COST_1', '5:16.50,10:20.50,20:28.50', 'Wpisz zakres wagowy oraz koszt przesyłki, np. 0-3:8.50', 6, 0, NULL, '2008-12-18 21:03:24', NULL, NULL),
(364, 'Włącz wysyłkę pocztą', 'MODULE_SHIPPING_FLAT_STATUS', 'True', 'Czy chcesz oferować wysyłkę paczką pocztową?', 6, 0, NULL, '2008-12-18 21:03:28', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(365, 'Koszt wysyłki', 'MODULE_SHIPPING_FLAT_COST', '15', '', 6, 0, NULL, '2008-12-18 21:03:28', NULL, NULL),
(366, 'Klasa podatkowa', 'MODULE_SHIPPING_FLAT_TAX_CLASS', '0', '', 6, 0, NULL, '2008-12-18 21:03:28', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes('),
(367, 'Strefa', 'MODULE_SHIPPING_FLAT_ZONE', '2', '', 6, 0, NULL, '2008-12-18 21:03:28', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(368, 'Sposób wyświetlania', 'MODULE_SHIPPING_FLAT_SORT_ORDER', '2', 'Na którym miejscu pokazać?', 6, 0, NULL, '2008-12-18 21:03:28', NULL, NULL),
(369, 'Włącz wysyłkę kurierem', 'MODULE_SHIPPING_FLAT2_STATUS', 'True', 'Czy chcesz oferować wysyłkę kurierem?', 6, 0, NULL, '2008-12-18 21:03:32', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(370, 'Koszt przesyłki', 'MODULE_SHIPPING_FLAT2_COST', '25.00', '', 6, 0, NULL, '2008-12-18 21:03:32', NULL, NULL),
(371, 'Klasa podatkowa', 'MODULE_SHIPPING_FLAT2_TAX_CLASS', '0', 'Wybierz klasę podatkową.', 6, 0, NULL, '2008-12-18 21:03:32', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes('),
(372, 'Strefa', 'MODULE_SHIPPING_FLAT2_ZONE', '2', '', 6, 0, NULL, '2008-12-18 21:03:32', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(373, 'Sposób wyświetlania', 'MODULE_SHIPPING_FLAT2_SORT_ORDER', '4', 'Na którym miejscu pokazać?.', 6, 0, NULL, '2008-12-18 21:03:32', NULL, NULL),
(409, 'Strefa', 'MODULE_SHIPPING_ITEM_ZONE', '0', 'Jeżeli strefa zostanie ustawiona, wysyłka będzie możliwa tylko do tej strefy.', 6, 0, NULL, '2008-12-18 21:14:08', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(408, 'Klasa podatkowa', 'MODULE_SHIPPING_ITEM_TAX_CLASS', '0', 'Wybierz klasę podatkową.', 6, 0, NULL, '2008-12-18 21:14:08', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes('),
(407, 'Koszty pakowania', 'MODULE_SHIPPING_ITEM_HANDLING', '0', 'Koszty pakowania przy tej metodzie wysyłki.', 6, 0, NULL, '2008-12-18 21:14:08', NULL, NULL),
(406, 'Koszty przesyłki', 'MODULE_SHIPPING_ITEM_COST', '2.50', 'Koszty przesyłki będą przemnożone przez ilość sztuk w zamówieniu przy użyciu tej metody.', 6, 0, NULL, '2008-12-18 21:14:08', NULL, NULL),
(420, 'Koszt obsługi', 'MODULE_SHIPPING_PICKUP_COST', '0.00', 'Cena, którą doliczysz do zamówienia za obsługę bezpośrednią klienta.', 6, 0, NULL, '2008-12-18 21:18:20', NULL, NULL),
(418, 'Strefa', 'MODULE_SHIPPING_PICKUP_ZONE', '0', 'Jeżeli strefa zostanie ustawiona, wysyłka będzie możliwa tylko do tej strefy.', 6, 0, NULL, '2008-12-18 21:18:20', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(416, 'Włącz odbiór osobisty', 'MODULE_SHIPPING_PICKUP_STATUS', 'True', 'Czy chcesz dopuszczasz odbiór osobisty?', 6, 0, NULL, '2008-12-18 21:18:20', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(417, 'Klasa podatkowa', 'MODULE_SHIPPING_PICKUP_TAX_CLASS', '0', 'Wybierz klasę podatkową', 6, 0, NULL, '2008-12-18 21:18:20', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes('),
(410, 'Kolejność', 'MODULE_SHIPPING_ITEM_SORT_ORDER', '5', 'Na którym miejscu pokazać?', 6, 0, NULL, '2008-12-18 21:14:08', NULL, NULL),
(430, 'Strefa', 'MODULE_SHIPPING_TABLE_ZONE', '0', 'Jeżeli strefa zostanie ustawiona, wysyłka będzie możliwa tylko do tej strefy.', 6, 0, NULL, '2008-12-18 21:31:36', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes('),
(428, 'Koszty pakowania', 'MODULE_SHIPPING_TABLE_HANDLING', '0', 'Koszty pakowania przy tej metodzie wysyłki.', 6, 0, NULL, '2008-12-18 21:31:36', NULL, NULL),
(429, 'Klasa podatkowa', 'MODULE_SHIPPING_TABLE_TAX_CLASS', '0', 'Wybierz klasę podatkową.', 6, 0, NULL, '2008-12-18 21:31:36', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes('),
(427, 'Podstawa', 'MODULE_SHIPPING_TABLE_MODE', 'weight', 'Koszty wysyłki bazować mogą na wadze przesyłki lub całkowitej kwocie zamówienia.', 6, 0, NULL, '2008-12-18 21:31:36', NULL, 'tep_cfg_select_option(array(''waga'', ''cena''), '),
(426, 'Stawka zależna od kwoty zamówienia', 'MODULE_SHIPPING_TABLE_COST', '25:8.50,50:5.50,10000:0.00', 'Koszty wysyłki bazuje na kwocie zamówienia lub jego wadze. Na przykład: 25:8.50,50:5.50,itd.. Do 25 opłata 8.50, od tego do 50 opłata 5.50, do 10000 gratis.', 6, 0, NULL, '2008-12-18 21:31:36', NULL, NULL),
(425, 'Włącz stawkę wg tabeli', 'MODULE_SHIPPING_TABLE_STATUS', 'True', 'Czy chcesz ofertować wysyłkę wg stawki zależnej od wagi lub kwoty zamówienia?', 6, 0, NULL, '2008-12-18 21:31:36', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(392, 'Poczta Polska', 'MODULE_SHIPPING_DP_STATUS', 'True', 'Czy chcesz oferować wysyłkę za pośrednictwem Poczty Polskiej?', 6, 0, NULL, '2008-12-18 21:03:42', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(405, 'Włącz opłatę za sztukę', 'MODULE_SHIPPING_ITEM_STATUS', 'True', 'Czy chcesz ofertować wysyłkę płatną za ilość przedmiotów?', 6, 0, NULL, '2008-12-18 21:14:08', NULL, 'tep_cfg_select_option(array(''True'', ''False''), '),
(419, 'Kolejność', 'MODULE_SHIPPING_PICKUP_SORT_ORDER', '6', 'Na którym miejscu pokazać?', 6, 0, NULL, '2008-12-18 21:18:20', NULL, NULL),
(431, 'Kolejność', 'MODULE_SHIPPING_TABLE_SORT_ORDER', '0', 'Na którym miejscu pokazać?', 6, 0, NULL, '2008-12-18 21:31:36', NULL, NULL),
(480, 'Typ platnosci', 'MODULE_PAYMENT_ECARD_PAYMENTTYPE', 'ALL', 'Dostepne formy platnosci', 6, 14, NULL, '2009-02-18 13:08:18', NULL, 'ecard_cfg_pull_down_paytype(array(''ALL'', ''CHOOSECARDS'', ''TRANSFERS''), '),
(481, 'Kodowanie', 'MODULE_PAYMENT_ECARD_CHARSET', 'utf-8', 'Kodowanie interfejsu platnosci', 7, 15, NULL, '2009-02-18 13:08:18', NULL, NULL),
(482, 'Pełna nazwa sklepu', 'STORE_NAME_FULL', 'Firmowy salon sprzedaży eurola', 'Pełna nazwa sklepu', 1, 0, '2009-03-01 20:50:16', '2008-12-12 19:02:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `configuration_group`
--

CREATE TABLE IF NOT EXISTS `configuration_group` (
  `configuration_group_id` int(11) NOT NULL auto_increment,
  `configuration_group_title` varchar(64) collate utf8_polish_ci NOT NULL,
  `configuration_group_description` varchar(255) collate utf8_polish_ci NOT NULL,
  `sort_order` int(5) default NULL,
  `visible` int(1) default '1',
  PRIMARY KEY  (`configuration_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=16 ;

--
-- Zrzut danych tabeli `configuration_group`
--

INSERT INTO `configuration_group` (`configuration_group_id`, `configuration_group_title`, `configuration_group_description`, `sort_order`, `visible`) VALUES
(1, 'Mój sklep', 'Główne dane konfiguracyjne sklepu', 1, 1),
(2, 'Wartości minimalne', 'Minimalne wartości konfiguracyjne', 2, 1),
(3, 'Wartości maxymalne', 'Maxymalne wartości konfiguracyjne', 3, 1),
(4, 'Obrazki', 'Parametry Obrazków', 4, 1),
(5, 'Konta Klientów', 'Konfiguracja kont klientów', 5, 1),
(6, 'Module Options', 'Hidden from configuration', 6, 0),
(7, 'Dostawa/Pakowanie', 'Konfiguracja Wysyłki', 7, 1),
(8, 'Lista Produktów', 'Opcje Listy Produktów', 8, 1),
(9, 'Magazyn', 'Opcje Magazynowe', 9, 1),
(10, 'Logging', 'Logging configuration options', 10, 1),
(11, 'Cache', 'Konfiguracja Cache', 11, 1),
(12, 'Opcje E-Mail', 'Główne ustawienia e-mail', 12, 1),
(13, 'Download', 'Ustawienia Produktów pobieralnych', 13, 1),
(14, 'Kompresja GZip', 'Opcje Kompresji GZip', 14, 1),
(15, 'Sesje', 'Ustawienia Sesji', 15, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `counter`
--

CREATE TABLE IF NOT EXISTS `counter` (
  `startdate` char(8) collate utf8_polish_ci default NULL,
  `counter` int(12) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `counter`
--

INSERT INTO `counter` (`startdate`, `counter`) VALUES
('20090501', 43);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `counter_history`
--

CREATE TABLE IF NOT EXISTS `counter_history` (
  `month` char(8) collate utf8_polish_ci default NULL,
  `counter` int(12) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `counter_history`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `countries_id` int(11) NOT NULL auto_increment,
  `countries_name` varchar(64) collate utf8_polish_ci NOT NULL,
  `countries_iso_code_2` char(2) collate utf8_polish_ci NOT NULL,
  `countries_iso_code_3` char(3) collate utf8_polish_ci NOT NULL,
  `address_format_id` int(11) NOT NULL,
  PRIMARY KEY  (`countries_id`),
  KEY `IDX_COUNTRIES_NAME` (`countries_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=241 ;

--
-- Zrzut danych tabeli `countries`
--

INSERT INTO `countries` (`countries_id`, `countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`) VALUES
(1, 'Afghanistan', 'AF', 'AFG', 1),
(2, 'Albania', 'AL', 'ALB', 1),
(3, 'Algeria', 'DZ', 'DZA', 1),
(4, 'American Samoa', 'AS', 'ASM', 1),
(5, 'Andorra', 'AD', 'AND', 1),
(6, 'Angola', 'AO', 'AGO', 1),
(7, 'Anguilla', 'AI', 'AIA', 1),
(8, 'Antarctica', 'AQ', 'ATA', 1),
(9, 'Antigua and Barbuda', 'AG', 'ATG', 1),
(10, 'Argentina', 'AR', 'ARG', 1),
(11, 'Armenia', 'AM', 'ARM', 1),
(12, 'Aruba', 'AW', 'ABW', 1),
(13, 'Australia', 'AU', 'AUS', 1),
(14, 'Austria', 'AT', 'AUT', 5),
(15, 'Azerbaijan', 'AZ', 'AZE', 1),
(16, 'Bahamas', 'BS', 'BHS', 1),
(17, 'Bahrain', 'BH', 'BHR', 1),
(18, 'Bangladesh', 'BD', 'BGD', 1),
(19, 'Barbados', 'BB', 'BRB', 1),
(20, 'Belarus', 'BY', 'BLR', 1),
(21, 'Belgium', 'BE', 'BEL', 1),
(22, 'Belize', 'BZ', 'BLZ', 1),
(23, 'Benin', 'BJ', 'BEN', 1),
(24, 'Bermuda', 'BM', 'BMU', 1),
(25, 'Bhutan', 'BT', 'BTN', 1),
(26, 'Bolivia', 'BO', 'BOL', 1),
(27, 'Bosnia and Herzegowina', 'BA', 'BIH', 1),
(28, 'Botswana', 'BW', 'BWA', 1),
(29, 'Bouvet Island', 'BV', 'BVT', 1),
(30, 'Brazil', 'BR', 'BRA', 1),
(31, 'British Indian Ocean Territory', 'IO', 'IOT', 1),
(32, 'Brunei Darussalam', 'BN', 'BRN', 1),
(33, 'Bulgaria', 'BG', 'BGR', 1),
(34, 'Burkina Faso', 'BF', 'BFA', 1),
(35, 'Burundi', 'BI', 'BDI', 1),
(36, 'Cambodia', 'KH', 'KHM', 1),
(37, 'Cameroon', 'CM', 'CMR', 1),
(38, 'Canada', 'CA', 'CAN', 1),
(39, 'Cape Verde', 'CV', 'CPV', 1),
(40, 'Cayman Islands', 'KY', 'CYM', 1),
(41, 'Central African Republic', 'CF', 'CAF', 1),
(42, 'Chad', 'TD', 'TCD', 1),
(43, 'Chile', 'CL', 'CHL', 1),
(44, 'China', 'CN', 'CHN', 1),
(45, 'Christmas Island', 'CX', 'CXR', 1),
(46, 'Cocos (Keeling) Islands', 'CC', 'CCK', 1),
(47, 'Colombia', 'CO', 'COL', 1),
(48, 'Comoros', 'KM', 'COM', 1),
(49, 'Congo', 'CG', 'COG', 1),
(50, 'Cook Islands', 'CK', 'COK', 1),
(51, 'Costa Rica', 'CR', 'CRI', 1),
(52, 'Cote D''Ivoire', 'CI', 'CIV', 1),
(53, 'Croatia', 'HR', 'HRV', 1),
(54, 'Cuba', 'CU', 'CUB', 1),
(55, 'Cyprus', 'CY', 'CYP', 1),
(56, 'Czech Republic', 'CZ', 'CZE', 1),
(57, 'Denmark', 'DK', 'DNK', 1),
(58, 'Djibouti', 'DJ', 'DJI', 1),
(59, 'Dominica', 'DM', 'DMA', 1),
(60, 'Dominican Republic', 'DO', 'DOM', 1),
(61, 'East Timor', 'TP', 'TMP', 1),
(62, 'Ecuador', 'EC', 'ECU', 1),
(63, 'Egypt', 'EG', 'EGY', 1),
(64, 'El Salvador', 'SV', 'SLV', 1),
(65, 'Equatorial Guinea', 'GQ', 'GNQ', 1),
(66, 'Eritrea', 'ER', 'ERI', 1),
(67, 'Estonia', 'EE', 'EST', 1),
(68, 'Ethiopia', 'ET', 'ETH', 1),
(69, 'Falkland Islands (Malvinas)', 'FK', 'FLK', 1),
(70, 'Faroe Islands', 'FO', 'FRO', 1),
(71, 'Fiji', 'FJ', 'FJI', 1),
(72, 'Finland', 'FI', 'FIN', 1),
(73, 'France', 'FR', 'FRA', 1),
(74, 'France, Metropolitan', 'FX', 'FXX', 1),
(75, 'French Guiana', 'GF', 'GUF', 1),
(76, 'French Polynesia', 'PF', 'PYF', 1),
(77, 'French Southern Territories', 'TF', 'ATF', 1),
(78, 'Gabon', 'GA', 'GAB', 1),
(79, 'Gambia', 'GM', 'GMB', 1),
(80, 'Georgia', 'GE', 'GEO', 1),
(81, 'Germany', 'DE', 'DEU', 5),
(82, 'Ghana', 'GH', 'GHA', 1),
(83, 'Gibraltar', 'GI', 'GIB', 1),
(84, 'Greece', 'GR', 'GRC', 1),
(85, 'Greenland', 'GL', 'GRL', 1),
(86, 'Grenada', 'GD', 'GRD', 1),
(87, 'Guadeloupe', 'GP', 'GLP', 1),
(88, 'Guam', 'GU', 'GUM', 1),
(89, 'Guatemala', 'GT', 'GTM', 1),
(90, 'Guinea', 'GN', 'GIN', 1),
(91, 'Guinea-bissau', 'GW', 'GNB', 1),
(92, 'Guyana', 'GY', 'GUY', 1),
(93, 'Haiti', 'HT', 'HTI', 1),
(94, 'Heard and Mc Donald Islands', 'HM', 'HMD', 1),
(95, 'Honduras', 'HN', 'HND', 1),
(96, 'Hong Kong', 'HK', 'HKG', 1),
(97, 'Hungary', 'HU', 'HUN', 1),
(98, 'Iceland', 'IS', 'ISL', 1),
(99, 'India', 'IN', 'IND', 1),
(100, 'Indonesia', 'ID', 'IDN', 1),
(101, 'Iran (Islamic Republic of)', 'IR', 'IRN', 1),
(102, 'Iraq', 'IQ', 'IRQ', 1),
(103, 'Ireland', 'IE', 'IRL', 1),
(104, 'Israel', 'IL', 'ISR', 1),
(105, 'Italy', 'IT', 'ITA', 1),
(106, 'Jamaica', 'JM', 'JAM', 1),
(107, 'Japan', 'JP', 'JPN', 1),
(108, 'Jordan', 'JO', 'JOR', 1),
(109, 'Kazakhstan', 'KZ', 'KAZ', 1),
(110, 'Kenya', 'KE', 'KEN', 1),
(111, 'Kiribati', 'KI', 'KIR', 1),
(112, 'Korea, Democratic People''s Republic of', 'KP', 'PRK', 1),
(113, 'Korea, Republic of', 'KR', 'KOR', 1),
(114, 'Kuwait', 'KW', 'KWT', 1),
(115, 'Kyrgyzstan', 'KG', 'KGZ', 1),
(116, 'Lao People''s Democratic Republic', 'LA', 'LAO', 1),
(117, 'Latvia', 'LV', 'LVA', 1),
(118, 'Lebanon', 'LB', 'LBN', 1),
(119, 'Lesotho', 'LS', 'LSO', 1),
(120, 'Liberia', 'LR', 'LBR', 1),
(121, 'Libyan Arab Jamahiriya', 'LY', 'LBY', 1),
(122, 'Liechtenstein', 'LI', 'LIE', 1),
(123, 'Lithuania', 'LT', 'LTU', 1),
(124, 'Luxembourg', 'LU', 'LUX', 1),
(125, 'Macau', 'MO', 'MAC', 1),
(126, 'Macedonia, The Former Yugoslav Republic of', 'MK', 'MKD', 1),
(127, 'Madagascar', 'MG', 'MDG', 1),
(128, 'Malawi', 'MW', 'MWI', 1),
(129, 'Malaysia', 'MY', 'MYS', 1),
(130, 'Maldives', 'MV', 'MDV', 1),
(131, 'Mali', 'ML', 'MLI', 1),
(132, 'Malta', 'MT', 'MLT', 1),
(133, 'Marshall Islands', 'MH', 'MHL', 1),
(134, 'Martinique', 'MQ', 'MTQ', 1),
(135, 'Mauritania', 'MR', 'MRT', 1),
(136, 'Mauritius', 'MU', 'MUS', 1),
(137, 'Mayotte', 'YT', 'MYT', 1),
(138, 'Mexico', 'MX', 'MEX', 1),
(139, 'Micronesia, Federated States of', 'FM', 'FSM', 1),
(140, 'Moldova, Republic of', 'MD', 'MDA', 1),
(141, 'Monaco', 'MC', 'MCO', 1),
(142, 'Mongolia', 'MN', 'MNG', 1),
(143, 'Montserrat', 'MS', 'MSR', 1),
(144, 'Morocco', 'MA', 'MAR', 1),
(145, 'Mozambique', 'MZ', 'MOZ', 1),
(146, 'Myanmar', 'MM', 'MMR', 1),
(147, 'Namibia', 'NA', 'NAM', 1),
(148, 'Nauru', 'NR', 'NRU', 1),
(149, 'Nepal', 'NP', 'NPL', 1),
(150, 'Netherlands', 'NL', 'NLD', 1),
(151, 'Netherlands Antilles', 'AN', 'ANT', 1),
(152, 'New Caledonia', 'NC', 'NCL', 1),
(153, 'New Zealand', 'NZ', 'NZL', 1),
(154, 'Nicaragua', 'NI', 'NIC', 1),
(155, 'Niger', 'NE', 'NER', 1),
(156, 'Nigeria', 'NG', 'NGA', 1),
(157, 'Niue', 'NU', 'NIU', 1),
(158, 'Norfolk Island', 'NF', 'NFK', 1),
(159, 'Northern Mariana Islands', 'MP', 'MNP', 1),
(160, 'Norway', 'NO', 'NOR', 1),
(161, 'Oman', 'OM', 'OMN', 1),
(162, 'Pakistan', 'PK', 'PAK', 1),
(163, 'Palau', 'PW', 'PLW', 1),
(164, 'Panama', 'PA', 'PAN', 1),
(165, 'Papua New Guinea', 'PG', 'PNG', 1),
(166, 'Paraguay', 'PY', 'PRY', 1),
(167, 'Peru', 'PE', 'PER', 1),
(168, 'Philippines', 'PH', 'PHL', 1),
(169, 'Pitcairn', 'PN', 'PCN', 1),
(170, 'Polska', 'PL', 'POL', 1),
(171, 'Portugal', 'PT', 'PRT', 1),
(172, 'Puerto Rico', 'PR', 'PRI', 1),
(173, 'Qatar', 'QA', 'QAT', 1),
(174, 'Reunion', 'RE', 'REU', 1),
(175, 'Romania', 'RO', 'ROM', 1),
(176, 'Russian Federation', 'RU', 'RUS', 1),
(177, 'Rwanda', 'RW', 'RWA', 1),
(178, 'Saint Kitts and Nevis', 'KN', 'KNA', 1),
(179, 'Saint Lucia', 'LC', 'LCA', 1),
(180, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 1),
(181, 'Samoa', 'WS', 'WSM', 1),
(182, 'San Marino', 'SM', 'SMR', 1),
(183, 'Sao Tome and Principe', 'ST', 'STP', 1),
(184, 'Saudi Arabia', 'SA', 'SAU', 1),
(185, 'Senegal', 'SN', 'SEN', 1),
(186, 'Seychelles', 'SC', 'SYC', 1),
(187, 'Sierra Leone', 'SL', 'SLE', 1),
(188, 'Singapore', 'SG', 'SGP', 4),
(189, 'Slovakia (Slovak Republic)', 'SK', 'SVK', 1),
(190, 'Slovenia', 'SI', 'SVN', 1),
(191, 'Solomon Islands', 'SB', 'SLB', 1),
(192, 'Somalia', 'SO', 'SOM', 1),
(193, 'South Africa', 'ZA', 'ZAF', 1),
(194, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', 1),
(195, 'Spain', 'ES', 'ESP', 3),
(196, 'Sri Lanka', 'LK', 'LKA', 1),
(197, 'St. Helena', 'SH', 'SHN', 1),
(198, 'St. Pierre and Miquelon', 'PM', 'SPM', 1),
(199, 'Sudan', 'SD', 'SDN', 1),
(200, 'Suriname', 'SR', 'SUR', 1),
(201, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM', 1),
(202, 'Swaziland', 'SZ', 'SWZ', 1),
(203, 'Sweden', 'SE', 'SWE', 1),
(204, 'Switzerland', 'CH', 'CHE', 1),
(205, 'Syrian Arab Republic', 'SY', 'SYR', 1),
(206, 'Taiwan', 'TW', 'TWN', 1),
(207, 'Tajikistan', 'TJ', 'TJK', 1),
(208, 'Tanzania, United Republic of', 'TZ', 'TZA', 1),
(209, 'Thailand', 'TH', 'THA', 1),
(210, 'Togo', 'TG', 'TGO', 1),
(211, 'Tokelau', 'TK', 'TKL', 1),
(212, 'Tonga', 'TO', 'TON', 1),
(213, 'Trinidad and Tobago', 'TT', 'TTO', 1),
(214, 'Tunisia', 'TN', 'TUN', 1),
(215, 'Turkey', 'TR', 'TUR', 1),
(216, 'Turkmenistan', 'TM', 'TKM', 1),
(217, 'Turks and Caicos Islands', 'TC', 'TCA', 1),
(218, 'Tuvalu', 'TV', 'TUV', 1),
(219, 'Uganda', 'UG', 'UGA', 1),
(220, 'Ukraine', 'UA', 'UKR', 1),
(221, 'United Arab Emirates', 'AE', 'ARE', 1),
(222, 'United Kingdom', 'GB', 'GBR', 1),
(223, 'United States', 'US', 'USA', 2),
(224, 'United States Minor Outlying Islands', 'UM', 'UMI', 1),
(225, 'Uruguay', 'UY', 'URY', 1),
(226, 'Uzbekistan', 'UZ', 'UZB', 1),
(227, 'Vanuatu', 'VU', 'VUT', 1),
(228, 'Vatican City State (Holy See)', 'VA', 'VAT', 1),
(229, 'Venezuela', 'VE', 'VEN', 1),
(230, 'Viet Nam', 'VN', 'VNM', 1),
(231, 'Virgin Islands (British)', 'VG', 'VGB', 1),
(232, 'Virgin Islands (U.S.)', 'VI', 'VIR', 1),
(233, 'Wallis and Futuna Islands', 'WF', 'WLF', 1),
(234, 'Western Sahara', 'EH', 'ESH', 1),
(235, 'Yemen', 'YE', 'YEM', 1),
(236, 'Yugoslavia', 'YU', 'YUG', 1),
(237, 'Zaire', 'ZR', 'ZAR', 1),
(238, 'Zambia', 'ZM', 'ZMB', 1),
(239, 'Zimbabwe', 'ZW', 'ZWE', 1),
(240, 'Neverland (webapi) ', 'NV', 'NVL', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `currencies`
--

CREATE TABLE IF NOT EXISTS `currencies` (
  `currencies_id` int(11) NOT NULL auto_increment,
  `title` varchar(32) collate utf8_polish_ci NOT NULL,
  `code` char(3) collate utf8_polish_ci NOT NULL,
  `symbol_left` varchar(12) collate utf8_polish_ci default NULL,
  `symbol_right` varchar(12) collate utf8_polish_ci default NULL,
  `decimal_point` char(1) collate utf8_polish_ci default NULL,
  `thousands_point` char(1) collate utf8_polish_ci default NULL,
  `decimal_places` char(1) collate utf8_polish_ci default NULL,
  `value` float(13,8) default NULL,
  `last_updated` datetime default NULL,
  PRIMARY KEY  (`currencies_id`),
  KEY `idx_currencies_code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=6 ;

--
-- Zrzut danych tabeli `currencies`
--

INSERT INTO `currencies` (`currencies_id`, `title`, `code`, `symbol_left`, `symbol_right`, `decimal_point`, `thousands_point`, `decimal_places`, `value`, `last_updated`) VALUES
(1, 'US Dollar', 'USD', '$', '', '.', ',', '2', 0.31434679, '2009-05-30 15:17:09'),
(2, 'Euro', 'EUR', '', 'EUR', '.', ',', '2', 0.22427559, '2009-05-30 15:17:10'),
(5, 'Złoty', 'PLN', '', 'zł', ',', '.', '2', 1.00000000, NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `customers`
--

CREATE TABLE IF NOT EXISTS `customers` (
  `customers_id` int(11) NOT NULL auto_increment,
  `customers_gender` char(1) collate utf8_polish_ci NOT NULL,
  `customers_firstname` varchar(32) collate utf8_polish_ci NOT NULL,
  `customers_lastname` varchar(32) collate utf8_polish_ci NOT NULL,
  `customers_dob` datetime NOT NULL default '0000-00-00 00:00:00',
  `customers_email_address` varchar(96) collate utf8_polish_ci NOT NULL,
  `customers_default_address_id` int(11) default NULL,
  `customers_telephone` varchar(32) collate utf8_polish_ci NOT NULL,
  `customers_fax` varchar(32) collate utf8_polish_ci default NULL,
  `customers_password` varchar(40) collate utf8_polish_ci default NULL,
  `customers_newsletter` char(1) collate utf8_polish_ci default NULL,
  `guest_account` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`customers_id`),
  KEY `idx_customers_email_address` (`customers_email_address`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `customers`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `customers_basket`
--

CREATE TABLE IF NOT EXISTS `customers_basket` (
  `customers_basket_id` int(11) NOT NULL auto_increment,
  `customers_id` int(11) NOT NULL,
  `products_id` tinytext collate utf8_polish_ci NOT NULL,
  `customers_basket_quantity` int(2) NOT NULL,
  `final_price` decimal(15,4) default NULL,
  `customers_basket_date_added` char(8) collate utf8_polish_ci default NULL,
  PRIMARY KEY  (`customers_basket_id`),
  KEY `idx_customers_basket_customers_id` (`customers_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `customers_basket`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `customers_basket_attributes`
--

CREATE TABLE IF NOT EXISTS `customers_basket_attributes` (
  `customers_basket_attributes_id` int(11) NOT NULL auto_increment,
  `customers_id` int(11) NOT NULL,
  `products_id` tinytext collate utf8_polish_ci NOT NULL,
  `products_options_id` int(11) NOT NULL,
  `products_options_value_id` int(11) NOT NULL,
  PRIMARY KEY  (`customers_basket_attributes_id`),
  KEY `idx_customers_basket_att_customers_id` (`customers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `customers_basket_attributes`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `customers_info`
--

CREATE TABLE IF NOT EXISTS `customers_info` (
  `customers_info_id` int(11) NOT NULL,
  `customers_info_date_of_last_logon` datetime default NULL,
  `customers_info_number_of_logons` int(5) default NULL,
  `customers_info_date_account_created` datetime default NULL,
  `customers_info_date_account_last_modified` datetime default NULL,
  `global_product_notifications` int(1) default '0',
  PRIMARY KEY  (`customers_info_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `customers_info`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `ecard`
--

CREATE TABLE IF NOT EXISTS `ecard` (
  `ecard_orderID` int(11) NOT NULL auto_increment,
  `osc_orderID` int(11) default NULL,
  `payment_status` varchar(48) collate utf8_polish_ci NOT NULL default '',
  `payment_prevstatus` varchar(48) collate utf8_polish_ci NOT NULL default '',
  `crDATE` datetime NOT NULL,
  `modDATE` datetime NOT NULL,
  `type` int(4) NOT NULL default '0',
  `bin` varchar(7) collate utf8_polish_ci NOT NULL default '',
  PRIMARY KEY  (`ecard_orderID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `ecard`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `geo_zones`
--

CREATE TABLE IF NOT EXISTS `geo_zones` (
  `geo_zone_id` int(11) NOT NULL auto_increment,
  `geo_zone_name` varchar(32) collate utf8_polish_ci NOT NULL,
  `geo_zone_description` varchar(255) collate utf8_polish_ci NOT NULL,
  `last_modified` datetime default NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY  (`geo_zone_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `geo_zones`
--

INSERT INTO `geo_zones` (`geo_zone_id`, `geo_zone_name`, `geo_zone_description`, `last_modified`, `date_added`) VALUES
(2, 'Polska', '', NULL, '2008-06-11 12:10:31');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `languages_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) collate utf8_polish_ci NOT NULL,
  `code` char(2) collate utf8_polish_ci NOT NULL,
  `image` varchar(64) collate utf8_polish_ci default NULL,
  `directory` varchar(32) collate utf8_polish_ci default NULL,
  `sort_order` int(3) default NULL,
  PRIMARY KEY  (`languages_id`),
  KEY `IDX_LANGUAGES_NAME` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=8 ;

--
-- Zrzut danych tabeli `languages`
--

INSERT INTO `languages` (`languages_id`, `name`, `code`, `image`, `directory`, `sort_order`) VALUES
(1, 'Polski', 'pl', 'icon.gif', 'polish', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `manufacturers`
--

CREATE TABLE IF NOT EXISTS `manufacturers` (
  `manufacturers_id` int(11) NOT NULL auto_increment,
  `manufacturers_name` varchar(32) collate utf8_polish_ci NOT NULL,
  `manufacturers_image` varchar(64) collate utf8_polish_ci default NULL,
  `date_added` datetime default NULL,
  `last_modified` datetime default NULL,
  PRIMARY KEY  (`manufacturers_id`),
  KEY `IDX_MANUFACTURERS_NAME` (`manufacturers_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=10 ;

--
-- Zrzut danych tabeli `manufacturers`
--

INSERT INTO `manufacturers` (`manufacturers_id`, `manufacturers_name`, `manufacturers_image`, `date_added`, `last_modified`) VALUES
(1, 'Matrox', '', '2008-12-12 19:02:38', '2008-12-12 21:17:41'),
(2, 'Microsoft', '', '2008-12-12 19:02:38', '2008-12-12 21:18:03'),
(3, 'Warner', '', '2008-12-12 19:02:38', '2008-12-12 21:18:13'),
(4, 'Fox', '', '2008-12-12 19:02:38', '2008-12-12 21:16:35'),
(5, 'Logitech', '', '2008-12-12 19:02:38', '2008-12-12 21:17:36'),
(6, 'Canon', '', '2008-12-12 19:02:38', '2008-12-12 21:16:25'),
(7, 'Sierra', '', '2008-12-12 19:02:38', '2008-12-12 21:18:08'),
(8, 'GT Interactive', '', '2008-12-12 19:02:38', '2008-12-12 21:16:42'),
(9, 'Hewlett Packard', '', '2008-12-12 19:02:38', '2008-12-12 21:17:31');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `manufacturers_info`
--

CREATE TABLE IF NOT EXISTS `manufacturers_info` (
  `manufacturers_id` int(11) NOT NULL,
  `languages_id` int(11) NOT NULL,
  `manufacturers_url` varchar(255) collate utf8_polish_ci NOT NULL,
  `url_clicked` int(5) NOT NULL default '0',
  `date_last_click` datetime default NULL,
  PRIMARY KEY  (`manufacturers_id`,`languages_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `manufacturers_info`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `newsletters_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate utf8_polish_ci NOT NULL,
  `content` text collate utf8_polish_ci NOT NULL,
  `module` varchar(255) collate utf8_polish_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `date_sent` datetime default NULL,
  `status` int(1) default NULL,
  `locked` int(1) default '0',
  PRIMARY KEY  (`newsletters_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `newsletters`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `orders_id` int(11) NOT NULL auto_increment,
  `customers_id` int(11) NOT NULL,
  `customers_name` varchar(64) collate utf8_polish_ci NOT NULL,
  `customers_company` varchar(32) collate utf8_polish_ci default NULL,
  `customers_nip` varchar(32) collate utf8_polish_ci NOT NULL,
  `customers_street_address` varchar(64) collate utf8_polish_ci NOT NULL,
  `customers_suburb` varchar(32) collate utf8_polish_ci default NULL,
  `customers_city` varchar(32) collate utf8_polish_ci NOT NULL,
  `customers_postcode` varchar(10) collate utf8_polish_ci NOT NULL,
  `customers_state` varchar(32) collate utf8_polish_ci default NULL,
  `customers_country` varchar(32) collate utf8_polish_ci NOT NULL,
  `customers_telephone` varchar(32) collate utf8_polish_ci NOT NULL,
  `customers_email_address` varchar(96) collate utf8_polish_ci NOT NULL,
  `customers_address_format_id` int(5) NOT NULL,
  `customers_dummy_account` tinyint(3) unsigned NOT NULL,
  `delivery_name` varchar(64) collate utf8_polish_ci NOT NULL,
  `delivery_company` varchar(32) collate utf8_polish_ci default NULL,
  `delivery_nip` varchar(32) collate utf8_polish_ci NOT NULL,
  `delivery_street_address` varchar(64) collate utf8_polish_ci NOT NULL,
  `delivery_suburb` varchar(32) collate utf8_polish_ci default NULL,
  `delivery_city` varchar(32) collate utf8_polish_ci NOT NULL,
  `delivery_postcode` varchar(10) collate utf8_polish_ci NOT NULL,
  `delivery_state` varchar(32) collate utf8_polish_ci default NULL,
  `delivery_country` varchar(32) collate utf8_polish_ci NOT NULL,
  `delivery_address_format_id` int(5) NOT NULL,
  `billing_name` varchar(64) collate utf8_polish_ci NOT NULL,
  `billing_company` varchar(32) collate utf8_polish_ci default NULL,
  `billing_nip` varchar(32) collate utf8_polish_ci NOT NULL,
  `billing_street_address` varchar(64) collate utf8_polish_ci NOT NULL,
  `billing_suburb` varchar(32) collate utf8_polish_ci default NULL,
  `billing_city` varchar(32) collate utf8_polish_ci NOT NULL,
  `billing_postcode` varchar(10) collate utf8_polish_ci NOT NULL,
  `billing_state` varchar(32) collate utf8_polish_ci default NULL,
  `billing_country` varchar(32) collate utf8_polish_ci NOT NULL,
  `billing_address_format_id` int(5) NOT NULL,
  `payment_method` varchar(255) collate utf8_polish_ci NOT NULL,
  `transport_method` varchar(255) collate utf8_polish_ci NOT NULL,
  `cc_type` varchar(20) collate utf8_polish_ci default NULL,
  `cc_owner` varchar(64) collate utf8_polish_ci default NULL,
  `cc_number` varchar(32) collate utf8_polish_ci default NULL,
  `cc_expires` varchar(4) collate utf8_polish_ci default NULL,
  `last_modified` datetime default NULL,
  `date_purchased` datetime default NULL,
  `orders_status` int(5) NOT NULL,
  `orders_date_finished` datetime default NULL,
  `currency` char(3) collate utf8_polish_ci default NULL,
  `currency_value` decimal(14,6) default NULL,
  `p24_session_id` varchar(255) collate utf8_polish_ci NOT NULL default '',
  `p24_languages_code` varchar(25) collate utf8_polish_ci NOT NULL default '',
  PRIMARY KEY  (`orders_id`),
  KEY `idx_orders_customers_id` (`customers_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=4 ;

--
-- Zrzut danych tabeli `orders`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `orders_products`
--

CREATE TABLE IF NOT EXISTS `orders_products` (
  `orders_products_id` int(11) NOT NULL auto_increment,
  `orders_id` int(11) NOT NULL,
  `products_id` int(11) NOT NULL,
  `products_model` varchar(12) collate utf8_polish_ci default NULL,
  `products_name` varchar(64) collate utf8_polish_ci NOT NULL,
  `products_price` decimal(15,4) NOT NULL,
  `final_price` decimal(15,4) NOT NULL,
  `products_tax` decimal(7,4) NOT NULL,
  `products_quantity` int(2) NOT NULL,
  PRIMARY KEY  (`orders_products_id`),
  KEY `idx_orders_products_orders_id` (`orders_id`),
  KEY `idx_orders_products_products_id` (`products_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=4 ;

--
-- Zrzut danych tabeli `orders_products`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `orders_products_attributes`
--

CREATE TABLE IF NOT EXISTS `orders_products_attributes` (
  `orders_products_attributes_id` int(11) NOT NULL auto_increment,
  `orders_id` int(11) NOT NULL,
  `orders_products_id` int(11) NOT NULL,
  `products_options` varchar(32) collate utf8_polish_ci NOT NULL,
  `products_options_values` varchar(32) collate utf8_polish_ci NOT NULL,
  `options_values_price` decimal(15,4) NOT NULL,
  `price_prefix` char(1) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`orders_products_attributes_id`),
  KEY `idx_orders_products_att_orders_id` (`orders_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `orders_products_attributes`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `orders_products_download`
--

CREATE TABLE IF NOT EXISTS `orders_products_download` (
  `orders_products_download_id` int(11) NOT NULL auto_increment,
  `orders_id` int(11) NOT NULL default '0',
  `orders_products_id` int(11) NOT NULL default '0',
  `orders_products_filename` varchar(255) collate utf8_polish_ci NOT NULL default '',
  `download_maxdays` int(2) NOT NULL default '0',
  `download_count` int(2) NOT NULL default '0',
  PRIMARY KEY  (`orders_products_download_id`),
  KEY `idx_orders_products_download_orders_id` (`orders_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `orders_products_download`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `orders_status`
--

CREATE TABLE IF NOT EXISTS `orders_status` (
  `orders_status_id` int(11) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '1',
  `orders_status_name` varchar(32) collate utf8_polish_ci NOT NULL,
  `public_flag` int(11) default '1',
  `downloads_flag` int(11) default '0',
  PRIMARY KEY  (`orders_status_id`,`language_id`),
  KEY `idx_orders_status_name` (`orders_status_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `orders_status`
--

INSERT INTO `orders_status` (`orders_status_id`, `language_id`, `orders_status_name`, `public_flag`, `downloads_flag`) VALUES
(4, 1, 'Oczekujący na realizację', 1, 0),
(5, 1, 'W trakcie realizacji', 1, 0),
(6, 1, 'Wysłano do klienta', 1, 0),
(7, 1, 'Zrealizowano', 1, 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `orders_status_history`
--

CREATE TABLE IF NOT EXISTS `orders_status_history` (
  `orders_status_history_id` int(11) NOT NULL auto_increment,
  `orders_id` int(11) NOT NULL,
  `orders_status_id` int(5) NOT NULL,
  `date_added` datetime NOT NULL,
  `customer_notified` int(1) default '0',
  `comments` text collate utf8_polish_ci,
  PRIMARY KEY  (`orders_status_history_id`),
  KEY `idx_orders_status_history_orders_id` (`orders_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=4 ;

--
-- Zrzut danych tabeli `orders_status_history`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `orders_total`
--

CREATE TABLE IF NOT EXISTS `orders_total` (
  `orders_total_id` int(10) unsigned NOT NULL auto_increment,
  `orders_id` int(11) NOT NULL,
  `title` varchar(255) collate utf8_polish_ci NOT NULL,
  `text` varchar(255) collate utf8_polish_ci NOT NULL,
  `value` decimal(15,4) NOT NULL,
  `class` varchar(32) collate utf8_polish_ci NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY  (`orders_total_id`),
  KEY `idx_orders_total_orders_id` (`orders_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=10 ;

--
-- Zrzut danych tabeli `orders_total`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `pages_id` int(11) NOT NULL auto_increment,
  `sort_order` int(11) default NULL,
  `status` int(11) NOT NULL default '1',
  PRIMARY KEY  (`pages_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Zrzut danych tabeli `pages`
--

INSERT INTO `pages` (`pages_id`, `sort_order`, `status`) VALUES
(1, 1, 1),
(2, 4, 1),
(3, 2, 0),
(6, 5, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `pages_description`
--

CREATE TABLE IF NOT EXISTS `pages_description` (
  `id` int(11) NOT NULL auto_increment,
  `pages_id` int(11) NOT NULL,
  `pages_title` varchar(128) NOT NULL,
  `pages_html_text` text,
  `intorext` char(1) default NULL,
  `externallink` varchar(255) default NULL,
  `language_id` int(3) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pages_id` (`pages_id`,`language_id`),
  UNIQUE KEY `pages_id_2` (`pages_id`,`language_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Zrzut danych tabeli `pages_description`
--

INSERT INTO `pages_description` (`id`, `pages_id`, `pages_title`, `pages_html_text`, `intorext`, `externallink`, `language_id`) VALUES
(1, 1, 'Regulamin', 'tutaj bÄ™dzie regulamin', '0', '', 1),
(5, 2, 'Kontakt', 'kontakt', '1', 'contact_us.php', 1),
(9, 3, 'Dostawa', 'dostawa', '0', '', 1),
(21, 6, 'Mapa strony', '', '1', 'sitemap.php', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `porownywarki_kategorie`
--

CREATE TABLE IF NOT EXISTS `porownywarki_kategorie` (
  `kategoria_porownywarka` varchar(32) NOT NULL COMMENT 'nazwa porównywarki',
  `kategoria_id` int(11) NOT NULL auto_increment,
  `kategoria_nazwa` varchar(255) NOT NULL,
  `kategoria_lisc` tinyint(1) NOT NULL,
  PRIMARY KEY  (`kategoria_porownywarka`,`kategoria_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `porownywarki_kategorie`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `porownywarki_kategorie_download`
--

CREATE TABLE IF NOT EXISTS `porownywarki_kategorie_download` (
  `kategorie_download_id` int(11) NOT NULL auto_increment,
  `kategorie_download_porownywarka` varchar(32) NOT NULL COMMENT 'nazwa porównywarki',
  `kategorie_download_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `kategorie_download_duration` float NOT NULL default '0',
  `kategorie_download_log` varchar(250) NOT NULL,
  `kategorie_download_ilosc` int(11) NOT NULL default '0',
  PRIMARY KEY  (`kategorie_download_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=64 ;

--
-- Zrzut danych tabeli `porownywarki_kategorie_download`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `porownywarki_kategorie_to_categories`
--

CREATE TABLE IF NOT EXISTS `porownywarki_kategorie_to_categories` (
  `pktc_id` int(11) NOT NULL auto_increment,
  `kategoria_porownywarka` varchar(20) NOT NULL,
  `kategoria_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL,
  PRIMARY KEY  (`pktc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=31 ;

--
-- Zrzut danych tabeli `porownywarki_kategorie_to_categories`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `porownywarki_monitor`
--

CREATE TABLE IF NOT EXISTS `porownywarki_monitor` (
  `monitor_id` int(11) NOT NULL auto_increment,
  `monitor_data` datetime NOT NULL default '0000-00-00 00:00:00',
  `monitor_wtyczka` varchar(32) NOT NULL,
  `monitor_IP` varchar(50) NOT NULL default '',
  `monitor_host` varchar(100) NOT NULL default '',
  `monitor_HTTP_USER_AGENT` varchar(250) character set latin1 NOT NULL default '',
  `monitor_duration` float NOT NULL default '0',
  `monitor_iloscOfert` int(11) NOT NULL default '0',
  PRIMARY KEY  (`monitor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=119 ;

--
-- Zrzut danych tabeli `porownywarki_monitor`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `products_id` int(11) NOT NULL auto_increment,
  `products_quantity` int(4) NOT NULL,
  `products_model` varchar(64) collate utf8_polish_ci default NULL,
  `products_image` varchar(64) collate utf8_polish_ci default NULL,
  `products_price` decimal(15,4) NOT NULL COMMENT 'W PLN wyświetlana na sklepie klientowi',
  `products_price_define` decimal(15,4) NOT NULL COMMENT 'Cena zdefiniowana przez obsługę sklepu',
  `products_price_define_currency_code` varchar(3) collate utf8_polish_ci NOT NULL default 'PLN' COMMENT 'Waluta, w której zdefiniowała obsługa sklepu',
  `products_date_added` datetime NOT NULL,
  `products_last_modified` datetime default NULL,
  `products_date_available` datetime default NULL,
  `products_weight` decimal(5,2) NOT NULL,
  `products_status` tinyint(1) NOT NULL,
  `products_tax_class_id` int(11) NOT NULL,
  `manufacturers_id` int(11) default NULL,
  `products_ordered` int(11) NOT NULL default '0',
  PRIMARY KEY  (`products_id`),
  KEY `idx_products_model` (`products_model`),
  KEY `idx_products_date_added` (`products_date_added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=32 ;

--
-- Zrzut danych tabeli `products`
--

INSERT INTO `products` (`products_id`, `products_quantity`, `products_model`, `products_image`, `products_price`, `products_price_define`, `products_price_define_currency_code`, `products_date_added`, `products_last_modified`, `products_date_available`, `products_weight`, `products_status`, `products_tax_class_id`, `manufacturers_id`, `products_ordered`) VALUES
(1, 30, 'MG200MMS', 'matrox/mg200mms.gif', 299.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 23.00, 1, 1, 1, 2),
(2, 32, 'MG400-32MB', 'matrox/mg400-32mb.gif', 499.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 23.00, 1, 1, 1, 0),
(3, 1, 'MSIMPRO', 'microsoft/msimpro.gif', 49.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 1),
(4, 13, 'DVD-RPMK', 'dvd/replacement_killers.gif', 42.0000, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 23.00, 1, 1, 2, 0),
(5, 17, 'DVD-BLDRNDC', 'dvd/blade_runner.gif', 35.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(6, 10, 'DVD-MATR', 'dvd/the_matrix.gif', 39.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(7, 10, 'DVD-YGEM', 'dvd/youve_got_mail.gif', 34.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(8, 10, 'DVD-ABUG', 'dvd/a_bugs_life.gif', 35.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(9, 10, 'DVD-UNSG', 'dvd/under_siege.gif', 29.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(10, 10, 'DVD-UNSG2', 'dvd/under_siege2.gif', 29.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(11, 10, 'DVD-FDBL', 'dvd/fire_down_below.gif', 29.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(12, 10, 'DVD-DHWV', 'dvd/die_hard_3.gif', 39.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 4, 0),
(13, 10, 'DVD-LTWP', 'dvd/lethal_weapon.gif', 34.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(14, 10, 'DVD-REDC', 'dvd/red_corner.gif', 32.0000, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(15, 10, 'DVD-FRAN', 'dvd/frantic.gif', 35.0000, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(16, 10, 'DVD-CUFI', 'dvd/courage_under_fire.gif', 38.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 4, 0),
(17, 10, 'DVD-SPEED', 'dvd/speed.gif', 39.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 4, 0),
(18, 10, 'DVD-SPEED2', 'dvd/speed_2.gif', 42.0000, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 4, 0),
(19, 8, 'DVD-TSAB', 'dvd/theres_something_about_mary.gif', 49.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 4, 2),
(20, 10, 'DVD-BELOVED', 'dvd/beloved.gif', 54.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 3, 0),
(21, 16, 'PC-SWAT3', 'sierra/swat_3.gif', 79.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 7, 0),
(22, 11, 'PC-UNTM', 'gt_interactive/unreal_tournament.gif', 89.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 7.00, 1, 1, 8, 2),
(23, 16, 'PC-TWOF', 'gt_interactive/wheel_of_time.gif', 99.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 10.00, 1, 1, 8, 0),
(24, 17, 'PC-DISC', 'gt_interactive/disciples.gif', 90.0000, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 8.00, 1, 1, 8, 0),
(25, 12, 'MSINTKB', 'microsoft/intkeyboardps2.gif', 69.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 8.00, 1, 1, 2, 4),
(26, 9, 'MSIMEXP', 'microsoft/imexplorer.gif', 64.9500, 0.0000, 'PLN', '2008-12-12 19:02:38', NULL, NULL, 8.00, 1, 1, 2, 1),
(27, 2, 'HPLJ1100XI', 'hp_laserjet_1100.jpg', 499.9900, 0.0000, 'PLN', '2008-12-12 19:02:38', '2009-02-20 18:36:05', NULL, 45.00, 1, 2, 9, 6);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_attributes`
--

CREATE TABLE IF NOT EXISTS `products_attributes` (
  `products_attributes_id` int(11) NOT NULL auto_increment,
  `products_id` int(11) NOT NULL,
  `options_id` int(11) NOT NULL,
  `options_values_id` int(11) NOT NULL,
  `options_values_price` decimal(15,4) NOT NULL,
  `price_prefix` char(1) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`products_attributes_id`),
  KEY `idx_products_attributes_products_id` (`products_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=28 ;

--
-- Zrzut danych tabeli `products_attributes`
--

INSERT INTO `products_attributes` (`products_attributes_id`, `products_id`, `options_id`, `options_values_id`, `options_values_price`, `price_prefix`) VALUES
(1, 1, 4, 1, 0.0000, '+'),
(2, 1, 4, 2, 50.0000, '+'),
(3, 1, 4, 3, 70.0000, '+'),
(4, 1, 3, 5, 0.0000, '+'),
(5, 1, 3, 6, 100.0000, '+'),
(6, 2, 4, 3, 10.0000, '-'),
(7, 2, 4, 4, 0.0000, '+'),
(8, 2, 3, 6, 0.0000, '+'),
(9, 2, 3, 7, 120.0000, '+'),
(10, 26, 3, 8, 0.0000, '+'),
(11, 26, 3, 9, 6.0000, '+'),
(26, 22, 5, 10, 0.0000, '+'),
(27, 22, 5, 13, 0.0000, '+');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_attributes_download`
--

CREATE TABLE IF NOT EXISTS `products_attributes_download` (
  `products_attributes_id` int(11) NOT NULL,
  `products_attributes_filename` varchar(255) collate utf8_polish_ci NOT NULL default '',
  `products_attributes_maxdays` int(2) default '0',
  `products_attributes_maxcount` int(2) default '0',
  PRIMARY KEY  (`products_attributes_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `products_attributes_download`
--

INSERT INTO `products_attributes_download` (`products_attributes_id`, `products_attributes_filename`, `products_attributes_maxdays`, `products_attributes_maxcount`) VALUES
(26, 'unreal.zip', 7, 3);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_description`
--

CREATE TABLE IF NOT EXISTS `products_description` (
  `products_id` int(11) NOT NULL auto_increment,
  `language_id` int(11) NOT NULL default '1',
  `products_name` varchar(64) collate utf8_polish_ci NOT NULL default '',
  `products_description` text collate utf8_polish_ci,
  `products_url` varchar(255) collate utf8_polish_ci default NULL,
  `products_viewed` int(5) default '0',
  PRIMARY KEY  (`products_id`,`language_id`),
  KEY `products_name` (`products_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=28 ;

--
-- Zrzut danych tabeli `products_description`
--

INSERT INTO `products_description` (`products_id`, `language_id`, `products_name`, `products_description`, `products_url`, `products_viewed`) VALUES
(1, 1, 'Matrox G200 MMS', 'Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8&quot; PCI board.<br><br>With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.<br><br>Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD.', 'www.matrox.com/mga/products/g200_mms/home.cfm', 9),
(2, 1, 'Matrox G400 32MB', '<b>Dramatically Different High Performance Graphics</b><br><br>Introducing the Millennium G400 Series - a dramatically different, high performance graphics experience. Armed with the industry''s fastest graphics chip, the Millennium G400 Series takes explosive acceleration two steps further by adding unprecedented image quality, along with the most versatile display options for all your 3D, 2D and DVD applications. As the most powerful and innovative tools in your PC''s arsenal, the Millennium G400 Series will not only change the way you see graphics, but will revolutionize the way you use your computer.<br><br><b>Key features:</b><ul><li>New Matrox G400 256-bit DualBus graphics chip</li><li>Explosive 3D, 2D and DVD performance</li><li>DualHead Display</li><li>Superior DVD and TV output</li><li>3D Environment-Mapped Bump Mapping</li><li>Vibrant Color Quality rendering </li><li>UltraSharp DAC of up to 360 MHz</li><li>3D Rendering Array Processor</li><li>Support for 16 or 32 MB of memory</li></ul>', 'www.matrox.com/mga/products/mill_g400/home.htm', 2),
(3, 1, 'Microsoft IntelliMouse Pro', 'Every element of IntelliMouse Pro - from its unique arched shape to the texture of the rubber grip around its base - is the product of extensive customer and ergonomic research. Microsoft''s popular wheel control, which now allows zooming and universal scrolling functions, gives IntelliMouse Pro outstanding comfort and efficiency.', 'www.microsoft.com/hardware/mouse/intellimouse.asp', 3),
(4, 1, 'The Replacement Killers', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>Languages: English, Deutsch.<br>Subtitles: English, Deutsch, Spanish.<br>Audio: Dolby Surround 5.1.<br>Picture Format: 16:9 Wide-Screen.<br>Length: (approx) 80 minutes.<br>Other: Interactive Menus, Chapter Selection, Subtitles (more languages).', 'www.replacement-killers.com', 0),
(5, 1, 'Blade Runner - Director''s Cut', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>Languages: English, Deutsch.<br>Subtitles: English, Deutsch, Spanish.<br>Audio: Dolby Surround 5.1.<br>Picture Format: 16:9 Wide-Screen.<br>Length: (approx) 112 minutes.<br>Other: Interactive Menus, Chapter Selection, Subtitles (more languages).', 'www.bladerunner.com', 3),
(6, 1, 'The Matrix', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch.\r<br>\nAudio: Dolby Surround.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 131 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Making Of.', 'www.thematrix.com', 2),
(7, 1, 'You''ve Got Mail', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch, Spanish.\r<br>\nSubtitles: English, Deutsch, Spanish, French, Nordic, Polish.\r<br>\nAudio: Dolby Digital 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 115 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', 'www.youvegotmail.com', 0),
(8, 1, 'A Bug''s Life', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Digital 5.1 / Dobly Surround Stereo.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 91 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', 'www.abugslife.com', 0),
(9, 1, 'Under Siege', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 98 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 2),
(10, 1, 'Under Siege 2 - Dark Territory', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 98 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 0),
(11, 1, 'Fire Down Below', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 100 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 2),
(12, 1, 'Die Hard With A Vengeance', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 122 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 0),
(13, 1, 'Lethal Weapon', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 100 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 0),
(14, 1, 'Red Corner', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 117 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 0),
(15, 1, 'Frantic', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 115 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 1),
(16, 1, 'Courage Under Fire', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 112 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 0),
(17, 1, 'Speed', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 112 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 0),
(18, 1, 'Speed 2: Cruise Control', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 120 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 13),
(19, 1, 'There''s Something About Mary', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 114 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 7),
(20, 1, 'Beloved', 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 164 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).', '', 9),
(21, 1, 'SWAT 3: Close Quarters Battle', '<b>Windows 95/98</b><br><br>211 in progress with shots fired. Officer down. Armed suspects with hostages. Respond Code 3! Los Angles, 2005, In the next seven days, representatives from every nation around the world will converge on Las Angles to witness the signing of the United Nations Nuclear Abolishment Treaty. The protection of these dignitaries falls on the shoulders of one organization, LAPD SWAT. As part of this elite tactical organization, you and your team have the weapons and all the training necessary to protect, to serve, and "When needed" to use deadly force to keep the peace. It takes more than weapons to make it through each mission. Your arsenal includes C2 charges, flashbangs, tactical grenades. opti-Wand mini-video cameras, and other devices critical to meeting your objectives and keeping your men free of injury. Uncompromised Duty, Honor and Valor!', 'www.swat3.com', 4),
(22, 1, 'Unreal Tournament', 'From the creators of the best-selling Unreal, comes Unreal Tournament. A new kind of single player experience. A ruthless multiplayer revolution.<br><br>This stand-alone game showcases completely new team-based gameplay, groundbreaking multi-faceted single player action or dynamic multi-player mayhem. It''s a fight to the finish for the title of Unreal Grand Master in the gladiatorial arena. A single player experience like no other! Guide your team of ''bots'' (virtual teamates) against the hardest criminals in the galaxy for the ultimate title - the Unreal Grand Master.', 'www.unrealtournament.net', 14),
(23, 1, 'The Wheel Of Time', 'The world in which The Wheel of Time takes place is lifted directly out of Jordan''s pages; it''s huge and consists of many different environments. How you navigate the world will depend largely on which game - single player or multipayer - you''re playing. The single player experience, with a few exceptions, will see Elayna traversing the world mainly by foot (with a couple notable exceptions). In the multiplayer experience, your character will have more access to travel via Ter''angreal, Portal Stones, and the Ways. However you move around, though, you''ll quickly discover that means of locomotion can easily become the least of the your worries...<br><br>During your travels, you quickly discover that four locations are crucial to your success in the game. Not surprisingly, these locations are the homes of The Wheel of Time''s main characters. Some of these places are ripped directly from the pages of Jordan''s books, made flesh with Legend''s unparalleled pixel-pushing ways. Other places are specific to the game, conceived and executed with the intent of expanding this game world even further. Either way, they provide a backdrop for some of the most intense first person action and strategy you''ll have this year.', 'www.wheeloftime.com', 13),
(24, 1, 'Disciples: Sacred Lands', 'A new age is dawning...<br><br>Enter the realm of the Sacred Lands, where the dawn of a New Age has set in motion the most momentous of wars. As the prophecies long foretold, four races now clash with swords and sorcery in a desperate bid to control the destiny of their gods. Take on the quest as a champion of the Empire, the Mountain Clans, the Legions of the Damned, or the Undead Hordes and test your faith in battles of brute force, spellbinding magic and acts of guile. Slay demons, vanquish giants and combat merciless forces of the dead and undead. But to ensure the salvation of your god, the hero within must evolve.<br><br>The day of reckoning has come... and only the chosen will survive.', '', 11),
(25, 1, 'Microsoft Internet Keyboard PS/2', 'The Internet Keyboard has 10 Hot Keys on a comfortable standard keyboard design that also includes a detachable palm rest. The Hot Keys allow you to browse the web, or check e-mail directly from your keyboard. The IntelliType Pro software also allows you to customize your hot keys - make the Internet Keyboard work the way you want it to!', '', 11),
(26, 1, 'Microsoft IntelliMouse Explorer', 'Microsoft introduces its most advanced mouse, the IntelliMouse Explorer! IntelliMouse Explorer features a sleek design, an industrial-silver finish, a glowing red underside and taillight, creating a style and look unlike any other mouse. IntelliMouse Explorer combines the accuracy and reliability of Microsoft IntelliEye optical tracking technology, the convenience of two new customizable function buttons, the efficiency of the scrolling wheel and the comfort of expert ergonomic design. All these great features make this the best mouse for the PC!', 'www.microsoft.com/hardware/mouse/explorer.asp', 11),
(27, 1, 'Hewlett Packard LaserJet 1100Xi', 'HP has always set the pace in laser printing technology. The new generation HP LaserJet 1100 series sets another impressive pace, delivering a stunning 8 pages per minute print speed. The 600 dpi print resolution with HP''s Resolution Enhancement technology (REt) makes every document more professional.\r\n\r\nEnhanced print speed and laser quality results are just the beginning. With 2MB standard memory, HP LaserJet 1100xi users will be able to print increasingly complex pages. Memory can be increased to 18MB to tackle even more complex documents with ease. The HP LaserJet 1100xi supports key operating systems including Windows 3.1, 3.11, 95, 98, NT 4.0, OS/2 and DOS. Network compatibility available via the optional HP JetDirect External Print Servers.\r\n\r\nHP LaserJet 1100xi also features The Document Builder for the Web Era from Trellix Corp. (featuring software to create Web documents).', 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100', 85);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_files`
--

CREATE TABLE IF NOT EXISTS `products_files` (
  `products_files_id` int(11) NOT NULL auto_increment,
  `products_id` int(11) NOT NULL,
  `products_files_file` varchar(64) collate utf8_polish_ci NOT NULL,
  `products_files_title` varchar(128) collate utf8_polish_ci NOT NULL,
  `products_files_order` int(11) NOT NULL,
  `products_files_size` int(11) NOT NULL,
  PRIMARY KEY  (`products_files_id`),
  KEY `products_id` (`products_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=7 ;

--
-- Zrzut danych tabeli `products_files`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_notifications`
--

CREATE TABLE IF NOT EXISTS `products_notifications` (
  `products_id` int(11) NOT NULL,
  `customers_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY  (`products_id`,`customers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `products_notifications`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_options`
--

CREATE TABLE IF NOT EXISTS `products_options` (
  `products_options_id` int(11) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '1',
  `products_options_name` varchar(32) collate utf8_polish_ci NOT NULL default '',
  PRIMARY KEY  (`products_options_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `products_options`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_options_values`
--

CREATE TABLE IF NOT EXISTS `products_options_values` (
  `products_options_values_id` int(11) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '1',
  `products_options_values_name` varchar(64) collate utf8_polish_ci NOT NULL default '',
  PRIMARY KEY  (`products_options_values_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `products_options_values`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_options_values_to_products_options`
--

CREATE TABLE IF NOT EXISTS `products_options_values_to_products_options` (
  `products_options_values_to_products_options_id` int(11) NOT NULL auto_increment,
  `products_options_id` int(11) NOT NULL,
  `products_options_values_id` int(11) NOT NULL,
  PRIMARY KEY  (`products_options_values_to_products_options_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=14 ;

--
-- Zrzut danych tabeli `products_options_values_to_products_options`
--

INSERT INTO `products_options_values_to_products_options` (`products_options_values_to_products_options_id`, `products_options_id`, `products_options_values_id`) VALUES
(1, 4, 1),
(2, 4, 2),
(3, 4, 3),
(4, 4, 4),
(5, 3, 5),
(6, 3, 6),
(7, 3, 7),
(8, 3, 8),
(9, 3, 9),
(10, 5, 10),
(13, 5, 13);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_pictures`
--

CREATE TABLE IF NOT EXISTS `products_pictures` (
  `products_pictures_id` int(11) NOT NULL auto_increment,
  `products_id` int(11) NOT NULL,
  `products_pictures_image` varchar(64) collate utf8_polish_ci NOT NULL,
  `products_pictures_order` int(11) NOT NULL,
  `products_pictures_size` int(11) NOT NULL,
  PRIMARY KEY  (`products_pictures_id`),
  KEY `products_id` (`products_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=88 ;

--
-- Zrzut danych tabeli `products_pictures`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `products_to_categories`
--

CREATE TABLE IF NOT EXISTS `products_to_categories` (
  `products_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL,
  PRIMARY KEY  (`products_id`,`categories_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `products_to_categories`
--

INSERT INTO `products_to_categories` (`products_id`, `categories_id`) VALUES
(1, 4),
(2, 4),
(3, 9),
(4, 10),
(5, 11),
(6, 10),
(7, 12),
(8, 13),
(9, 10),
(10, 10),
(11, 10),
(12, 10),
(13, 10),
(14, 15),
(15, 14),
(16, 15),
(17, 10),
(18, 10),
(19, 12),
(20, 15),
(21, 18),
(22, 19),
(23, 20),
(24, 20),
(25, 8),
(26, 9),
(27, 5);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `reviews`
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `reviews_id` int(11) NOT NULL auto_increment,
  `products_id` int(11) NOT NULL,
  `customers_id` int(11) default NULL,
  `customers_name` varchar(64) collate utf8_polish_ci NOT NULL,
  `reviews_rating` int(1) default NULL,
  `date_added` datetime default NULL,
  `last_modified` datetime default NULL,
  `reviews_read` int(5) NOT NULL default '0',
  PRIMARY KEY  (`reviews_id`),
  KEY `idx_reviews_products_id` (`products_id`),
  KEY `idx_reviews_customers_id` (`customers_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `reviews`
--

INSERT INTO `reviews` (`reviews_id`, `products_id`, `customers_id`, `customers_name`, `reviews_rating`, `date_added`, `last_modified`, `reviews_read`) VALUES
(1, 19, NULL, 'Jan Kowalski', 5, '2008-12-12 19:02:38', NULL, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `reviews_description`
--

CREATE TABLE IF NOT EXISTS `reviews_description` (
  `reviews_id` int(11) NOT NULL,
  `languages_id` int(11) NOT NULL,
  `reviews_text` text collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`reviews_id`,`languages_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `reviews_description`
--

INSERT INTO `reviews_description` (`reviews_id`, `languages_id`, `reviews_text`) VALUES
(1, 1, 'Jest to najzabawniejszy film z 1999 roku!');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `rewrites`
--

CREATE TABLE IF NOT EXISTS `rewrites` (
  `rewrite_id` int(11) NOT NULL auto_increment,
  `rewrite_rewrite` varchar(128) NOT NULL,
  `rewrite_script` varchar(32) NOT NULL,
  `rewrite_param_name` varchar(32) default NULL,
  `rewrite_param_value` varchar(32) default NULL,
  PRIMARY KEY  (`rewrite_id`),
  UNIQUE KEY `rewrite_rewrite` (`rewrite_rewrite`),
  UNIQUE KEY `rewrite_script` (`rewrite_script`,`rewrite_param_name`,`rewrite_param_value`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin2 AUTO_INCREMENT=487 ;

--
-- Zrzut danych tabeli `rewrites`
--

INSERT INTO `rewrites` (`rewrite_id`, `rewrite_rewrite`, `rewrite_script`, `rewrite_param_name`, `rewrite_param_value`) VALUES
(445, 'sprzet', 'index.php', 'cPath', '1'),
(446, 'oprogramowanie', 'index.php', 'cPath', '2'),
(447, 'filmy-dvd', 'index.php', 'cPath', '3'),
(448, 'hewlett-packard-laserjet-1100xi', 'product_info.php', 'products_id', '27'),
(449, 'microsoft-intellimouse-explorer', 'product_info.php', 'products_id', '26'),
(450, 'microsoft-internet-keyboard-ps2', 'product_info.php', 'products_id', '25'),
(451, 'disciples-sacred-lands', 'product_info.php', 'products_id', '24'),
(452, 'the-wheel-of-time', 'product_info.php', 'products_id', '23'),
(453, 'unreal-tournament', 'product_info.php', 'products_id', '22'),
(454, 'swat-3-close-quarters-battle', 'product_info.php', 'products_id', '21'),
(455, 'beloved', 'product_info.php', 'products_id', '20'),
(456, 'theres-something-about-mary', 'product_info.php', 'products_id', '19'),
(457, 'the-matrix', 'product_info.php', 'products_id', '6'),
(458, 'courage-under-fire', 'product_info.php', 'products_id', '16'),
(459, 'sprzet/drukarki', 'index.php', 'cPath', '1_5'),
(460, 'sprzet/myszki', 'index.php', 'cPath', '1_9'),
(461, 'sprzet/klawiatury', 'index.php', 'cPath', '1_8'),
(462, 'oprogramowanie/strategie', 'index.php', 'cPath', '2_20'),
(463, 'oprogramowanie/gry-akcji', 'index.php', 'cPath', '2_19'),
(464, 'oprogramowanie/symulacje', 'index.php', 'cPath', '2_18'),
(465, 'filmy-dvd/dramat', 'index.php', 'cPath', '3_15'),
(466, 'filmy-dvd/filmy-akcji', 'index.php', 'cPath', '3_10'),
(467, 'filmy-dvd/komedie', 'index.php', 'cPath', '3_12'),
(468, 'blade-runner-directors-cut', 'product_info.php', 'products_id', '5'),
(469, 'filmy-dvd/filmy-sf', 'index.php', 'cPath', '3_11'),
(470, 'microsoft-intellimouse-pro', 'product_info.php', 'products_id', '3'),
(471, 'filmy-dvd/dreszczowce', 'index.php', 'cPath', '3_14'),
(472, 'filmy-dvd/kreskowki', 'index.php', 'cPath', '3_13'),
(473, 'a-bugs-life', 'product_info.php', 'products_id', '8'),
(474, 'lethal-weapon', 'product_info.php', 'products_id', '13'),
(475, 'speed-2-cruise-control', 'product_info.php', 'products_id', '18'),
(476, 'under-siege-2-dark-territory', 'product_info.php', 'products_id', '10'),
(477, 'frantic', 'product_info.php', 'products_id', '15'),
(478, 'youve-got-mail', 'product_info.php', 'products_id', '7'),
(479, 'die-hard-with-a-vengeance', 'product_info.php', 'products_id', '12'),
(480, 'matrox-g200-mms', 'product_info.php', 'products_id', '1'),
(481, 'matrox-g400-32mb', 'product_info.php', 'products_id', '2'),
(482, 'sprzet/cd-rom', 'index.php', 'cPath', '1_17'),
(483, 'sprzet/glosniki', 'index.php', 'cPath', '1_7'),
(484, 'sprzet/karty-graficzne', 'index.php', 'cPath', '1_4'),
(485, 'sprzet/memory', 'index.php', 'cPath', '1_16'),
(486, 'sprzet/monitory', 'index.php', 'cPath', '1_6');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `seo`
--

CREATE TABLE IF NOT EXISTS `seo` (
  `seo_id` int(11) NOT NULL auto_increment,
  `script` varchar(32) collate utf8_polish_ci default NULL,
  `products_id` int(11) default NULL,
  `categories_id` int(11) default NULL,
  `seo_keywords` varchar(160) collate utf8_polish_ci NOT NULL default '',
  `seo_description` varchar(160) collate utf8_polish_ci NOT NULL default '',
  `seo_titlepostfix` varchar(160) collate utf8_polish_ci NOT NULL default '',
  PRIMARY KEY  (`seo_id`),
  KEY `script` (`script`,`products_id`,`categories_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Zrzut danych tabeli `seo`
--


-- --------------------------------------------------------

--
-- Struktura tabeli dla  `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `sesskey` varchar(32) collate utf8_polish_ci NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `value` text collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`sesskey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `sessions`
--

INSERT INTO `sessions` (`sesskey`, `expiry`, `value`) VALUES
('e321bd3d90f2ba1ad8e85e5cba027ec7', 1239116821, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('3dfa61ccf48a35589c4b620f3b9c29fc', 1239116881, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('e1dff18f17276d859c15e5fbc4d40979', 1239116942, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('d44f332fa723d7926583118a7b4560f2', 1239117001, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('b5fdf0b2482629ce3c84da10359e3696', 1239117061, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('d5af8003b7cbda68d6eb47489084d9bb', 1239117121, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('3b0515ff49f37e22e0539d6fdecf3fdb', 1239117182, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('001b76fad8cbef284000c48206be56fc', 1239117242, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('cd92422db2583636a9156dcb17d1ada7', 1239117302, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('f7c0c03626f4a9bc11002a7146b57508', 1239117362, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('e2511939e3fa60fb5e293953d3bf0b1b', 1239117421, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('dc56583a330ad2ac72c66de6750b4600', 1239117481, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('845c5acff2b0e6813664253bdac0c3be', 1239117542, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('52541ede429708fec314228514a8cce1', 1239117601, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('e775c90bb9c72a5f8b7d11a5f6ff565e', 1239117662, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('a39b2ca25060ca104e6a9dfd7f78b024', 1239117722, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('dbb1c06f3770ad4bc6436e3ef5095852', 1239117781, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('af4a3bfcf28686622abba28edfedeaf8', 1239117842, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('d3f3da88cecf86158f7e5326a463df84', 1239117901, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('836622d62933741952d0a34b8da6cd57', 1239117961, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('d479dc11b3120db09b2e15665f45dc4f', 1239118021, 'cart|N;language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";show_reviews|b:0;'),
('6ce37e10aedf32b0d343f734ead0bd52', 1241390799, 'language|s:6:"polish";languages_id|s:1:"1";redirect_origin|a:2:{s:4:"page";s:9:"index.php";s:3:"get";a:0:{}}selected_box|s:7:"catalog";admin|a:4:{s:2:"id";s:1:"1";s:8:"username";s:5:"admin";s:10:"userscript";s:29:"backup.php?selected_box=tools";s:8:"groupids";a:2:{i:0;s:1:"3";i:1;s:1:"1";}}'),
('e1a829fc1bf5ad5c0b0cbe5547fe737b', 1241682287, 'cart|O:12:"shoppingCart":5:{s:8:"contents";a:0:{}s:5:"total";i:0;s:6:"weight";i:0;s:6:"cartID";N;s:12:"content_type";b:0;}language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";navigation|N;show_reviews|b:0;'),
('84cd1b9d68a779e932f9694895f16815', 1241737642, 'cart|O:12:"shoppingCart":5:{s:8:"contents";a:0:{}s:5:"total";i:0;s:6:"weight";i:0;s:6:"cartID";N;s:12:"content_type";b:0;}language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";navigation|N;show_reviews|b:0;'),
('0f4597fdeaa803f52dadbad78c36d9d5', 1242139520, 'cart|O:12:"shoppingCart":5:{s:8:"contents";a:0:{}s:5:"total";i:0;s:6:"weight";i:0;s:6:"cartID";N;s:12:"content_type";b:0;}language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";navigation|O:17:"navigationHistory":2:{s:4:"path";a:2:{i:0;a:4:{s:4:"page";s:9:"index.php";s:4:"mode";s:6:"NONSSL";s:3:"get";a:0:{}s:4:"post";a:0:{}}i:1;a:4:{s:4:"page";s:19:"product_picture.php";s:4:"mode";s:6:"NONSSL";s:3:"get";a:4:{s:11:"products_id";s:1:"5";s:5:"width";s:3:"100";s:6:"height";s:2:"80";s:6:"strict";s:0:"";}s:4:"post";a:0:{}}}s:8:"snapshot";a:0:{}}show_reviews|b:0;'),
('a8d3c77e72215be84f5ca339c3f66f6a', 1243029764, 'cart|O:12:"shoppingCart":5:{s:8:"contents";a:0:{}s:5:"total";i:0;s:6:"weight";i:0;s:6:"cartID";N;s:12:"content_type";b:0;}language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";navigation|O:17:"navigationHistory":2:{s:4:"path";a:4:{i:0;a:4:{s:4:"page";s:9:"index.php";s:4:"mode";s:6:"NONSSL";s:3:"get";a:1:{s:5:"cPath";s:1:"3";}s:4:"post";a:0:{}}i:1;a:4:{s:4:"page";s:19:"product_picture.php";s:4:"mode";s:6:"NONSSL";s:3:"get";a:4:{s:11:"products_id";s:2:"15";s:5:"width";s:3:"100";s:6:"height";s:2:"80";s:6:"strict";s:0:"";}s:4:"post";a:0:{}}i:2;a:4:{s:4:"page";s:10:"router.php";s:4:"mode";s:6:"NONSSL";s:3:"get";a:3:{s:7:"rewrite";s:17:"templates/default";s:9:"script_js";s:0:"";s:13:"error_message";s:34:"Błąd 404 - nie znaleziono strony";}s:4:"post";a:0:{}}i:3;a:4:{s:4:"page";s:10:"router.php";s:4:"mode";s:6:"NONSSL";s:3:"get";a:3:{s:7:"rewrite";s:17:"templates/default";s:9:"script_js";s:0:"";s:13:"error_message";s:34:"Błąd 404 - nie znaleziono strony";}s:4:"post";a:0:{}}}s:8:"snapshot";a:0:{}}show_reviews|b:0;'),
('6f87881f871248cb2f61dfd8144775ad', 1243692777, 'cart|O:12:"shoppingCart":5:{s:8:"contents";a:0:{}s:5:"total";i:0;s:6:"weight";i:0;s:6:"cartID";N;s:12:"content_type";b:0;}language|s:6:"polish";languages_id|s:1:"1";currency|s:3:"PLN";navigation|O:17:"navigationHistory":2:{s:4:"path";a:1:{i:0;a:4:{s:4:"page";s:9:"index.php";s:4:"mode";s:6:"NONSSL";s:3:"get";a:1:{s:13:"error_message";s:34:"Błąd 404 - nie znaleziono strony";}s:4:"post";a:0:{}}}s:8:"snapshot";a:0:{}}show_reviews|b:0;');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `specials`
--

CREATE TABLE IF NOT EXISTS `specials` (
  `specials_id` int(11) NOT NULL auto_increment,
  `products_id` int(11) NOT NULL,
  `specials_new_products_price` decimal(15,4) NOT NULL,
  `specials_date_added` datetime default NULL,
  `specials_last_modified` datetime default NULL,
  `expires_date` datetime default NULL,
  `date_status_change` datetime default NULL,
  `status` int(1) NOT NULL default '1',
  PRIMARY KEY  (`specials_id`),
  KEY `idx_specials_products_id` (`products_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=5 ;

--
-- Zrzut danych tabeli `specials`
--

INSERT INTO `specials` (`specials_id`, `products_id`, `specials_new_products_price`, `specials_date_added`, `specials_last_modified`, `expires_date`, `date_status_change`, `status`) VALUES
(1, 3, 39.9900, '2008-12-12 19:02:38', NULL, NULL, NULL, 1),
(2, 5, 30.0000, '2008-12-12 19:02:38', NULL, NULL, NULL, 1),
(3, 6, 30.0000, '2008-12-12 19:02:38', NULL, NULL, NULL, 1),
(4, 16, 29.9900, '2008-12-12 19:02:38', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `tax_class`
--

CREATE TABLE IF NOT EXISTS `tax_class` (
  `tax_class_id` int(11) NOT NULL auto_increment,
  `tax_class_title` varchar(32) collate utf8_polish_ci NOT NULL,
  `tax_class_description` varchar(255) collate utf8_polish_ci NOT NULL,
  `last_modified` datetime default NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY  (`tax_class_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=6 ;

--
-- Zrzut danych tabeli `tax_class`
--

INSERT INTO `tax_class` (`tax_class_id`, `tax_class_title`, `tax_class_description`, `last_modified`, `date_added`) VALUES
(5, 'ZWOLNIONE', '', NULL, '2008-06-11 12:22:29'),
(4, 'VAT_0', '', NULL, '2008-06-11 12:22:17'),
(3, 'VAT_7', '', NULL, '2008-06-11 12:22:10'),
(2, 'VAT_22', '', NULL, '2008-06-11 12:22:03');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `tax_rates`
--

CREATE TABLE IF NOT EXISTS `tax_rates` (
  `tax_rates_id` int(11) NOT NULL auto_increment,
  `tax_zone_id` int(11) NOT NULL,
  `tax_class_id` int(11) NOT NULL,
  `tax_priority` int(5) default '1',
  `tax_rate` decimal(7,4) NOT NULL,
  `tax_description` varchar(255) collate utf8_polish_ci NOT NULL,
  `last_modified` datetime default NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY  (`tax_rates_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=6 ;

--
-- Zrzut danych tabeli `tax_rates`
--

INSERT INTO `tax_rates` (`tax_rates_id`, `tax_zone_id`, `tax_class_id`, `tax_priority`, `tax_rate`, `tax_description`, `last_modified`, `date_added`) VALUES
(4, 2, 3, 0, 7.0000, 'VAT 7%', NULL, '2008-06-11 12:25:20'),
(5, 2, 5, 0, 0.0000, 'Brak VAT-u', NULL, '2008-06-11 12:25:34'),
(3, 2, 2, 0, 22.0000, 'VAT 22%', NULL, '2008-06-11 12:25:04'),
(2, 2, 4, 0, 0.0000, 'VAT 0%', NULL, '2008-06-11 12:24:28'),
(1, 1, 1, 1, 7.0000, 'FL TAX 7.0%', '2008-06-11 09:43:37', '2008-06-11 09:43:37');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `theme_configuration`
--

CREATE TABLE IF NOT EXISTS `theme_configuration` (
  `configuration_id` int(11) NOT NULL auto_increment,
  `configuration_title` varchar(64) collate utf8_polish_ci NOT NULL default '',
  `configuration_key` varchar(64) collate utf8_polish_ci NOT NULL default 'BOX_HEADING_',
  `configuration_value` varchar(255) collate utf8_polish_ci NOT NULL default '',
  `configuration_column` varchar(64) collate utf8_polish_ci NOT NULL default 'lewa',
  `location` int(5) NOT NULL default '0',
  `last_modified` datetime default NULL,
  `date_added` datetime NOT NULL default '0000-00-00 00:00:00',
  `box_heading` varchar(64) collate utf8_polish_ci NOT NULL default '',
  PRIMARY KEY  (`configuration_id`),
  KEY `configuration_title` (`configuration_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=14 ;

--
-- Zrzut danych tabeli `theme_configuration`
--

INSERT INTO `theme_configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_column`, `location`, `last_modified`, `date_added`, `box_heading`) VALUES
(1, 'categories', 'BOX_HEADING_CATEGORIES', 'tak', 'lewa', 1, '2009-01-14 13:19:45', '0000-00-00 00:00:00', 'Kategorie'),
(2, 'manufacturers', 'BOX_HEADING_MANUFACTURERS', 'tak', 'lewa', 4, '2009-01-14 13:21:21', '0000-00-00 00:00:00', 'Producenci'),
(3, 'whats_new', 'BOX_HEADING_WHATS_NEW', 'tak', 'lewa', 5, '2009-01-14 13:21:27', '0000-00-00 00:00:00', 'Nowości'),
(4, 'search', 'BOX_HEADING_SEARCH', 'tak', 'lewa', 3, '2009-01-14 13:21:15', '0000-00-00 00:00:00', 'Wyszukiwanie'),
(5, 'information', 'BOX_HEADING_INFORMATION', 'tak', 'lewa', 2, '2009-01-14 13:20:45', '0000-00-00 00:00:00', 'Informacje'),
(6, 'shopping_cart', 'BOX_HEADING_SHOPPING_CART', 'tak', 'lewa', 1, NULL, '0000-00-00 00:00:00', 'Koszyk'),
(7, 'manufacturer_info', 'BOX_HEADING_MANUFACTURER_INFO', 'tak', 'prawa', 2, NULL, '0000-00-00 00:00:00', 'Producent'),
(8, 'order_history', 'BOX_HEADING_CUSTOMER_ORDERS', 'tak', 'prawa', 3, NULL, '0000-00-00 00:00:00', 'Zamówienia'),
(9, 'best_sellers', 'BOX_HEADING_BESTSELLERS', 'nie', 'prawa', 4, NULL, '0000-00-00 00:00:00', 'Bestsellery'),
(10, 'product_notifications', 'BOX_HEADING_NOTIFICATIONS', 'tak', 'prawa', 5, NULL, '0000-00-00 00:00:00', 'Powiadomienia'),
(11, 'tell_a_friend', 'BOX_HEADING_TELL_A_FRIEND', 'tak', 'prawa', 6, NULL, '0000-00-00 00:00:00', 'Dla Znajomego'),
(12, 'specials', 'BOX_HEADING_SPECIALS', 'tak', 'prawa', 7, NULL, '0000-00-00 00:00:00', 'Promocje'),
(13, 'reviews', 'BOX_HEADING_REVIEWS', 'nie', 'prawa', 8, NULL, '0000-00-00 00:00:00', 'Recenzje');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `whos_online`
--

CREATE TABLE IF NOT EXISTS `whos_online` (
  `customer_id` int(11) default NULL,
  `full_name` varchar(64) collate utf8_polish_ci NOT NULL,
  `session_id` varchar(128) collate utf8_polish_ci NOT NULL,
  `ip_address` varchar(15) collate utf8_polish_ci NOT NULL,
  `time_entry` varchar(14) collate utf8_polish_ci NOT NULL,
  `time_last_click` varchar(14) collate utf8_polish_ci NOT NULL,
  `last_page_url` text collate utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `whos_online`
--

INSERT INTO `whos_online` (`customer_id`, `full_name`, `session_id`, `ip_address`, `time_entry`, `time_last_click`, `last_page_url`) VALUES
(0, 'Guest', '6f87881f871248cb2f61dfd8144775ad', '78.155.120.23', '1243689424', '1243691337', '/favicon.ico');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `zones`
--

CREATE TABLE IF NOT EXISTS `zones` (
  `zone_id` int(11) NOT NULL auto_increment,
  `zone_country_id` int(11) NOT NULL,
  `zone_code` varchar(32) collate utf8_polish_ci NOT NULL,
  `zone_name` varchar(32) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`zone_id`),
  KEY `idx_zones_country_id` (`zone_country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=230 ;

--
-- Zrzut danych tabeli `zones`
--

INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES
(1, 223, 'AL', 'Alabama'),
(2, 223, 'AK', 'Alaska'),
(3, 223, 'AS', 'American Samoa'),
(4, 223, 'AZ', 'Arizona'),
(5, 223, 'AR', 'Arkansas'),
(6, 223, 'AF', 'Armed Forces Africa'),
(7, 223, 'AA', 'Armed Forces Americas'),
(8, 223, 'AC', 'Armed Forces Canada'),
(9, 223, 'AE', 'Armed Forces Europe'),
(10, 223, 'AM', 'Armed Forces Middle East'),
(11, 223, 'AP', 'Armed Forces Pacific'),
(12, 223, 'CA', 'California'),
(13, 223, 'CO', 'Colorado'),
(14, 223, 'CT', 'Connecticut'),
(15, 223, 'DE', 'Delaware'),
(16, 223, 'DC', 'District of Columbia'),
(17, 223, 'FM', 'Federated States Of Micronesia'),
(18, 223, 'FL', 'Florida'),
(19, 223, 'GA', 'Georgia'),
(20, 223, 'GU', 'Guam'),
(21, 223, 'HI', 'Hawaii'),
(22, 223, 'ID', 'Idaho'),
(23, 223, 'IL', 'Illinois'),
(24, 223, 'IN', 'Indiana'),
(25, 223, 'IA', 'Iowa'),
(26, 223, 'KS', 'Kansas'),
(27, 223, 'KY', 'Kentucky'),
(28, 223, 'LA', 'Louisiana'),
(29, 223, 'ME', 'Maine'),
(30, 223, 'MH', 'Marshall Islands'),
(31, 223, 'MD', 'Maryland'),
(32, 223, 'MA', 'Massachusetts'),
(33, 223, 'MI', 'Michigan'),
(34, 223, 'MN', 'Minnesota'),
(35, 223, 'MS', 'Mississippi'),
(36, 223, 'MO', 'Missouri'),
(37, 223, 'MT', 'Montana'),
(38, 223, 'NE', 'Nebraska'),
(39, 223, 'NV', 'Nevada'),
(40, 223, 'NH', 'New Hampshire'),
(41, 223, 'NJ', 'New Jersey'),
(42, 223, 'NM', 'New Mexico'),
(43, 223, 'NY', 'New York'),
(44, 223, 'NC', 'North Carolina'),
(45, 223, 'ND', 'North Dakota'),
(46, 223, 'MP', 'Northern Mariana Islands'),
(47, 223, 'OH', 'Ohio'),
(48, 223, 'OK', 'Oklahoma'),
(49, 223, 'OR', 'Oregon'),
(50, 223, 'PW', 'Palau'),
(51, 223, 'PA', 'Pennsylvania'),
(52, 223, 'PR', 'Puerto Rico'),
(53, 223, 'RI', 'Rhode Island'),
(54, 223, 'SC', 'South Carolina'),
(55, 223, 'SD', 'South Dakota'),
(56, 223, 'TN', 'Tennessee'),
(57, 223, 'TX', 'Texas'),
(58, 223, 'UT', 'Utah'),
(59, 223, 'VT', 'Vermont'),
(60, 223, 'VI', 'Virgin Islands'),
(61, 223, 'VA', 'Virginia'),
(62, 223, 'WA', 'Washington'),
(63, 223, 'WV', 'West Virginia'),
(64, 223, 'WI', 'Wisconsin'),
(65, 223, 'WY', 'Wyoming'),
(66, 38, 'AB', 'Alberta'),
(67, 38, 'BC', 'British Columbia'),
(68, 38, 'MB', 'Manitoba'),
(69, 38, 'NF', 'Newfoundland'),
(70, 38, 'NB', 'New Brunswick'),
(71, 38, 'NS', 'Nova Scotia'),
(72, 38, 'NT', 'Northwest Territories'),
(73, 38, 'NU', 'Nunavut'),
(74, 38, 'ON', 'Ontario'),
(75, 38, 'PE', 'Prince Edward Island'),
(76, 38, 'QC', 'Quebec'),
(77, 38, 'SK', 'Saskatchewan'),
(78, 38, 'YT', 'Yukon Territory'),
(79, 81, 'NDS', 'Niedersachsen'),
(80, 81, 'BAW', 'Baden-Württemberg'),
(81, 81, 'BAY', 'Bayern'),
(82, 81, 'BER', 'Berlin'),
(83, 81, 'BRG', 'Brandenburg'),
(84, 81, 'BRE', 'Bremen'),
(85, 81, 'HAM', 'Hamburg'),
(86, 81, 'HES', 'Hessen'),
(87, 81, 'MEC', 'Mecklenburg-Vorpommern'),
(88, 81, 'NRW', 'Nordrhein-Westfalen'),
(89, 81, 'RHE', 'Rheinland-Pfalz'),
(90, 81, 'SAR', 'Saarland'),
(91, 81, 'SAS', 'Sachsen'),
(92, 81, 'SAC', 'Sachsen-Anhalt'),
(93, 81, 'SCN', 'Schleswig-Holstein'),
(94, 81, 'THE', 'Thüringen'),
(95, 14, 'WI', 'Wien'),
(96, 14, 'NO', 'Niederösterreich'),
(97, 14, 'OO', 'Oberösterreich'),
(98, 14, 'SB', 'Salzburg'),
(99, 14, 'KN', 'Kärnten'),
(100, 14, 'ST', 'Steiermark'),
(101, 14, 'TI', 'Tirol'),
(102, 14, 'BL', 'Burgenland'),
(103, 14, 'VB', 'Voralberg'),
(104, 204, 'AG', 'Aargau'),
(105, 204, 'AI', 'Appenzell Innerrhoden'),
(106, 204, 'AR', 'Appenzell Ausserrhoden'),
(107, 204, 'BE', 'Bern'),
(108, 204, 'BL', 'Basel-Landschaft'),
(109, 204, 'BS', 'Basel-Stadt'),
(110, 204, 'FR', 'Freiburg'),
(111, 204, 'GE', 'Genf'),
(112, 204, 'GL', 'Glarus'),
(113, 204, 'JU', 'Graubünden'),
(114, 204, 'JU', 'Jura'),
(115, 204, 'LU', 'Luzern'),
(116, 204, 'NE', 'Neuenburg'),
(117, 204, 'NW', 'Nidwalden'),
(118, 204, 'OW', 'Obwalden'),
(119, 204, 'SG', 'St. Gallen'),
(120, 204, 'SH', 'Schaffhausen'),
(121, 204, 'SO', 'Solothurn'),
(122, 204, 'SZ', 'Schwyz'),
(123, 204, 'TG', 'Thurgau'),
(124, 204, 'TI', 'Tessin'),
(125, 204, 'UR', 'Uri'),
(126, 204, 'VD', 'Waadt'),
(127, 204, 'VS', 'Wallis'),
(128, 204, 'ZG', 'Zug'),
(129, 204, 'ZH', 'Zürich'),
(130, 195, 'A Coruña', 'A Coruña'),
(131, 195, 'Alava', 'Alava'),
(132, 195, 'Albacete', 'Albacete'),
(133, 195, 'Alicante', 'Alicante'),
(134, 195, 'Almeria', 'Almeria'),
(135, 195, 'Asturias', 'Asturias'),
(136, 195, 'Avila', 'Avila'),
(137, 195, 'Badajoz', 'Badajoz'),
(138, 195, 'Baleares', 'Baleares'),
(139, 195, 'Barcelona', 'Barcelona'),
(140, 195, 'Burgos', 'Burgos'),
(141, 195, 'Caceres', 'Caceres'),
(142, 195, 'Cadiz', 'Cadiz'),
(143, 195, 'Cantabria', 'Cantabria'),
(144, 195, 'Castellon', 'Castellon'),
(145, 195, 'Ceuta', 'Ceuta'),
(146, 195, 'Ciudad Real', 'Ciudad Real'),
(147, 195, 'Cordoba', 'Cordoba'),
(148, 195, 'Cuenca', 'Cuenca'),
(149, 195, 'Girona', 'Girona'),
(150, 195, 'Granada', 'Granada'),
(151, 195, 'Guadalajara', 'Guadalajara'),
(152, 195, 'Guipuzcoa', 'Guipuzcoa'),
(153, 195, 'Huelva', 'Huelva'),
(154, 195, 'Huesca', 'Huesca'),
(155, 195, 'Jaen', 'Jaen'),
(156, 195, 'La Rioja', 'La Rioja'),
(157, 195, 'Las Palmas', 'Las Palmas'),
(158, 195, 'Leon', 'Leon'),
(159, 195, 'Lleida', 'Lleida'),
(160, 195, 'Lugo', 'Lugo'),
(161, 195, 'Madrid', 'Madrid'),
(162, 195, 'Malaga', 'Malaga'),
(163, 195, 'Melilla', 'Melilla'),
(164, 195, 'Murcia', 'Murcia'),
(165, 195, 'Navarra', 'Navarra'),
(166, 195, 'Ourense', 'Ourense'),
(167, 195, 'Palencia', 'Palencia'),
(168, 195, 'Pontevedra', 'Pontevedra'),
(169, 195, 'Salamanca', 'Salamanca'),
(170, 195, 'Santa Cruz de Tenerife', 'Santa Cruz de Tenerife'),
(171, 195, 'Segovia', 'Segovia'),
(172, 195, 'Sevilla', 'Sevilla'),
(173, 195, 'Soria', 'Soria'),
(174, 195, 'Tarragona', 'Tarragona'),
(175, 195, 'Teruel', 'Teruel'),
(176, 195, 'Toledo', 'Toledo'),
(177, 195, 'Valencia', 'Valencia'),
(178, 195, 'Valladolid', 'Valladolid'),
(179, 195, 'Vizcaya', 'Vizcaya'),
(180, 195, 'Zamora', 'Zamora'),
(181, 195, 'Zaragoza', 'Zaragoza'),
(228, 170, 'wielkopolskie', 'wielkopolskie'),
(227, 170, 'warmińsko-mazurskie', 'warmińsko-mazurskie'),
(226, 170, 'świętokrzyskie', 'świętokrzyskie'),
(225, 170, 'śląskie', 'śląskie'),
(224, 170, 'pomorskie', 'pomorskie'),
(223, 170, 'podlaskie', 'podlaskie'),
(222, 170, 'podkarpackie', 'podkarpackie'),
(221, 170, 'opolskie', 'opolskie'),
(220, 170, 'mazowieckie', 'mazowieckie'),
(219, 170, 'małopolskie', 'małopolskie'),
(217, 170, 'lubuskie', 'lubuskie'),
(218, 170, 'łódzkie', 'łódzkie'),
(216, 170, 'lubelskie', 'lubelskie'),
(215, 170, 'kujawsko-pomorskie', 'kujawsko-pomorskie'),
(214, 170, 'dolnośląskie', 'dolnośląskie'),
(229, 170, 'zachodniopomorskie', 'zachodniopomorskie');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `zones_to_geo_zones`
--

CREATE TABLE IF NOT EXISTS `zones_to_geo_zones` (
  `association_id` int(11) NOT NULL auto_increment,
  `zone_country_id` int(11) NOT NULL,
  `zone_id` int(11) default NULL,
  `geo_zone_id` int(11) default NULL,
  `last_modified` datetime default NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY  (`association_id`),
  KEY `idx_zones_to_geo_zones_country_id` (`zone_country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `zones_to_geo_zones`
--

INSERT INTO `zones_to_geo_zones` (`association_id`, `zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES
(2, 170, 0, 2, NULL, '2008-06-11 12:10:42');
