<?php

namespace Ftech\AzoPay\Support;

/**
 * Static lookup table of Vietnamese banks supported by AzoPay / VietQR.
 *
 * Keyed by AzoPay bank code, with the napas BIN and display names. Used as a
 * fallback when the API does not echo back full bank metadata.
 */
class Banks
{
    /**
     * @var array<string, array{bin: string, short_name: string, full_name: string}>
     */
    public const MAP = [
        'VCB'    => ['bin' => '970436', 'short_name' => 'Vietcombank',  'full_name' => 'Ngân hàng TMCP Ngoại Thương Việt Nam'],
        'VPB'    => ['bin' => '970432', 'short_name' => 'VPBank',       'full_name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng'],
        'ACB'    => ['bin' => '970416', 'short_name' => 'ACB',          'full_name' => 'Ngân hàng TMCP Á Châu'],
        'STB'    => ['bin' => '970403', 'short_name' => 'Sacombank',    'full_name' => 'Ngân hàng TMCP Sài Gòn Thương Tín'],
        'HDB'    => ['bin' => '970437', 'short_name' => 'HDBank',       'full_name' => 'Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh'],
        'ICB'    => ['bin' => '970415', 'short_name' => 'VietinBank',   'full_name' => 'Ngân hàng TMCP Công thương Việt Nam'],
        'TCB'    => ['bin' => '970407', 'short_name' => 'Techcombank',  'full_name' => 'Ngân hàng TMCP Kỹ thương Việt Nam'],
        'MB'     => ['bin' => '970422', 'short_name' => 'MBBank',       'full_name' => 'Ngân hàng TMCP Quân đội'],
        'BIDV'   => ['bin' => '970418', 'short_name' => 'BIDV',         'full_name' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam'],
        'MSB'    => ['bin' => '970426', 'short_name' => 'MSB',          'full_name' => 'Ngân hàng TMCP Hàng Hải Việt Nam'],
        'SHBVN'  => ['bin' => '970424', 'short_name' => 'ShinhanBank',  'full_name' => 'Ngân hàng TNHH MTV Shinhan Việt Nam'],
        'TPB'    => ['bin' => '970423', 'short_name' => 'TPBank',       'full_name' => 'Ngân hàng TMCP Tiên Phong'],
        'EIB'    => ['bin' => '970431', 'short_name' => 'Eximbank',     'full_name' => 'Ngân hàng TMCP Xuất Nhập khẩu Việt Nam'],
        'VIB'    => ['bin' => '970441', 'short_name' => 'VIB',          'full_name' => 'Ngân hàng TMCP Quốc tế Việt Nam'],
        'VBA'    => ['bin' => '970405', 'short_name' => 'Agribank',     'full_name' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam'],
        'PBVN'   => ['bin' => '970439', 'short_name' => 'PublicBank',   'full_name' => 'Ngân hàng TNHH MTV Public Việt Nam'],
        'KLB'    => ['bin' => '970452', 'short_name' => 'KienLongBank', 'full_name' => 'Ngân hàng TMCP Kiên Long'],
        'OCB'    => ['bin' => '970448', 'short_name' => 'OCB',          'full_name' => 'Ngân hàng TMCP Phương Đông'],
        'ABBANK' => ['bin' => '970425', 'short_name' => 'ABBANK',       'full_name' => 'Ngân hàng TMCP An Bình'],
    ];

    /**
     * Resolve a bank by its AzoPay code, BIN or (case-insensitive) short name.
     *
     * @return array{bin: string, short_name: string, full_name: string}|null
     */
    public static function find(string $identifier): ?array
    {
        $upper = strtoupper($identifier);
        if (isset(self::MAP[$upper])) {
            return self::MAP[$upper];
        }

        foreach (self::MAP as $bank) {
            if ($bank['bin'] === $identifier
                || strcasecmp($bank['short_name'], $identifier) === 0) {
                return $bank;
            }
        }

        return null;
    }

    /**
     * Banks that prepend "SEVQR " to the transfer remark (VietinBank, ABBANK).
     */
    public static function requiresSevqrPrefix(?string $bin): bool
    {
        return in_array($bin, ['970415', '970425'], true);
    }
}
