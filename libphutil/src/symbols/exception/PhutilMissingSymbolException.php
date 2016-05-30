<?php

final class PhutilMissingSymbolException extends Exception {

  public function __construct($symbol, $type, $reason) {
    parent::__construct(
      pht(
        "Failed to load %s '%s': %s\n\n".
        "If you are not a developer, this almost always means that a library ".
        "is out of date. For example, you may have upgraded `phabricator` ".
        "without upgrading `libphutil`, or vice versa. It might also mean ".
        "that you need to restart Apache or PHP-FPM. Make sure all libraries ".
        "are up to date and all services have been restarted.\n\n".
        "If you are a developer and this symbol was recently added or moved, ".
        "your library map may need to be rebuilt. You can rebuild the map by ".
        "running '%s'. For more information, see:\n\n".
        "%s",
        $type,
        $symbol,
        $reason,
        'arc liberate',
        'https://secure.phabricator.com/book/phabcontrib/article/'.
          'adding_new_classes/'));
  }

}
