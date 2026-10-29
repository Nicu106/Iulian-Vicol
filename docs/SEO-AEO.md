# SEO, AEO și paginile legale — ce s-a făcut și ce rămâne (29.10.2026)

Scris pe baza a două cercetări cu surse: regulile Google, Bing și ale motoarelor AI, până în octombrie 2026, și legislația din Spania și Andaluzia (BOE, AEPD, BOJA). Commit-urile au detaliile: 9af6328 (legal), a4ce1bb (SEO), 6cb6505 (IndexNow), 081b0d6 (recenzii prin link), 66ca783 (prețuri garanție).

## 1. Ce caută oamenii

Am testat ce completează Google automat când scrii căutarea, pe Spania (volume exacte nu există gratuit). Pe sugestiile Google confirmi tu cu Keyword Planner și, peste câteva săptămâni, cu Search Console.

- **Cuvintele mari:** „coches segunda mano málaga”, „coches ocasión málaga”. Aici primele locuri le au portalurile (coches.net, Wallapop, Flexicar, OcasionPlus); pe termen scurt nu le batem.
- **Marcă + Málaga:** „bmw / audi / mercedes / porsche / volkswagen segunda mano málaga”, cu modelele (X1–X5, A3/Q5, GLC/CLA, Golf GTI). Pentru acestea există acum paginile noi pe mărci.
- **Nișe pe stocul real:** „7 plazas segunda mano málaga”, „automáticos segunda mano málaga” (are multe mașini cu 7 locuri).
- **„compramos tu coche málaga” e numele unui lanț de magazine.** Pentru /vende se țintește „vender mi coche málaga”.
- **Numele firmei:** „motorclass” singur duce la alte firme (Las Palmas, Murcia). Trebuie să apară mereu împreună cu „Málaga”.

## 2. Ce s-a făcut în cod

- **Pagini pe mărci:** `/catalogo/volkswagen`, `/audi`, `/bmw`, `/mercedes`, `/porsche`.
  - Titlul e de forma „BMW de segunda mano en Málaga”. Fiecare pagină arată doar mașinile mărcii (în stoc și livrate).
  - Bannerul n-are poză, ca să nu apară Porsche-ul pe pagina BMW.
  - O marcă fără mașini și cu sub 3 livrate (Porsche acum) nu intră în Google până nu are ce arăta.
  - În catalog, logo-ul fiecărei mărci duce la pagina ei.
- **Titlurile din Google** conțin acum ce caută oamenii („de segunda mano en Málaga”, plus prețul la fiecare mașină). Linkurile trimise pe WhatsApp arată titlul vechi, scurt. Niciun text vizibil de pe pagini nu s-a schimbat.
- **Fiecare mașină e marcată pentru Google și ca produs**, nu doar ca mașină (Product + Car). Fără asta, Google nu o ia în calcul pentru rezultatele cu produse.
- **IndexNow:**
  - Când adaugi, modifici sau vinzi o mașină, Bing (de unde își iau răspunsurile ChatGPT și Copilot), Yandex și celelalte motoare care folosesc IndexNow află în același minut.
  - Prima trimitere a mers: 55 de pagini, răspuns 202.
  - Google nu folosește IndexNow; el citește sitemap-ul.
- **Sitemap și llms.txt:** conțin paginile pe mărci și paginile legale.
- **Pagini legale:**
  - `/terminos` conține datele firmei, condițiile de folosire, prețurile cu REBU, garanția, rezervările, recenziile și reclamațiile.
  - `/privacidad` acoperă datele personale și cookie-urile.
  - În subsol sunt cele două texte cerute de Junta de Andalucía: foile de reclamații și documentul individual al fiecărei mașini.
  - Sub formularele /vende și /recomienda e un rând despre date.
- **Meniu:** pe calculator scria „Por quénosotros” (fără spațiu). E reparat.

## 3. Ce trebuie să faci tu, în ordinea efectului

### Pe server, o dată

1. **Migrarea pentru recenziile prin link.** Până o rulezi, butonul „Añadir opinión” merge ca înainte.

   ```
   cp /var/www/motorclass-v2/database/database.sqlite /root/database-before-review-links.sqlite
   cd /var/www/motorclass-v2 && php artisan migrate --force && php artisan queue:restart
   ```

2. **Datele firmei în `.env`.** Legea (LSSI art. 10) cere numele complet sau denumirea S.L., NIF-ul și adresa.

   ```
   LEGAL_HOLDER="Nume Prenume"          (sau "IV MOTORCLASS S.L.")
   LEGAL_NIF="..."
   LEGAL_ADDRESS="Calle ..., 29xxx Málaga"
   LEGAL_REGISTRY="..."                 (doar dacă e S.L.: Registro Mercantil de Málaga, tomo/folio/hoja)
   ```

   Ce lipsește nu se afișează.

3. **www:** în DNS la Hostinger, `www` trebuie să fie de tip A către 213.199.39.241 (acum e un CNAME spre vechiul CDN, care nu răspunde). Apoi rulezi:

   ```
   certbot --nginx -d ivmotorclass.com -d www.ivmotorclass.com --expand
   ```

### Conturi (contează cel mai mult pentru Google Maps și pentru răspunsurile AI)

4. **Google Business Profile.**
   - Tip: firmă cu zonă de deservire. Adresa ascunsă: nu are showroom, deci nu inventăm o adresă.
   - Zona: orașele din provincia Málaga.
   - Program: niciunul („numai cu programare”).
   - Numele: exact „IV MotorClass”, fără cuvinte adăugate.
   - Categoria principală: „Used car dealer” (Concesionario de vehículos de ocasión). Nu alege categorii de dealer oficial de marcă.
   - Poze cu fiecare mașină.
   - Verificarea va fi probabil prin video: mașinile, cheile, actele firmei.
   - În răspunsurile AI pentru afaceri locale, profilul Google e sursa citată de cele mai multe ori.
5. **Recenzii pe Google.** După fiecare predare, trimite pe WhatsApp linkul de recenzie. Fără recompense și fără să alegi cui ceri; Google interzice ambele. Răspunde la fiecare recenzie.
6. **Google Search Console.**
   - Adaugă o proprietate de tip „domeniu”, verificată cu un record TXT în DNS.
   - Trimite `https://ivmotorclass.com/sitemap.xml`.
   - Lasă pornită opțiunea pentru funcțiile AI din Search.
7. **Bing Webmaster Tools.** Import din Search Console, apoi sitemap-ul. IndexNow e deja activ. Raportul „AI Performance” arată când Copilot citează site-ul.
8. **Bing Places** (import din profilul Google) și **Apple Business** (fișa din Apple Maps).
9. **O singură dată, aceleași date peste tot** (nume, telefon, link): pagina de Facebook, Páginas Amarillas, QDQ, profilul de dealer de pe coches.net cu link spre site. Nu cumpăra pachete plătite de listări.
10. **Email:** `jvmotorclass@gmail.com` are „jv”, iar firma e „IV”. Gmail-ul gratuit nu semnează contractul de prelucrare a datelor cerut de RGPD. O adresă `@ivmotorclass.com` pe Google Workspace rezolvă ambele probleme.

### Date de corectat în admin

11. **„BMW 328i Exclsusive Edition”**: greșeală în nume (Exclusive). Apare în Google și în răspunsurile AI.

## 4. Decizii legale de luat (de preferat cu un gestor sau avocat)

- **Cookie-urile de recomandare** (`mc_ref`, `mc_rv`): după ghidul AEPD și liniile EDPB, ar putea cere consimțământ. Variante:
  - un banner cu „Acepto / Rechazo” la același nivel;
  - sau renunțăm la urmărirea paginilor vizitate de cei care vin prin link (`mc_rv`).

  Statisticile site-ului nu folosesc cookie-uri și se încadrează în excepția AEPD.
- **Dreptul de retragere în 14 zile:** un contract semnat în afara unui sediu comercial (la client, într-o parcare) sau la distanță (WhatsApp + transfer) dă cumpărătorului 14 zile să se răzgândească. Contractul trebuie să spună asta și să includă formularul tip. Altfel termenul crește cu 12 luni (TRLGDCU art. 92, 102, 105).
- **Garanția de 1 an** la mașinile second-hand trebuie scrisă în contract. Fără asta, legea dă 3 ani (art. 120).
- **Hoj@ (Andaluzia):** înregistrarea în sistemul electronic de reclamații se face până în mai 2027. Foile de reclamații pe hârtie trebuie să fie la el când vinde.
- **Rezervările cu avans:** suma, durata și ce se întâmplă cu banii trebuie puse în scris.
- **Recompensa pentru recomandare:** dacă o anunți, condițiile trebuie publicate pe /recomienda.

## 5. Ce urmează (cod, cu aprobarea ta)

- Un ghid scurt, la persoana I: „Cum cumperi de la mine fără showroom: programare, probă, plată, transfer”. Cel mai bun conținut pentru Google și AI e experiența proprie, nu textele generale.
- O pagină „7 locuri”, când sunt cel puțin 4 mașini (inclusiv vândute).
- Pe mașinile vândute, 3–4 mașini asemănătoare din stoc.
- O pagină în engleză pentru străinii de pe Costa del Sol (19,9% din provincie). Întâi verificăm în Search Console dacă apar căutări în engleză.

## 6. Ce nu facem, și de ce

- **Pagini pe orașe** (Marbella, Fuengirola…): Google le tratează ca „doorway” (pagini-poartă) și le penalizează.
- **Vehicle ads Google:** cer un sediu deschis publicului.
- **Steluțe din recenziile proprii:** Google nu le arată pentru recenziile de pe site-ul firmei.
- **FAQ în Google:** rezultatele cu întrebări au dispărut din mai 2026. Marcajul rămâne, nu strică.
- **llms.txt:** există, dar 97% dintre fișierele llms.txt nu sunt citite de nimeni. Nu mai investim în el.
