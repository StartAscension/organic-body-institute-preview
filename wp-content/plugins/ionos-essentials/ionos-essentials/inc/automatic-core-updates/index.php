<?php

namespace ionos\essentials\automatic_updates;

defined('ABSPATH') || exit();

\add_action(
  'admin_head-update-core.php',
  function (): void {
    \printf(
      <<<HTML
      <style>
        .auto-update-status{
          display: none;
        }
      </style>
      HTML
    );
  }
);
