-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 01 2026 г., 20:03
-- Версия сервера: 5.7.44-48
-- Версия PHP: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u0747697_neotracker`
--

-- --------------------------------------------------------

--
-- Структура таблицы `calories`
--

CREATE TABLE `calories` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `current_progress`
--

CREATE TABLE `current_progress` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `stage_id` int(11) NOT NULL,
  `dialogue_id` int(11) NOT NULL,
  `status` enum('in_progress','mission_complete','story_complete') NOT NULL DEFAULT 'in_progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `dialogues`
--

CREATE TABLE `dialogues` (
  `id` int(11) NOT NULL,
  `stage_id` int(11) NOT NULL,
  `speaker_bot` enum('tracker','alexns','maks') NOT NULL,
  `text` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_entry` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `dialogues_journal`
--

CREATE TABLE `dialogues_journal` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `dialogue_id` int(11) NOT NULL,
  `dialogue_options_id` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `stage_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `dialogue_options`
--

CREATE TABLE `dialogue_options` (
  `id` int(11) NOT NULL,
  `dialogue_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `text` text NOT NULL,
  `next_dialogue_id` int(11) DEFAULT NULL,
  `next_stage_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `feedback`
--

CREATE TABLE `feedback` (
  `id` int(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `missions`
--

CREATE TABLE `missions` (
  `id` int(11) NOT NULL,
  `story_id` int(11) NOT NULL,
  `is_entry` bit(1) NOT NULL,
  `is_final` bit(1) NOT NULL,
  `title` varchar(250) NOT NULL,
  `descr` text NOT NULL,
  `trigger_type` enum('cron','activity','signal') NOT NULL,
  `trigger_value` varchar(250) NOT NULL DEFAULT '''0'''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `mission_journal`
--

CREATE TABLE `mission_journal` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `status` enum('in_progress','success','fail','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `mission_requirments`
--

CREATE TABLE `mission_requirments` (
  `id` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `days` int(11) NOT NULL,
  `steps` int(11) NOT NULL,
  `water` int(11) NOT NULL,
  `cal` int(11) NOT NULL DEFAULT '500000',
  `inrow` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `mission_schedule`
--

CREATE TABLE `mission_schedule` (
  `id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `mission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `onboarding`
--

CREATE TABLE `onboarding` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `step` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `polls`
--

CREATE TABLE `polls` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `poll` text NOT NULL,
  `poll_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `GMT` tinyint(4) NOT NULL,
  `notification` tinyint(1) NOT NULL DEFAULT '1',
  `notification_time` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `stages`
--

CREATE TABLE `stages` (
  `id` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `desrc` text NOT NULL,
  `is_entry` bit(1) NOT NULL,
  `is_final` bit(1) NOT NULL,
  `next_mission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `steps`
--

CREATE TABLE `steps` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `descr` text NOT NULL,
  `premium` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `registered_date` date NOT NULL,
  `sex` enum('М','Ж','Н') DEFAULT 'Н',
  `age` tinyint(4) DEFAULT '0',
  `height` int(3) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `goal_steps` int(11) NOT NULL,
  `goal_water` int(11) NOT NULL,
  `goal_calories` int(11) NOT NULL,
  `premium` bit(1) NOT NULL DEFAULT b'0',
  `telegram_payment_charge_id` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `users_bot_reg`
--

CREATE TABLE `users_bot_reg` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `bot_name` varchar(20) NOT NULL,
  `last_message_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `user_settings`
-- (См. Ниже фактическое представление)
--
CREATE TABLE `user_settings` (
`telegram_id` bigint(20)
,`GMT` varchar(5)
,`notification` int(4)
,`notification_time` varchar(5)
);

-- --------------------------------------------------------

--
-- Структура таблицы `water`
--

CREATE TABLE `water` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура для представления `user_settings`
--
DROP TABLE IF EXISTS `user_settings`;

CREATE ALGORITHM=MERGE DEFINER=`u0747697_gpt_bud`@`localhost` SQL SECURITY DEFINER VIEW `user_settings`  AS SELECT `u`.`telegram_id` AS `telegram_id`, (case when (ifnull(`s`.`GMT`,3) >= 0) then concat('+',ifnull(`s`.`GMT`,3)) else concat('',ifnull(`s`.`GMT`,3)) end) AS `GMT`, ifnull(`s`.`notification`,0) AS `notification`, ifnull(`s`.`notification_time`,'18:00') AS `notification_time` FROM (`users` `u` left join `settings` `s` on((`s`.`telegram_id` = `u`.`telegram_id`))) ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `calories`
--
ALTER TABLE `calories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`,`date`);

--
-- Индексы таблицы `current_progress`
--
ALTER TABLE `current_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id_indx2` (`telegram_id`) USING BTREE;

--
-- Индексы таблицы `dialogues`
--
ALTER TABLE `dialogues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_id_indx` (`stage_id`);

--
-- Индексы таблицы `dialogues_journal`
--
ALTER TABLE `dialogues_journal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `all_set` (`telegram_id`,`dialogue_id`,`mission_id`,`stage_id`) USING BTREE,
  ADD KEY `telegram_id_indx2` (`telegram_id`);

--
-- Индексы таблицы `dialogue_options`
--
ALTER TABLE `dialogue_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dialogue_id_indx` (`dialogue_id`) USING BTREE;

--
-- Индексы таблицы `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `missions`
--
ALTER TABLE `missions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `story_id_indx` (`story_id`);

--
-- Индексы таблицы `mission_journal`
--
ALTER TABLE `mission_journal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `telegram_id_indx2` (`telegram_id`);

--
-- Индексы таблицы `mission_requirments`
--
ALTER TABLE `mission_requirments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mission_id_indx2` (`mission_id`);

--
-- Индексы таблицы `mission_schedule`
--
ALTER TABLE `mission_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `telegram_id_indx` (`telegram_id`);

--
-- Индексы таблицы `onboarding`
--
ALTER TABLE `onboarding`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`);

--
-- Индексы таблицы `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`,`poll_id`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`);

--
-- Индексы таблицы `stages`
--
ALTER TABLE `stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mission_id_inx` (`mission_id`);

--
-- Индексы таблицы `steps`
--
ALTER TABLE `steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`,`date`);

--
-- Индексы таблицы `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`);

--
-- Индексы таблицы `users_bot_reg`
--
ALTER TABLE `users_bot_reg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`,`bot_name`);

--
-- Индексы таблицы `water`
--
ALTER TABLE `water`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`,`date`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `calories`
--
ALTER TABLE `calories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `current_progress`
--
ALTER TABLE `current_progress`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `dialogues`
--
ALTER TABLE `dialogues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `dialogues_journal`
--
ALTER TABLE `dialogues_journal`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `dialogue_options`
--
ALTER TABLE `dialogue_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `missions`
--
ALTER TABLE `missions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `mission_journal`
--
ALTER TABLE `mission_journal`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `mission_requirments`
--
ALTER TABLE `mission_requirments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `mission_schedule`
--
ALTER TABLE `mission_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `onboarding`
--
ALTER TABLE `onboarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stages`
--
ALTER TABLE `stages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `steps`
--
ALTER TABLE `steps`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users_bot_reg`
--
ALTER TABLE `users_bot_reg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `water`
--
ALTER TABLE `water`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
