<?php

declare(strict_types=1);

$envDir = dirname(__DIR__);
$envPath = $envDir . DIRECTORY_SEPARATOR . '.env';

function envv(string $k, string $default = '', string $sanitize = 'raw'): string
{
    $v = getenv($k);
    if ($v === false || $v === '') {
        $v = $default;
    }
    $v = (string) $v;

    switch ($sanitize) {
        case 'url':
            $v = preg_replace("/[`\x00-\x1F\x7F]/u", '', $v);
            $v = trim($v);
            if (strlen($v) >= 2) {
                $first = $v[0];
                $last  = $v[strlen($v) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $v = substr($v, 1, -1);
                }
            }
            $v = trim($v);
            break;

        case 'host':
            $v = preg_replace("/[`\x00-\x1F\x7F]/u", '', $v);
            $v = preg_replace("/\s+/", '', $v);
            $v = trim($v, "\"'");
            break;

        case 'name':
            $v = preg_replace("/[`\x00-\x1F\x7F]/u", '', $v);
            $v = trim($v);
            if (strlen($v) >= 2) {
                $first = $v[0];
                $last  = $v[strlen($v) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $v = substr($v, 1, -1);
                }
            }
            $v = trim($v);
            break;

        case 'key':
            if (preg_match('/^\s*`(.*)`\s*$/s', $v, $m)) {
                $v = $m[1];
            } else {
                $v = trim($v);
                if (strlen($v) >= 2) {
                    $first = $v[0];
                    $last  = $v[strlen($v) - 1];
                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $v = substr($v, 1, -1);
                    }
                }
            }
            break;

        case 'raw':
        default:
            break;
    }

    return (string) $v;
}

function fallbackEnv(string ...$candidates): string
{
    foreach ($candidates as $c) {
        $v = getenv($c);
        if (is_string($v) && $v !== '') {
            return $v;
        }
    }
    return '';
}

$fb = '';
$fbJson = $envDir . '/storage/firebase-auth.json';
if (file_exists($fbJson)) {
    $fb = $fbJson;
}
$fbFromEnv = envv('FIREBASE_CREDENTIALS_PATH', '', 'raw');
if ($fbFromEnv !== '') {
    $fb = $fbFromEnv;
}

$env = [
    'APP_NAME'             => envv('APP_NAME',           'Amiga Gracia', 'name'),
    'APP_ENV'              => envv('APP_ENV',            'production'),
    'APP_DEBUG'            => envv('APP_DEBUG',          'true'),
    'APP_KEY'              => envv('APP_KEY',            '',           'key'),
    'APP_URL'              => envv('APP_URL',            'https://amiga-travel-production.up.railway.app', 'url'),
    'APP_LOCALE'           => envv('APP_LOCALE',         'en'),
    'APP_FALLBACK_LOCALE'  => envv('APP_FALLBACK_LOCALE','en'),
    'APP_FAKER_LOCALE'     => envv('APP_FAKER_LOCALE',   'en_US'),
    'APP_MAINTENANCE_DRIVER' => envv('APP_MAINTENANCE_DRIVER', 'file'),

    'BCRYPT_ROUNDS' => '12',
    'LOG_CHANNEL'   => envv('LOG_CHANNEL', 'stack'),
    'LOG_LEVEL'     => envv('LOG_LEVEL',   'debug'),

    'DB_CONNECTION' => envv('DB_CONNECTION', 'mysql'),
    'DB_HOST'       => envv('DB_HOST',       fallbackEnv('MYSQLHOST', 'MYSQL_HOST'), 'host'),
    'DB_PORT'       => envv('DB_PORT',       fallbackEnv('MYSQLPORT', 'MYSQL_PORT'), 'host'),
    'DB_DATABASE'   => envv('DB_DATABASE',   fallbackEnv('MYSQLDATABASE', 'MYSQL_DATABASE'), 'host'),
    'DB_USERNAME'   => envv('DB_USERNAME',   fallbackEnv('MYSQLUSER', 'MYSQL_USER'), 'key'),
    'DB_PASSWORD'   => envv('DB_PASSWORD',   fallbackEnv('MYSQLPASSWORD', 'MYSQL_ROOT_PASSWORD'), 'key'),

    'SESSION_DRIVER'   => envv('SESSION_DRIVER',   'database'),
    'CACHE_STORE'      => envv('CACHE_STORE',      'database'),
    'QUEUE_CONNECTION' => envv('QUEUE_CONNECTION', 'database'),

    'MAIL_MAILER'       => envv('MAIL_MAILER',       'smtp'),
    'MAIL_HOST'         => envv('MAIL_HOST',         'smtp.gmail.com', 'host'),
    'MAIL_PORT'         => envv('MAIL_PORT',         '587', 'host'),
    'MAIL_USERNAME'     => envv('MAIL_USERNAME',     '',   'key'),
    'MAIL_PASSWORD'     => envv('MAIL_PASSWORD',     '',   'key'),
    'MAIL_ENCRYPTION'   => envv('MAIL_ENCRYPTION',   'tls'),
    'MAIL_FROM_ADDRESS' => envv('MAIL_FROM_ADDRESS', '',   'key'),
    'RESEND_API_KEY'    => envv('RESEND_API_KEY',    '',   'key'),

    'NOCAPTCHA_SITEKEY'    => envv('NOCAPTCHA_SITEKEY',   '', 'key'),
    'NOCAPTCHA_SECRET'     => envv('NOCAPTCHA_SECRET',    '', 'key'),
    'FIREBASE_CREDENTIALS' => $fb,
    'MAIL_FROM_NAME'       => envv('MAIL_FROM_NAME',      '', 'name'),
    'MAIL_SCHEME'          => envv('MAIL_SCHEME',         '', 'raw'),
    'SENDGRID_API_KEY'     => envv('SENDGRID_API_KEY',    '', 'key'),

    'FILESYSTEM_DISK'      => envv('FILESYSTEM_DISK',     'local'),
    'BROADCAST_CONNECTION' => envv('BROADCAST_CONNECTION','log'),
];

$out = '';
foreach ($env as $k => $v) {
    $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $v);
    $out .= $k . '="' . $escaped . '"' . "\n";
}

file_put_contents($envPath, $out);

echo '[write_env.php] Wrote ' . count($env) . " vars to $envPath\n";
echo '[write_env.php] APP_KEY length: ' . strlen($env['APP_KEY']) . "\n";
echo '[write_env.php] APP_URL: [' . $env['APP_URL'] . "]\n";
echo '[write_env.php] DB_HOST: [' . $env['DB_HOST'] . "]\n";
echo '[write_env.php] MAIL_FROM_NAME: [' . $env['MAIL_FROM_NAME'] . "]\n";
