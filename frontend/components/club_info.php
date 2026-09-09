<?php
/**
 * Справка на главной: плитки «Роли и доступы» и «Разделы».
 * На экране — русские названия. В API по-прежнему guest / user / member.
 */

/** Карточка внизу стартового экрана */
function cabrio_home_info_block(): void
{
    ?>
        <article class="card home-club home-info">
          <p class="home-club-kicker">справка</p>
          <h2>Как устроен клуб</h2>
          <p class="home-club-lead">Роли людей и что умеет каждый раздел приложения.</p>
          <div class="home-outs">
            <button type="button" class="home-out" data-open-club-info="roles">
              Роли и доступы
              <small>какие есть и как получить</small>
            </button>
            <button type="button" class="home-out" data-open-club-info="sections">
              Разделы
              <small>для чего и кто видит</small>
            </button>
          </div>
        </article>
    <?php
}

/** Скрытые тексты модалок — JS подставляет в окно */
function cabrio_club_info_store(): void
{
    ?>
    <div id="clubInfoStore" hidden>
      <div data-club-info="roles"><?php cabrio_club_info_roles_inner(); ?></div>
      <div data-club-info="sections"><?php cabrio_club_info_sections_inner(); ?></div>
    </div>
    <?php
}

/** Каркас модалки для UI Kit */
function cabrio_club_info_sheet(string $kind = 'roles'): void
{
    $title = $kind === 'sections' ? 'Разделы' : 'Роли и доступы';
    ?>
    <div class="modal-content modal-compact sheet-card">
      <div class="modal-header">
        <div class="modal-title"><?php echo htmlspecialchars($title, ENT_QUOTES); ?></div>
        <div class="sheet-actions"><button type="button" class="modal-close">×</button></div>
      </div>
      <div class="modal-body">
        <?php
        if ($kind === 'sections') {
            cabrio_club_info_sections_inner();
        } else {
            cabrio_club_info_roles_inner();
        }
        ?>
      </div>
    </div>
    <?php
}

/**
 * Карточка справки. $rows: [модификатор класса, ярлык, текст]
 */
function cabrio_club_info_card(int $step, string $name, array $rows, string $mod = ''): void
{
    $n = str_pad((string)$step, 2, '0', STR_PAD_LEFT);
    $cls = 'club-info-role' . ($mod !== '' ? ' ' . $mod : '');
    ?>
      <article class="<?php echo htmlspecialchars($cls, ENT_QUOTES); ?>">
        <header class="club-info-role-head">
          <span class="club-info-step"><?php echo htmlspecialchars($n, ENT_QUOTES); ?></span>
          <h3 class="club-info-name"><?php echo htmlspecialchars($name, ENT_QUOTES); ?></h3>
        </header>
        <div class="club-info-rows">
          <?php foreach ($rows as $row): ?>
          <div class="club-info-row<?php echo $row[0] !== '' ? ' ' . htmlspecialchars($row[0], ENT_QUOTES) : ''; ?>">
            <p class="club-info-k"><?php echo htmlspecialchars($row[1], ENT_QUOTES); ?></p>
            <p class="club-info-v"><?php echo htmlspecialchars($row[2], ENT_QUOTES); ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </article>
    <?php
}

function cabrio_club_info_role_card(int $step, string $name, string $desc, string $how, string $access): void
{
    cabrio_club_info_card($step, $name, [
        ['', 'Описание', $desc],
        ['club-info-row--how', 'Как получить', $how],
        ['club-info-row--access', 'Доступ', $access],
    ]);
}

function cabrio_club_info_roles_inner(): void
{
    ?>
    <div class="club-info">
      <p class="club-info-lead">Одна роль на человека. Ступеньки: чат → анкета → личная встреча. Сам себя повысить или понизить нельзя.</p>
      <div class="club-info-ladder">
      <?php
      cabrio_club_info_role_card(
          1,
          'Внешний',
          'Человека нет в клубном чате.',
          'Ещё не вступил или вышел из чата. Дальше — вступить в чат клуба.',
          'Приложение закрыто. Только приглашение в чат.'
      );
      cabrio_club_info_role_card(
          2,
          'Гость',
          'Уже в чате, анкету ещё не заполнил.',
          'Вступить в клубный чат. Затем заполнить профиль и добавить авто (или отметить, что пока без авто).',
          'Главная и свой профиль: анкета, фото, своё авто. Списки людей, машин, встреч, отзывов и карта закрыты.'
      );
      cabrio_club_info_role_card(
          3,
          'Пользователь',
          'Анкета есть. Личной встречи ещё не было — можно приехать познакомиться.',
          'В профиле заполнить себя и добавить кабриолет (или указать, что без авто). Модератор на этой ступени не нужен.',
          'Авто и встречи клуба. Можно ответить «еду» / «думаю». Создать встречу, люди и отзывы — ещё нет. На карте можно показать себя; кто рядом — с роли участник.'
      );
      cabrio_club_info_role_card(
          4,
          'Участник',
          'Полноправный член клуба после личного знакомства.',
          'Приехать на встречу. Роль ставит модератор или администратор.',
          'Люди, отзывы, кто на карте. Создать встречу, ответить «еду», оставить отзыв.'
      );
      cabrio_club_info_role_card(
          5,
          'Модератор',
          'Как участник, плюс помогает вести клуб.',
          'Назначает только администратор через карточку человека. Не за заявку в чате.',
          'Подтвердить человека после встречи, править чужие карточки, удалять карточки отзывов.'
      );
      cabrio_club_info_role_card(
          6,
          'Администратор',
          'Полное управление клубом.',
          'Не через приложение. Назначается отдельно, вручную.',
          'Всё как у модератора, в том числе назначить модератора.'
      );
      ?>
      </div>
    </div>
    <?php
}

function cabrio_club_info_section_card(int $step, string $name, string $why, string $do, string $who): void
{
    cabrio_club_info_card($step, $name, [
        ['', 'Для чего', $why],
        ['club-info-row--how', 'Что можно делать', $do],
        ['club-info-row--access', 'Кто имеет доступ', $who],
    ], 'club-info-role--nav');
}

function cabrio_club_info_sections_inner(): void
{
    ?>
    <div class="club-info">
      <p class="club-info-lead">Главная и пункты нижнего меню. Если раздела нет на экране — ваша роль его ещё не открывает.</p>
      <div class="club-info-list">
      <?php
      cabrio_club_info_section_card(
          1,
          'Главная',
          'Лицо клуба: кто мы и сколько своих.',
          'Цифры ведут в разделы. Справка внизу — роли и эти разделы. Ссылки на сайт клуба и чат в Telegram.',
          'С роли гость. Внешний видит только приглашение вступить в чат.'
      );
      cabrio_club_info_section_card(
          2,
          'Участники',
          'Люди клуба: имя, роль, кабриолеты.',
          'Список, поиск, фильтр по роли. Тап по строке — карточка человека, по мини-авто — карточка машины. Если есть @ник — можно написать в Telegram.',
          'Смотреть список — с роли участник. Править чужую карточку — модератор и администратор.'
      );
      cabrio_club_info_section_card(
          3,
          'Авто',
          'Кабриолеты клуба — кто на чём ездит.',
          'Сетка машин, тап — карточка: фото, поля, ссылка на хозяина. Своё авто добавляют в профиле; сначала оно на модерации. Госномер можно скрыть — его видите вы и модераторы.',
          'Смотреть список — с роли пользователь. Добавить своё — с роли гость, в профиле. Хозяина в карточке видят с роли участник.'
      );
      cabrio_club_info_section_card(
          4,
          'Карта',
          'Кто из своих сейчас рядом.',
          'Живые точки тех, кто делится геолокацией. Кнопка ⏻ — показать или скрыть себя. Тап по метке или строке — карточка человека. Карту можно открыть и без GPS.',
          'Поставить себя на карту — с роли пользователь. Список «кто сейчас на карте» — с роли участник.'
      );
      cabrio_club_info_section_card(
          5,
          'События',
          'Поездки и встречи: куда приехать познакомиться.',
          'Список плиток и карточка: когда, где, сколько едут и думают. Ответ «еду» / «думаю», можно взять +1. Кнопка + — создать встречу. Свою встречу правит организатор.',
          'Смотреть и ответить «еду» — с роли пользователь. Имена кто едет и кнопка + — с роли участник.'
      );
      cabrio_club_info_section_card(
          6,
          'Отзывы',
          'Свои места клуба: мойки, масла, кафе, сервисы.',
          'Плитки с общей оценкой. В карточке — описание, ярлыки вроде #мойка, средние по качеству, скорости и цене, свой отзыв. Кнопка + — новая карточка места.',
          'Смотреть, писать отзыв и добавить карточку — с роли участник. Удалить карточку места — модератор.'
      );
      cabrio_club_info_section_card(
          7,
          'Профиль',
          'Ваша карточка в клубе.',
          'Фото, анкета, свои авто. «Изменить» — правка полей. «Добавить мой авто» — новая машина, сначала на модерации.',
          'Свой профиль — с роли гость. Чужой смотрят в разделе «Участники».'
      );
      ?>
      </div>
    </div>
    <?php
}
