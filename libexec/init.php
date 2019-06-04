<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

require_once dirname(__DIR__).'/vendor/autoload.php';
$baseDir = dirname(__DIR__);

use fkooman\Jwt\Keys\EdDSA\SecretKey;
use LC\Portal\CA\EasyRsaCa;
use LC\Portal\FileIO;
use LC\Portal\OpenVpn\TlsCrypt;
use LC\Portal\Storage;

try {
    $dataDir = sprintf('%s/data', $baseDir);
    FileIO::createDir($dataDir);

    // ca
    $easyRsaDir = sprintf('%s/easy-rsa', $baseDir);
    $easyRsaDataDir = sprintf('%s/data/easy-rsa', $baseDir);
    $ca = new EasyRsaCa($easyRsaDir, $easyRsaDataDir);
    $ca->init();

    // database
    $dataDir = sprintf('%s/data', $baseDir);
    $storage = new Storage(
        new PDO(sprintf('sqlite://%s/db.sqlite', $dataDir)),
        sprintf('%s/schema', $baseDir)
    );
    $storage->init();

    // tls-crypt
    $tlsCryptFile = sprintf('%s/tls-crypt.key', $dataDir);
    if (!FileIO::exists($tlsCryptFile)) {
        $tlsCrypt = TlsCrypt::generate();
        FileIO::writeFile($tlsCryptFile, $tlsCrypt->raw(), 0640);
    }

    // OAuth Key
    $oauthKeyFile = sprintf('%s/oauth.key', $dataDir);
    if (!FileIO::exists($oauthKeyFile)) {
        $secretKey = SecretKey::generate();
        FileIO::writeFile($oauthKeyFile, $secretKey->encode(), 0640);
    }
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
