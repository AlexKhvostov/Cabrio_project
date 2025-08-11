-- Добавление полей для превью фото (medium, mini) — обратная совместимость
-- Соглашение путей: в БД храним канонический путь без префикса uploads и размера,
-- например: "car/car_542_437.jpg". Генерация абсолютных ссылок делается на уровне API.

ALTER TABLE `photos`
  ADD COLUMN `url_medium` VARCHAR(255) NULL AFTER `url`,
  ADD COLUMN `url_mini`   VARCHAR(255) NULL AFTER `url_medium`;


