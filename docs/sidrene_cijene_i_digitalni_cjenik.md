# Sidrene cijene i digitalni cjenik (OpenCart 3)

## Instalacija bez terminala

1. Deployajte sadržaj repozitorija kroz uobičajeni Git/cPanel postupak.
2. U OpenCart administraciji otvorite **Proširenja > Proširenja > Moduli**.
3. Instalirajte **Sidrene cijene**. Instalacija je idempotentna: kreira/nadogradi tablice, postavke i evente, generira tajni cron ključ te radi početne snapshot zapise koristeći OpenCart porezni kalkulator.
4. Otvorite **Katalog > Sidrene cijene**, pregledajte bazni datum i zapise koji čekaju potvrdu.
5. Osvježite OpenCart **Modifications** i očistite Twig/Basel cache kako bi izvorni kontroleri i predlošci postali aktivni.

Deinstalacija modula ne briše sidrene cijene, revizijski trag ni arhivu cjenika, ali do ponovne instalacije isključuje javni prikaz i preuzimanje cjenika.

## Bazni datum i prikaz

Bazni referentni datum nije hardkodiran. Nova instalacija koristi datum instalacije u vremenskoj zoni Europe/Zagreb, a datum se može promijeniti u postavkama modula. Postojeći potvrđeni povijesni snapshoti se ne prepisuju. Potvrđena sidrena cijena prikazuje se na proizvodu, listama kategorija/pretrage/akcija, Basel karticama, wishlisti, košarici i checkoutu, uključujući mobilni prikaz.

## Masovni CSV uvoz

U administraciji odaberite CSV do 5 MB i najviše 10.000 podatkovnih redaka. Prvo ostavite uključeno **Samo provjera**. Tek nakon provjere bez grešaka ponovite unos bez te kvačice. Stvarni uvoz je all-or-nothing transakcija i svaka promjena ulazi u revizijski trag.

Podržani su `;` i `,` razdjelnik te UTF-8 BOM. Obvezni stupci:

- jedan ili više identifikatora: `product_id`, `model`, `sku`, `ean` (podržani su i `barcode` / `barkod`);
- `anchor_price` ili `gross_price` (bruto sidrena cijena);
- `reference_date` u obliku `YYYY-MM-DD`.

Opcionalni stupci su `net_price`, `status` (`confirmed`, `pending`, `disabled`) i `reason`. Ako je zadano više identifikatora, svi moraju upućivati na isti jednoznačni proizvod. Aktivni proizvod mora imati status `confirmed`.

Primjer:

```csv
sku;anchor_price;reference_date;status;reason
PRIMJER-001;12,90;2026-10-01;confirmed;Početni provjereni unos
```

## CSV/XML objava i javni URL-ovi

Svaka objava proizvodi atomski par CSV + XML datoteka s istim skupom proizvoda i SHA-256 kontrolnim zbrojevima. Sadrže ID, naziv, model/SKU, proizvođača, redovnu i aktualnu cijenu, sidrenu cijenu i datum, EAN barkod, raspoloživost, količinu, status zalihe i valutu. XML koristi naziv trgovine iz OpenCart postavki kao `brand` i `location="WEB"`.

Stabilni javni URL-ovi uvijek vraćaju najnoviju valjanu objavu:

- `index.php?route=information/price_list/download&format=csv`
- `index.php?route=information/price_list/download&format=xml`

Javna stranica `index.php?route=information/price_list` nudi oba formata za svaku objavu tijekom 30 dana. Starije datoteke automatski se brišu i zapis dobiva status `expired`.

## cPanel Cron Jobs

Admin modul prikazuje obični cron URL, tajni ključ i gotovi **cPanel cron URL s ključem**. U cPanel Cron Jobs GUI postavite dnevni poziv prije 08:00 po Europe/Zagreb vremenu, primjerice:

```text
curl -fsS "PUNI_URL_IZ_ADMINA" >/dev/null
```

Ako hosting ne nudi `curl`, može se koristiti `wget -qO- "PUNI_URL_IZ_ADMINA" >/dev/null`. URL s ključem je tajna. Integracije koje mogu slati zaglavlja trebaju koristiti osnovni URL i `X-Anchor-Price-Key` zaglavlje.

Objava se može ručno pokrenuti i gumbom **Objavi CSV i XML cjenik**. Objavu blokira bilo koji aktivni proizvod bez potvrđene sidrene cijene ili obveznih podataka.
