@extends('layouts.app')

@section('title', 'Kontakt — KS Krokus Nowy Sącz')
@section('meta_description', 'Dane kontaktowe, zarząd, komisja rewizyjna, instruktorzy i lokalizacje Klubu Strzeleckiego Krokus LOK w Nowym Sączu.')

@section('content')
    <section class="hero contact-hero" aria-labelledby="contact-title">
        <div class="hero-content contact-hero-content">
            <span class="category-tag">Centrala i władze klubu</span>
            <h1 id="contact-title">Skontaktuj się z <span class="highlight">Krokusem</span></h1>
            <p>
                Masz pytania dotyczące członkostwa, treningów, patentu strzeleckiego lub organizacji zawodów?
                Napisz do zarządu albo skontaktuj się bezpośrednio z właściwą osobą.
            </p>
            <div class="btn-group">
                <a href="mailto:zarzad@ks-krokus.pl" class="btn btn-primary">Napisz e-mail</a>
                <a href="#lokalizacje" class="btn btn-secondary">Zobacz lokalizacje</a>
            </div>
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="visual-canvas"></div>
            <div class="telemetry-overlay">
                <span>Sekretariat klubu</span>
                <span>Nowy Sącz / Tylicz</span>
            </div>
        </div>
    </section>

    <section class="hero-stats-bar" aria-label="Organizacje i kanały kontaktu">
        <article class="stat-card">
            <span class="stat-number">LOK</span>
            <span class="stat-label">Liga Obrony Kraju</span>
        </article>
        <article class="stat-card">
            <span class="stat-number">PZSS</span>
            <span class="stat-label">Polski Związek Strzelectwa Sportowego</span>
        </article>
        <article class="stat-card">
            <span class="stat-number">24/7</span>
            <span class="stat-label">kontakt e-mail</span>
        </article>
    </section>

    <section class="features-section" aria-labelledby="address-title">
        <header class="section-header">
            <h2 id="address-title" class="section-title">Dane teleadresowe</h2>
            <span class="section-meta">HQ_DATA</span>
        </header>

        <div class="bento-grid">
            <article class="card">
                <div>
                    <span class="card-code"><span class="status-dot"></span> SIEDZIBA KLUBU</span>
                    <h3>Klub Strzelecki „KROKUS” LOK</h3>
                    <p><strong>Adres:</strong> ul. Tarnowska 32, 33-300 Nowy Sącz</p>
                    <p>
                        <strong>E-mail:</strong>
                        <a href="mailto:zarzad@ks-krokus.pl" class="card-link inline-mail-link">zarzad@ks-krokus.pl</a>
                    </p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">LOKALIZACJA GŁÓWNA</span>
                    <a href="#lokalizacje" class="card-link">Mapa dojazdu →</a>
                </footer>
            </article>

            <article class="card bento-span-2">
                <div>
                    <span class="card-code">DANE DO FAKTUR VAT</span>
                    <h3>Dane księgowe</h3>
                    <p class="accounting-desc-main">
                        Biuro Zarządu Małopolsko-Świętokrzyskiej Organizacji Regionalnej LOK w Krakowie
                    </p>
                    <p class="accounting-desc-sub">Skrót: Biuro Zarządu Mał.-Święt. Org. Reg. LOK</p>
                    <p class="accounting-address"><strong>Adres:</strong> ul. Pomorska 2, 30-039 Kraków</p>
                    <p class="accounting-nip"><strong>NIP:</strong> 521-008-68-32</p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">OFICJALNE ROZLICZENIA</span>
                    <span class="card-link">NIP 521-008-68-32</span>
                </footer>
            </article>
        </div>
    </section>

    <section class="features-section" aria-labelledby="board-title">
        <header class="section-header">
            <h2 id="board-title" class="section-title">Zarząd klubu</h2>
            <span class="section-meta">BOARD_DIRECTORS</span>
        </header>

        <div class="bento-grid">
            <article class="card">
                <div>
                    <span class="card-code">PREZES</span>
                    <h3>Dariusz Cichostępski</h3>
                    <p class="board-desc">Kierownictwo i ogólny nadzór nad działalnością klubu.</p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">KONTAKT</span>
                    <a href="mailto:dcichostepski@gmail.com" class="card-link">dcichostepski@gmail.com</a>
                </footer>
            </article>

            <article class="card">
                <div>
                    <span class="card-code">SEKRETARZ</span>
                    <h3>Tomasz Szanser</h3>
                    <p class="board-desc">Sprawy administracyjne i dokumentacja członkowska.</p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">KONTAKT</span>
                    <a href="mailto:tomasz.szanser@gmail.com" class="card-link">tomasz.szanser@gmail.com</a>
                </footer>
            </article>

            <article class="card">
                <div>
                    <span class="card-code">SKARBNIK</span>
                    <h3>Renata Dulak</h3>
                    <p class="board-desc-phone"><a href="tel:+48513017897" class="club-link">+48 513 017 897</a></p>
                    <p class="board-desc-margin">Rozliczenia składek i finanse klubu.</p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">KONTAKT</span>
                    <a href="mailto:skarbnikkrokus@gmail.com" class="card-link">skarbnikkrokus@gmail.com</a>
                </footer>
            </article>

            <article class="card bento-span-2">
                <div>
                    <span class="card-code">WICEPREZESI</span>
                    <h3>Władze wykonawcze</h3>
                    <div class="vices-grid">
                        <div class="vice-item">
                            <strong class="vice-name">Marcin Oświęcimka</strong>
                            <a href="tel:+48660478716" class="vice-phone">+48 660 478 716</a>
                            <a href="mailto:xccmarcin@gmail.com" class="card-link vice-mail">xccmarcin@gmail.com</a>
                        </div>
                        <div class="vice-item">
                            <strong class="vice-name">Marcin Ogórek</strong>
                            <a href="tel:+48723771675" class="vice-phone">+48 723 771 675</a>
                            <a href="mailto:martinez555@wp.pl" class="card-link vice-mail">martinez555@wp.pl</a>
                        </div>
                        <div class="vice-item">
                            <strong class="vice-name">Michał Kohmann</strong>
                            <a href="tel:+48515117066" class="vice-phone">+48 515 117 066</a>
                            <a href="mailto:mimik.9@gmail.com" class="card-link vice-mail">mimik.9@gmail.com</a>
                        </div>
                        <div class="vice-item">
                            <strong class="vice-name">Marcin Schiller</strong>
                            <a href="tel:+48693291557" class="vice-phone">+48 693 291 557</a>
                            <a href="mailto:schiller@onet.eu" class="card-link vice-mail">schiller@onet.eu</a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="features-section" aria-labelledby="audit-title">
        <header class="section-header">
            <h2 id="audit-title" class="section-title">Komisja rewizyjna i szkolenia</h2>
            <span class="section-meta">AUDIT_AND_TRAINING</span>
        </header>

        <div class="bento-grid">
            <article class="card">
                <div>
                    <span class="card-code">NADZÓR KONTROLNY</span>
                    <h3>Komisja Rewizyjna</h3>
                    <p class="audit-mail-wrapper">
                        <a href="mailto:komisjarewizyjna@ks-krokus.pl" class="card-link">komisjarewizyjna@ks-krokus.pl</a>
                    </p>
                    <ul class="audit-list">
                        <li><strong>Przewodnicząca:</strong> Kinga Zmarzły-Jarek</li>
                        <li><strong>Zastępca:</strong> Krzysztof Kozioł</li>
                        <li><strong>Sekretarz:</strong> Andrzej Węgrzyn</li>
                    </ul>
                </div>
                <footer class="card-footer">
                    <span class="card-status">ORGAN KONTROLNY</span>
                    <span class="card-link">AUDIT</span>
                </footer>
            </article>

            <article class="card bento-span-2">
                <div>
                    <span class="card-code">PATENT STRZELECKI</span>
                    <h3>Osoby przygotowujące do egzaminu</h3>
                    <p class="trainers-desc">Kontakt do instruktorów prowadzących szkolenia przygotowawcze:</p>
                    <div class="trainers-grid">
                        <div class="trainer-card"><strong class="trainer-name">Tadeusz Górka</strong><a href="tel:+48502939561" class="trainer-phone">+48 502 939 561</a></div>
                        <div class="trainer-card"><strong class="trainer-name">Dawid Żołądź</strong><a href="tel:+48692590900" class="trainer-phone">+48 692 590 900</a></div>
                        <div class="trainer-card"><strong class="trainer-name">Marcin Ogórek</strong><a href="tel:+48723771675" class="trainer-phone">+48 723 771 675</a></div>
                        <div class="trainer-card"><strong class="trainer-name">Piotr Duszyński</strong><a href="tel:+48514006212" class="trainer-phone">+48 514 006 212</a></div>
                        <div class="trainer-card"><strong class="trainer-name">Marcin Oświęcimka</strong><a href="tel:+48660478716" class="trainer-phone">+48 660 478 716</a></div>
                        <div class="trainer-card"><strong class="trainer-name">Michał Kohmann</strong><a href="tel:+48515117066" class="trainer-phone">+48 515 117 066</a></div>
                    </div>
                </div>
                <footer class="card-footer">
                    <span class="card-status">SZKOLENIA PZSS</span>
                    <a href="{{ route('club') }}#szkolenie-patentowe" class="card-link">Informacje o szkoleniu →</a>
                </footer>
            </article>
        </div>
    </section>

    <section id="lokalizacje" class="features-section" aria-labelledby="locations-title">
        <header class="section-header">
            <h2 id="locations-title" class="section-title">Lokalizacje i mapy dojazdu</h2>
            <span class="section-meta">GEO_LOCATIONS</span>
        </header>

        <div class="bento-grid">
            <article class="card bento-span-2">
                <div>
                    <span class="card-code"><span class="status-dot"></span> STRZELNICA SPORTOWA</span>
                    <h3>Nowy Sącz, ul. Tarnowska 32</h3>
                    <p class="location-desc">Główny obiekt treningowy i siedziba klubu.</p>
                    <div class="map-wrapper">
                        <iframe
                            title="Mapa dojazdu do strzelnicy KS Krokus w Nowym Sączu"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d728!2d20.6942834!3d49.6353922!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x473dfaabef77b20f%3A0x807ec81260609079!2sKlub%20Strzelecki%20Krokus%20LOK%20Nowy%20S%C4%85cz!5e0!3m2!1spl!2spl!4v1650000000000!5m2!1spl!2spl"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
                <footer class="card-footer">
                    <span class="card-status">NOWY SĄCZ</span>
                    <a href="https://www.google.com/maps/search/?api=1&query=Klub+Strzelecki+Krokus+LOK+Nowy+S%C4%85cz" target="_blank" rel="noopener noreferrer" class="card-link">Otwórz mapę →</a>
                </footer>
            </article>

            <article class="card">
                <div>
                    <span class="card-code"><span class="status-dot"></span> STRZELNICA MYŚLIWSKA</span>
                    <h3>Tylicz „u Leśników”</h3>
                    <p class="location-desc">Leśny Zakład Doświadczalny w Krynicy-Zdroju.</p>
                    <div class="map-wrapper">
                        <iframe
                            title="Mapa dojazdu do strzelnicy myśliwskiej w Tyliczu"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2596.11!2d20.9934041!3d49.4060517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x473e77da8622b5ef%3A0x6b4be703772cd116!2sStrzelnica%20My%C5%9Bliwska%20%22u%20Le%C5%9Bnik%C3%B3w%22%20Le%C5%9Bny%20Zak%C5%82ad%20Do%C5%9Bwiadczalny%20w%20Krynicy-Zdroju!5e0!3m2!1spl!2spl!4v1650000000000!5m2!1spl!2spl"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
                <footer class="card-footer">
                    <span class="card-status">TYLICZ</span>
                    <a href="https://www.google.com/maps/search/?api=1&query=Strzelnica+My%C5%9Bliwska+u+Le%C5%9Bnik%C3%B3w+Tylicz" target="_blank" rel="noopener noreferrer" class="card-link">Otwórz mapę →</a>
                </footer>
            </article>
        </div>
    </section>
@endsection
