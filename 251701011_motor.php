<?php
declare(strict_types=1);

function adim(array $s, string $a): array {
    if ($s['bitti']) return $s;

    $x = $s['no'] % 10;
    $y = intdiv($s['no'], 10) % 10;

    if ($a === 'M') {
        if ($s['gunluk_musteri'] >= 5) return $s;

        $s['musteri_sira']++;
        $s['gunluk_musteri']++;

        $m = musteri($s['no'], $s['musteri_sira']);
        $uKod = $m['urun'];
        $urun = &$s['urunler'][$uKod];

        $elektrikGerektirir = in_array($uKod, ['tost', 'hamburger', 'kahve']);
        $elektrikEngeli = ($s['elektrik'] > 0 && $elektrikGerektirir);

        $adet = $s['yogunluk'] ? 2 : 1;
        $s['yogunluk'] = false;

        $birimFiyat = $urun['fiyat'];
        if ($m['tip'] === 'Burslu Öğrenci') {
            $birimFiyat = (int)round($birimFiyat * 0.9);
        }
        $toplamTutar = $birimFiyat * $adet;

        if (!$elektrikEngeli && $urun['stok'] >= $adet && $m['butce'] >= $toplamTutar) {
            $urun['stok'] -= $adet;
            $s['kasa'] += $toplamTutar;
            $s['seri']++;
            $s['ret_sayac'][$uKod] = 0;
            $s['memnuniyet'] = min(100, $s['memnuniyet'] + 2);

            if (!$s['zam'][$uKod] && in_array($uKod, ['tost', 'ayran', 'su'])) {
                $urun['fiyat'] += 5;
                $s['zam'][$uKod] = true;
            }

            $s['log'][] = "{$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urun['ad']} istiyor → Satış: {$adet} adet";
        } else {
            $s['seri'] = 0;
            $s['gunluk_ret']++;
            $s['ret_sayac'][$uKod]++;
            $s['memnuniyet'] = max(0, $s['memnuniyet'] - 3);

            if ($s['ret_sayac'][$uKod] === 2 && $urun['fiyat'] >= 80) {
                $urun['fiyat'] = (int)round($urun['fiyat'] * 0.9);
                $s['zam'][$uKod] = true;
            }

            $s['log'][] = "{$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urun['ad']} istiyor → Satış yok";
        }
    }

    return $s;
}
