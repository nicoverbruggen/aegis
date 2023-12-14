<?php declare(strict_types=1);

namespace Aegis;

function config($key): string {
    $value = getenv($key);

    if (empty($value)) {
        throw new \Exception("The environment variable `{$key}` is empty.");
    }

    return $value;
};