-- CabrioRide — наполнение справочника марок автомобилей (ref_car_brands)
-- Безопасный импорт: каждая марка вставляется только если ещё не существует

START TRANSACTION;

-- Базовые европейские и японские бренды
INSERT INTO ref_car_brands (brand)
SELECT 'BMW' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='BMW');
INSERT INTO ref_car_brands (brand)
SELECT 'Mercedes-Benz' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Mercedes-Benz');
INSERT INTO ref_car_brands (brand)
SELECT 'Audi' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Audi');
INSERT INTO ref_car_brands (brand)
SELECT 'Volkswagen' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Volkswagen');
INSERT INTO ref_car_brands (brand)
SELECT 'Porsche' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Porsche');
INSERT INTO ref_car_brands (brand)
SELECT 'Lexus' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Lexus');
INSERT INTO ref_car_brands (brand)
SELECT 'Toyota' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Toyota');
INSERT INTO ref_car_brands (brand)
SELECT 'Honda' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Honda');
INSERT INTO ref_car_brands (brand)
SELECT 'Mazda' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Mazda');
INSERT INTO ref_car_brands (brand)
SELECT 'Nissan' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Nissan');
INSERT INTO ref_car_brands (brand)
SELECT 'Subaru' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Subaru');
INSERT INTO ref_car_brands (brand)
SELECT 'Mitsubishi' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Mitsubishi');
INSERT INTO ref_car_brands (brand)
SELECT 'Suzuki' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Suzuki');
INSERT INTO ref_car_brands (brand)
SELECT 'Hyundai' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Hyundai');
INSERT INTO ref_car_brands (brand)
SELECT 'Kia' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Kia');
INSERT INTO ref_car_brands (brand)
SELECT 'Volvo' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Volvo');
INSERT INTO ref_car_brands (brand)
SELECT 'Saab' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Saab');
INSERT INTO ref_car_brands (brand)
SELECT 'Skoda' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Skoda');
INSERT INTO ref_car_brands (brand)
SELECT 'Seat' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Seat');
INSERT INTO ref_car_brands (brand)
SELECT 'Opel' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Opel');
INSERT INTO ref_car_brands (brand)
SELECT 'Peugeot' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Peugeot');
INSERT INTO ref_car_brands (brand)
SELECT 'Citroen' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Citroen');
INSERT INTO ref_car_brands (brand)
SELECT 'Renault' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Renault');
INSERT INTO ref_car_brands (brand)
SELECT 'Fiat' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Fiat');
INSERT INTO ref_car_brands (brand)
SELECT 'Alfa Romeo' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Alfa Romeo');
INSERT INTO ref_car_brands (brand)
SELECT 'Ferrari' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Ferrari');
INSERT INTO ref_car_brands (brand)
SELECT 'Maserati' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Maserati');
INSERT INTO ref_car_brands (brand)
SELECT 'Lamborghini' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Lamborghini');
INSERT INTO ref_car_brands (brand)
SELECT 'Aston Martin' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Aston Martin');
INSERT INTO ref_car_brands (brand)
SELECT 'Bentley' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Bentley');
INSERT INTO ref_car_brands (brand)
SELECT 'Rolls-Royce' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Rolls-Royce');
INSERT INTO ref_car_brands (brand)
SELECT 'Jaguar' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Jaguar');
INSERT INTO ref_car_brands (brand)
SELECT 'Land Rover' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Land Rover');
INSERT INTO ref_car_brands (brand)
SELECT 'MINI' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='MINI');
INSERT INTO ref_car_brands (brand)
SELECT 'Smart' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Smart');
INSERT INTO ref_car_brands (brand)
SELECT 'Tesla' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Tesla');

-- США
INSERT INTO ref_car_brands (brand)
SELECT 'Ford' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Ford');
INSERT INTO ref_car_brands (brand)
SELECT 'Chevrolet' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Chevrolet');
INSERT INTO ref_car_brands (brand)
SELECT 'Cadillac' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Cadillac');
INSERT INTO ref_car_brands (brand)
SELECT 'GMC' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='GMC');
INSERT INTO ref_car_brands (brand)
SELECT 'Dodge' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Dodge');
INSERT INTO ref_car_brands (brand)
SELECT 'RAM' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='RAM');
INSERT INTO ref_car_brands (brand)
SELECT 'Jeep' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Jeep');
INSERT INTO ref_car_brands (brand)
SELECT 'Chrysler' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Chrysler');
INSERT INTO ref_car_brands (brand)
SELECT 'Lincoln' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Lincoln');
INSERT INTO ref_car_brands (brand)
SELECT 'Buick' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Buick');

-- Китай/другие
INSERT INTO ref_car_brands (brand)
SELECT 'Geely' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Geely');
INSERT INTO ref_car_brands (brand)
SELECT 'Chery' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Chery');
INSERT INTO ref_car_brands (brand)
SELECT 'Haval' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Haval');
INSERT INTO ref_car_brands (brand)
SELECT 'Great Wall' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Great Wall');
INSERT INTO ref_car_brands (brand)
SELECT 'BAIC' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='BAIC');
INSERT INTO ref_car_brands (brand)
SELECT 'JAC' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='JAC');
INSERT INTO ref_car_brands (brand)
SELECT 'Changan' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Changan');
INSERT INTO ref_car_brands (brand)
SELECT 'BYD' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='BYD');
INSERT INTO ref_car_brands (brand)
SELECT 'FAW' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='FAW');
INSERT INTO ref_car_brands (brand)
SELECT 'Lifan' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Lifan');
INSERT INTO ref_car_brands (brand)
SELECT 'Zotye' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Zotye');
INSERT INTO ref_car_brands (brand)
SELECT 'Foton' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Foton');
INSERT INTO ref_car_brands (brand)
SELECT 'Hongqi' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Hongqi');
INSERT INTO ref_car_brands (brand)
SELECT 'Exeed' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Exeed');
INSERT INTO ref_car_brands (brand)
SELECT 'Jetour' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Jetour');

-- Прочие бренды, встречающиеся в регионе
INSERT INTO ref_car_brands (brand)
SELECT 'Lada' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Lada');
INSERT INTO ref_car_brands (brand)
SELECT 'UAZ' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='UAZ');
INSERT INTO ref_car_brands (brand)
SELECT 'GAZ' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='GAZ');
INSERT INTO ref_car_brands (brand)
SELECT 'Moskvich' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Moskvich');
INSERT INTO ref_car_brands (brand)
SELECT 'Aurus' WHERE NOT EXISTS (SELECT 1 FROM ref_car_brands WHERE brand='Aurus');

COMMIT;

-- Примечание: если нужна полная вёрстка ~100 брендов из docs/catalogs/car_brands.md,
-- можно расширить список выше аналогичными строками. Текущий набор покрывает основные популярные марки.


