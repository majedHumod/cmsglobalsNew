# Exercise translation overlays

Locale content for the exercise library lives in sibling JSON files:

- `ar.json` — Arabic (current)
- add `fr.json`, `tr.json`, … later using the same shape

## File shape

```json
{
  "meta": { "locale": "ar", "version": 1 },
  "labels": {
    "body_part": { "chest": "الصدر" },
    "equipment": { "dumbbell": "دمبل" }
  },
  "exercises": {
    "barbell-bench-press": {
      "name": "بنش برس بالباربل",
      "description": "optional",
      "instructions": ["optional", "steps"]
    }
  }
}
```

## Apply to tenants

```bash
php artisan exercises:apply-translations ar --all
php artisan exercises:apply-translations ar --tenant=waqt.cmsglobals.test --force
```

RepDB re-import with `--force` preserves overlay locales listed in `config/exercise_localization.php` (`overlay_locales`).

API clients can send `X-Locale: ar` (or `?locale=ar`) to select the content language.
