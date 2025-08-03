<?php

/**
 * 🔄 Хелпер для развертывания данных из справочников
 * 
 * Централизованные методы для преобразования ID в развернутые объекты
 * согласно стандартам CONVENTIONS.md п.6
 * 
 * @package CabrioRide\Utils
 */

require_once __DIR__ . '/ReferenceData.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Car.php';

class ExpandHelper
{
    // ========================================
    // РАЗВЕРТЫВАНИЕ ДАННЫХ АВТОМОБИЛЯ
    // ========================================
    
    /**
     * Развернуть данные автомобиля с полной информацией
     * 
     * @param array $carData Данные автомобиля из БД
     * @return array Развернутые данные автомобиля
     */
    public static function expandCarData($carData)
    {
        if (!$carData) {
            return null;
        }
        
        $expanded = $carData;
        
        // Развертываем статус
        if (isset($carData['status_id'])) {
            $expanded['status'] = ReferenceData::getCarStatusDetails($carData['status_id']);
            // Убираем дублирование согласно CONVENTIONS.md
            unset($expanded['status_id']);
        }
        
        // Развертываем владельца (если есть)
        if (isset($carData['owner_user_id']) && $carData['owner_user_id']) {
            $owner = User::findByIdWithDetails($carData['owner_user_id']);
            if ($owner) {
                $expanded['owner'] = $owner;
                // Убираем дублирование
                unset($expanded['owner_user_id']);
            }
        }
        
        // Развертываем создателя (если есть)
        if (isset($carData['create_user_id']) && $carData['create_user_id']) {
            $creator = User::findByIdWithDetails($carData['create_user_id']);
            if ($creator) {
                $expanded['creator'] = $creator;
                // Убираем дублирование
                unset($expanded['create_user_id']);
            }
        }
        
        // Развертываем марку (если есть)
        if (isset($carData['car_brand_id']) && $carData['car_brand_id']) {
            $brand = self::getCarBrandDetails($carData['car_brand_id']);
            if ($brand) {
                $expanded['brand'] = $brand;
                // Убираем дублирование
                unset($expanded['car_brand_id']);
            }
        }
        
        // Развертываем фото (если есть)
        if (isset($carData['photo_id']) && $carData['photo_id']) {
            $photo = self::getPhotoDetails($carData['photo_id']);
            if ($photo) {
                $expanded['photo'] = $photo;
                // Убираем дублирование
                unset($expanded['photo_id'], $expanded['photo_url'], $expanded['photo_description']);
            }
        }
        
        return $expanded;
    }
    
    /**
     * Развернуть список автомобилей
     * 
     * @param array $carsList Список автомобилей
     * @return array Список с развернутыми данными
     */
    public static function expandCarsList($carsList)
    {
        if (!is_array($carsList)) {
            return [];
        }
        
        $expanded = [];
        foreach ($carsList as $car) {
            $expanded[] = self::expandCarData($car);
        }
        
        return $expanded;
    }
    
    // ========================================
    // РАЗВЕРТЫВАНИЕ ДАННЫХ ПОЛЬЗОВАТЕЛЯ
    // ========================================
    
    /**
     * Развернуть данные пользователя с полной информацией
     * 
     * @param array $userData Данные пользователя из БД
     * @return array Развернутые данные пользователя
     */
    public static function expandUserData($userData)
    {
        if (!$userData) {
            return null;
        }
        
        $expanded = $userData;
        
        // Развертываем роль
        if (isset($userData['role_id'])) {
            $expanded['role'] = ReferenceData::getUserRoleDetails($userData['role_id']);
            // Убираем дублирование
            unset($expanded['role_id']);
        }
        
        // Развертываем фото (если есть)
        if (isset($userData['photo_id']) && $userData['photo_id']) {
            $photo = self::getPhotoDetails($userData['photo_id']);
            if ($photo) {
                $expanded['photo'] = $photo;
                // Убираем дублирование
                unset($expanded['photo_id'], $expanded['photo_url'], $expanded['photo_description']);
            }
        }
        
        return $expanded;
    }
    
    /**
     * Развернуть список пользователей
     * 
     * @param array $usersList Список пользователей
     * @return array Список с развернутыми данными
     */
    public static function expandUsersList($usersList)
    {
        if (!is_array($usersList)) {
            return [];
        }
        
        $expanded = [];
        foreach ($usersList as $user) {
            $expanded[] = self::expandUserData($user);
        }
        
        return $expanded;
    }
    
    // ========================================
    // РАЗВЕРТЫВАНИЕ ДАННЫХ ВИЗИТКИ
    // ========================================
    
    /**
     * Развернуть данные визитки с полной информацией
     * 
     * @param array $cardData Данные визитки из БД
     * @return array Развернутые данные визитки
     */
    public static function expandBusinessCardData($cardData)
    {
        if (!$cardData) {
            return null;
        }
        
        $expanded = $cardData;
        
        // Развертываем автомобиль
        if (isset($cardData['car_id']) && $cardData['car_id']) {
            $car = Car::findByIdWithDetails($cardData['car_id']);
            if ($car) {
                $expanded['car'] = $car;
                // Убираем дублирование
                unset($expanded['car_id']);
            }
        }
        
        // Развертываем пользователя
        if (isset($cardData['user_id']) && $cardData['user_id']) {
            $user = User::findByIdWithDetails($cardData['user_id']);
            if ($user) {
                $expanded['user'] = $user;
                // Убираем дублирование
                unset($expanded['user_id']);
            }
        }
        
        // Развертываем фото (если есть)
        if (isset($cardData['photo_id']) && $cardData['photo_id']) {
            $photo = self::getPhotoDetails($cardData['photo_id']);
            if ($photo) {
                $expanded['photo'] = $photo;
                // Убираем дублирование
                unset($expanded['photo_id'], $expanded['photo_url'], $expanded['photo_description']);
            }
        }
        
        return $expanded;
    }
    
    /**
     * Развернуть список визиток
     * 
     * @param array $cardsList Список визиток
     * @return array Список с развернутыми данными
     */
    public static function expandBusinessCardsList($cardsList)
    {
        if (!is_array($cardsList)) {
            return [];
        }
        
        $expanded = [];
        foreach ($cardsList as $card) {
            $expanded[] = self::expandBusinessCardData($card);
        }
        
        return $expanded;
    }
    
    // ========================================
    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ========================================
    
    /**
     * Получить детали марки автомобиля
     * 
     * @param int $brandId ID марки
     * @return array|null Детали марки или null
     */
    private static function getCarBrandDetails($brandId)
    {
        // TODO: Реализовать получение данных марки из БД
        // Пока возвращаем базовую структуру
        return [
            'id' => $brandId,
            'name' => 'Марка ' . $brandId,
            'code' => 'brand_' . $brandId
        ];
    }
    
    /**
     * Получить детали фото
     * 
     * @param int $photoId ID фото
     * @return array|null Детали фото или null
     */
    private static function getPhotoDetails($photoId)
    {
        // TODO: Реализовать получение данных фото из БД
        // Пока возвращаем базовую структуру
        return [
            'id' => $photoId,
            'url' => '/uploads/photo_' . $photoId . '.jpg',
            'description' => 'Фото'
        ];
    }
    
    /**
     * Развернуть статус автомобиля
     * 
     * @param int $statusId ID статуса
     * @return array|null Развернутые данные статуса
     */
    public static function expandCarStatus($statusId)
    {
        return ReferenceData::getCarStatusDetails($statusId);
    }
    
    /**
     * Развернуть роль пользователя
     * 
     * @param int $roleId ID роли
     * @return array|null Развернутые данные роли
     */
    public static function expandUserRole($roleId)
    {
        return ReferenceData::getUserRoleDetails($roleId);
    }
    
    /**
     * Проверить, нужно ли развертывать данные
     * 
     * @param array $data Данные для проверки
     * @return bool
     */
    public static function needsExpansion($data)
    {
        if (!is_array($data)) {
            return false;
        }
        
        // Проверяем наличие полей, которые нужно развернуть
        $fieldsToExpand = ['status_id', 'role_id', 'owner_user_id', 'car_id', 'user_id'];
        
        foreach ($fieldsToExpand as $field) {
            if (isset($data[$field])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Автоматически развернуть данные в зависимости от типа
     * 
     * @param array $data Данные для развертывания
     * @param string $type Тип данных (car, user, business_card)
     * @return array Развернутые данные
     */
    public static function autoExpand($data, $type = null)
    {
        if (!self::needsExpansion($data)) {
            return $data;
        }
        
        switch ($type) {
            case 'car':
                return self::expandCarData($data);
            case 'user':
                return self::expandUserData($data);
            case 'business_card':
                return self::expandBusinessCardData($data);
            default:
                // Автоопределение по полям
                if (isset($data['reg_number']) || isset($data['plate_number'])) {
                    return self::expandCarData($data);
                } elseif (isset($data['telegram_id']) || isset($data['first_name'])) {
                    return self::expandUserData($data);
                } elseif (isset($data['car_id']) && isset($data['user_id'])) {
                    return self::expandBusinessCardData($data);
                }
                
                return $data;
        }
    }
} 