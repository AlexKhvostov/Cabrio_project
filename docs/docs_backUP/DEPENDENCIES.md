# 📊 Схема зависимостей backend CabrioRide

В этом файле собраны зависимости между контроллерами, actions, моделями и утилитами. Это помогает быстро понять архитектуру и связи между компонентами.

---

## 📋 Текстовый список зависимостей

### Контроллеры
- **UserController**: User, Role, AuthHelper, ResponseHelper
- **CarController**: Car, CarBrand, User, LinkUserCar, AuthHelper, ResponseHelper, AddCarForUserAction, CreateCarWithPhotoAction
- **EventController**: Event, EventType, User, LinkEventParticipant, AuthHelper, ResponseHelper, CreateEventWithPhotoAction
- **GuideObjectController**: GuideObject, GuideObjectType, GuideObjectKind, User, Status, AuthHelper, ResponseHelper, CreateGuideObjectWithPhotoAction
- **ReviewController**: Review, GuideObject, User, Status, AuthHelper, ResponseHelper, CreateReviewWithPhotoAction
- **PhotoController**: Photo, User, Car, Event, Review, GuideObject, AuthHelper, ResponseHelper
- **BusinessCardController**: BusinessCard, Car, User, AddBusinessCardAction, CreateBusinessCardWithPhotoAction, AuthHelper, ResponseHelper

### Actions (ключевые)
- **AddCarForUserAction**: Car, User, LinkUserCar, Logger, ResponseHelper
- **CreateCarWithPhotoAction**: Car, Photo, Logger, ResponseHelper
- **AddBusinessCardAction**: BusinessCard, Car, CheckCarExistsAction, Logger, ResponseHelper
- **CreateBusinessCardWithPhotoAction**: BusinessCard, Photo, Logger, ResponseHelper
- **CheckCarExistsAction**: Car, Logger
- **RecognizeCarNumberFromPhotoAction**: CheckCarExistsAction, Logger, ResponseHelper
- **CreateUserWithPhotoAction**: User, Photo, Logger, ResponseHelper
- **CreateEventWithPhotoAction**: Event, Photo, Logger, ResponseHelper
- **CreateGuideObjectWithPhotoAction**: GuideObject, Photo, Logger, ResponseHelper
- **CreateReviewWithPhotoAction**: Review, Photo, Logger, ResponseHelper

---

## 🗺️ Визуальная схема зависимостей

Полная интерактивная диаграмма ключевых связей находится в файле:

[backend/docs/DEPENDENCIES_DIAGRAM.md](DEPENDENCIES_DIAGRAM.md)

---

> Если нужно — можно расширить схему для actions по модерации, активности, сессиям и т.д. 