@extends('layouts.app')

@section('title', 'Regulamin Klubu — KS Krokus')
@section('meta_description', 'Regulamin Klubu Strzeleckiego Krokus LOK w Nowym Sączu — tekst jednolity po zmianach z 3 lutego 2024 r.')

@section('content')
    <x-page-hero id="rules-page-title" eyebrow="DOKUMENTACJA FORMALNA" class="regulamin-hero">
        <x-slot:title>Regulamin <span class="highlight">Klubu</span></x-slot:title>
        <x-slot:description>
            <p>Regulamin Klubu Strzeleckiego „KROKUS” Ligi Obrony Kraju w Nowym Sączu (tekst jednolity po zmianach z 3
                lutego 2024 r.).</p>
        </x-slot:description>
    </x-page-hero>

    <section class="page-container page-section page-section--flush" aria-label="Treść regulaminu">
        <article class="card regulamin-card">
            <span class="card-code">[ DOC_ID: REG-LOK-2024 ]</span>

            <!-- Nagłówek prawniczy dokumentu -->
            <div class="regulamin-header">
                <h2>REGULAMIN</h2>
                <p class="regulamin-subtitle">Klubu Strzeleckiego „KROKUS” Ligi Obrony Kraju w Nowym Sączu</p>
                <span class="regulamin-date">(tekst jednolity po zmianach z 3 lutego 2024 r.)</span>
            </div>

            <div class="regulamin-body">

                <!-- Rozdział I -->
                <section>
                    <h3 class="regulamin-chapter-title">
                        Rozdział I — Postanowienia ogólne
                    </h3>

                    <div class="regulamin-list">
                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 1.</span>
                            <div class="regulamin-content">
                                <div><span class="regulamin-number">1.</span> Klub Strzelecki „Krokus” Ligi Obrony Kraju
                                    w Nowym Sączu stanowi samodzielną, samofinansującą się podstawową jednostkę
                                    działającą w ramach struktur organizacyjnych Ligi Obrony Kraju, bezpośrednio
                                    podległą Zarządowi Powiatowemu LOK Nowy Sącz, funkcjonującą według zasad określonych
                                    w Statucie Stowarzyszenia.
                                </div>
                                <div><span class="regulamin-number">2.</span> Klub zarejestrowany jest w Polskim Związku
                                    Strzelectwa Sportowego i pod względem fachowym i merytorycznym działa zgodnie z
                                    postanowieniami i wytycznymi PZSS.
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 2.</span>
                            <div class="regulamin-content">Siedzibą Klubu jest miasto Nowy Sącz. Klub działa, z
                                zachowaniem obowiązującego prawa, na obszarze kraju.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 3.</span>
                            <div class="regulamin-content">
                                <span>Celem działalności Klubu jest popularyzowanie, organizowanie oraz stwarzanie warunków do:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> uprawiania strzelectwa, w
                                        szczególności strzelectwa sportowego;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> kolekcjonerstwa broni palnej dla
                                        potrzeb kształtowania i umacniania obywatelskiej postawy wobec spraw obronności
                                        Rzeczypospolitej Polskiej, wyrabiania i utrwalania pożądanych zainteresowań,
                                        nawyków, wiedzy i umiejętności ważnych z punktu widzenia zdolności obronne
                                        Państwa oraz rozwoju strzelectwa;
                                    </div>
                                    <div><span class="regulamin-subsymbol">3)</span> w tym poprzez: kultywowanie i
                                        popularyzowanie tradycji narodowych i oręża polskiego, organizowanie i
                                        współorganizowanie imprez sportowych, okolicznościowych oraz masowych.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 4.</span>
                            <div class="regulamin-content">Klub opiera swoją działalność na bazie będącej w dyspozycji
                                Zarządu Powiatowego LOK Nowy Sącz i korzysta z niej na zasadach obowiązujących członków
                                Stowarzyszenia LOK oraz na potencjale tworzonym przez członków Klubu i ich społecznej
                                pracy, a także korzysta na ogólnych zasadach z wszelkich obiektów i urządzeń
                                umożliwiających ich zastosowanie w działalności statutowej.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Rozdział II -->
                <section>
                    <h3 class="regulamin-chapter-title">
                        Rozdział II — Władze Klubu
                    </h3>

                    <div class="regulamin-list">
                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 5.</span>
                            <div class="regulamin-content">
                                <span>Władzami Klubu są:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> Walne Zebranie Członków Klubu;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> Zarząd Klubu;</div>
                                    <div><span class="regulamin-subsymbol">3)</span> Komisja Rewizyjna.</div>
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 6.</span>
                            <div class="regulamin-content">
                                <div><span class="regulamin-number">1.</span> Uchwały władz podejmowane są zwykłą
                                    większością głosów, przy czym w pierwszym, zwykłym terminie wymagana jest obecność
                                    co najmniej połowy ogólnej liczby członków.
                                </div>
                                <div><span class="regulamin-number">2.</span> W razie braku kworum, zebrania członków
                                    odbywają się w drugim terminie określonym z góry przez Zarząd. Podejmowane uchwały w
                                    tym przypadku są prawomocne bez względu na liczbę uczestniczących w zebraniu osób
                                    uprawnionych do głosowania.
                                </div>
                                <div><span class="regulamin-number">3.</span> Uchwały podejmowane są w głosowaniu
                                    jawnym, o ile nie zostanie zgłoszony i przyjęty przez większość uczestników
                                    głosowania wniosek o przeprowadzenie głosowania tajnego.
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 7.</span>
                            <div class="regulamin-content">
                                <span>Do wyłącznej kompetencji Walnego Zebrania Członków Klubu należy:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> uchwalanie statutu lub dokonywanie
                                        jego zmian;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> uchwalanie programu działania na
                                        nową kadencję;
                                    </div>
                                    <div><span class="regulamin-subsymbol">3)</span> zatwierdzenie sprawozdania władz
                                        ustępujących;
                                    </div>
                                    <div><span class="regulamin-subsymbol">4)</span> udzielania lub odmowa udzielenie
                                        absolutorium władzom ustępującym – na podstawie wniosku Komisji Rewizyjnej;
                                    </div>
                                    <div><span class="regulamin-subsymbol">5)</span> wybór nowych władz;</div>
                                    <div><span class="regulamin-subsymbol">6)</span> uchwalanie regulaminu zebrania i
                                        regulaminu wyborów;
                                    </div>
                                    <div><span class="regulamin-subsymbol">7)</span> zatwierdzanie regulaminów Zarządu i
                                        Głównej Komisji Rewizyjnej;
                                    </div>
                                    <div><span class="regulamin-subsymbol">8)</span> rozpatrywanie wniosków zgłoszonych
                                        przez członków i władze;
                                    </div>
                                    <div><span class="regulamin-subsymbol">9)</span> nadawanie godności „Honorowy
                                        Członek Klubu”;
                                    </div>
                                    <div><span class="regulamin-subsymbol">10)</span> podejmowanie uchwał w sprawach
                                        ujętych w porządku obrad;
                                    </div>
                                    <div><span class="regulamin-subsymbol">11)</span> ustanowienie godła Klubu;</div>
                                    <div><span class="regulamin-subsymbol">12)</span> decydowanie o sprawach
                                        przekraczających czynności zwykłego zarządu;
                                    </div>
                                    <div><span class="regulamin-subsymbol">13)</span> decydowanie o rozwiązaniu i
                                        likwidacji Klubu oraz określanie celu, na jaki przeznacza się jego majątek.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 8.</span>
                            <div class="regulamin-content">
                                <div><span class="regulamin-number">1.</span> Walne Zebranie Sprawozdawczo-Wyborcze
                                    Członków Klubu odbywa się raz na pięć lat, najpóźniej do dnia 15 (piętnastego)
                                    lutego następnego roku po upływie kadencji.
                                </div>
                                <div><span class="regulamin-number">2.</span> Walne Zebranie Sprawozdawcze Członków
                                    Klubu odbywa się co roku, najpóźniej do dnia 15 lutego następnego roku.
                                </div>
                                <div><span class="regulamin-number">3.</span> Nadzwyczajne Walne Zebranie Członków Klubu
                                    zwołuje Zarząd.
                                </div>
                                <div><span class="regulamin-number">4.</span> W przypadku, gdy przepisy powszechnie
                                    obowiązującego prawa wykluczają przeprowadzenie Walnego Zebrania Członków Klubu w
                                    terminach wyżej określonych, Zarząd Klubu zwołuje Walne Zebranie Członków Klubu po
                                    ustaniu przesłanek uniemożliwiających przeprowadzenie zgromadzenia.
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 9.</span>
                            <div class="regulamin-content">Grupa stanowiąca jedną trzecią członków Klubu ma prawo
                                zobowiązać Zarząd do zwołania Nadzwyczajnego Zebrania Członków Klubu według zasad
                                określonych w statucie LOK.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 10.</span>
                            <div class="regulamin-content">
                                <div><span class="regulamin-number">1.</span> Termin, miejsce oraz porządek obrad ustala
                                    i podaje do wiadomości członkom Klubu Zarząd najpóźniej na 14 (czternaście) dni
                                    przed terminem zebrania poprzez ogłoszenie na stronie internetowej Klubu.
                                </div>
                                <div><span class="regulamin-number">2.</span> Propozycje zmian statutu – pod rygorem
                                    nieważności – muszą być publikowane wraz z proponowanym porządkiem obrad.
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 11.</span>
                            <div class="regulamin-content">Kadencja członków Zarządu trwa 5 (pięć) lat.</div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 12.</span>
                            <div class="regulamin-content">Członkowie Zarządu mogą być ponownie wybierani do pełnienia
                                swoich funkcji. Do władz Zarządu mają prawo kandydować i być wybierani członkowie Klubu
                                niepełniący funkcji we władzach nadrzędnych.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 12a.</span>
                            <div class="regulamin-content">
                                <div><span class="regulamin-number">1.</span> W skład Zarządu Klubu nie mogą wchodzić
                                    osoby pozostające ze sobą w stosunku pokrewieństwa lub powinowactwa w linii prostej
                                    tj. rodzic i dziecko, dziadek i wnuk, ani w linii bocznej do drugiego stopnia
                                    pokrewieństwa tj. rodzeństwo, ciotka/stryj i bratanek/siostrzeniec, a także osoby
                                    pozostające w stosunku powinowactwa w linii prostej bez ograniczenia stopnia tj.
                                    teściowie i zięciowie/synowie, oraz w linii bocznej do drugiego stopnia tj.
                                    szwagierstwo.
                                </div>
                                <div><span class="regulamin-number">2.</span> Zakaz ten dotyczy również osób
                                    pozostających w związkach małżeńskich, zarejestrowanych związkach partnerskich, a
                                    także osób pozostających w faktycznym wspólnym pożyciu, uznawanym przez Klub na
                                    podstawie dostarczonych dowodów lub oświadczeń.
                                </div>
                                <div><span class="regulamin-number">3.</span> W przypadku stwierdzenia, że członkowie
                                    Zarządu naruszają ograniczenia wymienione w ust. 1 i 2, osoby te są zobowiązane do
                                    niezwłocznego złożenia rezygnacji z funkcji w Zarządzie, aby zapewnić zgodność z
                                    Regulaminem.
                                </div>
                                <div><span class="regulamin-number">4.</span> Decyzję o stwierdzeniu występowania
                                    ograniczeń przyjętych w Regulaminie i o konieczności złożenia rezygnacji podejmuje
                                    Zarząd Klubu większością głosów, w obecności co najmniej połowy jego członków.
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 13.</span>
                            <div class="regulamin-content">
                                <div>Władze Klubu, tryb wyboru, kompetencje i zasady działania określa Statut LOK.</div>
                                <div><span class="regulamin-number">1.</span> Zarząd Klubu składa się (max) z siedmiu
                                    osób, w tym Prezesa, Sekretarza i Skarbnika oraz Wiceprezesów.
                                </div>
                                <div><span class="regulamin-number">2.</span> W głosowaniach Zarządu Klubu w przypadku
                                    równowagi głosów decyduje głos Prezesa Klubu oddany podczas tego głosowania.
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 14.</span>
                            <div class="regulamin-content">Zarząd realizuje swoje zadania statutowe w zakresie zarządu
                                zwykłego samodzielnie, a w zakresie przekraczającym zarząd zwykły na podstawie uchwał
                                Walnego Zebrania Członków Klubu. Do reprezentowania na zewnątrz Klubu oraz składania
                                oświadczeń woli wymagane jest łączne działanie Prezesa Zarządu z Sekretarzem lub
                                Skarbnikiem albo łączne działanie dwóch członków Zarządu na podstawie uchwały podjętej w
                                wyniku głosowania Zarządu.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 15.</span>
                            <div class="regulamin-content">
                                <span>Do zadań przekraczających uprawnienia zarządu zwykłego należy:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> zatwierdzenie planowanych
                                        przedsięwzięć inwestycyjno-rzeczowych niezwiązanych z funkcjonowaniem strzelnicy
                                        LOK oraz organizacją imprez sportowych;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> jednorazowe zadysponowanie
                                        majątkiem Klubu (zbycie lub nabycie ruchomości, rzeczy, praw) lub zaciągnięcie
                                        zobowiązania o wartości przekraczającej cztery piąte majątku wykazanego w
                                        ostatnim sprawozdaniu finansowym.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 16.</span>
                            <div class="regulamin-content">Podstawowym zadaniem Zarządu jest organizowanie oraz
                                stwarzanie optymalnych warunków do uprawiania strzelectwa i udziału członków Klubu w
                                strzeleckim współzawodnictwie sportowym oraz gospodarowanie wydzielonym majątkiem i
                                funduszami Klubu zgodnie z obowiązującymi przepisami, w tym Statutem LOK i regulaminem
                                Klubu oraz uchwałami Walnego Zgromadzenia Członków Klubu.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 17.</span>
                            <div class="regulamin-content">Zarząd składa sprawozdanie rzeczowe i finansowe z realizacji
                                zadań za okresy roczne do dnia 15 lutego następnego roku kalendarzowego. Praca Zarządu
                                za okres, za który złożono sprawozdanie, podlega ocenie Zebrania Członków Klubu, które
                                udziela lub nie udziela absolutorium władzom Klubu.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 18.</span>
                            <div class="regulamin-content">Komisja Rewizyjna jest władzą Klubu powołaną do sprawowania
                                kontroli całokształtu jego działalności. Posiedzenia komisji odbywają się w zależności
                                od potrzeb.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 19.</span>
                            <div class="regulamin-content">Kadencja Komisji Rewizyjnej upływa wraz z kadencją Zarządu.
                                Komisja Rewizyjna składa się z 3 osób, w tym Przewodniczącego i Sekretarza.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 20.</span>
                            <div class="regulamin-content">
                                <span>Do zakresu działania Komisji Rewizyjnej należy m.in.:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> kontrolowanie, co najmniej raz w
                                        roku, całokształtu działalności Klubu stosownie do swoich uprawnień;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> ocena projektów planów oraz
                                        sprawozdań rocznych, a także zwoływanie zebrania delegatów w razie niezwołania
                                        go przez Zarząd w terminie i trybie ustalonym w niniejszym regulaminie i
                                        Statucie;
                                    </div>
                                    <div><span class="regulamin-subsymbol">3)</span> kontrolowanie opłacania składki
                                        członkowskiej oraz zasadności gospodarowania funduszami pochodzącymi w
                                        szczególności ze składek członkowskich, darowizn, dotacji oraz dofinansowania
                                        zadań zleconych;
                                    </div>
                                    <div><span class="regulamin-subsymbol">4)</span> składanie Zebraniu Członków
                                        sprawozdania z własnej działalności oraz wniosków o udzielenie lub odmowę
                                        udzielenia absolutorium władzom ustępującym.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Rozdział III -->
                <section>
                    <h3 class="regulamin-chapter-title">
                        Rozdział III — Członkowie Klubu – prawa i obowiązki
                    </h3>

                    <div class="regulamin-list">
                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 21.</span>
                            <div class="regulamin-content">
                                <div><span class="regulamin-number">1.</span> Członkami zwyczajnymi mogą być osoby
                                    małoletnie i pełnoletnie niepozbawione praw publicznych, podporządkowujące się
                                    postanowieniom Statutu LOK.
                                </div>
                                <div><span class="regulamin-number">2.</span> Małoletni w wieku od 16 do 18 lat,
                                    posiadający ograniczoną zdolność do czynności prawnych, mogą należeć do Klubu i
                                    korzystać z czynnego oraz biernego prawa wyborczego, przy czym w składzie Zarządu
                                    większość muszą stanowić osoby o pełnej zdolności do czynności prawnych.
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 22.</span>
                            <div class="regulamin-content">Członkowi Klubu przysługują wszystkie uprawnienia wynikające
                                ze Statutu LOK oraz postanowień niniejszego regulaminu i uchwał Walnego Zgromadzenia
                                Członków Klubu, a w szczególności korzystania ze sprzętu i urządzeń Stowarzyszenia na
                                zasadach określonych przez Zarząd Główny lub zarządzającą tym wyposażeniem jednostkę
                                terenową LOK.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 23.</span>
                            <div class="regulamin-content">
                                <span>Członkowie Klubu mają obowiązek:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> przestrzegać postanowień statutu,
                                        regulaminu i uchwał władz Klubu i zasad etyki;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> brać czynny udział w zawodach
                                        strzeleckich organizowanych przez Klub zgodnie z odrębnymi wymogami;
                                    </div>
                                    <div><span class="regulamin-subsymbol">3)</span> brać czynny udział w pracach Klubu
                                        związanych z realizacją zadań statutowych;
                                    </div>
                                    <div><span class="regulamin-subsymbol">4)</span> opłacać składki członkowskie na
                                        każdy kolejny rok w terminie do 31 marca każdego kolejnego roku kalendarzowego i
                                        dokonywać obowiązujących wpłat na fundusz Klubu;
                                    </div>
                                    <div><span class="regulamin-subsymbol">5)</span> bezwzględnie przestrzegać wszelkich
                                        przepisów w zakresie strzelectwa sportowego i obchodzenia się z bronią palną, w
                                        tym regulaminów i zarządzeń dotyczących korzystania ze strzelnicy sportowej.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 24.</span>
                            <div class="regulamin-content">
                                <span>Członkostwo Klubu ustaje wskutek przyczyn określonych w Statucie LOK, z zastrzeżeniem iż:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> skreślenie z listy członków
                                        następuje z powodu zwłoki w opłacie rocznej składki członkowskiej
                                        przekraczającej jeden miesiąc, po uprzednim pisemnym, dokonanym po tym terminie
                                        wezwaniu do uregulowania płatności i udzieleniu dodatkowego terminu do zapłaty
                                        zaległości;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> ustanie członkostwa stwierdza
                                        Zarząd. Od decyzji Zarządu w tej sprawie przysługuje odwołanie do Komisji
                                        Rewizyjnej składane na ręce jednego z jej członków w terminie siedmiu dni od
                                        daty zawiadomienia o skreśleniu z listy. Komisja Rewizyjna podejmuje decyzję
                                        ostateczną w terminie miesiąca od otrzymania odwołania.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 24a.</span>
                            <div class="regulamin-content">
                                <div><span class="regulamin-number">1.</span> Członkowie Klubu szczególnie zasłużeni w
                                    działalność na rzecz Klubu lub ponadprzeciętnie zaangażowani w aktywność sportową
                                    mogą zostać wyróżnieni odznaczeniem Klubowym: „Krokus z Laurem”.
                                </div>
                                <div><span class="regulamin-number">2.</span> Odznaczenie Klubowe nadawane jest uchwałą
                                    Zarządu Klubu po akceptacji Komisji Rewizyjnej w formie odznaczenia I lub II rangi.
                                    Odznaczeniem pierwszej rangi jest „Złoty Krokus z Laurem”, który może być przyznany
                                    nie wcześniej niż rok po nadaniu odznaczenia drugiej rangi.
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Rozdział IV -->
                <section>
                    <h3 class="regulamin-chapter-title">
                        Rozdział IV — Majątek Klubu
                    </h3>

                    <div class="regulamin-list">
                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 25.</span>
                            <div class="regulamin-content">
                                <span>Majątek Klubu stanowią nieruchomości, ruchomości i inne środki:</span>
                                <div class="regulamin-sublist">
                                    <div><span class="regulamin-subsymbol">1)</span> majątek ruchomy i nieruchomy będący
                                        własnością Stowarzyszenia, jak również dotacje przyznane przez wyższe władze
                                        Stowarzyszenia, mogą być wykorzystane wyłącznie w działalności Klubu LOK
                                        „Krokus” Nowy Sącz według zasad określonych w Statucie;
                                    </div>
                                    <div><span class="regulamin-subsymbol">2)</span> majątek ruchomy zdobyty staraniem
                                        członków Klubu, niepochodzący z dotacji przyznanych przez władze Stowarzyszenia,
                                        jest użytkowany wyłącznie przez Klub. Przekazanie tego majątku innym jednostkom
                                        LOK może nastąpić wyłącznie na podstawie uchwały Walnego Zebrania Członków
                                        Klubu;
                                    </div>
                                    <div><span class="regulamin-subsymbol">3)</span> fundusze w postaci środków
                                        pieniężnych pochodzących ze składek członków Klubu, darowizn lub pozyskanych w
                                        inny sposób pozostają do wyłącznej dyspozycji Klubu.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Rozdział V -->
                <section>
                    <h3 class="regulamin-chapter-title">
                        Rozdział V — Postanowienia końcowe
                    </h3>

                    <div class="regulamin-list">
                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 26.</span>
                            <div class="regulamin-content">Regulacje prawne i organizacyjne niewymienione w niniejszym
                                regulaminie, związane z działalnością, organizacją i funkcjonowaniem Klubu,
                                kompetencjami i odpowiedzialnością Zarządu, a także prawami i obowiązkami członków,
                                reguluje Statut LOK oraz przepisy prawa powszechnie obowiązującego.
                            </div>
                        </div>

                        <div class="regulamin-paragraf">
                            <span class="regulamin-symbol">§ 27.</span>
                            <div class="regulamin-content">Niniejszy regulamin obowiązuje wraz z jego prawomocnym
                                zatwierdzeniem przez Walne Zgromadzenie Członków Klubu.
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <footer class="card-footer regulamin-footer">
                <span class="card-status">STATUS: OBOWIĄZUJĄCY</span>
                <a href="{{ route('home') }}" class="card-link">← Powrót do strony głównej</a>
            </footer>
        </article>
    </section>
@endsection
