<?php
/**
 * EventDeduplicator.php
 * 
 * Простейшая файловая дедупликация событий (join/leave)
 * Нужна, чтобы не отправлять сообщения дважды, когда Telegram присылает
 * одно и то же событие как chat_member и как service message (new_chat_members/left_chat_member)
 */

/**
 * Проверяет, обрабатывалось ли недавно событие с указанным ключом
 * 
 * @param string $key Уникальный ключ события (например, join:chatId:userId)
 * @param int $ttlSeconds Время жизни метки, секунд
 * @return bool true, если событие уже обрабатывалось недавно
 */
function hasRecentlyProcessed($key, $ttlSeconds = 30) {
	$dir = __DIR__ . '/../logs/dedup';
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}

	$file = $dir . '/' . md5($key) . '.lock';
	if (!file_exists($file)) {
		return false;
	}

	$mtime = @filemtime($file);
	if ($mtime === false) {
		return false;
	}

	return (time() - $mtime) <= $ttlSeconds;
}

/**
 * Помечает событие как обработанное (создаёт/обновляет lock-файл)
 * 
 * @param string $key Уникальный ключ события
 * @return void
 */
function markProcessed($key) {
	$dir = __DIR__ . '/../logs/dedup';
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}

	$file = $dir . '/' . md5($key) . '.lock';
	@file_put_contents($file, (string)time());
}


