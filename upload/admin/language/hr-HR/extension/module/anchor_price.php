<?php
// Naslov
$_['heading_title'] = 'Galerija Divila – Sidrene cijene';

// Tekst
$_['text_extension'] = 'Proširenja';
$_['text_list'] = 'Registar sidrenih cijena';
$_['text_edit'] = 'Uredi sidrenu cijenu';
$_['text_filter'] = 'Filtri';
$_['text_publications'] = 'Dnevni cjenici';
$_['text_settings'] = 'Postavke i automatizacija';
$_['text_import'] = 'Masovni CSV uvoz';
$_['text_import_errors'] = 'Uvoz nije primijenjen zbog sljedećih grešaka:';
$_['text_no_results'] = 'Nema pronađenih zapisa.';
$_['text_all_statuses'] = 'Svi statusi';
$_['text_status_confirmed'] = 'Potvrđeno';
$_['text_status_pending'] = 'Čeka provjeru';
$_['text_status_disabled'] = 'Isključeno';
$_['text_system'] = 'Sustav';
$_['text_missing_count'] = 'Aktivnih artikala bez sidrene cijene: %s';
$_['text_success_edit'] = 'Uspješno: Sidrena cijena i revizijski trag su ažurirani.';
$_['text_success_sync'] = 'Uspješno: Kreirano je %s nedostajućih sidrenih cijena.';
$_['text_success_publish'] = 'Uspješno: Objavljeni su CSV i XML cjenik: %s';
$_['text_success_settings'] = 'Uspješno: Postavke sidrenih cijena su spremljene.';
$_['text_success_import_dry_run'] = 'Provjera je uspješna: %s CSV redaka je valjano. Podaci nisu promijenjeni.';
$_['text_success_import'] = 'CSV uvoz je dovršen: kreirano %s, ažurirano %s zapisa.';
$_['text_reference_rule'] = 'Bazni referentni datum određuje se u postavkama (pri instalaciji je današnji datum). Noviji artikli koriste datum prve objave; povijesne izmjene ostaju u revizijskom tragu.';
$_['text_cron_help'] = 'Za tehničke integracije pozovite URL svaki dan prije 08:00 Europe/Zagreb i pošaljite ključ u zaglavlju X-Anchor-Price-Key. Generiraju se CSV i XML.';
$_['text_cron_cpanel_help'] = 'U cPanel Cron Jobs GUI unesite HTTP poziv ovog potpunog URL-a (npr. curl -fsS "URL" >/dev/null). Ključ je tajan i URL se ne smije javno dijeliti.';
$_['text_audit'] = 'Revizijski trag';

// Stupci
$_['column_product'] = 'Artikl';
$_['column_model'] = 'Model / SKU';
$_['column_net_price'] = 'Neto sidrena cijena';
$_['column_gross_price'] = 'Bruto sidrena cijena';
$_['column_reference_date'] = 'Referentni datum';
$_['column_status'] = 'Status';
$_['column_action'] = 'Radnja';
$_['column_location'] = 'Prodajno mjesto';
$_['column_sequence'] = 'Redni broj';
$_['column_filename'] = 'Datoteka';
$_['column_products'] = 'Artikala';
$_['column_published'] = 'Objavljeno';
$_['column_user'] = 'Korisnik';
$_['column_reason'] = 'Razlog';
$_['column_before'] = 'Prije';
$_['column_after'] = 'Poslije';
$_['column_date_added'] = 'Datum';

// Polja
$_['entry_filter_name'] = 'Naziv artikla';
$_['entry_filter_model'] = 'Model / SKU';
$_['entry_filter_status'] = 'Status provjere';
$_['entry_date_from'] = 'Referentni datum od';
$_['entry_date_to'] = 'Referentni datum do';
$_['entry_price'] = 'Neto iznos';
$_['entry_gross_price'] = 'Bruto iznos';
$_['entry_reference_date'] = 'Referentni datum';
$_['entry_verification_status'] = 'Status provjere';
$_['entry_reason'] = 'Razlog promjene';
$_['entry_default_unit'] = 'Zadana prodajna jedinica';
$_['entry_reference_date_setting'] = 'Bazni referentni datum';
$_['entry_cron_url'] = 'URL dnevnog cron zadatka';
$_['entry_cron_key'] = 'Cron ključ';
$_['entry_cron_cpanel'] = 'cPanel cron URL s ključem';
$_['entry_public_urls'] = 'Javni URL-ovi najnovijeg cjenika';
$_['entry_import_file'] = 'CSV datoteka';
$_['entry_dry_run'] = 'Samo provjera';

// Gumbi
$_['button_filter'] = 'Filtriraj';
$_['button_clear'] = 'Očisti';
$_['button_sync'] = 'Kreiraj nedostajuće sidrene cijene';
$_['button_publish'] = 'Objavi CSV i XML cjenik';
$_['button_settings'] = 'Spremi postavke';
$_['button_download'] = 'Preuzmi';
$_['button_import'] = 'Provjeri / uvezi CSV';

// Pomoć
$_['help_reason'] = 'Obvezno. Razlog se trajno čuva u revizijskom tragu.';
$_['help_gross_price'] = 'Bruto snapshot s porezom. Mijenjajte samo kada je potrebno ispraviti sam spremljeni snapshot.';
$_['help_default_unit'] = 'Koristi se za sve artikle jer trgovina nema strukturirano polje prodajne jedinice. Zadano: kom.';
$_['help_reference_date_setting'] = 'Primjenjuje se na nove bazne snapshot zapise. Već potvrđeni povijesni zapisi se ne prepisuju.';
$_['help_import_file'] = 'Do 5 MB / 10.000 redaka. Obvezno: jedan od product_id, model, sku ili ean; zatim anchor_price (ili gross_price) i reference_date. Opcionalno: net_price, status i reason.';
$_['help_dry_run'] = 'Preporučeno: provjeri sve retke bez upisa. Isključite kvačicu tek kada je provjera bez grešaka.';

// Upozorenja i greške
$_['warning_publication_due'] = 'Dnevni cjenik nije objavljen do 08:00.';
$_['error_permission'] = 'Upozorenje: Nemate ovlasti za izmjenu modula Sidrene cijene.';
$_['error_not_installed'] = 'Tablice modula ne postoje. Najprije instalirajte modul kroz Proširenja.';
$_['error_not_found'] = 'Tražena sidrena cijena nije pronađena.';
$_['error_price'] = 'Unesite ispravan nenegativan neto iznos.';
$_['error_gross_price'] = 'Unesite ispravan nenegativan bruto iznos.';
$_['error_reference_date'] = 'Unesite ispravan datum u obliku GGGG-MM-DD.';
$_['error_status'] = 'Odaberite ispravan status provjere.';
$_['error_reason'] = 'Razlog mora sadržavati između 3 i 255 znakova.';
$_['error_default_unit'] = 'Zadana jedinica mora sadržavati između 1 i 16 znakova.';
$_['error_reference_date_setting'] = 'Bazni referentni datum mora biti valjan datum koji nije u budućnosti.';
$_['error_import_upload'] = 'Odaberite CSV datoteku za uvoz.';
$_['error_import_file'] = 'Datoteka mora biti .csv, veličine između 1 B i 5 MB.';
$_['error_import_rows'] = 'CSV nije primijenjen. Pronađeno je %s grešaka.';
$_['error_file_missing'] = 'Datoteka objave nije dostupna ili joj je istekao rok čuvanja od 30 dana.';
