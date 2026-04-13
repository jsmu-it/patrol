<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully";
} else {
    echo "OPcache not available";
}
// Delete this file after use
unlink(__FILE__);
