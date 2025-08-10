<?php
function render_meta(string $title = 'CabrioRide') {
  echo '<meta charset="UTF-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">';
  echo '<meta name="format-detection" content="telephone=no">';
  echo '<meta name="theme-color" content="#1a1a1a">';
  echo '<title>' . htmlspecialchars($title) . '</title>';
}

