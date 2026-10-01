<?php

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    return 'The ToDo module requires PHP 8.2 or newer.';
}

return null;
