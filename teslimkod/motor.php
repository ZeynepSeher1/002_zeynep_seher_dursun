<?php
declare(strict_types=1);

function adim(array $s, string $a): array {
    if ($s['bitti']) return $s;

    $x = $s['no'] % 10;
    $y = intdiv($s['no'], 10) % 10;

    // 1. MÜŞTERİ HAMLESİ ('M')
    if ($a === 'M') {
        if ($s['gunluk_musteri'] >= 5) return $s;

        $s['musteri_sira']++;
        $s['gunluk_musteri']++;

        $m = musteri($s['no'], $s['musteri_sira']);
        $uKod = $m['urun'];
        $urun = &$s['urunler'][$uKod];

        // Elektrik kesintisinde sıcak/elektrikli ürünler satılamaz
        $elektrikGerektirir = in_array($uKod, ['tost', 'hamburger', 'kahve']);
        $elektrikEngeli = ($s['elektrik'] > 0 && $elektrikGerektirir);

        // Öğle yoğunluğu varsa müşteri 2 adet istiyor
        $adet = $s['yogunluk'] ? 2 : 1;
        $s['yogunluk'] = false;

        // Birim fiyat ve Burslu Öğrenci %10 indirimi
        $birimFiyat = $urun['fiyat'];
        if ($m['tip'] === 'Burslu Öğrenci') {
            $birimFiyat = (int)round($birimFiyat * 0.9);
        }
        $toplamTutar = $birimFiyat * $adet;

        // Satış Kontrolü
        if (!$elektrikEngeli && $urun['stok'] >= $adet && $m['butce'] >= $toplamTutar) {
            $urun['stok'] -= $adet;
            $s['kasa'] += $toplamTutar;
            $s['seri']++;
            $s['ret_sayac'][$uKod] = 0;
            $s['memnuniyet'] = min(100, $s['memnuniyet'] + 2);

            // Çok satılan ürünlere +5 TL zam (Tost 65, Ayran 25, Su 15)
            if (!$s['zam'][$uKod] && in_array($uKod, ['tost', 'ayran', 'su'])) {
                $urun['fiyat'] += 5;
                $s['zam'][$uKod] = true;
            }

            $s['log'][] = "{$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urun['ad']} istiyor → Satış: {$adet} adet";
        } else {
            // Satış Yok (Ret)
            $s['seri'] = 0;
            $s['gunluk_ret']++;
            $s['ret_sayac'][$uKod]++;
            $s['memnuniyet'] = max(0, $s['memnuniyet'] - 3);

            // Hamburger gibi üründe 2. rette %10 fiyat indirimi
            if ($s['ret_sayac'][$uKod] === 2 && $urun['fiyat'] >= 80) {
                $urun['fiyat'] = (int)round($urun['fiyat'] * 0.9);
                $s['zam'][$uKod] = true;
            }

            $s['log'][] = "{$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urun['ad']} istiyor → Satış yok";
        }

        if ($s['elektrik'] > 0) {
            $s['elektrik']--;
        }

        // Müşteri sonrası rastgele olay
        $olay = olay($s['no'], $s['musteri_sira']);
        if ($olay === 'elektrik') {
            $s['elektrik'] = 2;
            $s['log'][] = "⚡ Elektrik kesintisi";
        } elseif ($olay === 'yogunluk') {
            $s['yogunluk'] = true;
            $s['log'][] = "👥 Öğle yoğunluğu";
        } elseif ($olay === 'tedarik') {
            $s['tedarik_zam'] = ($s['tedarik_zam'] ?? 0) + 30;
            $s['log'][] = "📦 Tedarik sorunu";
        } elseif ($olay === 'bahsis') {
            $s['kasa'] += 20;
            $s['log'][] = "😊 Memnun müşteri";
        }
    }

    // 2. TEDARİK HAMLESİ ('T-urun-adet')
    elseif (str_starts_with($a, 'T-')) {
        [, $uKod, $adetStr] = explode('-', $a);
        $adet = (int)$adetStr;

        if (isset($s['urunler'][$uKod]) && $adet > 0) {
            $ekMaliyet = $s['tedarik_zam'] ?? 0;
            $maliyet = 60 + $ekMaliyet + ($s['urunler'][$uKod]['fiyat'] * $adet);
            if ($s['kasa'] >= $maliyet) {
                $s['kasa'] -= $maliyet;
                $s['urunler'][$uKod]['stok'] += $adet;
                $s['log'][] = "🚚 {$s['urunler'][$uKod]['ad']} tedarik: {$adet} adet";
            }
        }
    }

    // 3. GÜNÜ KAPAT ('G')
    elseif ($a === 'G') {
        $s['kasa'] -= 60;
        $s['memnuniyet'] = max(0, $s['memnuniyet'] - 5);

        // Gün sonu fire (Tost -3, Hamburger -2)
        $s['urunler']['tost']['stok'] = max(0, $s['urunler']['tost']['stok'] - 3);
        $s['urunler']['hamburger']['stok'] = max(0, $s['urunler']['hamburger']['stok'] - 2);

        $s['gunluk_musteri'] = 0;
        $s['gunluk_ret'] = 0;
        $s['yogunluk'] = false;

        $kapananGun = $s['gun'];
        if ($s['gun'] >= 5) {
            $s['bitti'] = true;
            $s['kazandi'] = ($s['kasa'] >= 1000 && $s['memnuniyet'] >= 80);
        } else {
            $s['gun']++;
        }

        $s['log'][] = "🌙 Gün {$kapananGun} kapandı";
    }

    if ($s['kasa'] < 0 || $s['memnuniyet'] <= 0) {
        $s['bitti'] = true;
        $s['kazandi'] = false;
    }

    return $s;
}