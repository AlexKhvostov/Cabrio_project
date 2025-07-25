<?php
/**
 * CheckCarExistsAction — проверка наличия автомобиля по номеру (или другим уникальным данным).
 *
 * Назначение:
 *   Проверяет, есть ли автомобиль с заданным номером (или VIN) в базе клуба.
 *   Возвращает информацию об авто, если найдено, иначе null.
 *
 * Зависимости:
 *   - Car (модель)
 *   - Logger (утилита)
 *
 * Пример использования:
 *   $carInfo = CheckCarExistsAction::handle($number);
 */
class CheckCarExistsAction {
    /**
     * @param string $number — номер автомобиля
     * @return array|null — данные авто или null
     */
    public static function handle($number) {
        // 1. Поиск авто по номеру (или VIN)
        // 2. Вернуть данные авто или null
    }
} 