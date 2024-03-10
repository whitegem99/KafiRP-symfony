<?php declare(strict_types=1);

namespace App\Helper;

class AccessHelper
{
    private const ROLE_ADMIN_TEXT = 'Sistem Yöneticisi';
    private const ROLE_USER_TEXT = 'Kullanıcı';
    private const ROLE_WL_READER = 'Başvuru Değerlendirmeni';
    private const ROLE_SUPPORT = 'Destek Ekibi';

    public function map(array $accessList): string
    {
        $accessText = [];
        foreach ($accessList as $access) {
            switch ($access) {
                case 'ROLE_ADMIN':
                    $accessText[] = self::ROLE_ADMIN_TEXT;
                    break;
                case 'ROLE_USER':
                    break;
                case 'ROLE_WL_READER':
                    $accessText[] = self::ROLE_WL_READER;
                    break;
                case 'ROLE_SUPPORT':
                    $accessText[] = self::ROLE_SUPPORT;
                    break;
            }
        }
        return implode(', ', $accessText);
    }

    public function getFormList(): array
    {
        return [
            self::ROLE_ADMIN_TEXT => 'ROLE_ADMIN',
            self::ROLE_WL_READER => 'ROLE_WL_READER',
            self::ROLE_SUPPORT => 'ROLE_SUPPORT',
        ];
    }
}