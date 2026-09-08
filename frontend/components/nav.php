<?php
require_once __DIR__ . '/nav_icons.php';
?>
<nav class="bottom-nav" aria-label="Разделы клуба">
  <!-- Люди клуба -->
  <a class="nav-item" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/users.php'), ENT_QUOTES); ?>">
    <span class="nav-icon"><?php echo cabrio_nav_pic('people'); ?></span>
    <span class="nav-label">Участники</span>
  </a>
  <!-- Машины: кабриолеты -->
  <a class="nav-item" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/cars.php'), ENT_QUOTES); ?>">
    <span class="nav-icon"><?php echo cabrio_nav_pic('car'); ?></span>
    <span class="nav-label">Авто</span>
  </a>
  <!-- Карта «видеть своих» -->
  <a class="nav-item" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/map.php'), ENT_QUOTES); ?>">
    <span class="nav-icon"><?php echo cabrio_nav_pic('map'); ?></span>
    <span class="nav-label">Карта</span>
  </a>
  <!-- Поездки и встречи -->
  <a class="nav-item" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/events.php'), ENT_QUOTES); ?>">
    <span class="nav-icon"><?php echo cabrio_nav_pic('events'); ?></span>
    <span class="nav-label">События</span>
  </a>
  <!-- Отзывы: мойки, масла, сервисы -->
  <a class="nav-item" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/services.php'), ENT_QUOTES); ?>">
    <span class="nav-icon"><?php echo cabrio_nav_pic('guide'); ?></span>
    <span class="nav-label">Отзывы</span>
  </a>
  <!-- Свой профиль: своё фото, если есть, иначе картинка-заглушка -->
  <a class="nav-item" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/me.php'), ENT_QUOTES); ?>">
    <span class="nav-icon">
      <span class="nav-avatar-wrap" style="display:none">
        <img id="navProfileAvatar" class="nav-avatar-img" alt="" />
      </span>
      <span id="navProfileEmoji" aria-hidden="true">
        <?php echo cabrio_nav_pic('profile'); ?>
      </span>
    </span>
    <span class="nav-label">Профиль</span>
  </a>
</nav>
