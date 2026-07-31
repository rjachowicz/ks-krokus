# SESSION_PROMPTS.md

## Start nowej sesji

```text
Pracuj na aktualnym branchu utworzonym z `v.0.4-refactor-calendar-and-admin-panel`.

Przeczytaj:
- AGENTS.md
- docs/PROJECT_GUIDE.md
- docs/ARCHITECTURE.md
- docs/UI_GUIDE.md
- docs/VALIDATION_GUIDE.md
- docs/AI_PROGRESS.md
- docs/TODO.md

Sprawdź branch i git status. Nie cofaj zmian v0.4. Przedstaw krótki plan i wykonaj zadanie do końca. Po zmianach uruchom testy, Pint i build oraz zaktualizuj AI_PROGRESS.md i TODO.md.
```

## Formularze i walidacja

```text
Wykonaj kompletny audyt wszystkich formularzy panelu administracyjnego.

Sprawdź pełny przepływ od Blade przez Form Request, kontroler, model i bazę. Napraw pola tekstowe, textarea, selecty, checkboxy, switche, daty, czas i uploady.

Usuń komunikaty typu validation.uploaded i wszystkie angielskie błędy. Dodaj polskie tłumaczenia, messages() i attributes(). Zdiagnozuj przyczynę błędów uploadu, w tym limity PHP i Railway. Dodaj testy regresyjne.

Nie kończ na analizie. Wprowadź zmiany.
```

## Motyw

```text
Dodaj dostępny przełącznik motywu light/dark w topbarze panelu. Użyj istniejących zmiennych CSS. Zapisuj preferencję w localStorage, uwzględnij prefers-color-scheme i ogranicz migotanie. Sprawdź wszystkie widoki panelu w obu motywach.
```

## Kontakt

```text
Dodaj na `/kontakt` formularz wysyłający do skonfigurowanego adresu `zarzad@ks-krokus.pl`.

Użyj Form Request, Mailable, polskich komunikatów, CSRF, rate limitingu, honeypota i testów Mail::fake(). Nie hardcoduj SMTP. Adres odbiorcy umieść w konfiguracji i .env.example.
```

## Koniec sesji

```text
1. Uruchom composer test.
2. Uruchom vendor/bin/pint --test.
3. Uruchom npm run build.
4. Napraw błędy.
5. Sprawdź git diff.
6. Zaktualizuj docs/AI_PROGRESS.md.
7. Zaktualizuj docs/TODO.md.
8. Zrób logiczny commit.
9. Podaj zmienione pliki, testy, migracje, zmienne środowiskowe i ograniczenia.
```
