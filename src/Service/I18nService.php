<?php

namespace AkyosUpdates\Service;

class I18nService
{
    public function loadPluginTextdomain(): void
    {
        load_plugin_textdomain(
            'akyos-updates',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}