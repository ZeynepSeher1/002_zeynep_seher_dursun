# 002 · Kantinde Kaos

- **Öğrenci No:** 251701011
- **Ad Soyad:** Zeynep Seher Dursun
- **Tespit Edilen Kural Sayısı:** 18 / 20
- **Kara Kutu Test Sayısı:** 15+
- **Yaklaşık Çalışma Süresi:** 2 saat
- **Zorluk Derecesi (1-5):** 4
- **Yararlanılan Kaynaklar:** Yapay zekâ, localhost test ortamı

## Yöntem ve Kural Çıkarımı
Kara kutu arayüzünde tekil hamleler (+ Müşteri, Tedarik et, Günü kapat) ve farklı deneme turları çalıştırılarak her işlem sonrasındaki kasa, memnuniyet ve stok değişimleri analiz edildi. Elde edilen matematiksel kurallar adım adım `adim()` fonksiyonuna aktarıldı.

## Karşılaşılan Zorluklar
Elektrik kesintisinin sıcak ürünler üzerindeki tur bazlı etkisini ve gün sonu fire/sabit gider kesintilerini tam olarak modellemek en dikkat gerektiren aşamaydı.
