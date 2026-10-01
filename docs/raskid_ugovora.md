# Jednostrani raskid ugovora (OpenCart 3.0.3.8)

## Što je uključeno

- javno dostupan obrazac **Raskid ugovora** u podnožju, korisničkom računu i uz narudžbe;
- provjera broja narudžbe i e-mail adrese kupca;
- raskid cijele narudžbe ili samo odabranih proizvoda;
- pregled izjave i svih unesenih podataka prije potvrde;
- potvrda kupcu i obavijest trgovini s datumom, vremenom i sadržajem zahtjeva;
- administratorska evidencija, statusi i povijest obrade;
- CSRF zaštita, honeypot, opcionalni OpenCart CAPTCHA i ograničenje na pet zaprimljenih zahtjeva po IP adresi tijekom 60 minuta;
- GDPR napomena uz obrazac i završni pregled.

## Aktivacija

1. Deployajte datoteke iz repozitorija.
2. U administratorskoj grupi provjerite pristup ruti `extension/sale/contract_withdrawal`; korisnicima s pravom na OpenCart povrate pristup se dopušta automatski.
3. Osvježite **Proširenja > Modifikacije** i očistite Twig/Basel cache.
4. Ako želite CAPTCHA zaštitu, uključite CAPTCHA proširenje u OpenCartu i omogućite ga za povrate.
5. Pošaljite jedan testni zahtjev sa stvarnom testnom narudžbom te provjerite korisnički i administratorski e-mail.

Tablice za zahtjeve i njihovu povijest kreiraju se idempotentno pri prvom korištenju. Adresa trgovine i primatelji administratorskih obavijesti preuzimaju se iz postojećih OpenCart postavki; u kodu nema hardkodiranih adresa drugih trgovaca.
