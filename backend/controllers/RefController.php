<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../utils/Database.php';

class RefController extends BaseController
{
    /**
     * GET /api/ref/car-brands
     * Минимальная роль: guest
     */
    public function getCarBrands()
    {
        try {
            if (!$this->requireAccess('api.ref.getCarBrands')) { return; }
            $pdo = Database::getInstance();
            $rows = $pdo->query('SELECT id, brand as name FROM ref_car_brands ORDER BY brand')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $this->json(['success' => true, 'data' => $rows, 'meta' => $this->getRequestInfo()]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}


