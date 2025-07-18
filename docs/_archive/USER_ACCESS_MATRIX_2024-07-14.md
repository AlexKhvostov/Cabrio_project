# ⚠️ УСТАРЕВШИЙ ДОКУМЕНТ

> Актуальная схема ролей и статусов пользователя — см. [docs/USER_ROLES.md](../USER_ROLES.md)

# 📋 Матрица доступов CabrioRide

## Как читать эту матрицу

- **Разрешение (✅)** — явно разрешено, если нет запрета на других уровнях.
- **Запрет (❌)** — явно запрещено, всегда имеет приоритет над разрешением.
- **Нейтраль (➖)** — не задано явно, решение принимается на другом уровне или по умолчанию (обычно «запрещено»).
- **Правило приоритета:**
  1. Если хотя бы на одном уровне (роль, статус, флаг) стоит ❌ — доступ запрещён.
  2. Если есть хотя бы одно ✅ и нет ❌ — доступ разрешён.
  3. Если везде ➖ — доступ запрещён по умолчанию.

---

## 1️⃣ Матрица "Роли × Функции"

| Функция / Раздел                | guest | user | moderator | admin | root |
|---------------------------------|:-----:|:----:|:---------:|:-----:|:----:|
| Просмотр гид-объектов           |  ✅   |  ✅  |    ✅     |  ✅   |  ✅  |
| Добавление авто                 |  ❌   |  ✅  |    ✅     |  ✅   |  ✅  |
| Редактирование профиля          |  ❌   |  ✅  |    ✅     |  ✅   |  ✅  |
| Модерация профилей              |  ❌   |  ❌  |    ✅     |  ✅   |  ✅  |
| Управление событиями            |  ❌   |  ❌  |    ✅     |  ✅   |  ✅  |
| Просмотр карты                  |  ✅   |  ✅  |    ✅     |  ✅   |  ✅  |
| Оставить отзыв                  |  ❌   |  ✅  |    ✅     |  ✅   |  ✅  |
| Админ-панель                    |  ❌   |  ❌  |    ❌     |  ✅   |  ✅  |
| Управление пользователями       |  ❌   |  ❌  |    ❌     |  ✅   |  ✅  |
| Блокировка пользователей        |  ❌   |  ❌  |    ❌     |  ✅   |  ✅  |
| ...                             |       |      |           |       |      |

---

## 2️⃣ Матрица "Статусы × Функции"

| Функция / Статус                | external | new | registered | pending | active | blocked | left | deleted |
|---------------------------------|:--------:|:---:|:----------:|:-------:|:------:|:-------:|:----:|:-------:|
| Просмотр гид-объектов           |   ❌     | ❌  |    ✅      |   ✅    |   ✅   |   ❌    |  ❌  |   ❌    |
| Добавление авто                 |   ❌     | ❌  |    ✅      |   ✅    |   ✅   |   ❌    |  ❌  |   ❌    |
| Редактирование профиля          |   ❌     | ❌  |    ✅      |   ✅    |   ✅   |   ❌    |  ❌  |   ❌    |
| Модерация профилей              |   ❌     | ❌  |    ❌      |   ❌    |   ✅   |   ❌    |  ❌  |   ❌    |
| Управление событиями            |   ❌     | ❌  |    ❌      |   ❌    |   ✅   |   ❌    |  ❌  |   ❌    |
| Просмотр карты                  |   ❌     | ❌  |    ✅      |   ✅    |   ✅   |   ❌    |  ❌  |   ❌    |
| Оставить отзыв                  |   ❌     | ❌  |    ✅      |   ✅    |   ✅   |   ❌    |  ❌  |   ❌    |
| Админ-панель                    |   ❌     | ❌  |    ❌      |   ❌    |   ✅   |   ❌    |  ❌  |   ❌    |
| ...                             |         |     |            |         |        |         |      |         |

---

## 3️⃣ Матрица "Флаги × Функции"

| Функция / Флаг                  | have_auto | respect | block |
|---------------------------------|:---------:|:-------:|:-----:|
| Доступ к клубным поездкам       |    ✅     |   ➖    |  ❌   |
| Доступ к закрытым событиям      |    ➖     |   ✅    |  ❌   |
| Доступ к профилю                |    ✅     |   ✅    |  ❌   |
| ...                             |           |         |       |

---

## Пример проверки доступа (алгоритм)

1. **Проверить статус:**  
   - Если в матрице "Статусы × Функции" стоит ❌ — доступ запрещён, дальше не проверяем.
2. **Проверить роль:**  
   - Если в матрице "Роли × Функции" стоит ❌ — доступ запрещён, даже если статус разрешает.
   - Если стоит ✅ — доступ разрешён, если нет ❌ на других уровнях.
3. **Проверить флаги:**  
   - Если в матрице "Флаги × Функции" стоит ❌ — доступ запрещён.
   - Если стоит ✅ — доступ разрешён, если нет ❌ на других уровнях.
4. **Если везде ➖ — доступ запрещён по умолчанию.**

---

## Рекомендации

- **Явно указывайте запреты (❌) для критичных функций.**
- **Используйте нейтраль (➖) только там, где решение должно приниматься на другом уровне.**
- **В начале документа всегда описывайте принцип работы и приоритеты.**
- **Для сложных случаев приводите примеры (см. выше).**
- **Обновляйте матрицу при добавлении новых функций, ролей, статусов или флагов.**

---

> Если потребуется расширить или изменить матрицу — обязательно согласовать с командой и обновить этот документ. 