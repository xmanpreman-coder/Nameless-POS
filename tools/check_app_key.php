<?php
$root = __DIR__ . '/../';
chdir($root);
function checkEnv($path) {
    if (!is_file($path)) return null;
    $env = trim(file_get_contents($path));
    if (preg_match('/^APP_KEY=(.*)/m', $env, $m)) {
        $val = trim($m[1]);
        if (strpos($val, 'base64:') === 0) {
            $b = substr($val, 7);
            $d = base64_decode($b);
            return [
                'key' => $val,
                'base64' => true,
                'decoded_len' => strlen($d)
            ];
        }
        return ['key' => $val, 'base64' => false];
    }
    return null;
}
$prod = checkEnv(__DIR__ . '/../.env.production');
$dev = checkEnv(__DIR__ . '/../.env');
echo "-- .env.production --\n";
if ($prod === null) echo "PROD_ENV_NOT_FOUND\n";
else print_r($prod);

echo "\n-- .env --\n";
if ($dev === null) echo "DEV_ENV_APPKEY_NOT_FOUND\n";
else print_r($dev);
