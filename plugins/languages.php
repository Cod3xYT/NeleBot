<?php

/*
NeleBotFramework
	Copyright (C) 2020  NeleBot Framework

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

# Configurazione del Plugin
$langnames = [
	"en" => "🇬🇧 English",
	"fr" => "🇫🇷 Français",
	"it" => "🇮🇹 Italiano"
];

$lang = $u['lang'];
if (!$exists_user or !$lang) {
	$lang = 'en';
}

function getLanguage($userID = false) {
	if (!$userID) return false;
	return db_query("SELECT * FROM utenti WHERE user_id = ?", [$userID])[0]['lang'];
}

if ($get = $redis->get("tr-BotXB-status")) {
} else {
	$redis->del($redis->keys("tr-BotXB*"));
	$linguebot = json_decode(file_get_contents($f['languages']), true);
	if (!is_array($linguebot) or !$linguebot) {
		botlog("Il file delle lingue è corrotto! \n" . code(substr(json_encode(error_get_last()), 0, 256)));
		die;
	}
	$redis->set("tr-BotXB-status", true);
	ksort($linguebot);
	foreach($linguebot as $tlang => $rr) {
		ksort($rr);
		$linguebot[$tlang] = $rr;
		foreach ($rr as $strName => $strTranslated) {
			$redis->set("tr-BotXB-{$tlang}-{$strName}", $strTranslated);
		}
	}
	file_put_contents($f['languages'], json_encode($linguebot, JSON_PRETTY_PRINT));
}

# Funzione da richiamare
function getTranslate($testo = 'start', $arr = [], $langp = 'def') {
	global $config;
	global $redis;
	global $lang;
	if ($langp == 'def') {
		$langp = $lang;
	}
	$testo = str_replace(' ', '', $testo);
	if ($sas = $redis->get("tr-BotXB-{$langp}-{$testo}")) {
	} elseif ($sas = $redis->get("tr-BotXB-en-{$testo}")) {
	} else {
		botlog("Language Warning: Impossibile trovare la stringa '$testo' sulla lingua $lang");
		$sas = "🤖";
	}
	if (is_array($arr) and $arr) {
		foreach(range(0, count($arr) - 1) as $num) {
			$e[$num] = htmlspecialchars($arr[$num]);
			$sas = str_replace("[$num]", $e[$num], $sas);
		}
	}
	return mb_convert_encoding($sas, "UTF-8");
}

?>