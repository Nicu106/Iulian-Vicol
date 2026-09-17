# IV MotorClass v2: strategia și tot ce mai trebuie schimbat

Actualizat: 17 septembrie 2026 · Mediu: `/var/www/motorclass-v2` → https://v2design.ivmotorclass.com
Producția (`/var/www/motorclass`, ivmotorclass.com) nu a fost modificată: HEAD rămâne `643a3b9`.

Documentul e scris fără spaniolă. Unde apare un text din site sau din admin, e dat
în ghilimele cu traducerea alături, ca să îl poți găsi pe ecran.

---

## 1. Strategia pe scurt

1. **v2 devine site-ul principal doar când spui tu.** Până atunci, tot lucrul se face
   în v2. Producția nu se atinge.
2. **Designul public e înghețat.** Modificările de design se fac doar în brandbook
   (`/brandbook`, anexele C și D), cu excepția cazurilor în care ceri explicit o schimbare.
3. **Adminul e construit pentru proprietar, care lucrează de pe telefon.** Lucrurile
   care cer o acțiune stau primele, iar fiecare ecran are cât mai puțini pași.
4. **Recomandările aduc clienți fără conturi.** Fiecare persoană are un link personal.
   Premiul se dă doar pentru o mașină vândută, niciodată pentru un clic.
5. **Datele se colectează minimal și cinstit.** Vizitatorii rămân anonimi, datele
   se șterg singure după 90 de zile, iar cifrele din admin arată doar ce se poate dovedi.

---

## 2. Sistemul de recomandări: cum funcționează

### Fluxul
1. **Se creează un link pentru o persoană.**
   - Îl creează proprietarul în admin, la „Recomendaciones” (Recomandări) → „Crear enlace”
     (Creează link), de obicei după ce i-a vândut o mașină.
   - Sau îl cere persoana însăși pe site, la `/recomienda` („Recomandă unui prieten”),
     completând doar numele și telefonul ei.
2. **Persoana trimite link-ul** (`/r/COD`, de exemplu `/r/MARIA7K2`) cui vrea, de obicei pe WhatsApp.
   Link-ul conține doar un cod, fără date personale.
3. **Prietenul deschide link-ul.** Site-ul reține codul 90 de zile. Contează primul link deschis:
   unul deschis mai târziu nu preia atribuirea. Orice mesaj WhatsApp trimis de pe site
   conține automat codul.
4. **Proprietarul vede ce s-a întâmplat.** Vezi secțiunea 3.
5. **La vânzare**, proprietarul alege persoana în fișa mașinii, la „Vino de parte de”
   (A venit din partea lui). Se creează automat un premiu în așteptare.
6. **Proprietarul decide:** aprobă premiul, apoi îl marchează ca plătit.

### Reguli fixe
- Un singur premiu pentru fiecare mașină vândută.
- Nu se dă premiu pentru propria cumpărare (telefonul cumpărătorului e același cu al recomandantului).
- Nu se cer și nu se salvează datele prietenului.
- Un link care are deja premii nu se poate șterge.
- Un telefon are un singur link. Dacă cineva cere din nou, primește același link.

---

## 3. Urmărirea: ce vezi după deschiderea link-ului

**Unde:** Admin → Recomandări → „Ver qué pasó” (Vezi ce s-a întâmplat) la fiecare link.

| Ce vezi | Ce înseamnă |
|---|---|
| Deschideri | De câte ori a fost deschis link-ul de o persoană reală |
| Persoane | Câți oameni diferiți l-au deschis |
| Au văzut mașini | Câți au intrat pe pagina a cel puțin unei mașini |
| Au apăsat pe contact | Câți au apăsat WhatsApp sau e-mail |
| Au cumpărat | Mașini vândute cu această persoană la „A venit din partea lui” |

Mai vezi:
- mașinile la care s-au uitat, cu numărul de vizualizări și de persoane;
- traseul fiecărei persoane, pas cu pas, cu ora: dispozitivul, sistemul, browserul,
  sursa (WhatsApp, Instagram sau Facebook), numărul de vizite și ultima vizită;
- în fișa mașinii, lista de la „A venit din partea lui” pune primele persoanele
  ale căror prieteni au văzut acea mașină.

### Limite, spuse direct
- **Vizitatorul e anonim până când scrie.** Nu salvăm nume și nici IP.
- **O apăsare pe WhatsApp nu dovedește că persoana a trimis mesajul.** De aceea eticheta e „a apăsat”.
- **Previzualizările de link nu se numără.** WhatsApp își deschide singur link-ul ca să facă
  previzualizarea; acest acces și roboții sunt filtrați.
- **Pe iPhone nu se știe din ce aplicație a venit link-ul**, așa că apare „origine necunoscută”.
- **Dacă persoana șterge cookie-urile** sau schimbă telefonul sau browserul, apare ca persoană nouă.
- **Se urmăresc doar vizitatorii veniți printr-un link de recomandare**, nu tot site-ul.
- **Datele se șterg automat** la 90 de zile după ultima vizită.

---

## 4. Ce trebuie schimbat: lista completă

Ordinea e cea de prioritate. **B** = blochează lansarea pe domeniul principal.

### 4.1 Legal (B)
- [ ] **Politica de confidențialitate.** Link-ul din subsol „Política de privacidad” duce acum
  la `#`, adică nicăieri. Trebuie o pagină reală care să explice:
  - formularele: „Vende tu coche” (Vinde-ți mașina), contact și recomandări;
  - numele și telefonul salvate pentru recomandări;
  - urmărirea anonimă a vizitatorilor veniți prin link (cookie-urile `mc_ref` și `mc_rv`, 90 de zile);
  - ștergerea automată și cine răspunde de date.
- [ ] **Anunț despre cookie-uri / consimțământ.** Legea din Spania (LSSI) și GDPR cer consimțământ
  pentru cookie-urile care nu sunt strict necesare. `mc_rv` (urmărirea traseului) intră aici.
  Variante:
  - banner cu acceptare, iar urmărirea pornește doar după acceptare (cel mai sigur);
  - sau păstrăm doar `mc_ref` (atribuirea premiului) și oprim urmărirea detaliată până la acceptare.
- [ ] **Termenii programului de recomandare**, câteva rânduri pe `/recomienda`: ce premiu,
  când se dă și regulile din secțiunea 2.
- [ ] **Verificarea textelor de un avocat sau gestor din Spania.** Recomandat.

### 4.2 Decizii care îți aparțin (B pentru recomandări)
- [ ] **Premiul.** Ce primește persoana: bani, reducere, service gratuit? Se setează
  `REFERRAL_REWARD` în `.env`. Cât timp e gol, site-ul nu promite nimic.
- [ ] **Revenirea `origin/main`.** Pe GitHub, `main` e acum la `a89165e`, împins din greșeală
  din v2. Producția de pe server e corectă (`643a3b9`), dar ramura de pe GitHub nu mai
  corespunde. Decizie: o readucem la `643a3b9`, sau rămâne așa până la lansarea v2.
- [ ] **Mesaje WhatsApp automate din admin.** Se poate doar cu WhatsApp Cloud API: număr
  dedicat, șabloane aprobate de Meta, acordul clientului, cost pe conversație. Bibliotecile
  neoficiale duc la blocarea numărului. Decizie: da sau nu. Până atunci, trimiterea rămâne
  cu un clic din admin.
- [ ] **Trei întrebări mai vechi despre admin:**
  - adminul se folosește mai mult de pe telefon sau de pe calculator?
  - ștergem funcțiile moarte (de exemplu analiza de prețuri)?
  - adăugarea de recenzii devine un pas fix după fiecare vânzare?

### 4.3 Înainte de lansarea v2 ca site principal (B)
- [ ] **Oprirea pagina de așteptare.** `BRANDBOOK_ONLY=false` în `.env`. Acum doar paginile din
  lista `BrandbookOnly::ALLOW` sunt deschise; restul afișează pagina de așteptare.
- [ ] **Paginile vechi încă nerefăcute.** Verificăm fiecare rută din `routes/web.php` care nu e în
  listă (de exemplu `/` cu vechea pagină principală, `/catalog`, `/vehicles/{slug}`,
  `/dashboard`, `/profile`): se reface, se redirecționează (301) sau se șterge. Cel mai
  important: `/` trebuie să afișeze noua pagină principală (`/inicio`).
- [ ] **Redirecționări 301** de la adresele vechi la cele noi (`/sell-car` → `/vende` există deja),
  ca să nu se piardă poziția în Google.
- [ ] **`sitemap.xml` și `robots.txt`** actualizate pentru adresele noi.
- [ ] **`APP_URL`, nginx și certificatul** pentru domeniul principal.
- [ ] **Baza de date.** v2 are baza ei. Trebuie stabilit cum se mută datele reale: mașini,
  mesaje, recenzii, vânzări.
- [ ] **Programator.** Pe server nu rulează `schedule:run`. Ștergerea datelor vechi de
  recomandări rulează ocazional la deschiderea unui link și se poate rula manual cu
  `php artisan referrals:prune`. Recomandat: un timer systemd zilnic, ca cele existente
  (`motorclass-v2-images.timer`).
- [ ] **Backup** pentru bază și pentru poze, înainte de mutare.
- [ ] **Rularea tuturor testelor** (`tools/audit/suites/*.mjs`) și a auditurilor
  (contrast, depășiri, ținte tactile) pe domeniul final.

### 4.4 Îmbunătățiri recomandate (nu blochează)
- [ ] **Recomandările în panou.** O cifră pe prima pagină a adminului: „persoane noi venite
  prin recomandare săptămâna aceasta”.
- [ ] **Legătura cu mesajele.** Un mesaj din formularul de contact, trimis de un vizitator venit
  prin link, ar putea fi marcat automat cu numele recomandantului.
- [ ] **Clasament.** Cine aduce cei mai mulți oameni care se uită la mașini, nu doar vânzări.
- [ ] **Export CSV** al premiilor plătite, pentru contabilitate.
- [ ] **Linkuri scurte pentru reclame** (`/r/INSTAGRAM`, `/r/TIKTOK`), ca să vezi ce aduce
  fiecare rețea socială, cu același sistem.

---

## 5. Ordinea de lucru propusă

| Pas | Ce | Cine |
|---|---|---|
| 1 | Alegi premiul și decizia pentru `origin/main` | Tu |
| 2 | Politica de confidențialitate + banner de cookie-uri + termenii programului | Eu scriu, verifică un avocat |
| 3 | Timer zilnic pentru ștergerea datelor vechi | Eu |
| 4 | Verificarea paginilor vechi, a redirecționărilor și a sitemap-ului | Eu |
| 5 | Planul de mutare a datelor + backup | Eu, cu acordul tău |
| 6 | Test complet pe v2 cu telefonul tău (un link real, o vânzare de probă) | Tu + eu |
| 7 | Lansarea: v2 devine site-ul principal | Doar când spui tu |

---

## 6. Referințe tehnice

- Reguli și arhitectură: `CLAUDE.md` (secțiunile 7a pentru admin și 7b pentru recomandări)
- Design: `docs/DESIGN-SYSTEM.md`, `docs/DESIGN-GUIDE.md`, `/brandbook` (anexele C și D)
- Recomandări: `app/Support/Referral.php`, `app/Support/Journey.php`,
  `app/Http/Middleware/TrackReferralJourney.php`, `config/referral.php`
- Teste: `tools/audit/suites/referral.mjs` (36 de verificări), `tools/audit/suites/admin-nav.mjs`
- Ultimele commit-uri: `f1daafd` (recomandări), `913582b` (urmărirea traseului),
  pe ramura `v2-design`
