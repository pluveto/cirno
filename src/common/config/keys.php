<?php
/**
 * Get your key here: https://travistidwell.com/jsencrypt/demo/
 */
$privateKey = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC9ooBhxteZSgVqXXWHRCoh9AxbmLxv4hnFx8FFCyQcm2INK73e
Jyd2d/+FuQVsEYJEEtsMjcAJuOSdYlBeiUiEXOMqNZN/WYMkcSlG/rpXWhM0CPkz
+q1N+zCNmNgXr8uHU/iPfrWxKQswpQn6Izi3JtSU8d8NPCTHHqMDMpbOtQIDAQAB
AoGBALoayX2rxBjnAQuS7u2yItsxhBM8gOoYbgyg7nbQ6T6962tbmzlzGZJ1T6qv
mrelMz+Vda4LSwT/lDU0sAUnG6dCXf1rD0rMrKI57zsH9uDiNETNRcPd2YdcUQ5b
zJg5u96ejBBfPA+zA8BtMQz+PS7Jp8MCxNwnOtKE5a5MLPhBAkEA4ajWkeNeDHCR
V0kOvfZ3GFaOI6dFVd/YsEYMun95yMyNnlC53D149EbeS9iaauoj1A2Jugml0fYC
eXuZ12RSpQJBANchspO0T1ha3W05ph++u+ymVaOehC04poBjtlsvGzdgomFm9c1X
8JCN7/Gs+hkfaLP+aTHKw9UpMVkLzrgAHtECQQDW8paAnI1HWCp5dDPAqvZCjqAS
QhD/fZvQPMl/FFIS/RWgydBgg4WlWaQBpy9fy+uY39RjCzGST72Hrj6aNwupAkAq
Lm9HSslr95UG/5C1FL7gLdUI2eHsw/jRn7t3sYrSlt3/3yI++wkuLsZnSpVXK7Np
525U1v8C4qrMXOZRaaShAkBLh+uQTaWNEa8gcrcY5lbX2c43iu0DJxC1BlA174No
sam+m7AgvNbrB1RgemKJZhOv9T2xziC167R4BxHvOqnp
-----END RSA PRIVATE KEY-----
EOD;

$publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC9ooBhxteZSgVqXXWHRCoh9Axb
mLxv4hnFx8FFCyQcm2INK73eJyd2d/+FuQVsEYJEEtsMjcAJuOSdYlBeiUiEXOMq
NZN/WYMkcSlG/rpXWhM0CPkz+q1N+zCNmNgXr8uHU/iPfrWxKQswpQn6Izi3JtSU
8d8NPCTHHqMDMpbOtQIDAQAB
-----END PUBLIC KEY-----
EOD;

return [
    "privateKey"=> $privateKey,
    "publicKey"=> $publicKey
];