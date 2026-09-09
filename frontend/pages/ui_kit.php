<?php
/**
 * Живой каталог UI. Не раздел клуба — стол, за которым смотрим и правим внешний вид.
 * Номера пунктов не меняем: по ним говорим «пункт 17 — сделай рамку толще».
 */
require __DIR__ . '/../partials/meta.php';
require_once __DIR__ . '/../components/nav_icons.php';
require_once __DIR__ . '/../components/club_info.php';

function kit_open(int $n, string $title, string $used = ''): void {
  echo '<article class="kit-item" id="k-' . $n . '">';
  echo '<header class="kit-item-head">';
  echo '<span class="kit-num">' . $n . '</span>';
  echo '<div><h2>' . htmlspecialchars($title) . '</h2>';
  if ($used !== '') {
    echo '<p class="kit-used">Где в приложении: ' . htmlspecialchars($used) . '</p>';
  }
  echo '</div></header><div class="kit-demo">';
}
function kit_close(): void {
  echo '</div></article>';
}
function kit_group(string $title, string $description): void {
  echo '<header class="kit-group-head">';
  echo '<span class="kit-group-kicker">Компоненты</span>';
  echo '<h2>' . htmlspecialchars($title) . '</h2>';
  echo '<p>' . htmlspecialchars($description) . '</p>';
  echo '</header>';
}

function kit_ph_user(string $ini = ''): string {
  $iniH = htmlspecialchars($ini);
  $has = $ini !== '' ? ' ph-has-ini' : '';
  $iniHtml = $ini !== '' ? '<span class="ph-ini">' . $iniH . '</span>' : '';
  return '<div class="ph ph-user' . $has . '"><span class="ph-fallback" aria-hidden="true"><img class="ph-draw ph-user-art" src="../assets/img/ph-user.png?v=4" alt="">' . $iniHtml . '</span></div>';
}
function kit_ph_car(): string {
  return '<div class="ph ph-car"><span class="ph-fallback" aria-hidden="true"><img class="ph-draw" src="../assets/img/ph-car.png" alt=""></span></div>';
}

/** Нижнее меню как в приложении — для пункта 45 */
function kit_nav_menu(): void {
  $items = [
    ['people', 'Участники', true],
    ['car', 'Авто', false],
    ['map', 'Карта', false],
    ['events', 'События', false],
    ['guide', 'Отзывы', false],
    ['profile', 'Профиль', false],
  ];
  echo '<div class="kit-nav-demo"><nav class="bottom-nav" aria-label="Пример меню">';
  foreach ($items as [$pic, $label, $on]) {
    $cls = $on ? 'nav-item active' : 'nav-item';
    echo '<a class="' . $cls . '" href="#k-45"><span class="nav-icon">' . cabrio_nav_pic($pic) . '</span><span class="nav-label">' . htmlspecialchars($label) . '</span></a>';
  }
  echo '</nav></div>';
}

$toc = [
  'Цвета' => [1=>'Фон экрана',2=>'Карточка / поверхность',3=>'Плитка поля',4=>'Рамка',5=>'Основной текст',6=>'Приглушённый текст',7=>'«не указано»',8=>'Акцент клуба'],
  'Текст' => [9=>'Приветствие главной',10=>'Заголовок карточки',11=>'Подпись поля'],
  'Кнопки' => [12=>'Ghost (Изменить / Отмена)',13=>'Primary (Сохранить)',14=>'Secondary',15=>'Закрыть модалку',16=>'Ряд действий в шапке'],
  'Поля' => [17=>'Поиск списка',18=>'Выпадающий список',19=>'Обычный инпут',20=>'Многострочное поле',21=>'Комбо с подсказками',22=>'Чекбокс',23=>'Строка: заполнено',24=>'Строка: пусто',25=>'Строка: правка',26=>'Последовательный список'],
  'Фото' => [27=>'Аватар-заглушка',28=>'Аватар с фото',29=>'Рамка авто-заглушка',30=>'Рамка авто с фото',31=>'Большая обложка 16:9',32=>'Кнопка загрузки фото',62=>'Все заглушки'],
  'Списки' => [33=>'Бейдж роли',34=>'Бейдж статуса авто',35=>'Карточка человека',36=>'Компоновка авто',37=>'Карточка авто в сетке',67=>'Плитка события',68=>'Плитка отзыва',38=>'Ссылка хозяин ↔ авто'],
  'Большая карточка' => [39=>'Шапка карточки',40=>'Шапка человека (фото + имя)',41=>'Поля большой карточки',42=>'Карточка целиком'],
  'Экраны' => [43=>'Статистика главной',69=>'Стартовый экран',70=>'Нет доступа — не в группе',44=>'Пустой / инфо-блок',45=>'Нижнее меню',46=>'Название в шапке Telegram',63=>'Всплывающая подсказка i'],
  'Карта и прочее' => [47=>'Переключатель геолокации',48=>'Круглые кнопки карты',49=>'Метка на карте',50=>'Кто сейчас на карте',51=>'Просмотр фото',52=>'Спиннер',53=>'Ошибка / тост'],
  'Модалки разделов' => [
    54=>'Каркас модалки',
    55=>'Участники — карточка человека',
    56=>'Авто — карточка машины',
    57=>'Карта — открытие с метки',
    58=>'События — карточка встречи',
    72=>'События — правка карточки',
    59=>'Отзывы — карточка',
    71=>'Отзывы — правка карточки',
    60=>'Профиль — sheet на странице',
    61=>'Режим редактирования',
    66=>'Режим создания',
    64=>'События — создание',
    65=>'Отзывы — добавление',
    73=>'Справка — роли и доступы',
    75=>'Справка — разделы',
    74=>'Справка — доступы (снято)',
  ],
];

$kitUrl = 'https://dev.cabrioride.by/app/frontend/pages/ui_kit.php';
?>
<!doctype html>
<html lang="ru" class="ui-kit">
  <head>
    <?php render_meta('UI Kit — CabrioRide'); ?>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/styles.css'); ?>" />
    <link rel="stylesheet" href="../assets/css/ui_kit_page.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/ui_kit_page.css'); ?>" />
  </head>
  <body>
    <div class="kit-shell">
      <header class="kit-hero">
        <h1>UI Kit CabrioRide</h1>
        <p>Эталон внешнего вида Mini App. Зафиксирован 8 сентября 2026: живые экраны копируют эти компоненты, а не наоборот.</p>
        <p>Все компоненты как в приложении. Напишите номер пункта и что сделать, например: «пункт 18 — сделай список выше».</p>
        <a class="kit-link" href="<?php echo htmlspecialchars($kitUrl); ?>"><?php echo htmlspecialchars($kitUrl); ?></a>
      </header>

      <div class="kit-layout">
        <nav class="kit-toc" aria-label="Оглавление по номерам">
          <h2>Оглавление</h2>
          <?php foreach ($toc as $group => $rows): ?>
            <div class="kit-toc-group"><?php echo htmlspecialchars($group); ?></div>
            <?php foreach ($rows as $n => $title): ?>
              <a href="#k-<?php echo (int)$n; ?>"><?php echo (int)$n; ?>. <?php echo htmlspecialchars($title); ?></a>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </nav>

        <main class="kit-stage">

<?php kit_group('Цвет и поверхности', 'Базовая палитра, глубина слоёв и контраст интерфейса.'); ?>
<?php kit_open(1, 'Фон экрана', 'Все страницы'); ?>
  <div class="kit-swatch" style="background:var(--bg);color:#fff"><span>#070b12 · --bg</span></div>
<?php kit_close(); ?>

<?php kit_open(2, 'Карточка / поверхность', 'Модалки, профиль, инфо-блоки'); ?>
  <div class="card">Текст на поверхности карточки</div>
  <div class="kit-swatch" style="background:var(--surface)"><span>стекло · --surface</span></div>
<?php kit_close(); ?>

<?php kit_open(3, 'Плитка поля', 'Поля в большой карточке'); ?>
  <div class="sheet-field"><span class="sheet-label">Пример</span><span class="sheet-value">Тёмная плитка поля</span></div>
  <div class="kit-swatch" style="background:var(--field)"><span>стекло · --field</span></div>
<?php kit_close(); ?>

<?php kit_open(4, 'Рамка', 'Карточки, поля, кнопки ghost'); ?>
  <div class="kit-swatch" style="background:var(--bg);border-color:var(--line)"><span>бирюза 16% · --line</span></div>
<?php kit_close(); ?>

<?php kit_open(5, 'Основной текст', 'Имена, значения полей'); ?>
  <p style="margin:0;color:var(--text);font-size:16px">Иван Петров — основной текст</p>
<?php kit_close(); ?>

<?php kit_open(6, 'Приглушённый текст', 'Подписи, ник, «где в приложении»'); ?>
  <p style="margin:0;color:var(--muted);font-size:13px">Город, ник, второстепенные подписи</p>
<?php kit_close(); ?>

<?php kit_open(7, 'Текст «не указано»', 'Пустые поля в карточке'); ?>
  <div id="d-empty-mark"><span class="sheet-empty">не указано</span></div>
  <p class="kit-note">Пустое поле не прячем — серая подпись напоминает заполнить.</p>
<?php kit_close(); ?>

<?php kit_open(8, 'Акцент клуба', 'Кнопка Сохранить, фокус, актив'); ?>
  <div class="kit-swatch" style="background:var(--accent);color:var(--ink)"><span>#46e0d6 · --accent</span></div>
<?php kit_close(); ?>

<?php kit_group('Типографика', 'Компактная шкала текста без случайных размеров.'); ?>
<?php kit_open(9, 'Приветствие главной', 'Поверх обложки, п. 69'); ?>
  <p class="home-hello kit-type-hero">Привет, Иван</p>
  <p class="home-lead">Клуб владельцев кабриолетов</p>
<?php kit_close(); ?>

<?php kit_open(10, 'Заголовок карточки', 'Шапка модалки и профиля'); ?>
  <p class="kit-type-title modal-title">BMW Z4</p>
<?php kit_close(); ?>

<?php kit_open(11, 'Подпись поля', 'Все плитки большой карточки'); ?>
  <p class="kit-type-label">Город</p>
<?php kit_close(); ?>

<?php kit_group('Кнопки и действия', 'Единая высота, радиус и понятная иерархия действий.'); ?>
<?php kit_open(12, 'Кнопка ghost', 'Изменить, Отмена'); ?>
  <div class="kit-row">
    <button type="button" class="btn-ghost">Изменить</button>
    <button type="button" class="btn-ghost">Отмена</button>
  </div>
<?php kit_close(); ?>

<?php kit_open(13, 'Кнопка primary', 'Сохранить, главное действие'); ?>
  <div class="kit-row">
    <button type="button" class="btn-primary">Сохранить</button>
    <a class="btn-primary" href="#">Ссылка-кнопка</a>
  </div>
<?php kit_close(); ?>

<?php kit_open(14, 'Кнопка secondary', 'Второстепенные действия'); ?>
  <div class="kit-row">
    <button type="button" class="btn-secondary">Второстепенно</button>
  </div>
<?php kit_close(); ?>

<?php kit_open(15, 'Кнопка закрыть модалку', 'Крестик в шапке карточки'); ?>
  <div class="kit-row">
    <button type="button" class="modal-close" aria-label="close">×</button>
  </div>
<?php kit_close(); ?>

<?php kit_open(16, 'Ряд действий в шапке', 'Большая карточка и Профиль'); ?>
  <p class="kit-note">Просмотр</p>
  <div class="sheet-actions" id="d-header-view">
    <button type="button" class="btn-ghost">Изменить</button>
    <button type="button" class="modal-close">×</button>
  </div>
  <p class="kit-note">Редактирование</p>
  <div class="sheet-actions" id="d-header-edit">
    <button type="button" class="btn-ghost">Отмена</button>
    <button type="button" class="btn-primary">Сохранить</button>
    <button type="button" class="modal-close">×</button>
  </div>
<?php kit_close(); ?>

<?php kit_group('Поля и ввод', 'Поиск, фильтры и поля просмотра/редактирования.'); ?>
<?php kit_open(17, 'Поиск списка', 'Участники, авто'); ?>
  <div class="search-bar">
    <span class="search-icon">🔎</span>
    <input class="search-input" type="text" placeholder="Поиск по имени или нику..." />
  </div>
<?php kit_close(); ?>

<?php kit_open(18, 'Выпадающий список', 'Фильтр роли, статус авто, крыша'); ?>
  <select class="filter-select">
    <option>Пользователь и выше</option>
    <option>Участник</option>
    <option>Модератор</option>
  </select>
<?php kit_close(); ?>

<?php kit_open(19, 'Обычный инпут', 'Имя, город, год, VIN — отдельно от плитки'); ?>
  <input class="filter-input" type="text" value="Минск" />
  <input class="filter-input" type="text" placeholder="Город не указан" />
<?php kit_close(); ?>

<?php kit_open(20, 'Многострочное поле', 'О себе, описание авто'); ?>
  <textarea class="filter-input" rows="3">Люблю открытый верх и вечерние маршруты.</textarea>
<?php kit_close(); ?>

<?php kit_open(21, 'Комбо с подсказками', 'Марка авто при правке'); ?>
  <div class="combo kit-combo">
    <input class="combo-input" type="text" value="BMW" autocomplete="off" />
    <div class="combo-list">
      <div class="combo-item">BMW</div>
      <div class="combo-item">Mazda</div>
      <div class="combo-item">Mercedes-Benz</div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(22, 'Чекбокс', 'Скрыть госномер'); ?>
  <label class="sheet-check"><input type="checkbox" checked /> Скрыть номер</label>
<?php kit_close(); ?>

<?php kit_open(23, 'Строка поля — заполнено', 'Просмотр большой карточки'); ?>
  <div class="sheet-field"><span class="sheet-label">Город</span><span class="sheet-value">Минск</span></div>
<?php kit_close(); ?>

<?php kit_open(24, 'Строка поля — пусто', 'Пустые поля не прячем'); ?>
  <div class="sheet-field"><span class="sheet-label">Телефон</span><span class="sheet-empty">не указано</span></div>
<?php kit_close(); ?>

<?php kit_open(25, 'Строка поля — правка', 'После нажатия Изменить'); ?>
  <div class="sheet-card editing">
    <div class="sheet-grid">
      <div class="sheet-field"><span class="sheet-label">Город</span><input class="filter-input" value="Минск" /></div>
      <div class="sheet-field"><span class="sheet-label">Роль</span>
        <select class="filter-select">
          <option>Участник</option>
          <option>Модератор</option>
        </select>
      </div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(26, 'Последовательный список полей', 'Большая карточка: одна логичная колонка, длинный текст отдельной строкой'); ?>
  <div class="sheet-grid" id="d-sheet-fields">
    <div class="sheet-field"><span class="sheet-label">Имя</span><span class="sheet-value">Иван</span></div>
    <div class="sheet-field"><span class="sheet-label">Город</span><span class="sheet-empty">не указано</span></div>
    <div class="sheet-field full"><span class="sheet-label">О себе</span><span class="sheet-value">Люблю открытый верх и вечерние маршруты.</span></div>
  </div>
<?php kit_close(); ?>

<?php kit_group('Фото и заглушки', 'Рамки медиа, состояния без фотографии и загрузка.'); ?>
<?php kit_open(27, 'Аватар-заглушка с инициалами', 'Нет фото человека'); ?>
  <div class="kit-row">
    <div id="d-ph-user-empty" class="kit-ph"><?php echo kit_ph_user('АК'); ?></div>
    <div id="d-ph-user-list" class="kit-ph-sm"><?php echo kit_ph_user('АК'); ?></div>
  </div>
  <p class="kit-note">Слева — в большой карточке (72px). Справа — в списке (48px).</p>
<?php kit_close(); ?>

<?php kit_open(28, 'Аватар с фото', 'Если фото есть — инициалы не видны'); ?>
  <div id="d-ph-user-photo" class="kit-ph"><?php echo kit_ph_user('ИП'); ?></div>
<?php kit_close(); ?>

<?php kit_open(29, 'Рамка авто — заглушка', 'Нет фото: кабриолет под чехлом на выставке, верх открыт'); ?>
  <div id="d-ph-car-empty" style="width:100%;height:118px;border-radius:12px;overflow:hidden"><?php echo kit_ph_car(); ?></div>
<?php kit_close(); ?>

<?php kit_open(30, 'Рамка авто — с фото', 'Плитка в сетке автомобилей'); ?>
  <div id="d-ph-car-photo" style="width:100%;height:118px;border-radius:12px;overflow:hidden"><?php echo kit_ph_car(); ?></div>
<?php kit_close(); ?>

<?php kit_open(31, 'Большая обложка 16:9', 'Модалка авто: фото на всю ширину, подпись снизу'); ?>
  <div class="kit-cover modal-content" id="d-cover">
    <div class="main-photo-compact">
      <?php echo kit_ph_car(); ?>
      <span class="sheet-photo-badge" style="position:absolute;top:10px;left:10px">На модерации</span>
      <div class="sheet-photo-caption">
        <div class="sheet-photo-title">BMW Z4</div>
        <div class="sheet-photo-meta">2016</div>
      </div>
      <button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(32, 'Кнопка загрузки фото', 'Только в режиме Изменить, правый нижний угол обложки. См. также пункт 31'); ?>
  <button type="button" class="photo-upload-fab"><span>📷</span><span>Фото</span></button>
  <p class="kit-note">Цвет клуба, чип с подписью «Фото». На обложке — угол кадра, на аватаре — угол круглой фотографии.</p>
<?php kit_close(); ?>

<?php kit_open(62, 'Все заглушки', 'Когда фото нет. Сравните размеры как в приложении'); ?>
  <p class="kit-note">Участник — нейтральный бюст без лица и пола. Авто — кабриолет под чехлом, верх открыт.</p>
  <div class="kit-ph-gallery">
    <figure class="kit-ph-cell">
      <div class="kit-ph kit-ph-round"><?php echo kit_ph_user(''); ?></div>
      <figcaption>Участник · круг 72</figcaption>
    </figure>
    <figure class="kit-ph-cell">
      <div class="kit-ph"><?php echo kit_ph_user(''); ?></div>
      <figcaption>Участник · квадрат 72</figcaption>
    </figure>
    <figure class="kit-ph-cell">
      <div class="kit-ph-sm"><?php echo kit_ph_user('АК'); ?></div>
      <figcaption>Список 48 · с инициалами</figcaption>
    </figure>
    <figure class="kit-ph-cell">
      <div class="kit-ph-nav"><?php echo kit_ph_user(''); ?></div>
      <figcaption>Меню 36</figcaption>
    </figure>
  </div>
  <div class="kit-ph-gallery kit-ph-gallery-cars">
    <figure class="kit-ph-cell kit-ph-cell-wide">
      <div class="kit-ph-car-lg"><?php echo kit_ph_car(); ?></div>
      <figcaption>Авто · обложка 16:9</figcaption>
    </figure>
    <figure class="kit-ph-cell">
      <div class="kit-ph-car-md"><?php echo kit_ph_car(); ?></div>
      <figcaption>Авто · 1 в карточке</figcaption>
    </figure>
    <figure class="kit-ph-cell">
      <div class="kit-ph-car-sm"><?php echo kit_ph_car(); ?></div>
      <figcaption>Авто · мини 64</figcaption>
    </figure>
  </div>
<?php kit_close(); ?>

<?php kit_group('Списки и карточки', 'Компактные элементы каталога участников и автомобилей.'); ?>
<?php kit_open(33, 'Бейдж роли', 'Список людей, профиль, большая карточка'); ?>
  <div class="kit-row">
    <span class="role-badge">Участник</span>
    <span class="role-badge">Модератор</span>
    <span class="role-badge">Пользователь</span>
  </div>
<?php kit_close(); ?>

<?php kit_open(34, 'Бейдж статуса авто', 'На фото, если машина не «активна»'); ?>
  <div class="kit-row">
    <span class="car-status-badge" style="position:static">На модерации</span>
    <span class="sheet-photo-badge">Замечен</span>
  </div>
<?php kit_close(); ?>

<?php kit_open(35, 'Карточка человека в списке', 'Экран Участники'); ?>
  <p class="kit-note">Одна машина — широкая мини-карточка. Две — рядом без нахлёста. Нахлёст включается только для 3+, когда места уже недостаточно. Марка подписана на каждой.</p>
  <div class="users-list">
    <p class="kit-note">3 машины</p>
    <div id="d-member-card-3"></div>
    <p class="kit-note">2 машины</p>
    <div id="d-member-card-2"></div>
    <p class="kit-note">1 машина</p>
    <div id="d-member-card-1"></div>
    <p class="kit-note">4 машины (3 видны + бейдж +1)</p>
    <div id="d-member-card-4"></div>
    <p class="kit-note">Без авто</p>
    <div id="d-member-card-empty"></div>
  </div>
<?php kit_close(); ?>

<?php kit_open(79, 'Свайп роли участника', 'Экран Участники, только модератор и администратор'); ?>
  <p class="kit-note">Свайп только открывает компактную кнопку. Влево — повысить справа, вправо — понизить слева. После нажатия обязательно подтверждение. Одновременно открыта только одна строка.</p>
  <div class="kit-role-swipe-examples">
    <div>
      <p class="kit-role-swipe-label">Обычная плитка</p>
      <div id="d-role-swipe-rest"></div>
    </div>
    <div>
      <p class="kit-role-swipe-label">Свайп влево</p>
      <div id="d-role-swipe-up"></div>
    </div>
    <div>
      <p class="kit-role-swipe-label">Свайп вправо</p>
      <div id="d-role-swipe-down"></div>
    </div>
  </div>
  <div class="kit-role-confirm">
    <div>
      <strong>Изменить роль?</strong>
      <span>Иван Петров · Участник → Модератор</span>
    </div>
    <div class="kit-role-confirm-actions">
      <button class="btn-ghost" type="button">Отмена</button>
      <button class="btn-primary" type="button">Повысить</button>
    </div>
  </div>
  <p class="kit-note">Ограничение: модератор не может назначать модераторов/администраторов и менять людей с равной или более высокой ролью. Администратор может менять доступные роли, кроме своей собственной.</p>
<?php kit_close(); ?>

<?php kit_open(36, 'Компоновка авто в карточке человека', 'Пункт 35 — читаемые мини-карточки с маркой'); ?>
  <p class="kit-note">Клик по любой мини-карточке открывает авто. Для 1–2 машин изображение и марка видны полностью; компактный нахлёст используется только начиная с третьей.</p>
  <div class="kit-stack-preview">
    <div id="d-car-stack"></div>
    <span class="kit-note">3 автомобиля</span>
  </div>
<?php kit_close(); ?>

<?php kit_open(37, 'Карточка авто в сетке', 'Экран Авто, две колонки'); ?>
  <div id="d-car-grid">
    <div class="cars-grid">
      <div class="car-card-compact">
        <div class="car-image-container">
          <div class="car-status-badge">На модерации</div>
          <?php echo kit_ph_car(); ?>
          <div class="car-overlay-info">
            <div class="car-title-overlay">BMW Z4</div>
            <div class="car-specs-overlay">2016</div>
          </div>
        </div>
        <div class="car-owner-compact">
          <?php echo kit_ph_user('ИП'); ?>
          <span class="owner-name-compact">Иван Петров</span>
        </div>
      </div>
      <div class="car-card-compact">
        <div class="car-image-container">
          <?php echo kit_ph_car(); ?>
          <div class="car-overlay-info">
            <div class="car-title-overlay">Mazda MX-5</div>
            <div class="car-specs-overlay">2018</div>
          </div>
        </div>
        <div class="car-owner-compact">
          <?php echo kit_ph_user('АК'); ?>
          <span class="owner-name-compact">Анна К.</span>
        </div>
      </div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(67, 'Плитка события', 'Экран События, две колонки'); ?>
  <p class="kit-note">Слева люди: едут и думают (свой голос — рамка и ✓ / ✓+1). Справа за чертой — свободные места, это не голос. ∞ — лимита нет.</p>
  <div id="d-event-grid"></div>
<?php kit_close(); ?>

<?php kit_open(68, 'Плитка отзыва', 'Экран Отзывы, две колонки'); ?>
  <p class="kit-note">Одна строка: средняя и звёзды слева, справа капсула с числом отзывов. Рамка и цифра: до 2,5 ★ красно-оранжевая, до 4 ★ жёлтая, 4–5 ★ зелёная.</p>
  <div id="d-guide-grid"></div>
<?php kit_close(); ?>

<?php kit_open(38, 'Ссылка хозяин ↔ авто', 'Внутри большой карточки, не плитка из списка'); ?>
  <div id="d-rel-person">
    <button type="button" class="rel-link">
      <?php echo kit_ph_user('ИП'); ?>
      <span class="rel-link-text">
        <span class="rel-link-title">Иван Петров</span>
        <span class="rel-link-meta">@ivan_cabriolet · Минск</span>
      </span>
      <span class="rel-link-go">›</span>
    </button>
  </div>
  <div id="d-rel-car">
    <button type="button" class="rel-link">
      <?php echo kit_ph_car(); ?>
      <span class="rel-link-text">
        <span class="rel-link-title">BMW Z4</span>
        <span class="rel-link-meta">2016 · оранжевый</span>
      </span>
      <span class="rel-link-go">›</span>
    </button>
  </div>
<?php kit_close(); ?>

<?php kit_group('Большая карточка', 'Общий каркас подробного просмотра участника и автомобиля.'); ?>
<?php kit_open(39, 'Шапка большой карточки', 'Название слева, действия справа'); ?>
  <div class="sheet-card">
    <div class="sheet-head">
      <div class="sheet-head-title">Мой профиль</div>
      <div class="sheet-actions">
        <button type="button" class="btn-ghost">Изменить</button>
        <button type="button" class="modal-close">×</button>
      </div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(40, 'Шапка человека (фото + имя)', 'Большая карточка участника и Профиль'); ?>
  <div class="sheet-hero" id="d-hero">
    <?php echo kit_ph_user('ИП'); ?>
    <div class="sheet-hero-text">
      <div class="sheet-hero-name">Иван Петров</div>
      <div class="sheet-hero-meta">@ivan_cabriolet · Минск</div>
      <span class="role-badge">Участник</span>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(41, 'Поля большой карточки', 'Те же плитки, что пункты 23–26'); ?>
  <div class="sheet-grid">
    <div class="sheet-field"><span class="sheet-label">Город</span><span class="sheet-value">Минск</span></div>
    <div class="sheet-field"><span class="sheet-label">Телефон</span><span class="sheet-empty">не указано</span></div>
    <div class="sheet-field full"><span class="sheet-label">О себе</span><span class="sheet-value">Люблю открытый верх и вечерние маршруты.</span></div>
  </div>
<?php kit_close(); ?>

<?php kit_open(42, 'Карточка целиком (схема)', 'Модалка и страница Профиль — один каркас'); ?>
  <div class="sheet-card">
    <div class="sheet-head">
      <div class="sheet-head-title">BMW Z4</div>
      <div class="sheet-actions">
        <button type="button" class="btn-ghost">Изменить</button>
        <button type="button" class="modal-close">×</button>
      </div>
    </div>
    <div class="sheet-body">
      <div class="main-photo-compact">
        <?php echo kit_ph_car(); ?>
        <div class="sheet-photo-caption">
          <div class="sheet-photo-title">BMW Z4</div>
          <div class="sheet-photo-meta">2016 · оранжевый</div>
        </div>
      </div>
      <button type="button" class="rel-link">
        <?php echo kit_ph_user('ИП'); ?>
        <span class="rel-link-text">
          <span class="rel-link-title">Иван Петров</span>
          <span class="rel-link-meta">@ivan_cabriolet · Минск</span>
        </span>
        <span class="rel-link-go">›</span>
      </button>
      <div class="sheet-grid">
        <div class="sheet-field"><span class="sheet-label">Марка</span><span class="sheet-value">BMW</span></div>
        <div class="sheet-field"><span class="sheet-label">Модель</span><span class="sheet-value">Z4</span></div>
      </div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_group('Экраны и навигация', 'Главная, пустые состояния и нижнее меню приложения.'); ?>
<?php kit_open(43, 'Статистика главной', 'Стартовый экран, п. 69'); ?>
  <p class="kit-note">Два ряда по три. Компактные, без лишнего воздуха. Подпись обрезается, если не влезает.</p>
  <div class="home-stats">
    <a class="home-stat" href="#k-69"><div class="stat-value">82</div><div class="stat-label">в клубе</div></a>
    <a class="home-stat" href="#k-69"><div class="stat-value">71</div><div class="stat-label">кабриолетов</div></a>
    <a class="home-stat" href="#k-69"><div class="stat-value">12</div><div class="stat-label">встреч</div></a>
    <a class="home-stat" href="#k-69"><div class="stat-value">24</div><div class="stat-label">отзывов</div></a>
    <a class="home-stat" href="#k-69"><div class="stat-value">9</div><div class="stat-label">городов</div></a>
    <a class="home-stat" href="#k-69"><div class="stat-value">0</div><div class="stat-label">на карте</div></a>
  </div>
<?php kit_close(); ?>

<?php
  $homeCover = '../assets/img/home-cover.jpg';
  $homeCoverV = is_file(__DIR__ . '/../assets/img/home-cover.jpg') ? filemtime(__DIR__ . '/../assets/img/home-cover.jpg') : time();
  kit_open(69, 'Стартовый экран', 'frontend/index.php; вне Telegram — landing.php с тем же кадром');
?>
  <p class="kit-note">Внизу справка: две плитки — роли и разделы (п. 73 и 75).</p>
  <section class="home">
    <figure class="home-cover">
      <img src="<?php echo htmlspecialchars($homeCover . '?v=' . $homeCoverV, ENT_QUOTES); ?>" alt="" width="640" height="360">
      <span class="home-cover-veil" aria-hidden="true"></span>
      <figcaption class="home-cover-text">
        <h1 class="home-hello">Привет, Иван</h1>
        <p class="home-lead">Клуб владельцев кабриолетов</p>
      </figcaption>
    </figure>
    <div class="home-stats">
      <a class="home-stat" href="#k-69"><div class="stat-value">82</div><div class="stat-label">в клубе</div></a>
      <a class="home-stat" href="#k-69"><div class="stat-value">71</div><div class="stat-label">кабриолетов</div></a>
      <a class="home-stat" href="#k-69"><div class="stat-value">12</div><div class="stat-label">встреч</div></a>
      <a class="home-stat" href="#k-69"><div class="stat-value">24</div><div class="stat-label">отзывов</div></a>
      <a class="home-stat" href="#k-69"><div class="stat-value">9</div><div class="stat-label">городов</div></a>
      <a class="home-stat" href="#k-69"><div class="stat-value">0</div><div class="stat-label">на карте</div></a>
    </div>
    <article class="card home-club">
      <p class="home-club-kicker">про нас</p>
      <h2>Люди с поехавшей крышей</h2>
      <p class="home-club-lead">Не лента для всех, а свои: кто на чём ездит, где катаются и когда следующая встреча. Минск и вся Беларусь.</p>
      <div class="home-outs">
        <a class="home-out" href="#k-69">Сайт клуба <small>cabrioride.by</small></a>
        <a class="home-out" href="#k-69">Чат в Telegram <small>живой разговор</small></a>
      </div>
    </article>
    <div class="home-hints">
      <p class="home-hint">Своё фото в профиле — и в списке участников вас сразу узнают.</p>
      <p class="home-hint">В профиле можно добавить свой авто, если его там ещё нет.</p>
    </div>
    <?php cabrio_home_info_block(); ?>
  </section>
<?php kit_close(); ?>

<?php kit_open(70, 'Нет доступа — не в группе', 'Любой экран Mini App, роль external'); ?>
  <p class="kit-note">Человек открыл бота, но не в клубном чате. Живой экран — оверлей в app.js на любом разделе Mini App. Кто уже в чате: пользователь видит авто, события и может ответить «еду»; люди и отзывы — с роли участник.</p>
  <div class="kit-modal-shell">
    <div class="club-gate-card">
      <figure class="home-cover">
        <img src="<?php echo htmlspecialchars($homeCover . '?v=' . $homeCoverV, ENT_QUOTES); ?>" alt="" width="640" height="360">
        <span class="home-cover-veil" aria-hidden="true"></span>
      </figure>
      <div class="club-gate-body">
        <p class="home-club-kicker">клуб закрыт</p>
        <h2>Для вас доступ закрыт</h2>
        <p>Разделы открыты только участникам клубной группы. Вступите в чат и затем нажмите «Проверить снова».</p>
        <div class="club-gate-actions">
          <a class="btn-primary" href="#k-70">Вступить в чат клуба</a>
          <button class="btn-ghost" type="button">Проверить снова</button>
        </div>
      </div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(44, 'Пустой / инфо-блок', 'События, гид, нет доступа к списку'); ?>
  <section class="info-block empty-section">
    <h2>События</h2>
    <p style="margin:0">Поездки и встречи клуба появятся здесь.</p>
  </section>
<?php kit_close(); ?>

<?php kit_open(45, 'Нижнее меню', 'Все экраны клуба, кроме лендинга'); ?>
  <p class="kit-note">Как в приложении: 3D-картинки, лёгкая рамка на кнопках, активный пункт с бирюзой.</p>
  <?php kit_nav_menu(); ?>
<?php kit_close(); ?>

<?php kit_open(46, 'Название в шапке Telegram', 'Полоска между «свернуть» и «закрыть»: приложение + раздел + i'); ?>
  <div class="app-topbar" style="display:flex;position:relative;height:48px;min-height:48px;z-index:1;pointer-events:auto">
    <div class="app-topbar-inner has-section" style="height:48px;padding:0 16px">
      <span class="app-topbar-name">Cabrio<span>Ride</span></span>
      <div class="app-topbar-section-row">
        <span class="app-topbar-section">Отзывы</span>
        <span class="hint-wrap">
          <button type="button" class="app-hint-btn" aria-expanded="false" aria-label="О разделе">i</button>
          <span class="hint-pop" hidden>Карточки для отзывов: мойки, масла, средства, кафе.

Ярлыки вроде #мойка. Кнопка + — добавить карточку.</span>
        </span>
      </div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(63, 'Всплывающая подсказка i', 'Шапка раздела, галка «по приглашению»'); ?>
  <p class="kit-note">Тап по i — пузырь внутри экрана, не за край. Повторный тап или тап снаружи — закрыть. Цифры события объясняет i в шапке раздела.</p>
  <div class="kit-hint-demo" style="position:relative;min-height:80px;padding:12px;border:1px dashed var(--line);border-radius:12px">
    <span class="hint-wrap">
      <button type="button" class="app-hint-btn" aria-expanded="false" aria-label="О разделе">i</button>
      <span class="hint-pop" hidden>Пример: текст остаётся внутри экрана, даже если кнопка почти у края.</span>
    </span>
  </div>
<?php kit_close(); ?>

<?php kit_group('Карта и обратная связь', 'Контролы карты, просмотр фото, загрузка и сообщения.'); ?>
<?php kit_open(47, 'Переключатель геолокации', 'Карта: «я на карте»'); ?>
  <div class="kit-row">
    <button type="button" class="toggle-btn"><span class="toggle-text">Поделиться</span><span class="toggle-indicator"></span></button>
    <button type="button" class="toggle-btn active"><span class="toggle-time">12 мин</span><span class="toggle-indicator"></span></button>
  </div>
<?php kit_close(); ?>

<?php kit_open(48, 'Круглые кнопки карты', 'Низ экрана карты'); ?>
  <div class="kit-fab-row">
    <button type="button" class="fab fab--power" aria-pressed="true"><span class="fab-icon">⏻</span><span class="blink-dot"></span></button>
    <button type="button" class="fab fab--refresh"><span class="fab-icon">↻</span></button>
    <button type="button" class="fab" aria-pressed="false"><span class="fab-icon">➤</span></button>
  </div>
<?php kit_close(); ?>

<?php kit_open(49, 'Метка человека на карте', 'Аватар-булавка'); ?>
  <div class="kit-map-mark">
    <div class="avatar-marker fresh">
      <div class="avatar-wrap"><?php echo kit_ph_user(''); ?></div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(50, 'Кто сейчас на карте', 'Выпадающий список над картой'); ?>
  <button type="button" class="people-toggle">Сейчас на карте — 3</button>
  <div class="people-list">
    <div class="people-item">
      <div class="pi-avatar"><?php echo kit_ph_user('ИП'); ?></div>
      <div class="pi-main"><div class="pi-name">Иван Петров</div><div class="pi-username">@ivan_cabriolet</div></div>
      <div class="pi-time">2 мин</div>
    </div>
    <div class="people-item">
      <div class="pi-avatar"><?php echo kit_ph_user('АК'); ?></div>
      <div class="pi-main"><div class="pi-name">Анна К.</div><div class="pi-username">@anna</div></div>
      <div class="pi-time">8 мин</div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(51, 'Просмотр фото', 'По тапу на фото хозяина или авто. Крестик или клик по снимку закрывает'); ?>
  <div class="kit-viewer-demo">
    <div class="photo-viewer-overlay">
      <div class="photo-viewer-content">
        <button class="photo-viewer-close" type="button">×</button>
        <button class="photo-viewer-nav photo-viewer-prev" type="button">‹</button>
        <div style="width:70%;height:140px;border-radius:8px;overflow:hidden"><?php echo kit_ph_car(); ?></div>
        <button class="photo-viewer-nav photo-viewer-next" type="button">›</button>
        <div class="photo-viewer-counter">1 / 3</div>
      </div>
    </div>
  </div>
<?php kit_close(); ?>

<?php kit_open(52, 'Спиннер загрузки', 'Пока грузится фото'); ?>
  <div class="kit-row"><div class="spinner"></div><span class="kit-note">Загрузка…</span></div>
<?php kit_close(); ?>

<?php kit_open(53, 'Ошибка / тост', 'Карта: сбой геолокации, короткие сообщения'); ?>
  <div class="map-error" style="position:relative;top:auto;left:auto;right:auto">Не удалось получить геолокацию</div>
  <div class="map-toast" style="position:relative;bottom:auto;left:auto;transform:none;display:inline-block">Точка обновлена</div>
<?php kit_close(); ?>

<?php kit_group('Модалки разделов', 'Полные визуальные макеты. Сначала согласуем здесь, затем переносим в приложение.'); ?>
<?php kit_open(54, 'Каркас модалки', 'Общая схема для всех разделов'); ?>
  <p class="kit-note">Затемнение + sheet-card. Шапка: название слева, «Изменить» и × справа. Тело прокручивается.</p>
  <div id="d-modal-frame"></div>
<?php kit_close(); ?>

<?php kit_open(55, 'Участники — карточка человека', 'Экран Участники, тап по строке списка'); ?>
  <p class="kit-note">«Написать» справа от имени, открывает диалог Telegram (нужен @username). Свой профиль — без этой кнопки.</p>
  <div id="d-modal-user"></div>
<?php kit_close(); ?>

<?php kit_open(56, 'Авто — карточка машины', 'Экран Авто, тап по плитке в сетке'); ?>
  <p class="kit-note">Обложка 16:9, ссылка на хозяина, сетка полей. Эталон для car_modal.js.</p>
  <div id="d-modal-car"></div>
<?php kit_close(); ?>

<?php kit_open(57, 'Карта — открытие с метки', 'Тап по аватар-метке или списку «Сейчас на карте»'); ?>
  <p class="kit-note">Отдельной модалки для карты нет — открывается та же карточка человека или авто.</p>
  <div id="d-modal-map"></div>
<?php kit_close(); ?>

<?php kit_open(58, 'События — карточка встречи', 'Экран События, тап по событию в списке'); ?>
  <p class="kit-note">«Кто ответил»: заголовок блока, затем рубрики Едут / Думают / Не едут сильнее имён. Список столбиком, гость +1 справа.</p>
  <div id="d-modal-event"></div>
<?php kit_close(); ?>

<?php kit_open(72, 'События — правка карточки', 'Та же модалка п. 58 после «Изменить»'); ?>
  <p class="kit-note">Тот же каркас. Название, описание, дата, место, тип, лимит и «по приглашению» чуть светлее. Статус и автор как есть. Регистрация не редактируется здесь.</p>
  <div id="d-modal-event-edit"></div>
<?php kit_close(); ?>

<?php kit_open(59, 'Отзывы — карточка', 'Экран Отзывы, тап по объекту в списке'); ?>
  <p class="kit-note">Под фото: название, описание, ярлыки отдельной плашкой. Автор тонкой серой строкой. Дальше три средние, «Поставить отзыв», список отзывов.</p>
  <div id="d-modal-guide"></div>
<?php kit_close(); ?>

<?php kit_open(71, 'Отзывы — правка карточки', 'Та же модалка п. 59 после «Изменить»'); ?>
  <p class="kit-note">Тот же каркас. Название, описание и ярлыки чуть светлее и доступны для правки. Автор, средние и список отзывов не трогаем. В шапке Отмена и Сохранить.</p>
  <div id="d-modal-guide-edit"></div>
<?php kit_close(); ?>

<?php kit_open(60, 'Профиль — sheet на странице', 'Раздел Профиль в меню, без overlay'); ?>
  <p class="kit-note">Тот же контент, что п. 55, но без затемнения и без крестика — только «Изменить». Внизу списка авто — кнопка «Добавить мой авто».</p>
  <div id="d-modal-profile"></div>
<?php kit_close(); ?>

<?php kit_open(61, 'Режим редактирования', 'Любая модалка с правами: Отмена + Сохранить в шапке, кнопка «Фото»'); ?>
  <p class="kit-note">Пример на авто. Светлые поля. Кнопки в шапке рядом с крестиком.</p>
  <div id="d-modal-edit"></div>
<?php kit_close(); ?>

<?php kit_open(66, 'Режим создания', 'Кнопка + : шапка «Создание…», Отмена и Сохранить внизу'); ?>
  <p class="kit-note">Поля как в п. 61. Кнопки внизу те же компактные, что в п. 16 — не на всю ширину и не выше остальных.</p>
  <div id="d-modal-create"></div>
<?php kit_close(); ?>

<?php kit_open(64, 'События — создание', 'Кнопка + на экране События'); ?>
  <p class="kit-note">Те же поля, что в правке п. 72: название, описание, дата, время, город, место, тип, лимит, по приглашению. Без автора, статуса и регистрации. Кнопки внизу.</p>
  <div id="d-modal-event-create"></div>
<?php kit_close(); ?>

<?php kit_open(65, 'Отзывы — добавление', 'Кнопка + на экране Отзывы'); ?>
  <p class="kit-note">Порядок как в карточке: название, описание, ярлыки. Кнопки внизу, как п. 66.</p>
  <div id="d-modal-guide-create"></div>
<?php kit_close(); ?>

<?php kit_open(73, 'Справка — роли и доступы', 'Главная, плитка в блоке «Как устроен клуб»'); ?>
  <p class="kit-note">Имя роли — заголовок карточки. Внутри три тихих блока: описание, как получить, доступ. Левая линия — ступеньки.</p>
  <div class="kit-modal-shell"><?php cabrio_club_info_sheet('roles'); ?></div>
<?php kit_close(); ?>

<?php kit_open(75, 'Справка — разделы', 'Главная, плитка «Разделы»'); ?>
  <p class="kit-note">Главная и нижнее меню. В каждой карточке: для чего, что можно делать, кто имеет доступ. Без лестницы — это не ступени роли.</p>
  <div class="kit-modal-shell"><?php cabrio_club_info_sheet('sections'); ?></div>
<?php kit_close(); ?>

<?php kit_open(74, 'Справка — доступы (снято)', 'Больше не отдельная модалка'); ?>
  <p class="kit-note">Отдельную плитку и окно убрали: всё вшито в п. 73, чтобы не дублировать.</p>
<?php kit_close(); ?>

        </main>
      </div>
    </div>
    <?php cabrio_club_info_store(); ?>
    <script type="module" src="../assets/js/ui_kit_demo.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/ui_kit_demo.js'); ?>"></script>
  </body>
</html>
